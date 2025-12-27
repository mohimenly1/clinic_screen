package com.clinic.screen.service

import android.util.Log
import com.clinic.screen.data.model.BroadcastItem
import com.clinic.screen.data.model.BroadcastMediaEvent
import com.clinic.screen.data.model.MediaItem
import com.clinic.screen.data.model.ScreenContentUpdatedEvent
import com.clinic.screen.util.ApiConfig
import com.google.gson.Gson
import com.pusher.client.Pusher
import com.pusher.client.PusherOptions
import com.pusher.client.channel.Channel
import com.pusher.client.channel.PusherEvent
import com.pusher.client.connection.ConnectionEventListener
import com.pusher.client.connection.ConnectionState
import com.pusher.client.connection.ConnectionStateChange
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow

/**
 * Service to handle Pusher real-time events
 */
class PusherService {
    
    private var pusher: Pusher? = null
    private var screenChannel: Channel? = null
    private var generalChannel: Channel? = null
    
    // State flows for real-time updates
    private val _screenContentUpdated = MutableStateFlow<List<MediaItem>?>(null)
    val screenContentUpdated: StateFlow<List<MediaItem>?> = _screenContentUpdated.asStateFlow()
    
    private val _broadcastMedia = MutableStateFlow<BroadcastItem?>(null)
    val broadcastMedia: StateFlow<BroadcastItem?> = _broadcastMedia.asStateFlow()
    
    private val _stopBroadcast = MutableStateFlow<Boolean>(false)
    val stopBroadcast: StateFlow<Boolean> = _stopBroadcast.asStateFlow()
    
    private val gson = Gson()
    
    /**
     * Initialize Pusher connection
     */
    fun initialize() {
        val options = PusherOptions()
        options.setCluster(ApiConfig.PUSHER_CLUSTER)
        options.isEncrypted = true
        
        pusher = Pusher(ApiConfig.PUSHER_KEY, options)
        
        pusher?.connection?.bind(ConnectionState.CONNECTED, object : ConnectionEventListener {
            override fun onConnectionStateChange(change: ConnectionStateChange) {
                Log.d(TAG, "Pusher Connected: ${change.currentState}")
            }
            
            override fun onError(message: String, code: String?, e: Exception?) {
                Log.e(TAG, "Pusher Connection Error: $message")
            }
        })
        
        pusher?.connection?.bind(ConnectionState.DISCONNECTED, object : ConnectionEventListener {
            override fun onConnectionStateChange(change: ConnectionStateChange) {
                Log.d(TAG, "Pusher Disconnected: ${change.currentState}")
            }
            
            override fun onError(message: String, code: String?, e: Exception?) {
                Log.e(TAG, "Pusher Disconnection Error: $message")
            }
        })
        
        pusher?.connect()
    }
    
    /**
     * Subscribe to screen-specific channel
     * @param screenCode The screen code (e.g., "SCREEN001")
     */
    fun subscribeToScreenChannel(screenCode: String) {
        val channelName = "displays.$screenCode"
        // Unsubscribe from previous channel if exists
        screenChannel?.let {
            try {
                pusher?.unsubscribe(channelName)
            } catch (e: Exception) {
                Log.w(TAG, "Error unsubscribing from previous channel: ${e.message}")
            }
        }
        screenChannel = pusher?.subscribe(channelName)
        
        screenChannel?.bind("ScreenContentUpdated") { event: PusherEvent ->
            try {
                // Parse the event data - Laravel sends mediaItems array directly
                val jsonObject = gson.fromJson(event.data, Map::class.java) as Map<*, *>
                val mediaItemsList = jsonObject["mediaItems"] as? List<Map<*, *>>
                
                val mediaItems = mediaItemsList?.mapNotNull { itemMap ->
                    try {
                        val itemJson = gson.toJson(itemMap)
                        gson.fromJson(itemJson, MediaItem::class.java)
                    } catch (e: Exception) {
                        Log.e(TAG, "Error parsing media item: ${e.message}")
                        null
                    }
                } ?: emptyList()
                
                _screenContentUpdated.value = mediaItems
                Log.d(TAG, "ScreenContentUpdated received: ${mediaItems.size} items")
            } catch (e: Exception) {
                Log.e(TAG, "Error parsing ScreenContentUpdated: ${e.message}", e)
            }
        }
        
        Log.d(TAG, "Subscribed to channel: $channelName")
    }
    
    /**
     * Subscribe to general broadcast channel
     */
    fun subscribeToGeneralChannel() {
        val channelName = "displays"
        // Unsubscribe from previous channel if exists
        generalChannel?.let {
            try {
                pusher?.unsubscribe(channelName)
            } catch (e: Exception) {
                Log.w(TAG, "Error unsubscribing from previous channel: ${e.message}")
            }
        }
        generalChannel = pusher?.subscribe(channelName)
        
        // Listen for BroadcastMedia event
        generalChannel?.bind("BroadcastMedia") { event: PusherEvent ->
            try {
                // Parse the event data - Laravel sends mediaItem object directly
                val jsonObject = gson.fromJson(event.data, Map::class.java) as Map<*, *>
                val mediaItemMap = jsonObject["mediaItem"] as? Map<*, *>
                
                mediaItemMap?.let {
                    val itemJson = gson.toJson(it)
                    val broadcastItem = BroadcastItem(
                        id = null,
                        url = it["url"] as? String ?: "",
                        type = it["type"] as? String ?: "",
                        fileName = null
                    )
                    _broadcastMedia.value = broadcastItem
                    Log.d(TAG, "BroadcastMedia received: ${broadcastItem.url}")
                }
            } catch (e: Exception) {
                Log.e(TAG, "Error parsing BroadcastMedia: ${e.message}", e)
            }
        }
        
        // Listen for StopBroadcast event
        generalChannel?.bind("StopBroadcast") { event: PusherEvent ->
            _stopBroadcast.value = true
            _broadcastMedia.value = null
            Log.d(TAG, "StopBroadcast received")
            // Reset after a short delay to allow observers to process
            CoroutineScope(Dispatchers.Main).launch {
                delay(100)
                _stopBroadcast.value = false
            }
        }
        
        Log.d(TAG, "Subscribed to general channel: displays")
    }
    
    /**
     * Disconnect Pusher
     */
    fun disconnect() {
        // Unsubscribe from channels (this automatically unbinds all events)
        screenChannel?.let {
            try {
                pusher?.unsubscribe("displays.${it.name.split(".").getOrNull(1) ?: ""}")
            } catch (e: Exception) {
                Log.w(TAG, "Error unsubscribing from screen channel: ${e.message}")
            }
        }
        generalChannel?.let {
            try {
                pusher?.unsubscribe("displays")
            } catch (e: Exception) {
                Log.w(TAG, "Error unsubscribing from general channel: ${e.message}")
            }
        }
        pusher?.disconnect()
        pusher = null
        screenChannel = null
        generalChannel = null
        Log.d(TAG, "Pusher disconnected")
    }
    
    companion object {
        private const val TAG = "PusherService"
    }
}

