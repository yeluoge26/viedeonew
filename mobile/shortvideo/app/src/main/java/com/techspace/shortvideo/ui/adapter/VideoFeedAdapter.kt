package com.techspace.shortvideo.ui.adapter

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.media3.common.MediaItem
import androidx.media3.common.Player
import androidx.media3.exoplayer.ExoPlayer
import androidx.media3.ui.PlayerView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import coil.load
import coil.transform.CircleCropTransformation
import com.techspace.shortvideo.R
import com.techspace.shortvideo.data.model.VideoBean

class VideoFeedAdapter(
    private val onLikeClick: (VideoBean, Int) -> Unit,
    private val onCommentClick: (VideoBean) -> Unit,
    private val onShareClick: (VideoBean) -> Unit,
    private val onUserClick: (VideoBean) -> Unit
) : ListAdapter<VideoBean, VideoFeedAdapter.VideoViewHolder>(VideoDiffCallback()) {

    private val players = mutableMapOf<Int, ExoPlayer>()
    private var currentPlayingPosition = 0  // 默认从第一个视频开始播放

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VideoViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_video_feed, parent, false)
        return VideoViewHolder(view)
    }

    override fun onBindViewHolder(holder: VideoViewHolder, position: Int) {
        holder.bind(getItem(position), position)
    }

    override fun onViewRecycled(holder: VideoViewHolder) {
        super.onViewRecycled(holder)
        holder.releasePlayer()
    }

    fun onPageSelected(position: Int) {
        // Pause previous player
        if (currentPlayingPosition >= 0 && currentPlayingPosition != position) {
            players[currentPlayingPosition]?.pause()
        }

        // Play current player
        currentPlayingPosition = position
        players[position]?.play()
    }

    fun pauseAllPlayers() {
        players.values.forEach { it.pause() }
    }

    fun releaseAllPlayers() {
        players.values.forEach { it.release() }
        players.clear()
    }

    inner class VideoViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val playerView: PlayerView = itemView.findViewById(R.id.player_view)
        private val ivAvatar: ImageView = itemView.findViewById(R.id.iv_avatar)
        private val tvNickname: TextView = itemView.findViewById(R.id.tv_nickname)
        private val tvTitle: TextView = itemView.findViewById(R.id.tv_title)
        private val tvLikes: TextView = itemView.findViewById(R.id.tv_likes)
        private val tvComments: TextView = itemView.findViewById(R.id.tv_comments)
        private val tvShares: TextView = itemView.findViewById(R.id.tv_shares)
        private val ivLike: ImageView = itemView.findViewById(R.id.iv_like)
        private val ivComment: ImageView = itemView.findViewById(R.id.iv_comment)
        private val ivShare: ImageView = itemView.findViewById(R.id.iv_share)
        private val btnFollow: TextView = itemView.findViewById(R.id.btn_follow)

        private var player: ExoPlayer? = null
        private var currentPosition: Int = -1

        fun bind(video: VideoBean, position: Int) {
            currentPosition = position

            // User info
            tvNickname.text = "@${video.userNickname}"
            tvTitle.text = video.title

            // Stats
            tvLikes.text = formatCount(video.likes)
            tvComments.text = formatCount(video.comments)
            tvShares.text = formatCount(video.shares)

            // Like state
            if (video.isLiked == 1) {
                ivLike.setColorFilter(itemView.context.getColor(R.color.primary))
            } else {
                ivLike.setColorFilter(itemView.context.getColor(R.color.white))
            }

            // Follow state
            if (video.isAttent == 1) {
                btnFollow.visibility = View.GONE
            } else {
                btnFollow.visibility = View.VISIBLE
            }

            // Avatar
            if (video.userAvatar.isNotEmpty()) {
                ivAvatar.load(video.userAvatar) {
                    crossfade(true)
                    transformations(CircleCropTransformation())
                    placeholder(R.drawable.ic_default_avatar)
                    error(R.drawable.ic_default_avatar)
                }
            }

            // Click listeners
            itemView.findViewById<View>(R.id.layout_like).setOnClickListener {
                onLikeClick(video, position)
            }

            itemView.findViewById<View>(R.id.layout_comment).setOnClickListener {
                onCommentClick(video)
            }

            itemView.findViewById<View>(R.id.layout_share).setOnClickListener {
                onShareClick(video)
            }

            ivAvatar.setOnClickListener { onUserClick(video) }
            tvNickname.setOnClickListener { onUserClick(video) }

            // Setup video player
            setupPlayer(video.videoUrl, position)
        }

        private fun setupPlayer(videoUrl: String, position: Int) {
            if (videoUrl.isEmpty()) return

            // Release old player if exists
            player?.release()

            // Create new player
            player = ExoPlayer.Builder(itemView.context).build().apply {
                repeatMode = Player.REPEAT_MODE_ONE
                volume = 1f
                playWhenReady = (position == currentPlayingPosition)  // 自动播放当前位置的视频

                // Prepare video
                val mediaItem = MediaItem.fromUri(videoUrl)
                setMediaItem(mediaItem)
                prepare()
            }

            playerView.player = player
            playerView.useController = false

            // Store player reference
            players[position] = player!!

            // Toggle play/pause on tap
            playerView.setOnClickListener {
                player?.let { p ->
                    if (p.isPlaying) {
                        p.pause()
                    } else {
                        p.play()
                    }
                }
            }
        }

        fun releasePlayer() {
            player?.release()
            players.remove(currentPosition)
            player = null
        }

        private fun formatCount(count: Int): String {
            return when {
                count >= 10000 -> String.format("%.1fw", count / 10000.0)
                count >= 1000 -> String.format("%.1fk", count / 1000.0)
                else -> count.toString()
            }
        }
    }

    class VideoDiffCallback : DiffUtil.ItemCallback<VideoBean>() {
        override fun areItemsTheSame(oldItem: VideoBean, newItem: VideoBean): Boolean {
            return oldItem.id == newItem.id
        }

        override fun areContentsTheSame(oldItem: VideoBean, newItem: VideoBean): Boolean {
            return oldItem == newItem
        }
    }
}
