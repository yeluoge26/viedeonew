package com.techspace.shortvideo.ui.adapter

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.ImageView
import android.widget.TextView
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import coil.load
import coil.transform.CircleCropTransformation
import com.techspace.shortvideo.R
import com.techspace.shortvideo.data.model.VideoBean

class VideoListAdapter(
    private val onItemClick: (VideoBean) -> Unit
) : ListAdapter<VideoBean, VideoListAdapter.VideoViewHolder>(VideoDiffCallback()) {

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): VideoViewHolder {
        val view = LayoutInflater.from(parent.context)
            .inflate(R.layout.item_video_list, parent, false)
        return VideoViewHolder(view)
    }

    override fun onBindViewHolder(holder: VideoViewHolder, position: Int) {
        holder.bind(getItem(position))
    }

    inner class VideoViewHolder(itemView: View) : RecyclerView.ViewHolder(itemView) {
        private val ivThumb: ImageView = itemView.findViewById(R.id.iv_thumb)
        private val ivAvatar: ImageView = itemView.findViewById(R.id.iv_avatar)
        private val tvTitle: TextView = itemView.findViewById(R.id.tv_title)
        private val tvNickname: TextView = itemView.findViewById(R.id.tv_nickname)
        private val tvLikes: TextView = itemView.findViewById(R.id.tv_likes)
        private val tvViews: TextView = itemView.findViewById(R.id.tv_views)

        fun bind(video: VideoBean) {
            tvTitle.text = video.title.ifEmpty { "No title" }
            tvNickname.text = "@${video.userNickname}"
            tvLikes.text = formatCount(video.likes)
            tvViews.text = formatCount(video.views)

            if (video.thumbUrl.isNotEmpty()) {
                ivThumb.load(video.thumbUrl) {
                    crossfade(true)
                    placeholder(R.drawable.bg_video_placeholder)
                    error(R.drawable.bg_video_placeholder)
                }
            }

            if (video.userAvatar.isNotEmpty()) {
                ivAvatar.load(video.userAvatar) {
                    crossfade(true)
                    transformations(CircleCropTransformation())
                    placeholder(R.drawable.ic_default_avatar)
                    error(R.drawable.ic_default_avatar)
                }
            }

            itemView.setOnClickListener { onItemClick(video) }
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
