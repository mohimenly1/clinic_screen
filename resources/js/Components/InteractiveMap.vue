<template>
    <div class="interactive-map-container" ref="mapContainer">
        <!-- Header with floor selector -->
        <div class="map-header">
            <div class="floor-selector">
                <button
                    v-for="floor in floors"
                    :key="floor.id"
                    @click="selectFloor(floor)"
                    :class="['floor-btn', { active: currentFloor?.id === floor.id }]"
                >
                    {{ floor.name }}
                </button>
            </div>
            <button @click="$emit('close')" class="close-btn">
                ✕
            </button>
        </div>

        <!-- Map Canvas -->
        <div class="map-canvas-wrapper" ref="mapWrapper">
            <div
                class="map-canvas"
                :style="mapStyle"
                @wheel="handleWheel"
                @mousedown="handleMouseDown"
                @mousemove="handleMouseMove"
                @mouseup="handleMouseUp"
                @mouseleave="handleMouseUp"
            >
                <!-- Background Map Image -->
                <img
                    v-if="currentFloor?.map_image_url"
                    :src="currentFloor.map_image_url"
                    class="map-background"
                    alt="Floor Map"
                    @load="onImageLoad"
                />

                <!-- SVG Overlay for Interactive Elements -->
                <svg
                    class="map-overlay"
                    :viewBox="`0 0 ${svgWidth} ${svgHeight}`"
                    ref="svgElement"
                >
                    <!-- Room Markers -->
                    <g v-for="room in currentRooms" :key="room.id">
                        <!-- Room Circle with Glow Effect -->
                        <defs>
                            <filter :id="`glow-${room.id}`" x="-50%" y="-50%" width="200%" height="200%">
                                <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                                <feMerge>
                                    <feMergeNode in="coloredBlur"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>

                        <!-- Room Marker -->
                        <circle
                            :cx="getRoomX(room)"
                            :cy="getRoomY(room)"
                            :r="markerRadius"
                            :fill="room.color || '#673AB7'"
                            :stroke="room.color || '#673AB7'"
                            stroke-width="2"
                            :filter="`url(#glow-${room.id})`"
                            :class="['room-marker', { 'selected': selectedRoom?.id === room.id, 'hovered': hoveredRoom?.id === room.id }]"
                            @click="selectRoom(room)"
                            @mouseenter="hoveredRoom = room"
                            @mouseleave="hoveredRoom = null"
                        >
                            <animate
                                attributeName="r"
                                :values="`${markerRadius};${markerRadius + 2};${markerRadius}`"
                                dur="2s"
                                repeatCount="indefinite"
                                v-if="selectedRoom?.id === room.id"
                            />
                        </circle>

                        <!-- Room Icon -->
                        <text
                            :x="getRoomX(room)"
                            :y="getRoomY(room) + 5"
                            text-anchor="middle"
                            class="room-icon"
                            font-size="20"
                        >
                            {{ room.icon || '📍' }}
                        </text>

                        <!-- Room Label -->
                        <g
                            :transform="`translate(${getRoomX(room)}, ${getRoomY(room) - markerRadius - 15})`"
                            class="room-label-group"
                        >
                            <rect
                                :width="labelWidth"
                                :height="20"
                                x="-50"
                                :fill="room.color || '#673AB7'"
                                :opacity="0.9"
                                rx="10"
                                class="room-label-bg"
                            />
                            <text
                                x="0"
                                y="14"
                                text-anchor="middle"
                                class="room-label-text"
                                fill="white"
                                font-weight="bold"
                            >
                                {{ room.name }}
                            </text>
                        </g>

                        <!-- Connection Path (if selected) -->
                        <path
                            v-if="selectedRoom && navigationPath && selectedRoom.id === navigationPath.from_room.id"
                            :d="pathPath"
                            stroke="#673AB7"
                            stroke-width="3"
                            fill="none"
                            stroke-dasharray="10,5"
                            class="navigation-path"
                            opacity="0.7"
                        >
                            <animate
                                attributeName="stroke-dashoffset"
                                from="0"
                                to="15"
                                dur="1s"
                                repeatCount="indefinite"
                            />
                        </path>
                    </g>

                    <!-- You Are Here Marker (if start room selected) -->
                    <g v-if="startRoom">
                        <circle
                            :cx="getRoomX(startRoom)"
                            :cy="getRoomY(startRoom)"
                            r="8"
                            fill="#FF5722"
                            class="you-are-here"
                        >
                            <animate
                                attributeName="r"
                                values="8;12;8"
                                dur="1.5s"
                                repeatCount="indefinite"
                            />
                        </circle>
                        <text
                            :x="getRoomX(startRoom)"
                            :y="getRoomY(startRoom) - 25"
                            text-anchor="middle"
                            class="you-are-here-label"
                            fill="#FF5722"
                            font-weight="bold"
                        >
                            أنت هنا
                        </text>
                    </g>
                </svg>
            </div>

            <!-- Navigation Instructions Panel -->
            <div v-if="navigationPath" class="navigation-panel">
                <div class="navigation-header">
                    <h3>🚶 طريق الوصول</h3>
                </div>
                <div class="navigation-content">
                    <div class="path-info">
                        <div class="from-to">
                            <span class="from">من: {{ navigationPath.from_room.name }}</span>
                            <span class="arrow">→</span>
                            <span class="to">إلى: {{ navigationPath.to_room.name }}</span>
                        </div>
                        <div class="directions">
                            {{ navigationPath.directions }}
                        </div>
                        <div v-if="navigationPath.estimated_time_seconds" class="time-estimate">
                            ⏱ الوقت المتوقع: {{ formatTime(navigationPath.estimated_time_seconds) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Room Details Panel -->
            <div v-if="selectedRoom && !navigationPath" class="room-details-panel">
                <div class="room-details-header">
                    <span class="room-icon-large">{{ selectedRoom.icon || '📍' }}</span>
                    <h3>{{ selectedRoom.name }}</h3>
                </div>
                <div class="room-details-content">
                    <p v-if="selectedRoom.description" class="mb-3">{{ selectedRoom.description }}</p>
                    <p class="room-type mb-3">النوع: {{ getRoomTypeLabel(selectedRoom.room_type) }}</p>
                    <p v-if="selectedRoom.room_number" class="text-sm text-gray-600 mb-3">رقم الغرفة: {{ selectedRoom.room_number }}</p>
                    <button
                        v-if="!startRoom"
                        @click="setStartRoom(selectedRoom)"
                        class="btn-start-navigation"
                    >
                        🚶 ابدأ من هنا
                    </button>
                    <button
                        v-if="startRoom && startRoom.id !== selectedRoom.id"
                        @click="getNavigationPath(startRoom, selectedRoom)"
                        class="btn-navigate"
                    >
                        📍 كيف أصل إلى هنا؟
                    </button>
                </div>
            </div>
        </div>

        <!-- Zoom Controls -->
        <div class="zoom-controls">
            <button @click="zoomIn" class="zoom-btn">+</button>
            <button @click="zoomOut" class="zoom-btn">−</button>
            <button @click="resetZoom" class="zoom-btn">⌂</button>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    screenCode: String,
    floors: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

// State
const floors = ref([]);
const currentFloor = ref(null);
const currentRooms = ref([]);
const selectedRoom = ref(null);
const hoveredRoom = ref(null);
const startRoom = ref(null);
const navigationPath = ref(null);
const mapContainer = ref(null);
const mapWrapper = ref(null);
const svgElement = ref(null);

// Map interaction state
const scale = ref(1);
const translateX = ref(0);
const translateY = ref(0);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });

