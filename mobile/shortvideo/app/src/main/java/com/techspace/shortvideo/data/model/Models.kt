package com.techspace.shortvideo.data.model

import com.google.gson.annotations.SerializedName

/**
 * API Response wrapper - 匹配PhalApi格式
 * 支持info为单个对象或数组
 */
data class ApiResponse<T>(
    @SerializedName("ret") val ret: Int,
    @SerializedName("data") val data: ApiData<T>?,
    @SerializedName("msg") val msg: String?
) {
    val isSuccess: Boolean get() = ret == 200 && data?.code == 0
    val errorMsg: String? get() = data?.msg ?: msg
}

data class ApiData<T>(
    @SerializedName("code") val code: Int,
    @SerializedName("msg") val msg: String?,
    @SerializedName("info") val info: T?
)

/**
 * 用户数据模型 - 匹配H5的userInfo结构
 */
data class UserBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("user_nicename") val nickname: String = "",
    @SerializedName("avatar") val avatar: String = "",
    @SerializedName("avatar_thumb") val avatarThumb: String = "",
    @SerializedName("sex") val sex: Int = 0,
    @SerializedName("signature") val signature: String = "",
    @SerializedName("birthday") val birthday: String = "",
    @SerializedName("city") val city: String = "",
    @SerializedName("mobile") val mobile: String = "",
    @SerializedName("fans") val fans: Int = 0,
    @SerializedName("follows") val follows: Int = 0,
    @SerializedName("praise_num") val praiseNum: Int = 0,
    @SerializedName("consumption") val consumption: String = "0",
    @SerializedName("votes") val votes: String = "0",
    @SerializedName("coin") val coin: String = "0",
    @SerializedName("isattent") val isAttent: Int = 0,
    @SerializedName("level") val level: Int = 1,
    @SerializedName("token") val token: String = "",
    @SerializedName("user_status") val userStatus: Int = 1,
    @SerializedName("vip_endtime") val vipEndTime: String = "",
    @SerializedName("is_vip") val isVip: Int = 0
)

/**
 * 视频数据模型 - 匹配H5的video结构
 * H5返回格式: { id, uid, title, href, thumb, likes, comments, shares, islike, isattent, userinfo: { id, user_nicename, avatar } }
 */
data class VideoBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("uid") val uid: String = "",
    @SerializedName("title") val title: String = "",
    @SerializedName("href") val videoUrl: String = "",
    @SerializedName("thumb") val thumbUrl: String = "",
    @SerializedName("thumb_s") val thumbSmall: String = "",
    @SerializedName("likes") val likes: Int = 0,
    @SerializedName("comments") val comments: Int = 0,
    @SerializedName("shares") val shares: Int = 0,
    @SerializedName("views") val views: Int = 0,
    @SerializedName("islike") val isLiked: Int = 0,
    @SerializedName("isstep") val isStepped: Int = 0,
    @SerializedName("iscollection") val isCollected: Int = 0,
    // 平铺字段 (部分API返回这种格式)
    @SerializedName("user_nicename") private val _userNickname: String = "",
    @SerializedName("avatar") private val _userAvatar: String = "",
    @SerializedName("isattent") val isAttent: Int = 0,
    // 嵌套userinfo对象 (H5返回格式)
    @SerializedName("userinfo") val userinfo: VideoUserInfo? = null,
    @SerializedName("music_id") val musicId: String = "",
    @SerializedName("music_title") val musicTitle: String = "",
    @SerializedName("music_img") val musicImg: String = "",
    @SerializedName("lat") val lat: Double = 0.0,
    @SerializedName("lng") val lng: Double = 0.0,
    @SerializedName("city") val city: String = "",
    @SerializedName("addtime") val addTime: String = "",
    @SerializedName("datetime") val dateTime: String = "",
    @SerializedName("label_name") val labelName: String = "",
    @SerializedName("status") val status: Int = 1
) {
    // 兼容两种数据格式: 优先使用userinfo嵌套对象
    val userNickname: String get() = userinfo?.nickname?.ifEmpty { _userNickname } ?: _userNickname
    val userAvatar: String get() = userinfo?.avatar?.ifEmpty { userinfo?.avatarThumb?.ifEmpty { _userAvatar } ?: _userAvatar } ?: _userAvatar
}

/**
 * 视频作者信息 - 匹配H5的userinfo嵌套结构
 */
data class VideoUserInfo(
    @SerializedName("id") val id: String = "",
    @SerializedName("user_nicename") val nickname: String = "",
    @SerializedName("avatar") val avatar: String = "",
    @SerializedName("avatar_thumb") val avatarThumb: String = ""
)

/**
 * 评论数据模型
 */
data class CommentBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("uid") val uid: String = "",
    @SerializedName("videoid") val videoId: String = "",
    @SerializedName("parentid") val parentId: String = "0",
    @SerializedName("content") val content: String = "",
    @SerializedName("likes") val likes: Int = 0,
    @SerializedName("islike") val isLiked: Int = 0,
    @SerializedName("addtime") val addTime: String = "",
    @SerializedName("datetime") val dateTime: String = "",
    @SerializedName("user_nicename") val userNickname: String = "",
    @SerializedName("avatar") val userAvatar: String = "",
    @SerializedName("at_info") val atInfo: List<AtInfo>? = null,
    @SerializedName("reply_num") val replyNum: Int = 0
)

