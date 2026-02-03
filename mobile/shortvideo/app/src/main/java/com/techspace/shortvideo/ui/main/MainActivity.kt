package com.techspace.shortvideo.ui.main

import android.content.Intent
import android.os.Bundle
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import com.techspace.shortvideo.App
import com.techspace.shortvideo.R
import com.techspace.shortvideo.databinding.ActivityMainBinding
import com.techspace.shortvideo.ui.feed.FeedFragment
import com.techspace.shortvideo.ui.follow.FollowFragment
import com.techspace.shortvideo.ui.login.LoginActivity
import com.techspace.shortvideo.ui.message.MessageFragment
import com.techspace.shortvideo.ui.profile.ProfileFragment

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding

    private var feedFragment: FeedFragment? = null
    private var followFragment: FollowFragment? = null
    private var messageFragment: MessageFragment? = null
    private var profileFragment: ProfileFragment? = null

    private var activeFragment: Fragment? = null
    private var lastBackPressTime = 0L

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupBottomNavigation()

        // Load default fragment
        if (savedInstanceState == null) {
            feedFragment = FeedFragment()
            supportFragmentManager.beginTransaction()
                .add(R.id.fragment_container, feedFragment!!, "feed")
                .commit()
            activeFragment = feedFragment
        }
    }

    private fun setupBottomNavigation() {
        binding.bottomNav.setOnItemSelectedListener { item ->
            when (item.itemId) {
                R.id.nav_home -> {
                    switchFragment(getOrCreateFeedFragment())
                    true
                }
                R.id.nav_follow -> {
                    switchFragment(getOrCreateFollowFragment())
                    true
                }
                R.id.nav_message -> {
                    if (!App.getUserPrefs().isLoggedIn) {
                        startActivity(Intent(this, LoginActivity::class.java))
                        false
                    } else {
                        switchFragment(getOrCreateMessageFragment())
                        true
                    }
                }
                R.id.nav_profile -> {
                    switchFragment(getOrCreateProfileFragment())
                    true
                }
                else -> false
            }
        }
    }

    private fun getOrCreateFeedFragment(): Fragment {
        if (feedFragment == null) {
            feedFragment = FeedFragment()
        }
        return feedFragment!!
    }

    private fun getOrCreateFollowFragment(): Fragment {
        if (followFragment == null) {
            followFragment = FollowFragment()
        }
        return followFragment!!
    }

    private fun getOrCreateMessageFragment(): Fragment {
        if (messageFragment == null) {
            messageFragment = MessageFragment()
        }
        return messageFragment!!
    }

    private fun getOrCreateProfileFragment(): Fragment {
        if (profileFragment == null) {
            profileFragment = ProfileFragment()
        }
        return profileFragment!!
    }

    private fun switchFragment(targetFragment: Fragment) {
        if (activeFragment === targetFragment) return

        val transaction = supportFragmentManager.beginTransaction()

        // Hide current fragment
        activeFragment?.let { transaction.hide(it) }

        // Show or add target fragment
        if (targetFragment.isAdded) {
            transaction.show(targetFragment)
        } else {
            transaction.add(R.id.fragment_container, targetFragment)
        }

        transaction.commit()
        activeFragment = targetFragment
    }

    override fun onBackPressed() {
        val currentTime = System.currentTimeMillis()
        if (currentTime - lastBackPressTime > 2000) {
            lastBackPressTime = currentTime
            Toast.makeText(this, "Press back again to exit", Toast.LENGTH_SHORT).show()
        } else {
            super.onBackPressed()
        }
    }
}
