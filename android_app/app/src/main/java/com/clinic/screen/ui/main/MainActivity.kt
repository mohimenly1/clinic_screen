package com.clinic.screen.ui.main

import android.content.pm.ActivityInfo
import android.os.Bundle
import android.view.View
import android.view.WindowManager
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.clinic.screen.databinding.ActivityMainBinding
import com.clinic.screen.service.PusherService
import com.clinic.screen.ui.main.viewmodel.MainViewModel
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private val viewModel: MainViewModel by viewModels()
    private lateinit var pusherService: PusherService
    private var screenCode: String = ""

    companion object {
        const val EXTRA_SCREEN_CODE = "SCREEN_CODE"
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // Get screen code from intent
        screenCode = intent.getStringExtra(EXTRA_SCREEN_CODE) ?:
                    intent.data?.getQueryParameter("code") ?: ""

        if (screenCode.isEmpty()) {
            // No screen code provided, go to setup
            startActivity(android.content.Intent(this, com.clinic.screen.ui.setup.SetupActivity::class.java))
            finish()
            return
        }

        android.util.Log.d("MainActivity", "Screen Code: $screenCode")

        // Setup Full Screen Kiosk Mode
        setupFullScreen()

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Setup UI components
        setupUI()

        // Add MediaPlayerFragment
        if (savedInstanceState == null) {
            supportFragmentManager.beginTransaction()
                .replace(binding.mediaPlayerContainer.id, MediaPlayerFragment())
                .commitNow()
        }

        // Initialize Pusher
        pusherService = PusherService()
        pusherService.initialize()
        pusherService.subscribeToScreenChannel(screenCode)
        pusherService.subscribeToGeneralChannel()

        // Setup observers
        setupObservers()

        // Load initial screen data
        viewModel.loadScreenData(screenCode)
    }

    private fun setupFullScreen() {
        // Hide system UI
        window.decorView.systemUiVisibility = (
            View.SYSTEM_UI_FLAG_FULLSCREEN
            or View.SYSTEM_UI_FLAG_HIDE_NAVIGATION
            or View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
            or View.SYSTEM_UI_FLAG_LAYOUT_STABLE
            or View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
            or View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
        )

        // Keep screen on
        window.addFlags(WindowManager.LayoutParams.FLAG_KEEP_SCREEN_ON)

        // Orientation will be set dynamically based on API response
        // Default to landscape until screen data is loaded
        requestedOrientation = ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE
    }

    private fun applyScreenOrientation(orientation: String) {
        android.util.Log.d("MainActivity", "applyScreenOrientation called with: '$orientation'")

        val requestedOrientationValue = when (orientation.lowercase()) {
            "portrait" -> ActivityInfo.SCREEN_ORIENTATION_PORTRAIT
            "landscape" -> ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE
            else -> {
                android.util.Log.w("MainActivity", "Unknown orientation: '$orientation', defaulting to landscape")
                ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE // Default
            }
        }

        android.util.Log.d("MainActivity", "Current requestedOrientation: $requestedOrientation, New: $requestedOrientationValue")

        if (requestedOrientation != requestedOrientationValue) {
            requestedOrientation = requestedOrientationValue
            android.util.Log.d("MainActivity", "Orientation changed to: $orientation (${requestedOrientationValue})")
        } else {
            android.util.Log.d("MainActivity", "Orientation already set to: $orientation")
        }
    }

    private fun setupObservers() {
        // Observe screen data
        lifecycleScope.launch {
            viewModel.screenData.collect { screenResponse ->
                screenResponse?.let {
                    // Update UI with screen data
                    updateScreenContent(it)
                }
            }
        }

        // Observe screen content updates from Pusher
        lifecycleScope.launch {
            pusherService.screenContentUpdated.collect { mediaItems ->
                mediaItems?.let {
                    // Update media items in real-time
                    viewModel.updateMediaItems(it)
                }
            }
        }

        // Observe broadcast media from Pusher
        lifecycleScope.launch {
            pusherService.broadcastMedia.collect { broadcastItem ->
                broadcastItem?.let {
                    // Show broadcast media
                    viewModel.showBroadcastMedia(it)
                }
            }
        }

        // Observe stop broadcast from Pusher
        lifecycleScope.launch {
            pusherService.stopBroadcast.collect { stop ->
                if (stop) {
                    // Stop broadcast media
                    viewModel.stopBroadcast()
                }
            }
        }

        // Observe errors
        lifecycleScope.launch {
            viewModel.error.collect { error ->
                error?.let {
                    // Handle error (show message, retry, etc.)
                    handleError(it)
                }
            }
        }
    }

    private fun updateScreenContent(screenResponse: com.clinic.screen.data.model.ScreenResponse) {
        android.util.Log.d("MainActivity", "Updating screen content: ${screenResponse.mediaItems.size} items")
        android.util.Log.d("MainActivity", "Screen data - orientation: '${screenResponse.screen.orientation}', resolution: '${screenResponse.screen.resolution}'")

        // Apply screen orientation from API
        val orientation = screenResponse.screen.orientation
        android.util.Log.d("MainActivity", "Received orientation from API: '$orientation'")
        applyScreenOrientation(orientation)
        viewModel.setScreenOrientation(orientation)

        // Apply screen resolution from API
        screenResponse.screen.resolution?.let { resolution ->
            viewModel.setScreenResolution(resolution)
            android.util.Log.d("MainActivity", "Screen resolution: $resolution")
        }

        // Update media player with new content
        viewModel.updateMediaPlayer(screenResponse.mediaItems)

        // Update background audio if available
        screenResponse.backgroundAudioUrl?.let {
            viewModel.setBackgroundAudio(it)
            android.util.Log.d("MainActivity", "Background audio URL: $it")
        }
    }

    private fun setupUI() {
        // Setup inquiry button click listener
        binding.inquiryButton.setOnClickListener {
            // TODO: Open inquiry dialog/fragment
            android.util.Log.d("MainActivity", "Inquiry button clicked")
        }

        // Setup microphone button click listener
        binding.microphoneButton.setOnClickListener {
            // TODO: Implement voice recognition
            android.util.Log.d("MainActivity", "Microphone button clicked")
        }

        // Sidebar tools are always on the left (matching web implementation)
        // No need to observe orientation changes for sidebar position
        binding.sidebarToolsContainer.visibility = View.VISIBLE

        // Observe background audio to show/hide audio control button
        lifecycleScope.launch {
            viewModel.backgroundAudioUrl.collect { audioUrl ->
                binding.audioControlButton.visibility = if (audioUrl != null) {
                    android.view.View.VISIBLE
                } else {
                    android.view.View.GONE
                }
            }
        }
    }

    private fun updateLayoutForOrientation(orientation: String) {
        // Sidebar tools should always be on the left side (matching web: left-4)
        // This matches the web implementation where tools are always on the left
        // regardless of portrait or landscape orientation
        binding.sidebarToolsContainer.visibility = View.VISIBLE
        android.util.Log.d("MainActivity", "Layout updated for orientation: $orientation (sidebar always on left)")
    }

    private fun handleError(error: String) {
        android.util.Log.e("MainActivity", "Error: $error")
        // Show error message to user
        android.widget.Toast.makeText(this, "Error: $error", android.widget.Toast.LENGTH_LONG).show()
    }

    override fun onResume() {
        super.onResume()
        // Ensure full screen mode is maintained
        setupFullScreen()
    }

    override fun onDestroy() {
        super.onDestroy()
        pusherService.disconnect()
        viewModel.cleanup()
    }

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (hasFocus) {
            setupFullScreen()
        }
    }
}

