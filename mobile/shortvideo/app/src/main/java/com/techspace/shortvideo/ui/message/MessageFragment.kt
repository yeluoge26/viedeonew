package com.techspace.shortvideo.ui.message

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import com.techspace.shortvideo.App
import com.techspace.shortvideo.R
import com.techspace.shortvideo.databinding.FragmentMessageBinding
import com.techspace.shortvideo.ui.login.LoginActivity
import kotlinx.coroutines.launch

class MessageFragment : Fragment() {

    private var _binding: FragmentMessageBinding? = null
    private val binding get() = _binding!!

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentMessageBinding.inflate(inflater, container, false)
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
        binding.btnLogin.setOnClickListener {
            startActivity(Intent(requireContext(), LoginActivity::class.java))
        }

        // Set up message type clicks
        binding.layoutFans.setOnClickListener {
            Toast.makeText(context, "Fans messages", Toast.LENGTH_SHORT).show()
            // TODO: Open fans messages
        }

        binding.layoutLikes.setOnClickListener {
            Toast.makeText(context, "Likes messages", Toast.LENGTH_SHORT).show()
            // TODO: Open likes messages
        }

        binding.layoutComments.setOnClickListener {
            Toast.makeText(context, "Comment messages", Toast.LENGTH_SHORT).show()
            // TODO: Open comment messages
        }

        binding.layoutAtMe.setOnClickListener {
            Toast.makeText(context, "@ messages", Toast.LENGTH_SHORT).show()
            // TODO: Open @ messages
        }

        binding.layoutSystem.setOnClickListener {
            Toast.makeText(context, "System messages", Toast.LENGTH_SHORT).show()
            // TODO: Open system messages
        }

        binding.swipeRefresh.setColorSchemeResources(R.color.primary)
        binding.swipeRefresh.setOnRefreshListener {
            loadMessageSummary()
        }
    }

    private fun checkLoginAndLoad() {
        if (App.getUserPrefs().isLoggedIn) {
            binding.layoutNotLogin.visibility = View.GONE
            binding.layoutContent.visibility = View.VISIBLE
            loadMessageSummary()
        } else {
            binding.layoutNotLogin.visibility = View.VISIBLE
            binding.layoutContent.visibility = View.GONE
        }
    }

    private fun loadMessageSummary() {
        val uid = App.getUserPrefs().uid ?: return

        viewLifecycleOwner.lifecycleScope.launch {
            try {
                val result = App.repository.getLastMessage(uid)
                if (result.isSuccess) {
                    val summary = result.getOrNull()
                    summary?.let {
                        // Update UI with message counts
                        if (it.fansNum > 0) {
                            binding.tvFansBadge.visibility = View.VISIBLE
                            binding.tvFansBadge.text = if (it.fansNum > 99) "99+" else it.fansNum.toString()
                        } else {
                            binding.tvFansBadge.visibility = View.GONE
                        }

                        if (it.zanNum > 0) {
                            binding.tvLikesBadge.visibility = View.VISIBLE
                            binding.tvLikesBadge.text = if (it.zanNum > 99) "99+" else it.zanNum.toString()
                        } else {
                            binding.tvLikesBadge.visibility = View.GONE
                        }

                        if (it.commentNum > 0) {
                            binding.tvCommentsBadge.visibility = View.VISIBLE
                            binding.tvCommentsBadge.text = if (it.commentNum > 99) "99+" else it.commentNum.toString()
                        } else {
                            binding.tvCommentsBadge.visibility = View.GONE
                        }

                        if (it.atNum > 0) {
                            binding.tvAtBadge.visibility = View.VISIBLE
                            binding.tvAtBadge.text = if (it.atNum > 99) "99+" else it.atNum.toString()
                        } else {
                            binding.tvAtBadge.visibility = View.GONE
                        }

                        if (it.systemNum > 0) {
                            binding.tvSystemBadge.visibility = View.VISIBLE
                            binding.tvSystemBadge.text = if (it.systemNum > 99) "99+" else it.systemNum.toString()
                        } else {
                            binding.tvSystemBadge.visibility = View.GONE
                        }

                        // Update message previews
                        binding.tvFansPreview.text = it.fansMsg.ifEmpty { getString(R.string.no_data) }
                        binding.tvLikesPreview.text = it.zanMsg.ifEmpty { getString(R.string.no_data) }
                        binding.tvCommentsPreview.text = it.commentMsg.ifEmpty { getString(R.string.no_data) }
                        binding.tvAtPreview.text = it.atMsg.ifEmpty { getString(R.string.no_data) }
                        binding.tvSystemPreview.text = it.systemMsg.ifEmpty { getString(R.string.no_data) }
                    }
                }
            } catch (e: Exception) {
                // Silent fail
            } finally {
                binding.swipeRefresh.isRefreshing = false
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
