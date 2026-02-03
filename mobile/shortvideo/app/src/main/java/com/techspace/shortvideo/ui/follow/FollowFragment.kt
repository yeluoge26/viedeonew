package com.techspace.shortvideo.ui.follow

import android.content.Intent
import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.techspace.shortvideo.App
import com.techspace.shortvideo.R
import com.techspace.shortvideo.data.model.VideoBean
import com.techspace.shortvideo.databinding.FragmentFollowBinding
import com.techspace.shortvideo.ui.adapter.VideoListAdapter
import com.techspace.shortvideo.ui.login.LoginActivity
import kotlinx.coroutines.launch

class FollowFragment : Fragment() {

    private var _binding: FragmentFollowBinding? = null
    private val binding get() = _binding!!

    private lateinit var adapter: VideoListAdapter
    private var page = 1
    private var isLoading = false
    private var hasMoreData = true

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentFollowBinding.inflate(inflater, container, false)
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
        adapter = VideoListAdapter { video ->
            // Handle video click - open video player
        }

        binding.recyclerView.layoutManager = LinearLayoutManager(context)
        binding.recyclerView.adapter = adapter

        binding.swipeRefresh.setColorSchemeResources(R.color.primary)
        binding.swipeRefresh.setOnRefreshListener {
            page = 1
            hasMoreData = true
            loadData()
        }

        binding.btnLogin.setOnClickListener {
            startActivity(Intent(requireContext(), LoginActivity::class.java))
        }
    }

    private fun checkLoginAndLoad() {
        if (App.getUserPrefs().isLoggedIn) {
            binding.layoutNotLogin.visibility = View.GONE
            binding.swipeRefresh.visibility = View.VISIBLE
            if (adapter.itemCount == 0) {
                loadData()
            }
        } else {
            binding.layoutNotLogin.visibility = View.VISIBLE
            binding.swipeRefresh.visibility = View.GONE
            adapter.submitList(emptyList())
        }
    }

    private fun loadData() {
        if (isLoading) return
        isLoading = true

        val uid = App.getUserPrefs().uid ?: "-1"

        viewLifecycleOwner.lifecycleScope.launch {
            try {
                val result = App.repository.getFollowVideos(uid, page)
                if (result.isSuccess) {
                    val videos = result.getOrNull() ?: emptyList()
                    if (page == 1) {
                        adapter.submitList(videos)
                    } else {
                        val currentList = adapter.currentList.toMutableList()
                        currentList.addAll(videos)
                        adapter.submitList(currentList)
                    }
                    hasMoreData = videos.size >= 20
                    if (hasMoreData) page++

                    if (adapter.itemCount == 0) {
                        binding.layoutEmpty.visibility = View.VISIBLE
                    } else {
                        binding.layoutEmpty.visibility = View.GONE
                    }
                } else {
                    Toast.makeText(context, result.exceptionOrNull()?.message, Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                Toast.makeText(context, getString(R.string.network_error), Toast.LENGTH_SHORT).show()
            } finally {
                isLoading = false
                binding.swipeRefresh.isRefreshing = false
                binding.progressBar.visibility = View.GONE
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