// SVG dimensions (will be set based on image)
const svgWidth = ref(800);
const svgHeight = ref(600);

// Constants
const markerRadius = 15;
const labelWidth = 100;
const minScale = 0.5;
const maxScale = 3;

// Computed
const mapStyle = computed(() => ({
    transform: `translate(${translateX.value}px, ${translateY.value}px) scale(${scale.value})`,
    transformOrigin: 'center center',
}));

// Methods
const loadFloors = async () => {
    if (props.floors && props.floors.length > 0) {
        floors.value = props.floors;
        selectFloor(floors.value[0]);
        return;
    }
    
    try {
        const response = await axios.get('/api/v1/navigation/floors');
        if (response.data.success) {
            floors.value = response.data.data;
            if (floors.value.length > 0) {
                selectFloor(floors.value[0]);
            }
        }
    } catch (error) {
        console.error('Error loading floors:', error);
    }
};

const selectFloor = async (floor) => {
    console.log('Selecting floor:', floor);
    currentFloor.value = floor;
    selectedRoom.value = null;
    navigationPath.value = null;
    
    // Use rooms from floor data if available
    if (floor.rooms && floor.rooms.length > 0) {
        currentRooms.value = floor.rooms.map(room => ({
            ...room,
            map_x: room.map_x ?? 50,
            map_y: room.map_y ?? 50,
            color: room.color || getRoomTypeColor(room.room_type),
            icon: room.icon || getRoomTypeIcon(room.room_type),
        }));
        console.log('Loaded rooms from floor data:', currentRooms.value);
    } else {
        console.log('No rooms in floor data, trying API...');
        // Fallback: try to fetch from API
        try {
            const response = await axios.get(`/api/v1/navigation/floors/${floor.id}`);
            if (response.data.success && response.data.data.rooms) {
                currentRooms.value = response.data.data.rooms.map(room => ({
                    ...room,
                    map_x: room.map_x ?? 50,
                    map_y: room.map_y ?? 50,
                    color: room.color || getRoomTypeColor(room.room_type),
                    icon: room.icon || getRoomTypeIcon(room.room_type),
                }));
                console.log('Loaded rooms from API:', currentRooms.value);
            } else {
                currentRooms.value = [];
                console.log('No rooms found in API response');
            }
        } catch (error) {
            console.error('Error loading floor rooms:', error);
            currentRooms.value = [];
        }
    }
};

