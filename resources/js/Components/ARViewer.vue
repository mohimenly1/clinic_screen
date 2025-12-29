<template>
    <div class="ar-viewer-container" :class="{ 'active': show }" ref="container">
        <!-- Full Screen AR View -->
        <div class="ar-viewer" :class="{ 'active': isActive && show }">
            <!-- Close Button -->
            <button @click="close" class="close-ar-btn">
                ✕
            </button>

            <!-- Image Gallery with AR Effect -->
            <div class="ar-image-wrapper" ref="imageWrapper">
                <!-- Loading State -->
                <div v-if="!images || images.length === 0" class="text-white text-center">
                    <p class="text-2xl mb-4">لا توجد صور متاحة</p>
                    <p class="text-lg opacity-75">يرجى إضافة صور لهذه الغرفة من لوحة الإدارة</p>
                </div>
                
                <!-- Images -->
                <transition v-else-if="currentImage" name="fade" mode="out-in">
                    <div
                        :key="currentImage.id"
                        class="ar-image-container"
                        :style="imageStyle"
                        ref="imageContainer"
                        @mousedown="startDrag"
                        @mousemove="onDrag"
                        @mouseup="endDrag"
                        @mouseleave="endDrag"
                    >
                        <!-- Main Image -->
                        <img
                            :src="currentImage.image_url"
                            :alt="currentImage.description || roomName"
                            class="ar-image"
                            ref="arImage"
                            @load="onImageLoad"
                            @error="onImageError"
                        />

                        <!-- Description Overlay -->
                        <div v-if="currentImage.description" class="image-description">
                            <div class="description-content">
                                <h3>{{ roomName }}</h3>
                                <p>{{ currentImage.description }}</p>
                                <p v-if="currentImage.ar_instructions" class="ar-instructions">
                                    📍 {{ currentImage.ar_instructions }}
                                </p>
                            </div>
                        </div>

                        <!-- Navigation Arrows -->
                        <button
                            v-if="canGoPrevious"
                            @click="previousImage"
                            class="nav-arrow nav-arrow-left"
                        >
                            ‹
                        </button>
                        <button
                            v-if="canGoNext"
                            @click="nextImage"
                            class="nav-arrow nav-arrow-right"
                        >
                            ›
                        </button>
                    </div>
                </transition>
            </div>

            <!-- Progress Indicator -->
            <div v-if="images.length > 0" class="progress-indicator">
                <div
                    v-for="(img, index) in images"
                    :key="img.id"
                    class="progress-dot"
                    :class="{ 'active': index === currentIndex }"
                    @click="goToImage(index)"
                ></div>
            </div>

            <!-- Image Counter -->
            <div v-if="images && images.length > 0" class="image-counter">
                {{ currentIndex + 1 }} / {{ images.length }}
            </div>
            
            <!-- Debug Info (remove in production) -->
            <div v-if="show" class="debug-info" style="position: absolute; top: 80px; right: 20px; background: rgba(0,0,0,0.7); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 10002;">
                <div>Images: {{ images?.length || 0 }}</div>
                <div>Current Index: {{ currentIndex }}</div>
                <div>Has Current Image: {{ !!currentImage }}</div>
                <div v-if="currentImage">Image URL: {{ currentImage.image_url?.substring(0, 50) }}...</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';

