package com.techspace.shortvideo.ui.login

import android.content.Intent
import android.os.Bundle
import android.os.CountDownTimer
import android.view.View
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.techspace.shortvideo.App
import com.techspace.shortvideo.R
import com.techspace.shortvideo.databinding.ActivityLoginBinding
import com.techspace.shortvideo.ui.main.MainActivity
import kotlinx.coroutines.launch

/**
 * 登录页面 - 匹配H5的手机号+验证码登录方式
 * 参考 h5/login.html 实现
 */
class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding
    private var countDownTimer: CountDownTimer? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        setupViews()
    }

    private fun setupViews() {
        // 返回按钮
        binding.btnBack.setOnClickListener { finish() }

        // 获取验证码按钮
        binding.btnGetCode.setOnClickListener {
            val phone = binding.etPhone.text.toString().trim()
            if (phone.isEmpty()) {
                Toast.makeText(this, getString(R.string.input_phone), Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            if (phone.length < 11) {
                Toast.makeText(this, "请输入正确的手机号", Toast.LENGTH_SHORT).show()
                return@setOnClickListener
            }
            sendCode(phone)
        }

        // 登录按钮
        binding.btnLogin.setOnClickListener {
            doLogin()
        }
    }

    /**
     * 发送验证码 - 匹配H5的 API.sendCode(phone)
     */
    private fun sendCode(phone: String) {
        lifecycleScope.launch {
            try {
                binding.btnGetCode.isEnabled = false
                val result = App.repository.sendCode(phone)
                if (result.isSuccess) {
                    Toast.makeText(this@LoginActivity, getString(R.string.code_sent), Toast.LENGTH_SHORT).show()
                    startCountDown()
                } else {
                    binding.btnGetCode.isEnabled = true
                    Toast.makeText(this@LoginActivity, result.exceptionOrNull()?.message ?: getString(R.string.send_code_failed), Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                binding.btnGetCode.isEnabled = true
                Toast.makeText(this@LoginActivity, getString(R.string.network_error), Toast.LENGTH_SHORT).show()
            }
        }
    }

    /**
     * 登录 - 匹配H5的 API.login(phone, code)
     * H5登录流程：手机号 + 验证码 → 返回用户信息 → 保存到localStorage
     */
    private fun doLogin() {
        val phone = binding.etPhone.text.toString().trim()
        val code = binding.etCode.text.toString().trim()

        if (phone.isEmpty()) {
            Toast.makeText(this, getString(R.string.input_phone), Toast.LENGTH_SHORT).show()
            return
        }
        if (code.isEmpty()) {
            Toast.makeText(this, getString(R.string.input_code), Toast.LENGTH_SHORT).show()
            return
        }

        lifecycleScope.launch {
            try {
                binding.btnLogin.isEnabled = false
                binding.progressBar.visibility = View.VISIBLE

                val result = App.repository.login(phone, code)
                if (result.isSuccess) {
                    val user = result.getOrNull()!!

                    // 保存用户信息 - 匹配H5的localStorage存储方式
                    // H5: localStorage.setItem('token', data.token || data.id)
                    // H5: localStorage.setItem('uid', data.id)
                    // H5: localStorage.setItem('userInfo', JSON.stringify({...}))
                    App.getUserPrefs().apply {
                        setLoginInfo(user.id, user.token.ifEmpty { user.id })
                        userInfo = user
                    }

                    Toast.makeText(this@LoginActivity, getString(R.string.login_success), Toast.LENGTH_SHORT).show()

                    // 跳转到主页
                    startActivity(Intent(this@LoginActivity, MainActivity::class.java).apply {
                        flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
                    })
                    finish()
                } else {
                    Toast.makeText(this@LoginActivity, result.exceptionOrNull()?.message ?: getString(R.string.login_failed), Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(this@LoginActivity, getString(R.string.network_error), Toast.LENGTH_SHORT).show()
            } finally {
                binding.btnLogin.isEnabled = true
                binding.progressBar.visibility = View.GONE
            }
        }
    }

    private fun startCountDown() {
        countDownTimer = object : CountDownTimer(60000, 1000) {
            override fun onTick(millisUntilFinished: Long) {
                binding.btnGetCode.text = "${millisUntilFinished / 1000}s"
            }

            override fun onFinish() {
                binding.btnGetCode.isEnabled = true
                binding.btnGetCode.text = getString(R.string.get_code)
            }
        }.start()
    }

    override fun onDestroy() {
        super.onDestroy()
        countDownTimer?.cancel()
    }
}
