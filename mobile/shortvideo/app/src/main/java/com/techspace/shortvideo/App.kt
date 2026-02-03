package com.techspace.shortvideo

import android.app.Application
import com.techspace.shortvideo.data.UserPreferences
import com.techspace.shortvideo.data.api.ApiService
import com.techspace.shortvideo.data.repository.AppRepository

class App : Application() {

    companion object {
        lateinit var instance: App
            private set

        val apiService: ApiService by lazy { ApiService.create() }
        val repository: AppRepository by lazy { AppRepository(apiService) }

        fun getUserPrefs(): UserPreferences = UserPreferences.getInstance(instance)
    }

    override fun onCreate() {
        super.onCreate()
        instance = this
    }
}
