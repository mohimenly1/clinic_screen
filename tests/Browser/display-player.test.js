import { createApp, h, nextTick, ref } from 'vue';
import Player from '../../resources/js/Components/DisplayMediaPlayer.vue';

// Drive media events deterministically; actual decoding is checked separately on-device.
const timers = new Map();
let timerId = 0;
window.setTimeout = (callback, delay) => {
    const id = ++timerId;
    timers.set(id, { callback, delay });
    return id;
};
window.clearTimeout = id => timers.delete(id);
HTMLMediaElement.prototype.play = function () {
    this.playCalls = (this.playCalls || 0) + 1;
    this.testPaused = false;
    return Promise.resolve();
};
HTMLMediaElement.prototype.pause = function () { this.testPaused = true; };
HTMLMediaElement.prototype.load = function () {};
Object.defineProperty(HTMLMediaElement.prototype, 'buffered', {
    get() { return { length: 1, start: () => 0, end: () => this.bufferEnd || 0 }; },
});
Object.defineProperty(HTMLMediaElement.prototype, 'duration', { get: () => 10 });

const assert = (condition, message) => { if (!condition) throw new Error(message); };
const flush = async () => { await nextTick(); await nextTick(); };
const fire = async (el, event) => { el.dispatchEvent(new Event(event)); await flush(); };
const advanceTime = async (delay) => {
    for (const [id, timer] of [...timers]) {
        if (timer.delay === delay && timers.delete(id)) timer.callback();
    }
    await flush();
};
const item = (id, type = 'video') => ({ id, type, url: `/fixture-${id}.${type === 'video' ? 'mp4' : 'jpg'}`, duration: 2 });
const visible = el => el.parentElement.style.opacity === '1';
let mounted;
const mount = async (items = [item('a'), item('b')]) => {
    const state = ref({ items, paused: false });
    const host = document.createElement('div');
    document.body.append(host);
    const app = createApp({ render: () => h(Player, state.value) });
    app.mount(host);
    mounted = { app, host };
    await flush();
    return { state, host, videos: () => [...host.querySelectorAll('video')] };
};
const start = async (video) => {
    await fire(video, 'loadeddata');
    video.bufferEnd = 10;
    await fire(video, 'playing');
};
const results = [];
const run = async (name, test) => {
    try {
        await test();
        results.push({ name, passed: true });
    } catch (error) {
        results.push({ name, passed: false, error: error.message });
    } finally {
        mounted?.app.unmount();
        mounted?.host.remove();
        mounted = null;
        if (timers.size) {
            results.push({ name: `${name}: timer cleanup`, passed: false });
            timers.clear();
        }
    }
};

await run('first video has priority before the next preload', async () => {
    const { videos } = await mount();
    const first = videos()[0];
    assert(videos().length === 1 && first.playCalls === 1, 'Only the first video should start');
    await fire(first, 'loadeddata');
    await fire(first, 'playing');
    assert(videos().length === 1, 'Do not compete with an empty current buffer');
    first.bufferEnd = 6;
    await fire(first, 'progress');
    assert(videos().length === 2 && !videos()[1].playCalls, 'Preload only one paused video');
});

await run('keep the last frame until the prepared video is playing', async () => {
    const { videos } = await mount();
    const first = videos()[0];
    await start(first);
    const second = videos()[1];
    await fire(first, 'ended');
    assert(visible(first) && !visible(second), 'Keep current frame while loading');
    await fire(second, 'canplay');
    assert(second.playCalls === 1 && visible(first), 'Keep current frame until playing');
    await fire(second, 'playing');
    assert(visible(second) && !visible(first), 'Switch on playing');
    await fire(second, 'ended');
    await fire(first, 'playing');
    assert(videos()[0] === first && first.playCalls === 2 && visible(first), 'Reuse buffered video on wraparound');
});

await run('inquiry pauses playback and resumes without reloading', async () => {
    const { state, videos } = await mount();
    const first = videos()[0];
    await start(first);
    state.value.paused = true;
    await flush();
    assert(first.testPaused, 'Pause the active video');
    await fire(videos()[1], 'canplay');
    assert(!videos()[1].playCalls, 'Do not start prepared video behind inquiry');
    state.value.paused = false;
    await flush();
    assert(videos()[0] === first && first.playCalls === 2, 'Resume the existing video');
});

await run('image duration starts after loading and waits for the next frame', async () => {
    const { host, videos } = await mount([item('image', 'image'), item('video')]);
    assert(![...timers.values()].some(timer => timer.delay === 2000), 'No countdown before image load');
    const image = host.querySelector('img');
    await fire(image, 'load');
    assert([...timers.values()].some(timer => timer.delay === 2000), 'Start countdown on load');
    await advanceTime(2000);
    assert(visible(image), 'Keep image until video starts');
    await fire(videos()[0], 'canplay');
    await fire(videos()[0], 'playing');
    assert(visible(videos()[0]) && !visible(image), 'Show next video');
});

await run('missing first file is skipped before metadata loads', async () => {
    const { videos } = await mount();
    await fire(videos()[0], 'error');
    const next = videos()[1];
    await fire(next, 'canplay');
    await fire(next, 'playing');
    assert(visible(next) && next.playCalls === 1, 'Recover from early relative-URL errors');
});

await run('broadcast replaces the playlist and loops a single video', async () => {
    const { state, videos } = await mount();
    await start(videos()[0]);
    state.value.items = [item('broadcast')];
    await flush();
    assert(videos().length === 1 && videos()[0].loop, 'Broadcast uses one looping video');
    assert(videos()[0].getAttribute('src') === '/fixture-broadcast.mp4', 'Play broadcast immediately');
    state.value.items = [item('a'), item('b')];
    await flush();
    assert(videos().length === 1 && !videos()[0].loop, 'Return to regular playlist');
});

await run('failed playlist retries with a cooldown and cleans timers', async () => {
    const { videos } = await mount();
    await fire(videos()[0], 'error');
    await fire(videos()[1], 'error');
    assert([...timers.values()].filter(timer => timer.delay === 15000).length === 1, 'Bound retries when every file fails');
    await advanceTime(15000);
    assert(videos().length === 1 && videos()[0].playCalls === 1, 'Retry from first file after cooldown');
});

await run('a stalled video eventually advances without repeated load calls', async () => {
    const { videos } = await mount();
    const first = videos()[0];
    await start(first);
    const second = videos()[1];
    await fire(second, 'canplay');
    await fire(first, 'waiting');
    await advanceTime(45000);
    await fire(second, 'playing');
    assert(visible(second), 'Advance to prepared video after prolonged buffering');
});

document.body.innerHTML = '';
const output = document.createElement('pre');
output.id = 'test-results';
output.textContent = JSON.stringify(results);
document.body.append(output);