const getRoomTypeColor = (type) => {
    const colors = {
        clinic: '#673AB7',
        pharmacy: '#4CAF50',
        lab: '#2196F3',
        reception: '#FF9800',
        restroom: '#9E9E9E',
        elevator: '#F44336',
        stairs: '#795548',
    };
    return colors[type] || '#607D8B';
};

const getRoomTypeIcon = (type) => {
    const icons = {
        clinic: '🏥',
        pharmacy: '💊',
        lab: '🔬',
        reception: '📋',
        restroom: '🚻',
        elevator: '🛗',
        stairs: '🪜',
    };
    return icons[type] || '📍';
};

const selectRoom = async (room) => {
    selectedRoom.value = room;
    navigationPath.value = null;
};

const setStartRoom = (room) => {
    startRoom.value = room;
};

const getNavigationPath = async (from, to) => {
    try {
        const response = await axios.get('/api/v1/navigation/path', {
            params: {
                from_room_id: from.id,
                to_room_id: to.id,
            },
        });
        if (response.data.success) {
            navigationPath.value = {
                ...response.data.data,
                from_room: from,
                to_room: to,
            };
        }
    } catch (error) {
        console.error('Error getting navigation path:', error);
    }
};

const formatTime = (seconds) => {
    if (seconds < 60) return `${seconds} ثانية`;
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return secs > 0 ? `${minutes} دقيقة ${secs} ثانية` : `${minutes} دقيقة`;
};

const getRoomTypeLabel = (type) => {
    const labels = {
        clinic: 'عيادة',
        pharmacy: 'صيدلية',
        lab: 'مختبر',
        reception: 'استقبال',
        restroom: 'دورة مياه',
        elevator: 'مصعد',
        stairs: 'سلالم',
        other: 'أخرى',
    };
    return labels[type] || type || 'غرفة';
};

// Map interaction handlers
const handleWheel = (e) => {
    e.preventDefault();
    const delta = e.deltaY > 0 ? 0.9 : 1.1;
    const newScale = Math.max(minScale, Math.min(maxScale, scale.value * delta));
    scale.value = newScale;
};

const handleMouseDown = (e) => {
    if (e.button === 0) { // Left mouse button
        isDragging.value = true;
        dragStart.value = { x: e.clientX - translateX.value, y: e.clientY - translateY.value };
    }
};

const handleMouseMove = (e) => {
    if (isDragging.value) {
        translateX.value = e.clientX - dragStart.value.x;
        translateY.value = e.clientY - dragStart.value.y;
    }
};

const handleMouseUp = () => {
    isDragging.value = false;
};

const zoomIn = () => {
    scale.value = Math.min(maxScale, scale.value * 1.2);
};

const zoomOut = () => {
    scale.value = Math.max(minScale, scale.value * 0.8);
};

const resetZoom = () => {
    scale.value = 1;
    translateX.value = 0;
    translateY.value = 0;
};

const onImageLoad = (e) => {
    const img = e.target;
    svgWidth.value = img.naturalWidth || img.width || 800;
    svgHeight.value = img.naturalHeight || img.height || 600;
    // Update SVG viewBox when image loads
    if (svgElement.value) {
        svgElement.value.setAttribute('viewBox', `0 0 ${svgWidth.value} ${svgHeight.value}`);
    }
};

const onImageError = () => {
    console.error('Failed to load map image');
    svgWidth.value = 800;
    svgHeight.value = 600;
};

// Helper functions to get room coordinates
const getRoomX = (room) => {
    if (room.map_x !== null && room.map_x !== undefined) {
        return (room.map_x / 100) * svgWidth.value;
    }
    return svgWidth.value / 2;
};

const getRoomY = (room) => {
    if (room.map_y !== null && room.map_y !== undefined) {
        return (room.map_y / 100) * svgHeight.value;
    }
    return svgHeight.value / 2;
};

