<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    paused: Boolean,
});

// Two reusable layers: the current frame remains visible until the next is playing.
const slots = ref([null, null]);
const activeSlot = ref(0);
const visibleSlot = ref(0);
const videos = [];
const failedUrls = new Set();
let sequence = 0;
let advancing = false;
let imageTimer;
let watchdog;
let watchedSlot;
let retryTimer;
let disposed = false;

const isLive = (index, slot) => !disposed && slots.value[index] === slot;
const clearWatchdog = () => {
    clearTimeout(watchdog);
    watchedSlot = null;
};

const releaseVideo = (video) => {
    if (!video) return;
    video.pause();
    video.removeAttribute('src');
    video.load();
};

const assign = (index, itemIndex) => {
    releaseVideo(videos[index]);
    slots.value[index] = {
        item: props.items[itemIndex], itemIndex, token: ++sequence, ready: false,
    };
};

const nextIndex = (after) => {
    for (let offset = 1; offset <= props.items.length; offset++) {
        const index = (after + offset) % props.items.length;
        if (props.items[index]?.url && !failedUrls.has(props.items[index].url)) return index;
    }
    return -1;
};

const retryLater = () => {
    if (retryTimer) return;
    retryTimer = setTimeout(() => {
        retryTimer = null;
        if (!disposed) reset();
    }, 15000);
};

const armWatchdog = (index, slot) => {
    if (props.paused || watchedSlot === slot) return;
    clearWatchdog();
    watchedSlot = slot;
    watchdog = setTimeout(() => {
        clearWatchdog();
        if (isLive(index, slot) && !props.paused) fail(index, slot);
    }, 45000);
};

const prepareNext = () => {
    if (props.items.length < 2 || advancing) return;
    const current = slots.value[activeSlot.value];
    if (!current) return;
    const index = nextIndex(current.itemIndex);
    if (index < 0) return;
    const target = 1 - activeSlot.value;
    if (slots.value[target]?.itemIndex !== index) assign(target, index);
};

const play = async (index, slot) => {
    await nextTick();
    if (!isLive(index, slot) || index !== activeSlot.value || props.paused) return;
    if (slot.item.type === 'image') {
        if (slot.ready) present(index, slot);
        return;
    }
    const video = videos[index];
    if (!video) return;
    try {
        await video.play();
    } catch (error) {
        // Pausing or replacing a source can cancel an outstanding play request.
        if (error.name !== 'AbortError' && isLive(index, slot) && !props.paused) {
            console.warn('Display video could not play:', error);
            fail(index, slot);
        }
    }
};

const activateNext = () => {
    const target = 1 - activeSlot.value;
    const slot = slots.value[target];
    if (!advancing || !slot?.ready || props.paused) return;
    clearWatchdog();
    videos[activeSlot.value]?.pause();
    activeSlot.value = target;
    advancing = false;
    armWatchdog(target, slot);
    play(target, slot);
};

const advance = () => {
    if (props.paused || advancing) return;
    clearTimeout(imageTimer);
    const current = slots.value[activeSlot.value];
    if (!current) return;
    const index = nextIndex(current.itemIndex);
    if (index < 0) {
        retryLater();
        return;
    }
    const target = 1 - activeSlot.value;
    if (slots.value[target]?.itemIndex !== index) assign(target, index);
    advancing = true;
    armWatchdog(target, slots.value[target]);
    activateNext();
};

const maybePreload = (index, slot, video) => {
    if (!isLive(index, slot) || index !== activeSlot.value || props.paused || !slot.ready) return;
    // Give the current stream a buffer before starting the next network request.
    for (let range = 0; range < video.buffered.length; range++) {
        if (video.buffered.start(range) <= video.currentTime &&
            video.buffered.end(range) - video.currentTime >= Math.min(5, video.duration - video.currentTime)) {
            prepareNext();
            break;
        }
    }
};

