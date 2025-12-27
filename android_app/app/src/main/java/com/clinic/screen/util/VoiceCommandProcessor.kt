package com.clinic.screen.util

import com.clinic.screen.data.model.Department
import com.clinic.screen.data.model.Doctor

/**
 * Processes voice commands and matches them with departments and doctors
 */
object VoiceCommandProcessor {
    
    /**
     * Processes a voice command and returns the result
     */
    fun processVoiceCommand(
        transcript: String,
        departments: List<Department>
    ): VoiceCommandResult {
        val normalizedTranscript = transcript.lowercase().trim()
        
        // Try to match doctor first (more specific, higher priority)
        val doctorMatch = findDoctor(normalizedTranscript, departments)
        if (doctorMatch != null) {
            return VoiceCommandResult(
                type = VoiceCommandType.DOCTOR,
                doctor = doctorMatch.doctor,
                department = doctorMatch.department
            )
        }
        
        // Try to match department
        val departmentMatch = findDepartment(normalizedTranscript, departments)
        if (departmentMatch != null) {
            return VoiceCommandResult(
                type = VoiceCommandType.DEPARTMENT,
                department = departmentMatch
            )
        }
        
        return VoiceCommandResult(
            type = VoiceCommandType.UNKNOWN,
            errorMessage = "لم أتمكن من فهم الأمر. يرجى المحاولة مرة أخرى."
        )
    }
    
    private fun findDepartment(transcript: String, departments: List<Department>): Department? {
        return departments.firstOrNull { department ->
            val departmentName = department.name.lowercase()
            val normalizedName = normalizeArabic(departmentName)
            val normalizedTranscript = normalizeArabic(transcript)
            
            // Exact match
            normalizedTranscript.contains(normalizedName) ||
            normalizedTranscript.contains(departmentName) ||
            // Match with "قسم" prefix
            normalizedTranscript.contains("قسم $normalizedName") ||
            normalizedTranscript.contains("قسم$normalizedName") ||
            normalizedTranscript.contains("قسم $departmentName") ||
            normalizedTranscript.contains("قسم$departmentName") ||
            // Partial match (at least 3 characters)
            (normalizedName.length >= 3 && normalizedTranscript.contains(normalizedName.takeLast(3))) ||
            (departmentName.length >= 3 && transcript.contains(departmentName.takeLast(3)))
        }
    }
    
    private fun findDoctor(
        transcript: String,
        departments: List<Department>
    ): DoctorMatch? {
        for (department in departments) {
            val doctors = department.doctors ?: emptyList()
            val doctor = doctors.firstOrNull { doctor ->
                val doctorName = doctor.name.lowercase()
                val normalizedName = normalizeArabic(doctorName)
                val normalizedTranscript = normalizeArabic(transcript)
                
                // Exact match
                normalizedTranscript.contains(normalizedName) ||
                normalizedTranscript.contains(doctorName) ||
                // Match with "دكتور" or "طبيب" prefix
                normalizedTranscript.contains("دكتور $normalizedName") ||
                normalizedTranscript.contains("طبيب $normalizedName") ||
                // Partial match (at least 4 characters for doctor names)
                (normalizedName.length >= 4 && normalizedTranscript.contains(normalizedName.takeLast(4))) ||
                (doctorName.length >= 4 && transcript.contains(doctorName.takeLast(4)))
            }
            
            if (doctor != null) {
                return DoctorMatch(doctor = doctor, department = department)
            }
        }
        
        return null
    }
    
    // Simple Arabic normalization for better matching
    private fun normalizeArabic(text: String): String {
        return text
            .replace("ة", "ه")
            .replace("[أإآء]".toRegex(), "ا")
            .replace("[يى]".toRegex(), "ي")
            .replace("[\u064B-\u065F\u0670]".toRegex(), "") // Remove diacritics
    }
    
    private data class DoctorMatch(
        val doctor: Doctor,
        val department: Department
    )
    
    enum class VoiceCommandType {
        DEPARTMENT,
        DOCTOR,
        UNKNOWN
    }
    
    data class VoiceCommandResult(
        val type: VoiceCommandType,
        val department: Department? = null,
        val doctor: Doctor? = null,
        val errorMessage: String? = null
    )
}
