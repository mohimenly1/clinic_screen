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
import com.clinic.screen.ui.inquiry.InquiryFragment
import com.clinic.screen.util.VoiceRecognitionHelper
import com.clinic.screen.util.VoiceCommandProcessor
import android.widget.Toast
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private val viewModel: MainViewModel by viewModels()
    
    var voiceRecognitionHelper: VoiceRecognitionHelper? = null
    private lateinit var pusherService: PusherService
    private var screenCode: String = ""
    private var isListening = false
    private var listeningAnimator: android.animation.ObjectAnimator? = null

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
        
        // Set default orientation to portrait until API response loads
        // This will be updated by applyScreenOrientation() when screen data is received
        requestedOrientation = ActivityInfo.SCREEN_ORIENTATION_PORTRAIT

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
        // Only set default portrait on first call (onCreate), not on resume
        // This prevents overriding the orientation set from API
    }

    private fun applyScreenOrientation(orientation: String) {
        android.util.Log.d("MainActivity", "applyScreenOrientation called with: '$orientation'")

        val requestedOrientationValue = when (orientation.lowercase()) {
            "portrait" -> ActivityInfo.SCREEN_ORIENTATION_PORTRAIT
            "landscape" -> ActivityInfo.SCREEN_ORIENTATION_LANDSCAPE
            else -> {
                android.util.Log.w("MainActivity", "Unknown orientation: '$orientation', defaulting to portrait")
                ActivityInfo.SCREEN_ORIENTATION_PORTRAIT // Default
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

        // Apply screen orientation from API (portrait or landscape)
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
            android.util.Log.d("MainActivity", "Inquiry button clicked")
            showInquiryFragment()
        }

        // Setup voice recognition
        setupVoiceRecognition()

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

    override fun onWindowFocusChanged(hasFocus: Boolean) {
        super.onWindowFocusChanged(hasFocus)
        if (hasFocus) {
            // Ensure full screen mode is maintained (but don't reset orientation)
            setupFullScreen()
            // Note: Don't reset requestedOrientation here to preserve API setting
        }
    }

    private fun showInquiryFragment() {
        // Check if inquiry fragment is already showing
        val existingFragment = supportFragmentManager.findFragmentByTag("InquiryFragment")
        if (existingFragment != null) {
            return
        }

        // Create and show inquiry fragment
        val inquiryFragment = InquiryFragment()
        supportFragmentManager.beginTransaction()
            .add(android.R.id.content, inquiryFragment, "InquiryFragment")
            .commit()
    }
    
    private fun setupVoiceRecognition() {
        try {
            // Initialize voice recognition helper directly with Activity
            voiceRecognitionHelper = VoiceRecognitionHelper(
                activity = this,
                onResult = { transcript: String ->
                    handleVoiceCommand(transcript)
                },
                onError = { errorMessage: String ->
                    android.util.Log.e("MainActivity", "Voice recognition error: $errorMessage")
                    // Only hide if it's a critical error, otherwise keep listening
                    // Critical errors will be handled separately
                    Toast.makeText(this, errorMessage, Toast.LENGTH_SHORT).show()
                }
            )
        } catch (e: Exception) {
            android.util.Log.e("MainActivity", "Error setting up voice recognition", e)
            Toast.makeText(this, "خطأ في تهيئة التعرف الصوتي: ${e.message ?: "خطأ غير معروف"}", Toast.LENGTH_SHORT).show()
        }
        
        binding.voiceButton.setOnClickListener {
            startVoiceRecognition()
        }
    }
    
    private fun startVoiceRecognition() {
        try {
            // Toggle listening state
            if (isListening) {
                stopVoiceRecognition()
                return
            }
            
            if (voiceRecognitionHelper == null) {
                // Try to initialize again if null
                setupVoiceRecognition()
            }
            
            if (voiceRecognitionHelper == null) {
                Toast.makeText(this, "لم يتم تهيئة التعرف الصوتي. يرجى المحاولة مرة أخرى.", Toast.LENGTH_SHORT).show()
                return
            }
            
            // Start listening
            isListening = true
            
            // Show listening status with visual feedback
            showListeningState()
            
            // Start listening continuously
            voiceRecognitionHelper?.startListening()
        } catch (e: Exception) {
            android.util.Log.e("MainActivity", "Error starting voice recognition", e)
            hideListeningState()
            Toast.makeText(this, "حدث خطأ في بدء التعرف الصوتي: ${e.message ?: "خطأ غير معروف"}", Toast.LENGTH_SHORT).show()
        }
    }
    
    private fun stopVoiceRecognition() {
        try {
            isListening = false
            voiceRecognitionHelper?.stopListening()
            hideListeningState()
        } catch (e: Exception) {
            android.util.Log.e("MainActivity", "Error stopping voice recognition", e)
        }
    }
    
    private fun showListeningState() {
        // Show status text
        binding.voiceStatusText.text = "المساعد الذكي يستمع الآن..."
        binding.voiceStatusText.visibility = View.VISIBLE
        
        // Change button color to green (active state)
        binding.voiceButton.setColorFilter(0xFF4CAF50.toInt(), android.graphics.PorterDuff.Mode.SRC_IN)
        
        // Start pulsating animation
        startListeningAnimation()
    }
    
    private fun hideListeningState() {
        // Hide status text
        binding.voiceStatusText.text = ""
        binding.voiceStatusText.visibility = View.GONE
        
        // Reset button appearance to white
        binding.voiceButton.setColorFilter(0xFFFFFFFF.toInt(), android.graphics.PorterDuff.Mode.SRC_IN)
        
        // Stop animation
        stopListeningAnimation()
    }
    
    private fun startListeningAnimation() {
        stopListeningAnimation() // Stop any existing animation
        
        // Create scale animation (pulsating effect)
        listeningAnimator = android.animation.ObjectAnimator.ofFloat(
            binding.voiceButton,
            "scaleX",
            1.0f, 1.15f, 1.0f
        ).apply {
            duration = 1000 // 1 second per cycle
            repeatCount = android.animation.ObjectAnimator.INFINITE
            repeatMode = android.animation.ObjectAnimator.REVERSE
            interpolator = android.view.animation.AccelerateDecelerateInterpolator()
        }
        
        // Also animate scaleY
        val scaleYAnimator = android.animation.ObjectAnimator.ofFloat(
            binding.voiceButton,
            "scaleY",
            1.0f, 1.15f, 1.0f
        ).apply {
            duration = 1000
            repeatCount = android.animation.ObjectAnimator.INFINITE
            repeatMode = android.animation.ObjectAnimator.REVERSE
            interpolator = android.view.animation.AccelerateDecelerateInterpolator()
        }
        
        // Start both animations together
        val animatorSet = android.animation.AnimatorSet()
        animatorSet.playTogether(listeningAnimator, scaleYAnimator)
        animatorSet.start()
    }
    
    private fun stopListeningAnimation() {
        listeningAnimator?.cancel()
        listeningAnimator = null
        binding.voiceButton.animate().cancel()
        binding.voiceButton.scaleX = 1.0f
        binding.voiceButton.scaleY = 1.0f
    }
    
    private fun handleVoiceCommand(transcript: String) {
        android.util.Log.d("MainActivity", "Processing voice command: $transcript")
        
        // Don't hide listening state - keep it active for continuous listening
        // The user can stop it manually by pressing the button again
        
        val departments = viewModel.departments.value
        if (departments.isEmpty()) {
            Toast.makeText(this, "جاري تحميل البيانات...", Toast.LENGTH_SHORT).show()
            viewModel.loadDepartments()
            return
        }
        
        val result = VoiceCommandProcessor.processVoiceCommand(transcript, departments)
        
        when (result.type) {
            VoiceCommandProcessor.VoiceCommandType.DEPARTMENT -> {
                result.department?.let { department ->
                    // Always open inquiry (it will replace current fragment if exists)
                    showInquiryFragment()
                    // Wait a bit for fragment to be ready, then select department
                    binding.root.postDelayed({
                        val fragment = supportFragmentManager.findFragmentByTag("InquiryFragment") as? InquiryFragment
                        fragment?.let {
                            it.selectDepartment(department)
                            Toast.makeText(
                                this,
                                "تم فتح قسم ${department.name}",
                                Toast.LENGTH_SHORT
                            ).show()
                        } ?: run {
                            // Retry if fragment not ready yet
                            binding.root.postDelayed({
                                val retryFragment = supportFragmentManager.findFragmentByTag("InquiryFragment") as? InquiryFragment
                                retryFragment?.selectDepartment(department)
                            }, 300)
                        }
                    }, 500)
                }
            }
            VoiceCommandProcessor.VoiceCommandType.DOCTOR -> {
                result.doctor?.let { doctor ->
                    result.department?.let { department ->
                        // Always open inquiry (it will replace current fragment if exists)
                        showInquiryFragment()
                        // Wait a bit for fragment to be ready, then select department and doctor
                        binding.root.postDelayed({
                            val fragment = supportFragmentManager.findFragmentByTag("InquiryFragment") as? InquiryFragment
                            fragment?.let {
                                it.selectDepartment(department)
                                binding.root.postDelayed({
                                    it.selectDoctor(doctor)
                                    Toast.makeText(
                                        this,
                                        "تم فتح مواعيد الدكتور ${doctor.name}",
                                        Toast.LENGTH_SHORT
                                    ).show()
                                }, 300)
                            } ?: run {
                                // Retry if fragment not ready yet
                                binding.root.postDelayed({
                                    val retryFragment = supportFragmentManager.findFragmentByTag("InquiryFragment") as? InquiryFragment
                                    retryFragment?.selectDepartment(department)
                                    binding.root.postDelayed({
                                        retryFragment?.selectDoctor(doctor)
                                    }, 300)
                                }, 300)
                            }
                        }, 500)
                    }
                }
            }
            VoiceCommandProcessor.VoiceCommandType.UNKNOWN -> {
                result.errorMessage?.let { message ->
                    Toast.makeText(this, message, Toast.LENGTH_SHORT).show()
                }
            }
        }
    }
    
    override fun onRequestPermissionsResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults)
        voiceRecognitionHelper?.handlePermissionResult(requestCode, permissions, grantResults)
    }
    
    override fun onDestroy() {
        super.onDestroy()
        voiceRecognitionHelper?.destroy()
        voiceRecognitionHelper = null
        pusherService.disconnect()
        viewModel.cleanup()
    }
    
    override fun onPause() {
        super.onPause()
        try {
            stopVoiceRecognition()
        } catch (e: Exception) {
            android.util.Log.e("MainActivity", "Error stopping voice recognition", e)
        }
    }
}

