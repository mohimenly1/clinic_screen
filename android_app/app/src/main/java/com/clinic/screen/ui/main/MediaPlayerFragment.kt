package com.clinic.screen.ui.main

import android.graphics.Bitmap
import android.graphics.Matrix
import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.view.animation.AccelerateDecelerateInterpolator
import android.view.animation.DecelerateInterpolator
import android.widget.ImageView
import androidx.exifinterface.media.ExifInterface
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.lifecycleScope
import androidx.media3.common.MediaItem as ExoMediaItem
import androidx.media3.common.Player
import androidx.media3.exoplayer.ExoPlayer
import com.bumptech.glide.Glide
import com.bumptech.glide.request.target.CustomTarget
import com.bumptech.glide.request.transition.Transition
import com.clinic.screen.data.model.BroadcastItem
import com.clinic.screen.data.model.MediaItem
import com.clinic.screen.databinding.FragmentMediaPlayerBinding
import com.clinic.screen.ui.main.viewmodel.MainViewModel
import com.clinic.screen.util.ApiConfig
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.delay
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import java.io.ByteArrayInputStream
import java.io.InputStream
import java.net.URL

/**
 * Fragment to handle media playback (images and videos)
 */
class MediaPlayerFragment : Fragment() {

    private var _binding: FragmentMediaPlayerBinding? = null
    private val binding get() = _binding!!

    private val viewModel: MainViewModel by activityViewModels()

    // Track current image URL to detect changes and apply animations
    private var currentImageUrl: String? = null
    private var currentMediaType: String? = null

