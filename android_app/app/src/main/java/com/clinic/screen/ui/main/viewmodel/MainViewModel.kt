package com.clinic.screen.ui.main.viewmodel

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.clinic.screen.data.api.RetrofitClient
import com.clinic.screen.data.model.*
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import android.util.Log

class MainViewModel : ViewModel() {
    
    private val apiService = RetrofitClient.apiService
    
    // State flows
    private val _screenData = MutableStateFlow<ScreenResponse?>(null)
    val screenData: StateFlow<ScreenResponse?> = _screenData.asStateFlow()
    
    private val _mediaItems = MutableStateFlow<List<MediaItem>>(emptyList())
    val mediaItems: StateFlow<List<MediaItem>> = _mediaItems.asStateFlow()
    
    private val _currentMediaIndex = MutableStateFlow(0)
    val currentMediaIndex: StateFlow<Int> = _currentMediaIndex.asStateFlow()
    
    private val _broadcastItem = MutableStateFlow<BroadcastItem?>(null)
    val broadcastItem: StateFlow<BroadcastItem?> = _broadcastItem.asStateFlow()
    
    private val _isBroadcasting = MutableStateFlow(false)
    val isBroadcasting: StateFlow<Boolean> = _isBroadcasting.asStateFlow()
    
    private val _error = MutableStateFlow<String?>(null)
    val error: StateFlow<String?> = _error.asStateFlow()
    
    private val _backgroundAudioUrl = MutableStateFlow<String?>(null)
    val backgroundAudioUrl: StateFlow<String?> = _backgroundAudioUrl.asStateFlow()
    
    private val _screenOrientation = MutableStateFlow<String>("landscape")
    val screenOrientation: StateFlow<String> = _screenOrientation.asStateFlow()
    
    private val _screenResolution = MutableStateFlow<String?>(null)
    val screenResolution: StateFlow<String?> = _screenResolution.asStateFlow()
    
    /**
     * Set screen orientation
     */
    fun setScreenOrientation(orientation: String) {
        _screenOrientation.value = orientation
    }
    
    /**
     * Set screen resolution
     */
    fun setScreenResolution(resolution: String) {
        _screenResolution.value = resolution
    }
    
    /**
     * Load screen data from API
     */
    fun loadScreenData(screenCode: String) {
        viewModelScope.launch {
            try {
                val response = apiService.getScreen(screenCode)
                if (response.isSuccessful && response.body()?.success == true) {
                    val screenResponse = response.body()!!.data
                    
                    // IMPORTANT: Set orientation FIRST before media items
                    // This ensures that orientation is available when images are loaded
                    _screenOrientation.value = screenResponse.screen.orientation
                    screenResponse.screen.resolution?.let {
                        _screenResolution.value = it
                    }
                    
                    _screenData.value = screenResponse
                    _mediaItems.value = screenResponse.mediaItems
                    _backgroundAudioUrl.value = screenResponse.backgroundAudioUrl
                    
                    // Check if there's an active broadcast
                    screenResponse.broadcastItem?.let {
                        _broadcastItem.value = it
                        _isBroadcasting.value = true
                    }
                    
                    _currentMediaIndex.value = 0
                    _error.value = null
                } else {
                    _error.value = "Failed to load screen data: ${response.message()}"
                }
            } catch (e: Exception) {
                Log.e(TAG, "Error loading screen data", e)
                _error.value = "Error: ${e.message}"
            }
        }
    }
    
    /**
     * Update media items (called from Pusher events)
     */
    fun updateMediaItems(newMediaItems: List<MediaItem>) {
        _mediaItems.value = newMediaItems
        _currentMediaIndex.value = 0
        Log.d(TAG, "Media items updated: ${newMediaItems.size} items")
    }
    
    /**
     * Update media player with new content
     */
    fun updateMediaPlayer(mediaItems: List<MediaItem>) {
        _mediaItems.value = mediaItems
        _currentMediaIndex.value = 0
    }
    
    /**
     * Show broadcast media (called from Pusher events)
     */
    fun showBroadcastMedia(broadcastItem: BroadcastItem) {
        _broadcastItem.value = broadcastItem
        _isBroadcasting.value = true
        Log.d(TAG, "Broadcast media started: ${broadcastItem.url}")
    }
    
    /**
     * Stop broadcast (called from Pusher events)
     */
    fun stopBroadcast() {
        _broadcastItem.value = null
        _isBroadcasting.value = false
        Log.d(TAG, "Broadcast stopped")
    }
    
    /**
     * Set background audio URL
     */
    fun setBackgroundAudio(url: String) {
        _backgroundAudioUrl.value = url
    }
    
    /**
     * Move to next media item
     */
    fun nextMediaItem() {
        val currentIndex = _currentMediaIndex.value
        val itemsCount = _mediaItems.value.size
        if (itemsCount > 0) {
            _currentMediaIndex.value = (currentIndex + 1) % itemsCount
        }
    }
    
    /**
     * Get current media item
     */
    fun getCurrentMediaItem(): MediaItem? {
        val index = _currentMediaIndex.value
        val items = _mediaItems.value
        return if (index < items.size) items[index] else null
    }
    
    /**
     * Cleanup resources
     */
    fun cleanup() {
        // Cleanup any resources if needed
    }
    
    companion object {
        private const val TAG = "MainViewModel"
    }
}

