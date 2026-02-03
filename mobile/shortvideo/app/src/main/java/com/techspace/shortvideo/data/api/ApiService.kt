package com.techspace.shortvideo.data.api

import com.techspace.shortvideo.BuildConfig
import com.techspace.shortvideo.data.model.*
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Response
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import retrofit2.http.*
import java.util.concurrent.TimeUnit

/**
 * API Service matching H5's api.js format
 * Using ?s=Appapi.xxx.method instead of ?service=xxx.method
 */
interface ApiService {

    // ==================== Login APIs (匹配H5) ====================

    @GET("api/public/")
    suspend fun getCode(
        @Query("s") service: String = "Appapi.Login.getCode",
        @Query("mobile") mobile: String
    ): Response<ApiResponse<Any>>

    @GET("api/public/")
    suspend fun mobileLogin(
        @Query("s") service: String = "Appapi.Login.mobileLogin",
        @Query("mobile") mobile: String,
        @Query("code") code: String
    ): Response<ApiResponse<UserBean>>

    @GET("api/public/")
    suspend fun userReg(
        @Query("s") service: String = "Appapi.Login.userReg",
        @Query("mobile") mobile: String,
        @Query("code") code: String,
        @Query("agentcode") agentcode: String = ""
    ): Response<ApiResponse<UserBean>>

    @GET("api/public/")
    suspend fun loginByThird(
        @Query("s") service: String = "Appapi.Login.userLoginByThird",
        @Query("openid") openid: String,
        @Query("nicename") nicename: String,
        @Query("avatar") avatar: String,
        @Query("type") type: String,
        @Query("source") source: String = "android",
        @Query("agentcode") agentcode: String = ""
    ): Response<ApiResponse<UserBean>>

    // ==================== User APIs ====================

    @GET("api/public/")
    suspend fun getBaseInfo(
        @Query("s") service: String = "Appapi.User.getBaseInfo",
        @Query("uid") uid: String,
        @Query("token") token: String
    ): Response<ApiResponse<UserBean>>

    @GET("api/public/")
    suspend fun getUserHome(
        @Query("s") service: String = "Appapi.User.getUserHome",
        @Query("uid") uid: String,
        @Query("touid") touid: String
    ): Response<ApiResponse<UserHomeBean>>

    @GET("api/public/")
    suspend fun setAttention(
        @Query("s") service: String = "Appapi.User.setAttention",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("touid") touid: String
    ): Response<ApiResponse<Any>>

    @GET("api/public/")
    suspend fun getFansList(
        @Query("s") service: String = "Appapi.User.getFansList",
        @Query("uid") uid: String,
        @Query("touid") touid: String,
        @Query("p") page: Int
    ): Response<ApiResponse<List<UserBean>>>

    @GET("api/public/")
    suspend fun getFollowsList(
        @Query("s") service: String = "Appapi.User.getFollowsList",
        @Query("uid") uid: String,
        @Query("touid") touid: String,
        @Query("key") key: String = "",
        @Query("p") page: Int
    ): Response<ApiResponse<List<UserBean>>>

    @GET("api/public/")
    suspend fun getBlackList(
        @Query("s") service: String = "Appapi.User.getBlackList",
        @Query("uid") uid: String,
        @Query("touid") touid: String,
        @Query("p") page: Int
    ): Response<ApiResponse<List<UserBean>>>

    @GET("api/public/")
    suspend fun setBlack(
        @Query("s") service: String = "Appapi.User.setBlack",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("touid") touid: String
    ): Response<ApiResponse<Any>>

    @FormUrlEncoded
    @POST("api/public/")
    suspend fun updateUserInfo(
        @Query("s") service: String = "Appapi.User.updateUserInfo",
        @Field("uid") uid: String,
        @Field("token") token: String,
        @Field("user_nicename") nickname: String? = null,
        @Field("avatar") avatar: String? = null,
        @Field("sex") sex: Int? = null,
        @Field("signature") signature: String? = null,
        @Field("birthday") birthday: String? = null,
        @Field("city") city: String? = null
    ): Response<ApiResponse<Any>>

    // ==================== Video APIs ====================

    @GET("api/public/")
    suspend fun getRecommendVideos(
        @Query("s") service: String = "Appapi.Video.getRecommendVideos",
        @Query("uid") uid: String = "-1",
        @Query("p") page: Int = 1,
        @Query("isstart") isStart: Int = 0
    ): Response<ApiResponse<List<VideoBean>>>

