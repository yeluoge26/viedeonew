package com.techspace.shortvideo.data

import android.content.Context
import android.content.SharedPreferences
import com.google.gson.Gson
import com.techspace.shortvideo.data.model.UserBean

class UserPreferences(context: Context) {

    private val prefs: SharedPreferences = context.getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
    private val gson = Gson()

    companion object {
        private const val PREFS_NAME = "techspace_user"
        private const val KEY_UID = "uid"
        private const val KEY_TOKEN = "token"
        private const val KEY_USER_INFO = "user_info"
        private const val KEY_LOCATION_LNG = "location_lng"
        private const val KEY_LOCATION_LAT = "location_lat"
        private const val KEY_LOCATION_CITY = "location_city"
        private const val KEY_LOCATION_PROVINCE = "location_province"
        private const val KEY_IS_FIRST_LAUNCH = "is_first_launch"

        @Volatile
        private var instance: UserPreferences? = null

        fun getInstance(context: Context): UserPreferences {
            return instance ?: synchronized(this) {
                instance ?: UserPreferences(context.applicationContext).also { instance = it }
            }
        }
    }

    // ==================== Login State ====================

    val isLoggedIn: Boolean
        get() = !uid.isNullOrEmpty() && uid != "-1" && !token.isNullOrEmpty()

    var uid: String?
        get() = prefs.getString(KEY_UID, "-1")
        set(value) = prefs.edit().putString(KEY_UID, value).apply()

    var token: String?
        get() = prefs.getString(KEY_TOKEN, null)
        set(value) = prefs.edit().putString(KEY_TOKEN, value).apply()

    var userInfo: UserBean?
        get() {
            val json = prefs.getString(KEY_USER_INFO, null)
            return if (json != null) {
                try {
                    gson.fromJson(json, UserBean::class.java)
                } catch (e: Exception) {
                    null
                }
            } else null
        }
        set(value) {
            val json = if (value != null) gson.toJson(value) else null
            prefs.edit().putString(KEY_USER_INFO, json).apply()
        }

    fun setLoginInfo(uid: String, token: String) {
        prefs.edit()
            .putString(KEY_UID, uid)
            .putString(KEY_TOKEN, token)
            .apply()
    }

    fun clearLoginInfo() {
        prefs.edit()
            .remove(KEY_UID)
            .remove(KEY_TOKEN)
            .remove(KEY_USER_INFO)
            .apply()
    }

    // ==================== Location ====================

    var locationLng: Double
        get() = prefs.getString(KEY_LOCATION_LNG, "0")?.toDoubleOrNull() ?: 0.0
        set(value) = prefs.edit().putString(KEY_LOCATION_LNG, value.toString()).apply()

    var locationLat: Double
        get() = prefs.getString(KEY_LOCATION_LAT, "0")?.toDoubleOrNull() ?: 0.0
        set(value) = prefs.edit().putString(KEY_LOCATION_LAT, value.toString()).apply()

    var locationCity: String?
        get() = prefs.getString(KEY_LOCATION_CITY, null)
        set(value) = prefs.edit().putString(KEY_LOCATION_CITY, value).apply()

    var locationProvince: String?
        get() = prefs.getString(KEY_LOCATION_PROVINCE, null)
        set(value) = prefs.edit().putString(KEY_LOCATION_PROVINCE, value).apply()

    fun setLocation(lng: Double, lat: Double, city: String?, province: String?) {
        prefs.edit()
            .putString(KEY_LOCATION_LNG, lng.toString())
            .putString(KEY_LOCATION_LAT, lat.toString())
            .putString(KEY_LOCATION_CITY, city)
            .putString(KEY_LOCATION_PROVINCE, province)
            .apply()
    }

    // ==================== App State ====================

    var isFirstLaunch: Boolean
        get() = prefs.getBoolean(KEY_IS_FIRST_LAUNCH, true)
        set(value) = prefs.edit().putBoolean(KEY_IS_FIRST_LAUNCH, value).apply()
}