    // ExoPlayer instances for video playback
    private var exoPlayer: ExoPlayer? = null
    private var broadcastExoPlayer: ExoPlayer? = null

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentMediaPlayerBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)

        setupObservers()
        setupImageViewScaleType()
    }

    private fun setupImageViewScaleType() {
        // Observe screen orientation to update image scale type
        lifecycleScope.launch {
            viewModel.screenOrientation.collect { orientation ->
                // In both portrait and landscape: use centerCrop to fill screen completely
                // No rotation needed - images should display naturally
                // Glide will handle EXIF orientation automatically
                binding.imageView.rotation = 0f
                binding.imageView.scaleType = ImageView.ScaleType.CENTER_CROP

                // Also apply to broadcast image view
                binding.broadcastImageView.rotation = 0f
                binding.broadcastImageView.scaleType = ImageView.ScaleType.CENTER_CROP

                Log.d(TAG, "Image scale type updated for orientation: $orientation (CENTER_CROP, no rotation)")
            }
        }
    }

    private fun setupObservers() {
        // Observe media items list - for initial load
        lifecycleScope.launch {
            viewModel.mediaItems.collect { items ->
                if (items.isNotEmpty()) {
                    val mediaItem = viewModel.getCurrentMediaItem()
                    mediaItem?.let {
                        displayMedia(it)

                        // Auto-advance to next item for images
                        if (it.type == "image") {
                            val duration = (it.duration ?: 10) * 1000L // Convert to milliseconds
                            lifecycleScope.launch {
                                delay(duration)
                                if (isResumed) {
                                    viewModel.nextMediaItem()
                                }
                            }
                        }
                    }
                }
            }
        }

        // Observe current media index changes - for navigation between items
        lifecycleScope.launch {
            viewModel.currentMediaIndex.collect {
                val mediaItem = viewModel.getCurrentMediaItem()
                mediaItem?.let {
                    displayMedia(it)

                    // Auto-advance to next item for images
                    if (it.type == "image") {
                        val duration = (it.duration ?: 10) * 1000L // Convert to milliseconds
                        lifecycleScope.launch {
                            delay(duration)
                            if (isResumed) {
                                viewModel.nextMediaItem()
                            }
                        }
                    }
                }
            }
        }

        // Observe broadcast media
        lifecycleScope.launch {
            viewModel.broadcastItem.collect { broadcastItem ->
                broadcastItem?.let {
                    // Show broadcast media (overlay on top of regular content)
                    displayBroadcastMedia(it)
                } ?: run {
                    // Hide broadcast overlay
                    hideBroadcastMedia()
                }
            }
        }
    }

    private fun displayMedia(mediaItem: MediaItem) {
        // Build full URL from relative path
        val fullUrl = if (mediaItem.url.startsWith("http")) {
            mediaItem.url
        } else {
            // Extract base URL from ApiConfig (remove /api/v1/)
            val baseUrl = ApiConfig.BASE_URL.replace("/api/v1/", "")
            "$baseUrl${mediaItem.url}"
        }

        Log.d(TAG, "Displaying media: $fullUrl, type: ${mediaItem.type}")

        val isMediaTypeChange = currentMediaType != mediaItem.type
        
        when (mediaItem.type) {
            "image" -> {
                // Smooth transition: fade out current media if changing type, otherwise cross-fade images
                if (isMediaTypeChange) {
                    fadeOutCurrentMedia {
                        showImageView()
                        loadImageWithAnimation(fullUrl)
                    }
                } else {
                    // Cross-fade between images
                    loadImageWithAnimation(fullUrl)
                }

                currentImageUrl = fullUrl
                currentMediaType = "image"
            }
            "video" -> {
                // Smooth transition: fade out current media, then fade in video
                if (isMediaTypeChange) {
                    fadeOutCurrentMedia {
                        showVideoView()
                        // Stop any currently playing video before starting new one
                        releaseExoPlayer()
                        // Setup ExoPlayer for video playback with fade in
                        playVideoWithAnimation(fullUrl)
                    }
                } else {
                    // Direct video switch (shouldn't happen often, but handle it)
                    releaseExoPlayer()
                    playVideoWithAnimation(fullUrl)
                }

                currentImageUrl = null
                currentMediaType = "video"
            }
            else -> {
                Log.w(TAG, "Unknown media type: ${mediaItem.type}")
            }
        }
    }

    private fun displayBroadcastMedia(broadcastItem: BroadcastItem) {
        // Build full URL from relative path
        val fullUrl = if (broadcastItem.url.startsWith("http")) {
            broadcastItem.url
        } else {
            val baseUrl = ApiConfig.BASE_URL.replace("/api/v1/", "")
            "$baseUrl${broadcastItem.url}"
        }

        Log.d(TAG, "Displaying broadcast media: $fullUrl, type: ${broadcastItem.type}")
        binding.broadcastOverlay.visibility = View.VISIBLE

        when (broadcastItem.type) {
            "image" -> {
                binding.broadcastImageView.visibility = View.VISIBLE
                binding.broadcastVideoView.visibility = View.GONE

                // Load broadcast image with EXIF orientation handling and smooth animation
                binding.broadcastImageView.alpha = 0f
                Glide.with(this)
                    .asBitmap()
                    .load(fullUrl)
                    .into(object : CustomTarget<Bitmap>() {
                        override fun onResourceReady(
                            resource: Bitmap,
                            transition: Transition<in Bitmap>?
                        ) {
                            lifecycleScope.launch {
                                val correctedBitmap = applyExifOrientation(fullUrl, resource)
                                binding.broadcastImageView.setImageBitmap(correctedBitmap)
                                
                                // Smooth fade in animation
                                binding.broadcastImageView.animate()
                                    .alpha(1f)
                                    .setDuration(500)
                                    .setInterpolator(DecelerateInterpolator())
                                    .start()
                            }
                        }

                        override fun onLoadCleared(placeholder: android.graphics.drawable.Drawable?) {
                            // Cleanup if needed
                        }
                    })
            }
            "video" -> {
                binding.broadcastImageView.visibility = View.GONE
                binding.broadcastVideoView.visibility = View.VISIBLE
                binding.broadcastVideoView.alpha = 0f

                // Setup ExoPlayer for broadcast video with fade in animation
                playBroadcastVideo(fullUrl)
                binding.broadcastVideoView.animate()
                    .alpha(1f)
                    .setDuration(500)
                    .setInterpolator(DecelerateInterpolator())
                    .start()
            }
        }
    }

    private fun hideBroadcastMedia() {
        Log.d(TAG, "Hiding broadcast media")
        binding.broadcastOverlay.visibility = View.GONE
        // Stop and release broadcast video player
        releaseBroadcastExoPlayer()
    }

    /**
     * Read EXIF orientation from image URL and apply rotation to bitmap
     * This ensures images display correctly matching web browser behavior
     *
     * IMPORTANT: ExifInterface requires an InputStream that supports mark/reset
     * We need to read the entire stream into memory first, then create ExifInterface from it
     */
    private suspend fun applyExifOrientation(imageUrl: String, bitmap: Bitmap): Bitmap = withContext(Dispatchers.IO) {
        return@withContext try {
            val url = URL(imageUrl)
            val connection = url.openConnection()
            connection.connect()
            val inputStream = connection.getInputStream()

            // Read entire stream into ByteArray to support ExifInterface mark/reset requirement
            val bytes = inputStream.readBytes()
            inputStream.close()

            // Create ExifInterface from ByteArrayInputStream
            // Explicitly specify InputStream type to resolve overload ambiguity
            val inputStreamForExif: InputStream = ByteArrayInputStream(bytes)
            val exif = ExifInterface(inputStreamForExif)
            val orientation = exif.getAttributeInt(
                ExifInterface.TAG_ORIENTATION,
                ExifInterface.ORIENTATION_NORMAL
            )

            Log.d(TAG, "EXIF Orientation for $imageUrl: $orientation")

            val matrix = Matrix()
            when (orientation) {
                ExifInterface.ORIENTATION_ROTATE_90 -> {
                    matrix.postRotate(90f)
                    Log.d(TAG, "Applying 90 degree rotation")
                }
                ExifInterface.ORIENTATION_ROTATE_180 -> {
                    matrix.postRotate(180f)
                    Log.d(TAG, "Applying 180 degree rotation")
                }
                ExifInterface.ORIENTATION_ROTATE_270 -> {
                    matrix.postRotate(270f)
                    Log.d(TAG, "Applying 270 degree rotation")
                }
                ExifInterface.ORIENTATION_FLIP_HORIZONTAL -> {
                    matrix.postScale(-1f, 1f, bitmap.width / 2f, bitmap.height / 2f)
                    Log.d(TAG, "Applying horizontal flip")
                }
                ExifInterface.ORIENTATION_FLIP_VERTICAL -> {
                    matrix.postScale(1f, -1f, bitmap.width / 2f, bitmap.height / 2f)
                    Log.d(TAG, "Applying vertical flip")
                }
                else -> {
                    Log.d(TAG, "No rotation needed (ORIENTATION_NORMAL=$orientation or unsupported)")
                    return@withContext bitmap
                }
            }

            val rotatedBitmap = Bitmap.createBitmap(
                bitmap, 0, 0, bitmap.width, bitmap.height, matrix, true
            )

            // Don't recycle the original bitmap here - let Glide handle it
            rotatedBitmap
        } catch (e: Exception) {
            Log.e(TAG, "Failed to read EXIF orientation from $imageUrl: ${e.message}", e)
            e.printStackTrace()
            bitmap // Return original bitmap if EXIF reading fails
        }
    }

    /**
     * Load image with smooth cross-fade animation
     */
    private fun loadImageWithAnimation(imageUrl: String) {
        binding.imageView.alpha = 0f
        
        // Load image using Glide with explicit EXIF orientation handling
        // We handle animation manually using View.animate() for better control
        Glide.with(this)
            .asBitmap()
            .load(imageUrl)
            .into(object : CustomTarget<Bitmap>() {
                override fun onResourceReady(
                    resource: Bitmap,
                    transition: Transition<in Bitmap>?
                ) {
                    // Read EXIF orientation from the URL and apply rotation
                    lifecycleScope.launch {
                        val correctedBitmap = applyExifOrientation(imageUrl, resource)
                        binding.imageView.setImageBitmap(correctedBitmap)
                        
                        // Smooth fade in animation
                        binding.imageView.animate()
                            .alpha(1f)
                            .setDuration(500)
                            .setInterpolator(DecelerateInterpolator())
                            .start()
                        
                        Log.d(TAG, "Image loaded with EXIF correction and animation: $imageUrl")
                    }
                }

                override fun onLoadCleared(placeholder: android.graphics.drawable.Drawable?) {
                    // Cleanup if needed
                }
            })
    }

    /**
     * Show image view with smooth animation
     */
    private fun showImageView() {
        binding.videoView.visibility = View.GONE
        binding.broadcastOverlay.visibility = View.GONE
        binding.imageView.visibility = View.VISIBLE
        binding.imageView.alpha = 0f
    }

    /**
     * Show video view with smooth animation
     */
    private fun showVideoView() {
        binding.imageView.visibility = View.GONE
        binding.broadcastOverlay.visibility = View.GONE
        binding.videoView.visibility = View.VISIBLE
        binding.videoView.alpha = 0f
    }

    /**
     * Fade out current media (image or video) and execute callback when done
     */
    private fun fadeOutCurrentMedia(onComplete: () -> Unit) {
        val currentView = when {
            binding.imageView.visibility == View.VISIBLE -> binding.imageView
            binding.videoView.visibility == View.VISIBLE -> binding.videoView
            else -> null
        }

        currentView?.let { view ->
            view.animate()
                .alpha(0f)
                .setDuration(300)
                .setInterpolator(AccelerateDecelerateInterpolator())
                .withEndAction {
                    onComplete()
                }
                .start()
        } ?: onComplete()
    }

    /**
     * Play video using ExoPlayer with smooth fade-in animation
     */
    private fun playVideoWithAnimation(videoUrl: String) {
        playVideo(videoUrl)
        
        // Smooth fade in animation for video
        binding.videoView.alpha = 0f
        binding.videoView.animate()
            .alpha(1f)
            .setDuration(500)
            .setInterpolator(DecelerateInterpolator())
            .start()
    }

    /**
     * Play video using ExoPlayer
     */
    private fun playVideo(videoUrl: String) {
        try {
            // Release existing player if any
            releaseExoPlayer()

            // Check if there are multiple media items
            val mediaItemsCount = viewModel.mediaItems.value.size
            val shouldLoop = mediaItemsCount == 1

            // Create new ExoPlayer instance
            exoPlayer = ExoPlayer.Builder(requireContext()).build().apply {
                // Prepare media item
                val mediaItem = ExoMediaItem.fromUri(videoUrl)
                setMediaItem(mediaItem)
                prepare()

                // Auto-play
                playWhenReady = true

                // If only one video, loop it. Otherwise, advance to next item when finished
                if (shouldLoop) {
                    repeatMode = Player.REPEAT_MODE_ONE
                    Log.d(TAG, "Playing single video with loop: $videoUrl")
                } else {
                    repeatMode = Player.REPEAT_MODE_OFF
                    // Add listener to advance to next item when video ends
                    addListener(object : Player.Listener {
                        override fun onPlaybackStateChanged(playbackState: Int) {
                            if (playbackState == Player.STATE_ENDED) {
                                // Video finished, advance to next media item
                                Log.d(TAG, "Video finished, advancing to next item")
                                lifecycleScope.launch {
                                    if (isResumed) {
                                        viewModel.nextMediaItem()
                                    }
                                }
                            }
                        }
                    })
                    Log.d(TAG, "Playing video in playlist (no loop): $videoUrl")
                }

                // Set player to PlayerView
                binding.videoView.player = this
            }

            Log.d(TAG, "Playing video: $videoUrl (media items count: $mediaItemsCount)")
        } catch (e: Exception) {
            Log.e(TAG, "Error playing video: $videoUrl", e)
        }
    }

    /**
     * Play broadcast video using ExoPlayer
     */
    private fun playBroadcastVideo(videoUrl: String) {
        try {
            // Release existing broadcast player if any
            releaseBroadcastExoPlayer()

            // Create new ExoPlayer instance
            broadcastExoPlayer = ExoPlayer.Builder(requireContext()).build().apply {
                // Prepare media item
                val mediaItem = ExoMediaItem.fromUri(videoUrl)
                setMediaItem(mediaItem)
                prepare()

                // Auto-play and loop
                playWhenReady = true
                repeatMode = Player.REPEAT_MODE_ONE

                // Set player to PlayerView
                binding.broadcastVideoView.player = this
            }

            Log.d(TAG, "Playing broadcast video: $videoUrl")
        } catch (e: Exception) {
            Log.e(TAG, "Error playing broadcast video: $videoUrl", e)
        }
    }

    /**
     * Release main ExoPlayer instance
     */
    private fun releaseExoPlayer() {
        exoPlayer?.let { player ->
            player.release()
            exoPlayer = null
            binding.videoView.player = null
            Log.d(TAG, "Released ExoPlayer")
        }
    }

    /**
     * Release broadcast ExoPlayer instance
     */
    private fun releaseBroadcastExoPlayer() {
        broadcastExoPlayer?.let { player ->
            player.release()
            broadcastExoPlayer = null
            binding.broadcastVideoView.player = null
            Log.d(TAG, "Released broadcast ExoPlayer")
        }
    }

    companion object {
        private const val TAG = "MediaPlayerFragment"
    }

    override fun onDestroyView() {
        super.onDestroyView()
        // Release ExoPlayer instances
        releaseExoPlayer()
        releaseBroadcastExoPlayer()
        _binding = null
    }
}

