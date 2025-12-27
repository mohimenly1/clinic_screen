package com.clinic.screen.ui.setup

import android.content.Context
import android.content.Intent
import android.content.SharedPreferences
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.clinic.screen.databinding.ActivitySetupBinding
import com.clinic.screen.ui.main.MainActivity

class SetupActivity : AppCompatActivity() {
    
    private lateinit var binding: ActivitySetupBinding
    private lateinit var sharedPreferences: SharedPreferences
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySetupBinding.inflate(layoutInflater)
        setContentView(binding.root)
        
        sharedPreferences = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        
        // Check if screen code is already set
        val savedScreenCode = sharedPreferences.getString(KEY_SCREEN_CODE, null)
        if (savedScreenCode != null) {
            // Screen code already set, go to main activity
            startMainActivity(savedScreenCode)
            finish()
            return
        }
        
        binding.saveButton.setOnClickListener {
            val screenCode = binding.screenCodeInput.text.toString().trim()
            
            if (screenCode.isEmpty()) {
                Toast.makeText(this, "الرجاء إدخال رقم الشاشة", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            
            // Save screen code
            sharedPreferences.edit()
                .putString(KEY_SCREEN_CODE, screenCode)
                .apply()
            
            // Go to main activity
            startMainActivity(screenCode)
            finish()
        }
    }
    
    private fun startMainActivity(screenCode: String) {
        val intent = Intent(this, MainActivity::class.java)
        intent.putExtra(MainActivity.EXTRA_SCREEN_CODE, screenCode)
        startActivity(intent)
    }
    
    companion object {
        private const val PREFS_NAME = "clinic_screen_prefs"
        const val KEY_SCREEN_CODE = "screen_code"
        
        /**
         * Clear saved screen code (useful for testing or reset)
         */
        fun clearScreenCode(context: Context) {
            context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
                .edit()
                .remove(KEY_SCREEN_CODE)
                .apply()
        }
        
        /**
         * Get saved screen code
         */
        fun getScreenCode(context: Context): String? {
            return context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
                .getString(KEY_SCREEN_CODE, null)
        }
    }
}