data class AtInfo(
    @SerializedName("uid") val uid: String = "",
    @SerializedName("name") val name: String = ""
)

/**
 * 配置数据模型
 */
data class ConfigBean(
    @SerializedName("app_android") val appAndroid: String = "",
    @SerializedName("app_name") val appName: String = "",
    @SerializedName("version") val version: String = "",
    @SerializedName("update_des") val updateDes: String = "",
    @SerializedName("download_apk_url") val downloadApkUrl: String = "",
    @SerializedName("share_title") val shareTitle: String = "",
    @SerializedName("share_des") val shareDes: String = "",
    @SerializedName("maintain_switch") val maintainSwitch: Int = 0,
    @SerializedName("maintain_tips") val maintainTips: String = "",
    @SerializedName("login_type") val loginType: String = "",
    @SerializedName("video_audit_switch") val videoAuditSwitch: Int = 0,
    @SerializedName("votes_name") val votesName: String = "TECHSPACE",
    @SerializedName("site_name") val siteName: String = "TECHSPACE"
)

/**
 * 消息数据模型
 */
data class MessageLastBean(
    @SerializedName("fans_num") val fansNum: Int = 0,
    @SerializedName("zan_num") val zanNum: Int = 0,
    @SerializedName("comment_num") val commentNum: Int = 0,
    @SerializedName("at_num") val atNum: Int = 0,
    @SerializedName("system_num") val systemNum: Int = 0,
    @SerializedName("fans_msg") val fansMsg: String = "",
    @SerializedName("zan_msg") val zanMsg: String = "",
    @SerializedName("comment_msg") val commentMsg: String = "",
    @SerializedName("at_msg") val atMsg: String = "",
    @SerializedName("system_msg") val systemMsg: String = ""
)

data class MessageFansBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("uid") val uid: String = "",
    @SerializedName("touid") val touid: String = "",
    @SerializedName("addtime") val addTime: String = "",
    @SerializedName("datetime") val dateTime: String = "",
    @SerializedName("user_nicename") val userNickname: String = "",
    @SerializedName("avatar") val userAvatar: String = "",
    @SerializedName("isattent") val isAttent: Int = 0
)

data class MessageZanBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("uid") val uid: String = "",
    @SerializedName("videoid") val videoId: String = "",
    @SerializedName("addtime") val addTime: String = "",
    @SerializedName("datetime") val dateTime: String = "",
    @SerializedName("user_nicename") val userNickname: String = "",
    @SerializedName("avatar") val userAvatar: String = "",
    @SerializedName("video_thumb") val videoThumb: String = ""
)

data class MessageCommentBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("uid") val uid: String = "",
    @SerializedName("videoid") val videoId: String = "",
    @SerializedName("content") val content: String = "",
    @SerializedName("addtime") val addTime: String = "",
    @SerializedName("datetime") val dateTime: String = "",
    @SerializedName("user_nicename") val userNickname: String = "",
    @SerializedName("avatar") val userAvatar: String = "",
    @SerializedName("video_thumb") val videoThumb: String = ""
)

/**
 * 搜索结果
 */
data class SearchUserBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("user_nicename") val nickname: String = "",
    @SerializedName("avatar") val avatar: String = "",
    @SerializedName("sex") val sex: Int = 0,
    @SerializedName("signature") val signature: String = "",
    @SerializedName("fans") val fans: Int = 0,
    @SerializedName("isattent") val isAttent: Int = 0
)

/**
 * 提现相关
 */
data class CashAccountBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("account") val account: String = "",
    @SerializedName("name") val name: String = "",
    @SerializedName("account_bank") val accountBank: String = "",
    @SerializedName("type") val type: Int = 0 // 1: alipay, 2: wechat, 3: bank
)

data class ProfitBean(
    @SerializedName("profit") val profit: String = "0.00",
    @SerializedName("total") val total: String = "0.00",
    @SerializedName("cash_min") val cashMin: String = "1",
    @SerializedName("votes_rate") val votesRate: String = "1"
)

/**
 * 用户主页
 */
data class UserHomeBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("user_nicename") val nickname: String = "",
    @SerializedName("avatar") val avatar: String = "",
    @SerializedName("avatar_thumb") val avatarThumb: String = "",
    @SerializedName("sex") val sex: Int = 0,
    @SerializedName("signature") val signature: String = "",
    @SerializedName("birthday") val birthday: String = "",
    @SerializedName("city") val city: String = "",
    @SerializedName("fans") val fans: Int = 0,
    @SerializedName("follows") val follows: Int = 0,
    @SerializedName("praise_num") val praiseNum: Int = 0,
    @SerializedName("works_num") val worksNum: Int = 0,
    @SerializedName("like_num") val likeNum: Int = 0,
    @SerializedName("isattent") val isAttent: Int = 0,
    @SerializedName("isblack") val isBlack: Int = 0,
    @SerializedName("level") val level: Int = 1
)

/**
 * 轮播图
 */
data class SlideBean(
    @SerializedName("id") val id: String = "",
    @SerializedName("slide_pic") val slidePic: String = "",
    @SerializedName("slide_url") val slideUrl: String = "",
    @SerializedName("slide_name") val slideName: String = ""
)
