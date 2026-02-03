package com.techspace.shortvideo.ui.feed

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import android.widget.Toast
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.viewpager2.widget.ViewPager2
import com.techspace.shortvideo.App
import com.techspace.shortvideo.R
import com.techspace.shortvideo.data.model.VideoBean
import com.techspace.shortvideo.databinding.FragmentFeedBinding
import com.techspace.shortvideo.ui.adapter.VideoFeedAdapter
import kotlinx.coroutines.launch

/**
 * 视频首页 - 匹配H5的index.html样式
 * 支持标签切换：推荐 / 热门 / 附近
 */
class FeedFragment : Fragment() {

    private var _binding: FragmentFeedBinding? = null
    private val binding get() = _binding!!

    private lateinit var adapter: VideoFeedAdapter
    private val videos = mutableListOf<VideoBean>()
    private var page = 1
    private var isLoading = false
    private var hasMoreData = true
    private var currentPosition = 0

    // 当前选中的标签: 0=推荐, 1=热门, 2=附近
    private var currentTab = 0

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentFeedBinding.inflate(inflater, container, false)
        return binding.root
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        setupViews()
        loadVideos()
    }

    private fun setupViews() {
        // 设置视频适配器
        adapter = VideoFeedAdapter(
            onLikeClick = { video, position ->
                handleLike(video, position)
            },
            onCommentClick = { video ->
                Toast.makeText(context, "评论: ${video.title}", Toast.LENGTH_SHORT).show()
            },
            onShareClick = { video ->
                Toast.makeText(context, "分享: ${video.title}", Toast.LENGTH_SHORT).show()
            },
            onUserClick = { video ->
                Toast.makeText(context, "用户: ${video.userNickname}", Toast.LENGTH_SHORT).show()
            }
        )

        binding.viewPager.adapter = adapter
        binding.viewPager.orientation = ViewPager2.ORIENTATION_VERTICAL
        binding.viewPager.offscreenPageLimit = 1  // 预加载相邻页面

        // 监听页面切换
        binding.viewPager.registerOnPageChangeCallback(object : ViewPager2.OnPageChangeCallback() {
            override fun onPageSelected(position: Int) {
                currentPosition = position
                adapter.onPageSelected(position)

                // 快到底部时加载更多
                if (position >= videos.size - 3 && hasMoreData && !isLoading) {
                    loadMoreVideos()
                }
            }
        })

        // 下拉刷新
        binding.swipeRefresh.setColorSchemeResources(R.color.primary)
        binding.swipeRefresh.setOnRefreshListener {
            page = 1
            hasMoreData = true
            videos.clear()
            loadVideos()
        }

        // 标签点击事件 - 匹配H5的标签切换逻辑
        binding.tabRecommend.setOnClickListener { selectTab(0) }
        binding.tabHot.setOnClickListener { selectTab(1) }
        binding.tabNearby.setOnClickListener { selectTab(2) }
    }

    /**
     * 切换标签 - 匹配H5的switchTab函数
     */
    private fun selectTab(index: Int) {
        if (currentTab == index) return

        currentTab = index
        updateTabUI()

        // 重新加载数据
        page = 1
        hasMoreData = true
        videos.clear()
        adapter.submitList(emptyList())
        loadVideos()
    }

    private fun updateTabUI() {
        val tabs = listOf(binding.tabRecommend, binding.tabHot, binding.tabNearby)
        tabs.forEachIndexed { index, tab ->
            if (index == currentTab) {
                tab.setTextColor(resources.getColor(R.color.white, null))
                tab.setTypeface(null, android.graphics.Typeface.BOLD)
            } else {
                tab.setTextColor(resources.getColor(R.color.text_secondary, null))
                tab.setTypeface(null, android.graphics.Typeface.NORMAL)
            }
        }
    }

    /**
     * 加载视频 - 根据当前标签调用不同API
     * 匹配H5的loadVideos函数逻辑
     */
    private fun loadVideos() {
        if (isLoading) return
        isLoading = true

        val uid = App.getUserPrefs().uid ?: "-1"

        viewLifecycleOwner.lifecycleScope.launch {
            try {
                binding.progressBar.visibility = View.VISIBLE

                val result = when (currentTab) {
                    0 -> App.repository.getRecommendVideos(uid, page)  // 推荐
                    1 -> App.repository.getHotVideos(uid, page)        // 热门
                    else -> App.repository.getRecommendVideos(uid, page) // 附近（暂用推荐代替）
                }

                if (result.isSuccess) {
                    val newVideos = result.getOrNull() ?: emptyList()

                    if (page == 1) {
                        videos.clear()
                    }

                    videos.addAll(newVideos)
                    adapter.submitList(videos.toList())

                    hasMoreData = newVideos.size >= 20
                    if (hasMoreData) page++

                    binding.emptyView.visibility = if (videos.isEmpty()) View.VISIBLE else View.GONE

                    // 首次加载时自动播放第一个视频
                    if (page == 2 && videos.isNotEmpty()) {
                        binding.viewPager.post {
                            adapter.onPageSelected(0)
                        }
                    }
                } else {
                    if (videos.isEmpty()) {
                        binding.emptyView.visibility = View.VISIBLE
                    }
                    Toast.makeText(context, result.exceptionOrNull()?.message ?: getString(R.string.error), Toast.LENGTH_SHORT).show()
                }
            } catch (e: Exception) {
                if (videos.isEmpty()) {
                    binding.emptyView.visibility = View.VISIBLE
                }
                Toast.makeText(context, getString(R.string.network_error), Toast.LENGTH_SHORT).show()
            } finally {
                isLoading = false
                binding.swipeRefresh.isRefreshing = false
                binding.progressBar.visibility = View.GONE
            }
        }
    }

    private fun loadMoreVideos() {
        if (isLoading || !hasMoreData) return
        isLoading = true

        val uid = App.getUserPrefs().uid ?: "-1"

        viewLifecycleOwner.lifecycleScope.launch {
            try {
                val result = when (currentTab) {
                    0 -> App.repository.getRecommendVideos(uid, page)
                    1 -> App.repository.getHotVideos(uid, page)
                    else -> App.repository.getRecommendVideos(uid, page)
                }

                if (result.isSuccess) {
                    val newVideos = result.getOrNull() ?: emptyList()
                    videos.addAll(newVideos)
                    adapter.submitList(videos.toList())

                    hasMoreData = newVideos.size >= 20
                    if (hasMoreData) page++
                }
            } catch (e: Exception) {
                // 静默失败
            } finally {
                isLoading = false
            }
        }
    }

    private fun handleLike(video: VideoBean, position: Int) {
        if (!App.getUserPrefs().isLoggedIn) {
            Toast.makeText(context, getString(R.string.please_login), Toast.LENGTH_SHORT).show()
            return
        }

        val uid = App.getUserPrefs().uid ?: return
        val token = App.getUserPrefs().token ?: return

        viewLifecycleOwner.lifecycleScope.launch {
            try {
                val result = App.repository.setVideoLike(uid, token, video.id)
                if (result.isSuccess) {
                    // 本地切换点赞状态
                    val updatedVideo = video.copy(
                        isLiked = if (video.isLiked == 1) 0 else 1,
                        likes = if (video.isLiked == 1) video.likes - 1 else video.likes + 1
                    )
                    videos[position] = updatedVideo
                    adapter.submitList(videos.toList())
                }
            } catch (e: Exception) {
                // 静默失败
            }
        }
    }

    override fun onResume() {
        super.onResume()
        adapter.onPageSelected(currentPosition)
    }

    override fun onPause() {
        super.onPause()
        adapter.pauseAllPlayers()
    }

    override fun onDestroyView() {
        super.onDestroyView()
        adapter.releaseAllPlayers()
        _binding = null
    }
}
