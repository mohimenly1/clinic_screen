package com.clinic.screen.data.api

import com.clinic.screen.data.model.*
import retrofit2.Response
import retrofit2.http.GET
import retrofit2.http.Path

interface ApiService {
    
    /**
     * Get screen data by code
     */
    @GET("screens/{code}")
    suspend fun getScreen(@Path("code") code: String): Response<ApiResponse<ScreenResponse>>
    
    /**
     * Get all departments with doctors and schedules
     */
    @GET("departments")
    suspend fun getDepartments(): Response<ApiResponse<List<Department>>>
    
    /**
     * Get single department
     */
    @GET("departments/{id}")
    suspend fun getDepartment(@Path("id") id: Int): Response<ApiResponse<Department>>
    
    /**
     * Get media item details
     */
    @GET("media/{id}")
    suspend fun getMedia(@Path("id") id: Int): Response<ApiResponse<MediaItem>>
    
    /**
     * Get broadcast status
     */
    @GET("broadcast/status")
    suspend fun getBroadcastStatus(): Response<ApiResponse<BroadcastStatusResponse>>
}

