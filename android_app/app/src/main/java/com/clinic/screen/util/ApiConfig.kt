package com.clinic.screen.util

object ApiConfig {
    // API Base URL
    // للجهاز الحقيقي (على نفس الشبكة):
    // const val BASE_URL = "http://172.20.10.2:8000/api/v1/"

    // للـ Android Emulator:
    // const val BASE_URL = "http://10.0.2.2:8000/api/v1/"
    const val BASE_URL = "http://192.168.86.196:8000/api/v1/"

    // للإنتاج (Production):
    // const val BASE_URL = "https://your-domain.com/api/v1/"

    // Pusher Configuration
    const val PUSHER_APP_ID = "2059282"
    const val PUSHER_KEY = "86253c368b61504804fa"
    const val PUSHER_CLUSTER = "eu"

    // Timeouts
    const val CONNECT_TIMEOUT = 30L
    const val READ_TIMEOUT = 30L
    const val WRITE_TIMEOUT = 30L
}