const present = (index, slot) => {
    if (!isLive(index, slot) || index !== activeSlot.value || props.paused) return;
    clearWatchdog();
    visibleSlot.value = index;
    if (slot.item.type === 'image') {
        clearTimeout(imageTimer);
        if (props.items.length > 1) {
            const duration = Number(slot.item.duration);
            imageTimer = setTimeout(advance, (duration > 0 ? duration : 10) * 1000);
        }
        prepareNext();
    } else if (videos[index]) {
        maybePreload(index, slot, videos[index]);
    }
};

const ready = (index, slot) => {
    if (!isLive(index, slot)) return;
    slot.ready = true;
    if (index === activeSlot.value) {
        if (slot.item.type === 'image') present(index, slot);
    } else {
        activateNext();
    }
};

const fail = (index, slot) => {
    if (!isLive(index, slot)) return;
    console.warn('Skipping unavailable display media:', slot.item.url);
    failedUrls.add(slot.item.url);
    slot.ready = false;
    if (index === activeSlot.value) {
        clearWatchdog();
        advance();
    } else {
        const replacement = nextIndex(slot.itemIndex);
        if (replacement < 0) {
            retryLater();
            return;
        }
        assign(index, replacement);
        if (advancing) armWatchdog(index, slots.value[index]);
    }
};

const reset = () => {
    clearTimeout(imageTimer);
    clearTimeout(retryTimer);
    retryTimer = null;
    clearWatchdog();
    videos.forEach(releaseVideo);
    failedUrls.clear();
    advancing = false;
    activeSlot.value = 0;
    visibleSlot.value = 0;
    slots.value = [null, null];
    const first = nextIndex(-1);
    if (first < 0) return;
    assign(0, first);
    const slot = slots.value[0];
    armWatchdog(0, slot);
    play(0, slot);
};

watch(() => props.items, reset, { immediate: true });
watch(() => props.paused, (paused) => {
    clearTimeout(imageTimer);
    clearWatchdog();
    if (paused) {
        videos.forEach(video => video?.pause());
    } else if (advancing) {
        const target = 1 - activeSlot.value;
        armWatchdog(target, slots.value[target]);
        activateNext();
    } else {
        const slot = slots.value[activeSlot.value];
        if (slot) {
            armWatchdog(activeSlot.value, slot);
            play(activeSlot.value, slot);
        }
    }
});

onBeforeUnmount(() => {
    disposed = true;
    clearTimeout(imageTimer);
    clearTimeout(retryTimer);
    clearWatchdog();
    videos.forEach(releaseVideo);
});
</script>

<template>
    <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
        <template v-for="(slot, index) in slots" :key="index">
            <div v-if="slot" :key="slot.token" class="media-layer" :style="{ opacity: index === visibleSlot ? 1 : 0 }">
                <video
                    v-if="slot.item.type === 'video'"
                    :ref="element => videos[index] = element"
                    :src="slot.item.url"
                    :loop="items.length === 1"
                    muted
                    playsinline
                    webkit-playsinline
                    preload="auto"
                    class="h-full w-full object-cover"
                    @loadeddata="ready(index, slot)"
                    @canplay="ready(index, slot)"
                    @playing="present(index, slot)"
                    @progress="maybePreload(index, slot, $event.target)"
                    @timeupdate="maybePreload(index, slot, $event.target)"
                    @waiting="index === activeSlot && armWatchdog(index, slot)"
                    @ended="index === activeSlot && advance()"
                    @error="fail(index, slot)"
                />
                <img
                    v-else
                    :src="slot.item.url"
                    alt=""
                    class="h-full w-full object-cover"
                    @load="ready(index, slot)"
                    @error="fail(index, slot)"
                />
            </div>
        </template>
    </div>
</template>

<style scoped>
.media-layer {
    position: absolute;
    inset: 0;
    contain: layout paint;
    pointer-events: none;
}
</style>
