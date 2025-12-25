# API Documentation

## Base URL
```
/api/v1
```

## Authentication
Currently, the API endpoints are public (no authentication required). For production, consider implementing API tokens or Sanctum authentication.

## Endpoints

### 1. Get Screen Data
Get screen configuration and media items by screen code.

**Endpoint:** `GET /api/v1/screens/{code}`

**Parameters:**
- `code` (string, required): Screen code (e.g., "SCREEN001")

**Response:**
```json
{
    "success": true,
    "data": {
        "screen": {
            "id": 1,
            "name": "شاشة الاستقبال",
            "code": "SCREEN001",
            "orientation": "landscape",
            "resolution": "1920x1080"
        },
        "media_items": [
            {
                "id": 1,
                "url": "http://your-domain.com/storage/media/image1.jpg",
                "type": "image",
                "duration": 10,
                "file_name": "image1.jpg",
                "file_size": 524288,
                "mime_type": "image/jpeg"
            }
        ],
        "background_audio_url": "http://your-domain.com/storage/audio/background.mp3",
        "broadcast_item": null
    }
}
```

---

### 2. Get All Departments
Get all departments with their doctors and schedules.

**Endpoint:** `GET /api/v1/departments`

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "قسم العظام",
            "doctors": [
                {
                    "id": 1,
                    "name": "د. محمد أحمد",
                    "photo_url": "http://your-domain.com/storage/doctors/doctor1.jpg",
                    "schedules": [
                        {
                            "id": 1,
                            "day_of_week": "Sunday",
                            "start_time": "09:00",
                            "end_time": "12:00",
                            "clinic_number": "عيادة 101",
                            "floor": "الطابق الأول"
                        }
                    ]
                }
            ]
        }
    ]
}
```

---

### 3. Get Single Department
Get a specific department with doctors and schedules.

**Endpoint:** `GET /api/v1/departments/{id}`

**Parameters:**
- `id` (integer, required): Department ID

**Response:** Same structure as above, but single object instead of array.

---

### 4. Get Media Item
Get media item details including download URL.

**Endpoint:** `GET /api/v1/media/{id}`

**Parameters:**
- `id` (integer, required): Media item ID

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "url": "http://your-domain.com/storage/media/image1.jpg",
        "type": "image",
        "duration": 10,
        "file_name": "image1.jpg",
        "file_size": 524288,
        "mime_type": "image/jpeg",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

---

### 5. Get Broadcast Status
Get current broadcast status and active broadcast item.

**Endpoint:** `GET /api/v1/broadcast/status`

**Response:**
```json
{
    "success": true,
    "data": {
        "is_active": true,
        "broadcast_item": {
            "id": 5,
            "url": "http://your-domain.com/storage/media/video1.mp4",
            "type": "video",
            "file_name": "video1.mp4"
        }
    }
}
```

---

## Error Responses

All endpoints return errors in the following format:

```json
{
    "success": false,
    "message": "Error message here",
    "errors": {
        "field": ["Error message for this field"]
    }
}
```

### HTTP Status Codes
- `200` - Success
- `404` - Resource not found
- `422` - Validation error
- `500` - Server error

---

## Notes for Android Development (Kotlin)

### Base URL Configuration
```kotlin
const val BASE_URL = "https://your-domain.com/api/v1/"
```

### Example Retrofit Interface
```kotlin
interface ClinicApiService {
    @GET("screens/{code}")
    suspend fun getScreen(@Path("code") code: String): Response<ScreenResponse>
    
    @GET("departments")
    suspend fun getDepartments(): Response<DepartmentsResponse>
    
    @GET("media/{id}")
    suspend fun getMedia(@Path("id") id: Int): Response<MediaResponse>
    
    @GET("broadcast/status")
    suspend fun getBroadcastStatus(): Response<BroadcastStatusResponse>
}
```

### Data Classes Example
```kotlin
data class ApiResponse<T>(
    val success: Boolean,
    val data: T
)

data class ScreenData(
    val screen: Screen,
    val media_items: List<MediaItem>,
    val background_audio_url: String?,
    val broadcast_item: BroadcastItem?
)
```