// Computed path for navigation line
const pathPath = computed(() => {
    if (!navigationPath.value || !navigationPath.value.from_room || !navigationPath.value.to_room) {
        return '';
    }
    const from = navigationPath.value.from_room;
    const to = navigationPath.value.to_room;
    return `M ${getRoomX(from)} ${getRoomY(from)} L ${getRoomX(to)} ${getRoomY(to)}`;
});

// Lifecycle
onMounted(() => {
    loadFloors();
});

// Watch for props changes
watch(() => props.floors, (newFloors) => {
    if (newFloors && newFloors.length > 0) {
        floors.value = newFloors;
        if (floors.value.length > 0 && !currentFloor.value) {
            selectFloor(floors.value[0]);
        }
    }
}, { immediate: true });
</script>

<style scoped>
.interactive-map-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    font-family: 'Cairo', 'Tajawal', sans-serif;
}

.map-header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.floor-selector {
    display: flex;
    gap: 0.5rem;
}

.floor-btn {
    padding: 0.5rem 1.5rem;
    border: 2px solid #673AB7;
    background: white;
    color: #673AB7;
    border-radius: 25px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.floor-btn:hover {
    background: #673AB7;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(103, 58, 183, 0.4);
}

.floor-btn.active {
    background: #673AB7;
    color: white;
    box-shadow: 0 4px 12px rgba(103, 58, 183, 0.4);
}

.close-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f44336;
    color: white;
    border: none;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.close-btn:hover {
    background: #d32f2f;
    transform: scale(1.1) rotate(90deg);
}

.map-canvas-wrapper {
    flex: 1;
    position: relative;
    overflow: hidden;
    background: #f5f5f5;
}

.map-canvas {
    width: 100%;
    height: 100%;
    position: relative;
    cursor: grab;
    transition: transform 0.1s ease-out;
}

.map-canvas:active {
    cursor: grabbing;
}

.map-background {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 100%;
    max-height: 100%;
    opacity: 0.9;
    filter: drop-shadow(0 10px 30px rgba(0, 0, 0, 0.3));
}

.map-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.room-marker {
    pointer-events: all;
    cursor: pointer;
    transition: all 0.3s ease;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

.room-marker:hover {
    transform: scale(1.2);
    filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.4));
}

.room-marker.selected {
    stroke-width: 4;
    animation: pulse 2s infinite;
}

.room-icon {
    pointer-events: none;
    font-size: 20px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.room-label-group {
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.room-marker:hover + .room-icon + .room-label-group,
.room-marker.selected ~ .room-label-group {
    opacity: 1;
}

.room-label-bg {
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

.room-label-text {
    font-size: 12px;
    font-weight: bold;
}

.navigation-path {
    pointer-events: none;
    stroke-linecap: round;
    stroke-linejoin: round;
    filter: drop-shadow(0 2px 4px rgba(103, 58, 183, 0.5));
}

.you-are-here {
    pointer-events: none;
    filter: drop-shadow(0 4px 8px rgba(255, 87, 34, 0.6));
}

.you-are-here-label {
    font-size: 14px;
    font-weight: bold;
    filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.8));
}

.navigation-panel,
.room-details-panel {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    max-width: 500px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        transform: translateY(100px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.navigation-header,
.room-details-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #673AB7;
}

.navigation-header h3,
.room-details-header h3 {
    margin: 0;
    color: #673AB7;
    font-size: 1.5rem;
}

.room-icon-large {
    font-size: 2rem;
}

.from-to {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    font-weight: bold;
}

.from {
    color: #FF5722;
}

.to {
    color: #4CAF50;
}

.arrow {
    color: #673AB7;
    font-size: 1.5rem;
}

.directions {
    color: #333;
    line-height: 1.8;
    margin-bottom: 1rem;
}

.time-estimate {
    color: #673AB7;
    font-weight: bold;
    padding: 0.5rem;
    background: rgba(103, 58, 183, 0.1);
    border-radius: 10px;
}

.btn-start-navigation,
.btn-navigate {
    width: 100%;
    padding: 1rem;
    background: linear-gradient(135deg, #673AB7 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(103, 58, 183, 0.4);
}

.btn-start-navigation:hover,
.btn-navigate:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(103, 58, 183, 0.6);
}

.zoom-controls {
    position: absolute;
    top: 100px;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.zoom-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 2px solid #673AB7;
    color: #673AB7;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.zoom-btn:hover {
    background: #673AB7;
    color: white;
    transform: scale(1.1);
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}
</style>

