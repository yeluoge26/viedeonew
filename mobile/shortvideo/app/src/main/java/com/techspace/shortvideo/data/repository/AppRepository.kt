package com.techspace.shortvideo.data.repository

import com.techspace.shortvideo.data.api.ApiService
import com.techspace.shortvideo.data.model.*
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext

class AppRepository(private val api: ApiService) {

    // ==================== Auth (匹配H5登录流程) ====================

    /**
     * 发送验证码 - 匹配H5的 Appapi.Login.getCode
     */
    suspend fun sendCode(mobile: String): Result<Boolean> = withContext(Dispatchers.IO) {
        try {
            val response = api.getCode(mobile = mobile)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(true)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "发送验证码失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    /**
     * 手机号+验证码登录 - 匹配H5的 Appapi.Login.mobileLogin
     */
    suspend fun login(mobile: String, code: String): Result<UserBean> = withContext(Dispatchers.IO) {
        try {
            val response = api.mobileLogin(mobile = mobile, code = code)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true && body.data?.info != null) {
                    val user = body.data.info
                    Result.success(user)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "登录失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    /**
     * 注册 - 匹配H5的 Appapi.Login.userReg
     */
    suspend fun register(mobile: String, code: String, agentCode: String = ""): Result<UserBean> = withContext(Dispatchers.IO) {
        try {
            val response = api.userReg(mobile = mobile, code = code, agentcode = agentCode)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true && body.data?.info != null) {
                    Result.success(body.data.info)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "注册失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // ==================== User ====================

    suspend fun getBaseInfo(uid: String, token: String): Result<UserBean> = withContext(Dispatchers.IO) {
        try {
            val response = api.getBaseInfo(uid = uid, token = token)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true && body.data?.info != null) {
                    Result.success(body.data.info)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取用户信息失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getUserHome(uid: String, touid: String): Result<UserHomeBean> = withContext(Dispatchers.IO) {
        try {
            val response = api.getUserHome(uid = uid, touid = touid)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true && body.data?.info != null) {
                    Result.success(body.data.info)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取用户主页失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun setAttention(uid: String, token: String, touid: String): Result<Boolean> = withContext(Dispatchers.IO) {
        try {
            val response = api.setAttention(uid = uid, token = token, touid = touid)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(true)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "关注失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getFansList(uid: String, touid: String, page: Int): Result<List<UserBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getFansList(uid = uid, touid = touid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取粉丝列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getFollowsList(uid: String, touid: String, page: Int): Result<List<UserBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getFollowsList(uid = uid, touid = touid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取关注列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // ==================== Video ====================

    suspend fun getRecommendVideos(uid: String, page: Int): Result<List<VideoBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getRecommendVideos(uid = uid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取视频列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getHotVideos(uid: String, page: Int): Result<List<VideoBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getVideoList(uid = uid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取视频列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getFollowVideos(uid: String, page: Int): Result<List<VideoBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getFollowVideos(uid = uid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取视频列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getUserVideos(uid: String, touid: String, page: Int): Result<List<VideoBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getHomeVideos(uid = uid, touid = touid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取视频列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun getLikeVideos(uid: String, page: Int): Result<List<VideoBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getLikeVideos(uid = uid, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取视频列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun setVideoLike(uid: String, token: String, videoId: String): Result<Boolean> = withContext(Dispatchers.IO) {
        try {
            val response = api.setVideoLike(uid = uid, token = token, videoId = videoId)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(true)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "点赞失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // ==================== Comment ====================

    suspend fun getVideoComments(uid: String, videoId: String, page: Int): Result<List<CommentBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.getVideoComments(uid = uid, videoId = videoId, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取评论列表失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun addComment(uid: String, token: String, videoId: String, content: String): Result<Boolean> = withContext(Dispatchers.IO) {
        try {
            val response = api.addComment(uid = uid, token = token, videoId = videoId, content = content)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(true)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "评论失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // ==================== Message ====================

    suspend fun getLastMessage(uid: String): Result<MessageLastBean> = withContext(Dispatchers.IO) {
        try {
            val response = api.getLastMessage(uid = uid)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true && body.data?.info != null) {
                    Result.success(body.data.info)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取消息失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // ==================== Search ====================

    suspend fun searchUser(uid: String, key: String, page: Int): Result<List<SearchUserBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.searchUser(uid = uid, key = key, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "搜索失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    suspend fun searchVideo(uid: String, key: String, page: Int): Result<List<VideoBean>> = withContext(Dispatchers.IO) {
        try {
            val response = api.searchVideo(uid = uid, key = key, page = page)
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true) {
                    Result.success(body.data?.info ?: emptyList())
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "搜索失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }

    // ==================== Config ====================

    suspend fun getConfig(): Result<ConfigBean> = withContext(Dispatchers.IO) {
        try {
            val response = api.getConfig()
            if (response.isSuccessful) {
                val body = response.body()
                if (body?.isSuccess == true && body.data?.info != null) {
                    Result.success(body.data.info)
                } else {
                    Result.failure(Exception(body?.errorMsg ?: "获取配置失败"))
                }
            } else {
                Result.failure(Exception("网络错误: ${response.code()}"))
            }
        } catch (e: Exception) {
            Result.failure(e)
        }
    }
}