    @GET("api/public/")
    suspend fun getVideoList(
        @Query("s") service: String = "Appapi.Video.getVideoList",
        @Query("uid") uid: String = "-1",
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    @GET("api/public/")
    suspend fun getNearbyVideos(
        @Query("s") service: String = "Appapi.Video.getNearby",
        @Query("uid") uid: String = "-1",
        @Query("lng") lng: Double,
        @Query("lat") lat: Double,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    @GET("api/public/")
    suspend fun getFollowVideos(
        @Query("s") service: String = "Appapi.Video.getAttentionVideo",
        @Query("uid") uid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    @GET("api/public/")
    suspend fun getHomeVideos(
        @Query("s") service: String = "Appapi.Video.getHomeVideo",
        @Query("uid") uid: String,
        @Query("touid") touid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    @GET("api/public/")
    suspend fun getLikeVideos(
        @Query("s") service: String = "Appapi.User.getLikeVideos",
        @Query("uid") uid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    @GET("api/public/")
    suspend fun setVideoLike(
        @Query("s") service: String = "Appapi.Video.addLike",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("videoid") videoId: String
    ): Response<ApiResponse<Any>>

    @GET("api/public/")
    suspend fun setVideoShare(
        @Query("s") service: String = "Appapi.Video.setShare",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("videoid") videoId: String
    ): Response<ApiResponse<Any>>

    @GET("api/public/")
    suspend fun getViewRecord(
        @Query("s") service: String = "Appapi.Video.GetViewRecord",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    // ==================== Comment APIs ====================

    @GET("api/public/")
    suspend fun getVideoComments(
        @Query("s") service: String = "Appapi.Video.getCommentList",
        @Query("uid") uid: String = "-1",
        @Query("videoid") videoId: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<CommentBean>>>

    @FormUrlEncoded
    @POST("api/public/")
    suspend fun addComment(
        @Query("s") service: String = "Appapi.Video.setComment",
        @Field("uid") uid: String,
        @Field("token") token: String,
        @Field("videoid") videoId: String,
        @Field("content") content: String,
        @Field("parentid") parentId: String = "0",
        @Field("touid") touid: String = "",
        @Field("at_info") atInfo: String = ""
    ): Response<ApiResponse<Any>>

    @GET("api/public/")
    suspend fun setCommentLike(
        @Query("s") service: String = "Appapi.Video.commentLike",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("commentid") commentId: String
    ): Response<ApiResponse<Any>>

    // ==================== Message APIs ====================

    @GET("api/public/")
    suspend fun getLastMessage(
        @Query("s") service: String = "Appapi.Message.getLastTime",
        @Query("uid") uid: String
    ): Response<ApiResponse<MessageLastBean>>

    @GET("api/public/")
    suspend fun getFansMessages(
        @Query("s") service: String = "Appapi.Message.fansLists",
        @Query("uid") uid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<MessageFansBean>>>

    @GET("api/public/")
    suspend fun getZanMessages(
        @Query("s") service: String = "Appapi.Message.praiseLists",
        @Query("uid") uid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<MessageZanBean>>>

    @GET("api/public/")
    suspend fun getCommentMessages(
        @Query("s") service: String = "Appapi.Message.commentLists",
        @Query("uid") uid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<MessageCommentBean>>>

    @GET("api/public/")
    suspend fun getAtMessages(
        @Query("s") service: String = "Appapi.Message.atLists",
        @Query("uid") uid: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<MessageCommentBean>>>

    // ==================== Search APIs ====================

    @GET("api/public/")
    suspend fun searchUser(
        @Query("s") service: String = "Appapi.Home.search",
        @Query("uid") uid: String = "-1",
        @Query("key") key: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<SearchUserBean>>>

    @GET("api/public/")
    suspend fun searchVideo(
        @Query("s") service: String = "Appapi.Home.videoSearch",
        @Query("uid") uid: String = "-1",
        @Query("key") key: String,
        @Query("p") page: Int = 1
    ): Response<ApiResponse<List<VideoBean>>>

    // ==================== Config APIs ====================

    @GET("api/public/")
    suspend fun getConfig(
        @Query("s") service: String = "Appapi.Home.getConfig"
    ): Response<ApiResponse<ConfigBean>>

    @GET("api/public/")
    suspend fun getSlideLists(
        @Query("s") service: String = "Appapi.Message.getSlideLists"
    ): Response<ApiResponse<List<SlideBean>>>

    // ==================== Cash APIs ====================

    @GET("api/public/")
    suspend fun getProfit(
        @Query("s") service: String = "Appapi.Cash.GetProfit",
        @Query("uid") uid: String,
        @Query("token") token: String
    ): Response<ApiResponse<ProfitBean>>

    @GET("api/public/")
    suspend fun getCashAccountList(
        @Query("s") service: String = "Appapi.Cash.GetAccountList",
        @Query("uid") uid: String,
        @Query("token") token: String
    ): Response<ApiResponse<List<CashAccountBean>>>

    @GET("api/public/")
    suspend fun setCash(
        @Query("s") service: String = "Appapi.Cash.setCash",
        @Query("uid") uid: String,
        @Query("token") token: String,
        @Query("money") money: String,
        @Query("accountid") accountId: String
    ): Response<ApiResponse<Any>>

    companion object {
        private const val BASE_URL = BuildConfig.API_BASE_URL

        fun create(): ApiService {
            val logging = HttpLoggingInterceptor().apply {
                level = if (BuildConfig.DEBUG) {
                    HttpLoggingInterceptor.Level.BODY
                } else {
                    HttpLoggingInterceptor.Level.NONE
                }
            }

            val client = OkHttpClient.Builder()
                .addInterceptor(logging)
                .connectTimeout(60, TimeUnit.SECONDS)
                .readTimeout(60, TimeUnit.SECONDS)
                .writeTimeout(60, TimeUnit.SECONDS)
                .build()

            return Retrofit.Builder()
                .baseUrl(BASE_URL)
                .client(client)
                .addConverterFactory(GsonConverterFactory.create())
                .build()
                .create(ApiService::class.java)
        }
    }
}