const props = defineProps({
    room: {
        type: Object,
        required: true,
    },
    images: {
        type: Array,
        default: () => [],
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const container = ref(null);
const imageWrapper = ref(null);
const arImage = ref(null);
const isActive = ref(false);
const currentIndex = ref(0);
const scale = ref(1);
const translateX = ref(0);
const translateY = ref(0);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });

const roomName = computed(() => props.room?.name || '');
const currentImage = computed(() => {
    if (!props.images || props.images.length === 0) {
        return null;
    }
    const img = props.images[currentIndex.value];
    console.log('Current image computed:', img, 'Index:', currentIndex.value);
    return img || null;
});
const canGoPrevious = computed(() => currentIndex.value > 0);
const canGoNext = computed(() => currentIndex.value < (props.images?.length || 0) - 1);

const imageStyle = computed(() => ({
    transform: `translate(${translateX.value}px, ${translateY.value}px) scale(${scale.value})`,
    transformOrigin: 'center center',
}));

const resetTransform = () => {
    scale.value = 1;
    translateX.value = 0;
    translateY.value = 0;
};

watch(() => props.show, (newVal) => {
    console.log('Show prop changed:', newVal);
    if (newVal) {
        isActive.value = true;
        currentIndex.value = 0;
        resetTransform();
        // Lock body scroll
        document.body.style.overflow = 'hidden';
        // Add wheel event listener with passive: false to allow preventDefault
        nextTick(() => {
            if (imageWrapper.value) {
                imageWrapper.value.addEventListener('wheel', handleZoom, { passive: false });
            }
        });
    } else {
        isActive.value = false;
        document.body.style.overflow = '';
        // Remove wheel event listener
        if (imageWrapper.value) {
            imageWrapper.value.removeEventListener('wheel', handleZoom);
        }
    }
}, { immediate: true });

watch(() => props.images, (newImages) => {
    console.log('Images updated:', newImages);
    console.log('Images length:', newImages?.length);
    if (newImages && newImages.length > 0) {
        if (currentIndex.value >= newImages.length) {
            currentIndex.value = 0;
        }
        console.log('First image:', newImages[0]);
    } else {
        console.warn('No images available');
    }
}, { immediate: true, deep: true });

watch(() => currentImage.value, (newImage) => {
    console.log('Current image changed:', newImage);
    if (newImage) {
        console.log('Image URL:', newImage.image_url);
    }
}, { immediate: true });

const onImageLoad = () => {
    console.log('Image loaded successfully');
    resetTransform();
};

const onImageError = (e) => {
    console.error('Image failed to load:', e.target.src);
    // Show error message or fallback
};

const startDrag = (e) => {
    if (e.button !== 0) return; // Only left mouse button
    isDragging.value = true;
    dragStart.value = {
        x: e.clientX - translateX.value,
        y: e.clientY - translateY.value,
    };
};

const onDrag = (e) => {
    if (!isDragging.value) return;
    translateX.value = e.clientX - dragStart.value.x;
    translateY.value = e.clientY - dragStart.value.y;
};

const endDrag = () => {
    isDragging.value = false;
};

const handleZoom = (e) => {
    if (!props.show || !currentImage.value) return;
    e.preventDefault();
    e.stopPropagation();
    const delta = e.deltaY > 0 ? 0.9 : 1.1;
    scale.value = Math.max(0.5, Math.min(3, scale.value * delta));
};

const previousImage = () => {
    if (canGoPrevious.value) {
        currentIndex.value--;
        resetTransform();
    }
};

const nextImage = () => {
    if (canGoNext.value) {
        currentIndex.value++;
        resetTransform();
    }
};

const goToImage = (index) => {
    if (index >= 0 && index < props.images.length) {
        currentIndex.value = index;
        resetTransform();
    }
};

const close = () => {
    emit('close');
};

// Keyboard navigation
const handleKeyPress = (e) => {
    if (!props.show) return;
    
    switch (e.key) {
        case 'ArrowLeft':
            previousImage();
            break;
        case 'ArrowRight':
            nextImage();
            break;
        case 'Escape':
            close();
            break;
        case '+':
        case '=':
            scale.value = Math.min(3, scale.value * 1.1);
            break;
        case '-':
            scale.value = Math.max(0.5, scale.value * 0.9);
            break;
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeyPress);
    console.log('ARViewer mounted, images:', props.images);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyPress);
    document.body.style.overflow = '';
    // Clean up wheel event listener
    if (imageWrapper.value) {
        imageWrapper.value.removeEventListener('wheel', handleZoom);
    }
});
</script>

<style scoped>
.ar-viewer-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    transition: opacity 0.3s ease;
    pointer-events: none;
    opacity: 0;
    visibility: hidden;
}

.ar-viewer-container.active {
    pointer-events: all;
    opacity: 1;
    visibility: visible;
}

.ar-viewer {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    pointer-events: all;
}

.close-ar-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    border: none;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10001;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.close-ar-btn:hover {
    background: #fff;
    transform: scale(1.1) rotate(90deg);
}

.ar-image-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: grab;
    user-select: none;
    position: relative;
}

.ar-image-wrapper:active {
    cursor: grabbing;
}

.ar-image-container {
    position: relative;
    max-width: 100%;
    max-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.1s ease-out;
}

.ar-image {
    max-width: 90vw;
    max-height: 90vh;
    width: auto;
    height: auto;
    object-fit: contain;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
}

.image-description {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
    padding: 2rem;
    color: white;
    font-family: 'Cairo', 'Tajawal', sans-serif;
}

.description-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: bold;
}

.description-content p {
    margin: 0.5rem 0;
    font-size: 1rem;
    line-height: 1.6;
}

.ar-instructions {
    background: rgba(103, 58, 183, 0.8);
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: bold;
    margin-top: 1rem !important;
}

.nav-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    border: none;
    font-size: 36px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-arrow:hover {
    background: #fff;
    transform: translateY(-50%) scale(1.1);
}

.nav-arrow-left {
    left: 20px;
}

.nav-arrow-right {
    right: 20px;
}

.progress-indicator {
    position: absolute;
    bottom: 100px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10001;
}

.progress-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
}

.progress-dot:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.2);
}

.progress-dot.active {
    background: #fff;
    width: 16px;
    height: 16px;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
}

.image-counter {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
    z-index: 10001;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

