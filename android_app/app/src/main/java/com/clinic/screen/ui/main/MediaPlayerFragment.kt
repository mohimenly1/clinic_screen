package com.clinic.screen.ui.main

import android.graphics.Bitmap
import android.graphics.Matrix
import android.os.Bundle
import android.util.Log
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import androidx.exifinterface.media.ExifInterface
import androidx.fragment.app.Fragment
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.lifecycleScope
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

        when (mediaItem.type) {
            "image" -> {
                binding.imageView.visibility = View.VISIBLE
                binding.videoView.visibility = View.GONE
                binding.broadcastOverlay.visibility = View.GONE

                // Load image using Glide with explicit EXIF orientation handling
                // Glide doesn't always handle EXIF orientation correctly for URLs,
                // so we read EXIF data from the URL and apply rotation manually
                Glide.with(this)
                    .asBitmap()
                    .load(fullUrl)
                    .into(object : CustomTarget<Bitmap>() {
                        override fun onResourceReady(
                            resource: Bitmap,
                            transition: Transition<in Bitmap>?
                        ) {
                            // Read EXIF orientation from the URL and apply rotation
                            lifecycleScope.launch {
                                val correctedBitmap = applyExifOrientation(fullUrl, resource)
                                binding.imageView.setImageBitmap(correctedBitmap)
                                Log.d(TAG, "Image loaded with EXIF correction: $fullUrl")
                            }
                        }

                        override fun onLoadCleared(placeholder: android.graphics.drawable.Drawable?) {
                            // Cleanup if needed
                        }
                    })

                // Update current image URL
                currentImageUrl = fullUrl
            }
            "video" -> {
                binding.imageView.visibility = View.GONE
                binding.videoView.visibility = View.VISIBLE
                binding.broadcastOverlay.visibility = View.GONE
                currentImageUrl = null

                // TODO: Setup ExoPlayer for video playback
                Log.w(TAG, "Video playback not yet implemented")
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

                // Load broadcast image with EXIF orientation handling
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

                // TODO: Setup ExoPlayer for broadcast video
                Log.w(TAG, "Broadcast video playback not yet implemented")
            }
        }
    }

    private fun hideBroadcastMedia() {
        Log.d(TAG, "Hiding broadcast media")
        binding.broadcastOverlay.visibility = View.GONE
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

    companion object {
        private const val TAG = "MediaPlayerFragment"
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}

