package com.clinic.screen.util

import android.Manifest
import android.app.Activity
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Bundle
import android.speech.RecognizerIntent
import android.speech.SpeechRecognizer
import android.util.Log
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat

/**
 * Helper class for handling voice recognition
 */
class VoiceRecognitionHelper(
    private val activity: Activity,
    private val onResult: (String) -> Unit,
    private val onError: (String) -> Unit
) {
    
    private var speechRecognizer: SpeechRecognizer? = null
    private var isListening = false
    
    companion object {
        private const val TAG = "VoiceRecognitionHelper"
        private const val PERMISSION_REQUEST_CODE = 100
    }
    
    init {
        try {
            if (SpeechRecognizer.isRecognitionAvailable(activity)) {
                speechRecognizer = SpeechRecognizer.createSpeechRecognizer(activity)
                setupSpeechRecognizer()
            } else {
                Log.e(TAG, "Speech recognition is not available on this device")
                onError("التعرف الصوتي غير متوفر على هذا الجهاز")
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error initializing speech recognizer", e)
            onError("حدث خطأ في تهيئة التعرف الصوتي: ${e.message ?: "خطأ غير معروف"}")
        }
    }
    
    private fun setupSpeechRecognizer() {
        speechRecognizer?.setRecognitionListener(object : android.speech.RecognitionListener {
            override fun onReadyForSpeech(params: Bundle?) {
                Log.d(TAG, "Ready for speech")
                isListening = true
            }
            
            override fun onBeginningOfSpeech() {
                Log.d(TAG, "Beginning of speech")
            }
            
            override fun onRmsChanged(rmsdB: Float) {
                // Volume level changed
            }
            
            override fun onBufferReceived(buffer: ByteArray?) {
                // Audio buffer received
            }
            
            override fun onEndOfSpeech() {
                Log.d(TAG, "End of speech")
                isListening = false
            }
            
            override fun onError(error: Int) {
                isListening = false
                val errorMessage = when (error) {
                    SpeechRecognizer.ERROR_AUDIO -> "خطأ في الصوت"
                    SpeechRecognizer.ERROR_CLIENT -> "تم إيقاف الاستماع" // Usually happens when stopListening is called
                    SpeechRecognizer.ERROR_INSUFFICIENT_PERMISSIONS -> "لا توجد صلاحيات كافية"
                    SpeechRecognizer.ERROR_NETWORK -> "خطأ في الشبكة"
                    SpeechRecognizer.ERROR_NETWORK_TIMEOUT -> "انتهت مهلة الاتصال بالشبكة"
                    SpeechRecognizer.ERROR_NO_MATCH -> "لم يتم العثور على تطابق"
                    SpeechRecognizer.ERROR_RECOGNIZER_BUSY -> "المعرف الصوتي مشغول"
                    SpeechRecognizer.ERROR_SERVER -> "خطأ في الخادم"
                    SpeechRecognizer.ERROR_SPEECH_TIMEOUT -> "انتهت مهلة الكلام"
                    else -> "حدث خطأ غير معروف: $error"
                }
                Log.e(TAG, "Recognition error: $errorMessage (code: $error)")
                
                // ERROR_CLIENT usually means stopListening was called, so don't restart
                // For NO_MATCH or SPEECH_TIMEOUT, restart listening automatically
                if (error == SpeechRecognizer.ERROR_NO_MATCH || 
                    error == SpeechRecognizer.ERROR_SPEECH_TIMEOUT) {
                    android.os.Handler(android.os.Looper.getMainLooper()).postDelayed({
                        // Only restart if not explicitly stopped and speechRecognizer is still available
                        if (!isListening && speechRecognizer != null) {
                            try {
                                startListening()
                            } catch (e: Exception) {
                                Log.e(TAG, "Error restarting after timeout", e)
                            }
                        }
                    }, 800) // Slightly longer delay to avoid conflicts
                } else if (error == SpeechRecognizer.ERROR_CLIENT) {
                    // ERROR_CLIENT is expected when stopListening is called - don't show error or restart
                    Log.d(TAG, "ERROR_CLIENT received (likely from stopListening) - ignoring")
                } else {
                    // Show error for other errors, but don't restart automatically
                    android.os.Handler(android.os.Looper.getMainLooper()).post {
                        onError(errorMessage)
                    }
                }
            }
            
            override fun onResults(results: Bundle?) {
                isListening = false
                val matches = results?.getStringArrayList(SpeechRecognizer.RESULTS_RECOGNITION)
                if (matches != null && matches.isNotEmpty()) {
                    val transcript = matches[0]
                    Log.d(TAG, "Recognition result: $transcript")
                    // Post to main thread to ensure UI updates happen correctly
                    android.os.Handler(android.os.Looper.getMainLooper()).post {
                        onResult(transcript)
                    }
                } else {
                    // No match - restart listening automatically
                    android.os.Handler(android.os.Looper.getMainLooper()).postDelayed({
                        if (!isListening) {
                            startListening()
                        }
                    }, 500)
                }
            }
            
            override fun onPartialResults(partialResults: Bundle?) {
                // Partial results received
            }
            
            override fun onEvent(eventType: Int, params: Bundle?) {
                // Event occurred
            }
        })
    }
    
    fun startListening() {
        try {
            if (speechRecognizer == null) {
                onError("التعرف الصوتي غير متوفر")
                return
            }
            
            // Check if already listening to avoid conflicts
            if (isListening) {
                Log.d(TAG, "Already listening, skipping start")
                return
            }
            
            // Check permissions
            if (!checkPermissions()) {
                requestPermissions()
                return
            }
            
            try {
                val intent = Intent(RecognizerIntent.ACTION_RECOGNIZE_SPEECH).apply {
                    putExtra(RecognizerIntent.EXTRA_LANGUAGE_MODEL, RecognizerIntent.LANGUAGE_MODEL_FREE_FORM)
                    putExtra(RecognizerIntent.EXTRA_LANGUAGE, "ar-SA") // Arabic (Saudi Arabia)
                    putExtra(RecognizerIntent.EXTRA_PROMPT, "أستمع...")
                    putExtra(RecognizerIntent.EXTRA_MAX_RESULTS, 1)
                    putExtra(RecognizerIntent.EXTRA_PARTIAL_RESULTS, true)
                }
                
                speechRecognizer?.startListening(intent)
                Log.d(TAG, "Started listening")
            } catch (e: Exception) {
                Log.e(TAG, "Error starting speech recognition", e)
                isListening = false
                onError("حدث خطأ في بدء التعرف الصوتي: ${e.message ?: "خطأ غير معروف"}")
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error in startListening", e)
            onError("حدث خطأ: ${e.message ?: "خطأ غير معروف"}")
        }
    }
    
    fun stopListening() {
        try {
            if (isListening && speechRecognizer != null) {
                speechRecognizer?.stopListening()
                isListening = false
                Log.d(TAG, "Stopped listening")
            } else {
                isListening = false // Ensure flag is set even if already stopped
            }
        } catch (e: Exception) {
            Log.e(TAG, "Error stopping listening", e)
            isListening = false // Ensure flag is reset on error
        }
    }
    
    private fun checkPermissions(): Boolean {
        return ContextCompat.checkSelfPermission(
            activity,
            Manifest.permission.RECORD_AUDIO
        ) == PackageManager.PERMISSION_GRANTED
    }
    
    private fun requestPermissions() {
        try {
            ActivityCompat.requestPermissions(
                activity,
                arrayOf(Manifest.permission.RECORD_AUDIO),
                PERMISSION_REQUEST_CODE
            )
        } catch (e: Exception) {
            Log.e(TAG, "Error requesting permissions", e)
            onError("حدث خطأ في طلب الصلاحيات: ${e.message ?: "خطأ غير معروف"}")
        }
    }
    
    fun handlePermissionResult(
        requestCode: Int,
        permissions: Array<out String>,
        grantResults: IntArray
    ): Boolean {
        if (requestCode == PERMISSION_REQUEST_CODE) {
            if (grantResults.isNotEmpty() && grantResults[0] == PackageManager.PERMISSION_GRANTED) {
                // Permission granted, start listening
                startListening()
                return true
            } else {
                onError("تم رفض صلاحية الميكروفون. يرجى السماح بالوصول في الإعدادات.")
                return false
            }
        }
        return false
    }
    
    fun destroy() {
        stopListening()
        speechRecognizer?.destroy()
        speechRecognizer = null
    }
}

