package com.clinic.screen.data.model

import com.google.gson.annotations.SerializedName

/**
 * Response wrapper for API calls
 */
data class ApiResponse<T>(
    @SerializedName("success") val success: Boolean,
    @SerializedName("data") val data: T
)

/**
 * Screen information
 */
data class Screen(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("code") val code: String,
    @SerializedName("orientation") val orientation: String,
    @SerializedName("resolution") val resolution: String?
)

/**
 * Media item
 */
data class MediaItem(
    @SerializedName("id") val id: Int,
    @SerializedName("url") val url: String,
    @SerializedName("type") val type: String, // "image" or "video"
    @SerializedName("duration") val duration: Int?,
    @SerializedName("file_name") val fileName: String?,
    @SerializedName("file_size") val fileSize: Long?,
    @SerializedName("mime_type") val mimeType: String?
)

/**
 * Screen response with media items
 */
data class ScreenResponse(
    @SerializedName("screen") val screen: Screen,
    @SerializedName("media_items") val mediaItems: List<MediaItem>,
    @SerializedName("background_audio_url") val backgroundAudioUrl: String?,
    @SerializedName("broadcast_item") val broadcastItem: BroadcastItem?
)

/**
 * Broadcast item (for general broadcast)
 */
data class BroadcastItem(
    @SerializedName("id") val id: Int?,
    @SerializedName("url") val url: String,
    @SerializedName("type") val type: String,
    @SerializedName("file_name") val fileName: String?
)

/**
 * Schedule information
 */
data class Schedule(
    @SerializedName("id") val id: Int,
    @SerializedName("day_of_week") val dayOfWeek: String,
    @SerializedName("start_time") val startTime: String,
    @SerializedName("end_time") val endTime: String,
    @SerializedName("clinic_number") val clinicNumber: String,
    @SerializedName("floor") val floor: String
)

/**
 * Doctor information
 */
data class Doctor(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("photo_url") val photoUrl: String?,
    @SerializedName("schedules") val schedules: List<Schedule>?
)

/**
 * Department information
 */
data class Department(
    @SerializedName("id") val id: Int,
    @SerializedName("name") val name: String,
    @SerializedName("doctors") val doctors: List<Doctor>?
)

/**
 * Broadcast status response
 */
data class BroadcastStatusResponse(
    @SerializedName("is_active") val isActive: Boolean,
    @SerializedName("broadcast_item") val broadcastItem: BroadcastItem?
)

/**
 * Pusher Events
 * Note: These match the structure sent from Laravel events
 */
data class ScreenContentUpdatedEvent(
    @SerializedName("mediaItems") val mediaItems: List<Map<String, Any>>
)

data class BroadcastMediaEvent(
    @SerializedName("mediaItem") val mediaItem: Map<String, Any>
)

