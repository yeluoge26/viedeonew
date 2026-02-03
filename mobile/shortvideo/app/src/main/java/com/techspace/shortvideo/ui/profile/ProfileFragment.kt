package com.techspace.shortvideo.ui.profile

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import coil.load
import coil.transform.CircleCropTransformation
import com.techspace.shortvideo.App
import com.techspace.shortvideo.R
import com.techspace.shortvideo.databinding.FragmentProfileBinding
import com.techspace.shortvideo.ui.login.LoginActivity
import kotlinx.coroutines.launch

/**
 * 个人中心页面 - 匹配H5的me.html样式
 */
class ProfileFragment : Fragment() {

    private var _binding: FragmentProfileBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentProfileBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupViews()
    }

    override fun onResume() {
        super.onResume()
        checkLoginAndLoad()
    }

    private fun setupViews() {
        // 登录按钮
        binding.btnLogin.setOnClickListener {
            startActivity(Intent(requireContext(), LoginActivity::class.java))
        }

        // 设置按钮
        binding.btnSettings.setOnClickListener {
            Toast.makeText(context, getString(R.string.settings), Toast.LENGTH_SHORT).show()
        }

        // 粉丝/关注/点赞统计
        binding.layoutFans.setOnClickListener {
            Toast.makeText(context, getString(R.string.fans), Toast.LENGTH_SHORT).show()
        }

        binding.layoutFollowing.setOnClickListener {
            Toast.makeText(context, getString(R.string.follows), Toast.LENGTH_SHORT).show()
        }

        binding.layoutLikes.setOnClickListener {
            Toast.makeText(context, getString(R.string.likes), Toast.LENGTH_SHORT).show()
        }

        // 编辑资料
        binding.btnEditProfile.setOnClickListener {
            Toast.makeText(context, getString(R.string.edit_profile), Toast.LENGTH_SHORT).show()
        }

        // 菜单项 (H5 style)
        binding.menuMyLikes.setOnClickListener {
            Toast.makeText(context, getString(R.string.my_likes), Toast.LENGTH_SHORT).show()
        }

        binding.menuHistory.setOnClickListener {
            Toast.makeText(context, getString(R.string.view_history), Toast.LENGTH_SHORT).show()
        }

        binding.menuWallet.setOnClickListener {
            Toast.makeText(context, getString(R.string.my_wallet), Toast.LENGTH_SHORT).show()
        }

        binding.menuService.setOnClickListener {
            Toast.makeText(context, getString(R.string.customer_service), Toast.LENGTH_SHORT).show()
        }

        // 退出登录
        binding.btnLogout.setOnClickListener {
            showLogoutDialog()
        }

        // 下拉刷新
        binding.swipeRefresh.setColorSchemeResources(R.color.primary)
        binding.swipeRefresh.setOnRefreshListener {
            loadUserInfo()
        }
    }

    private fun checkLoginAndLoad() {
        if (App.getUserPrefs().isLoggedIn) {
            binding.layoutNotLogin.visibility = View.GONE
            binding.layoutContent.visibility = View.VISIBLE
            loadUserInfo()
        } else {
            binding.layoutNotLogin.visibility = View.VISIBLE
            binding.layoutContent.visibility = View.GONE
        }
    }

    private fun loadUserInfo() {
        val uid = App.getUserPrefs().uid ?: return
        val token = App.getUserPrefs().token ?: return

        viewLifecycleOwner.lifecycleScope.launch {
            try {
                val result = App.repository.getBaseInfo(uid, token)
                if (result.isSuccess) {
                    val user = result.getOrNull()
                    user?.let {
                        // 保存到本地
                        App.getUserPrefs().userInfo = it

                        // 更新UI
                        binding.tvNickname.text = it.nickname.ifEmpty { "用户$uid" }
                        binding.tvSignature.text = it.signature.ifEmpty { getString(R.string.no_signature) }
                        binding.tvUid.text = "ID: ${it.id}"

                        binding.tvFansCount.text = formatCount(it.fans)
                        binding.tvFollowingCount.text = formatCount(it.follows)
                        binding.tvLikesCount.text = formatCount(it.praiseNum)

                        if (it.avatar.isNotEmpty()) {
                            binding.ivAvatar.load(it.avatar) {
                                crossfade(true)
                                transformations(CircleCropTransformation())
                                placeholder(R.drawable.ic_default_avatar)
                                error(R.drawable.ic_default_avatar)
                            }
                        }

                        // 性别图标
                        when (it.sex) {
                            1 -> {
                                binding.ivSex.visibility = View.VISIBLE
                                binding.ivSex.setImageResource(R.drawable.ic_male)
                            }
                            2 -> {
                                binding.ivSex.visibility = View.VISIBLE
                                binding.ivSex.setImageResource(R.drawable.ic_female)
                            }
                            else -> binding.ivSex.visibility = View.GONE
                        }
                    }
                }
            } catch (e: Exception) {
                // 从缓存加载
                App.getUserPrefs().userInfo?.let { user ->
                    binding.tvNickname.text = user.nickname.ifEmpty { "用户$uid" }
                    binding.tvSignature.text = user.signature.ifEmpty { getString(R.string.no_signature) }
                    binding.tvUid.text = "ID: ${user.id}"
                    binding.tvFansCount.text = formatCount(user.fans)
                    binding.tvFollowingCount.text = formatCount(user.follows)
                    binding.tvLikesCount.text = formatCount(user.praiseNum)
                }
            } finally {
                binding.swipeRefresh.isRefreshing = false
            }
        }
    }

    private fun formatCount(count: Int): String {
        return when {
            count >= 10000 -> String.format("%.1fw", count / 10000.0)
            count >= 1000 -> String.format("%.1fk", count / 1000.0)
            else -> count.toString()
        }
    }

    private fun showLogoutDialog() {
        AlertDialog.Builder(requireContext())
            .setTitle(getString(R.string.logout))
            .setMessage(getString(R.string.logout_confirm))
            .setPositiveButton(getString(R.string.confirm)) { _, _ ->
                doLogout()
            }
            .setNegativeButton(getString(R.string.cancel), null)
            .show()
    }

    private fun doLogout() {
        App.getUserPrefs().clearLoginInfo()
        Toast.makeText(context, getString(R.string.logout), Toast.LENGTH_SHORT).show()

        // 返回登录页
        startActivity(Intent(requireContext(), LoginActivity::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK
        })
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
