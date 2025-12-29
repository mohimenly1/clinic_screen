<template>
    <div class="room-list-container">
        <!-- Header -->
        <div class="room-list-header">
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

        <!-- Rooms Grid -->
        <div class="rooms-grid">
            <div
                v-for="room in currentRooms"
                :key="room.id"
                class="room-card"
                @click="openARViewer(room)"
            >
                <div class="room-icon-large">
                    {{ room.icon || '📍' }}
                </div>
                <h3 class="room-name">{{ room.name }}</h3>
                <p v-if="room.room_number" class="room-number">{{ room.room_number }}</p>
                <p v-if="room.description" class="room-description">{{ room.description }}</p>
                <div class="room-images-count" v-if="room.images && room.images.length > 0">
                    📸 {{ room.images.length }} صورة
                </div>
                <div v-else class="no-images">
                    لا توجد صور
                </div>
            </div>
        </div>

        <!-- AR Viewer -->
        <ARViewer
            v-if="selectedRoom"
            :room="selectedRoom"
            :images="selectedRoom.images || []"
            :show="showARViewer"
            @close="closeARViewer"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import ARViewer from './ARViewer.vue';

const props = defineProps({
    floors: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const currentFloor = ref(null);
const selectedRoom = ref(null);
const showARViewer = ref(false);

const currentRooms = computed(() => {
    if (!currentFloor.value) return [];
    return currentFloor.value.rooms || [];
});

const selectFloor = (floor) => {
    currentFloor.value = floor;
};

const openARViewer = (room) => {
    console.log('Opening AR Viewer for room:', room);
    console.log('Room images:', room.images);
    
    if (!room.images || room.images.length === 0) {
        alert('لا توجد صور متاحة لهذه الغرفة');
        return;
    }
    
    // Ensure images have the correct structure
    const images = room.images.map(img => ({
        id: img.id,
        image_url: img.image_url || img.url,
        description: img.description,
        ar_instructions: img.ar_instructions,
        display_order: img.display_order || 0,
    }));
    
    selectedRoom.value = {
        ...room,
        images: images,
    };
    showARViewer.value = true;
};

const closeARViewer = () => {
    showARViewer.value = false;
    selectedRoom.value = null;
};

// Initialize with first floor
watch(() => props.floors, (newFloors) => {
    if (newFloors && newFloors.length > 0 && !currentFloor.value) {
        selectFloor(newFloors[0]);
    }
}, { immediate: true });
</script>

<style scoped>
.room-list-container {
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

.room-list-header {
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

.rooms-grid {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.room-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    padding: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.room-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    background: white;
}

.room-icon-large {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.room-name {
    margin: 0.5rem 0;
    color: #673AB7;
    font-size: 1.5rem;
    font-weight: bold;
}

.room-number {
    color: #666;
    font-size: 0.9rem;
    margin: 0.25rem 0;
}

.room-description {
    color: #555;
    font-size: 0.9rem;
    margin: 0.5rem 0;
    line-height: 1.5;
}

.room-images-count {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #673AB7;
    color: white;
    border-radius: 20px;
    font-weight: bold;
    font-size: 0.9rem;
}

.no-images {
    margin-top: 1rem;
    padding: 0.5rem 1rem;
    background: #ccc;
    color: #666;
    border-radius: 20px;
    font-size: 0.9rem;
}
</style>

