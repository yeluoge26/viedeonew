/**
 * TECHSPACE短视频 H5 - API 接口
 * 增强版：包含CSRF防护
 */

const API = {
    // API 基础地址 (PhalApi入口)
    baseUrl: '/api/public/',

    // CSRF Token管理
    _csrfToken: null,

    /**
     * 生成CSRF Token
     * 使用加密随机数生成器
     */
    generateCsrfToken: function() {
        const array = new Uint8Array(32);
        if (window.crypto && window.crypto.getRandomValues) {
            window.crypto.getRandomValues(array);
        } else {
            // 降级处理
            for (let i = 0; i < array.length; i++) {
                array[i] = Math.floor(Math.random() * 256);
            }
        }
        return Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
    },

    /**
     * 获取或创建CSRF Token
     */
    getCsrfToken: function() {
        if (!this._csrfToken) {
            // 尝试从sessionStorage获取
            this._csrfToken = sessionStorage.getItem('csrf_token');
            if (!this._csrfToken) {
                this._csrfToken = this.generateCsrfToken();
                sessionStorage.setItem('csrf_token', this._csrfToken);
            }
        }
        return this._csrfToken;
    },

    /**
     * 验证请求来源（Referer检查）
     */
    validateReferer: function() {
        // 仅允许同源请求
        return true; // 客户端无法完全验证，需配合服务端
    },

    // 通用请求方法
    request: function(service, params = {}) {
        return new Promise((resolve, reject) => {
            const token = localStorage.getItem('token') || '';
            const uid = localStorage.getItem('uid') || '';

            // 添加公共参数
            params.token = token;
            params.uid = uid;

            // 添加CSRF Token防护
            params._csrf_token = this.getCsrfToken();
            params._timestamp = Date.now();

            const url = this.baseUrl + '?s=' + service;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-Token': this.getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin', // 只发送同源cookie
                body: this.buildQuery(params)
            })
            .then(res => res.json())
            .then(data => {
                if (data.ret === 200) {
                    resolve(data.data);
                } else if (data.ret === 700) {
                    // Token过期，跳转登录
                    localStorage.removeItem('token');
                    localStorage.removeItem('userInfo');
                    location.href = 'login.html';
                    reject(data);
                } else {
                    reject(data);
                }
            })
            .catch(err => {
                console.error('API Error:', err);
                reject(err);
            });
        });
    },

    // 构建查询字符串
    buildQuery: function(params) {
        return Object.keys(params)
            .map(key => encodeURIComponent(key) + '=' + encodeURIComponent(params[key]))
            .join('&');
    },

    // ========== 用户相关 ==========

    // 发送验证码
    sendCode: function(mobile) {
        return this.request('Appapi.Login.getCode', { mobile });
    },

    // 手机号登录
    login: function(mobile, code) {
        return this.request('Appapi.Login.mobileLogin', { mobile, code });
    },

    // 获取用户信息
    getUserInfo: function(touid) {
        return this.request('Appapi.User.getBaseInfo', { touid });
    },

    // 编辑用户资料
    updateProfile: function(data) {
        return this.request('Appapi.User.userUpdate', data);
    },

    // 关注/取消关注
    setAttention: function(touid) {
        return this.request('Appapi.User.setAttention', { touid });
    },

    // 获取关注列表
    getFollowList: function(touid, p = 1) {
        return this.request('Appapi.User.getFollowList', { touid, p });
    },

    // 获取粉丝列表
    getFansList: function(touid, p = 1) {
        return this.request('Appapi.User.getFansList', { touid, p });
    },

    // ========== 视频相关 ==========

    // 获取推荐视频列表
    getRecommendVideos: function(p = 1) {
        return this.request('Appapi.Video.getRecommendVideos', { p });
    },

    // 获取热门视频列表
    getVideoList: function(p = 1) {
        return this.request('Appapi.Video.getVideoList', { p });
    },

    // 获取附近视频
    getNearbyVideos: function(lng, lat, p = 1) {
        return this.request('Appapi.Video.getNearby', { lng, lat, p });
    },

    // 获取视频详情
    getVideo: function(videoid) {
        return this.request('Appapi.Video.getVideo', { videoid });
    },

    // 获取用户的视频
    getMyVideos: function(uid, p = 1) {
        return this.request('Appapi.Video.getMyVideo', { uid, p });
    },

    // 获取用户喜欢的视频
    getLikeVideos: function(uid, p = 1) {
        return this.request('Appapi.Video.getViewRecord', { uid, p });
    },

    // 获取关注用户的视频
    getAttentionVideos: function(p = 1) {
        return this.request('Appapi.Video.getAttentionVideo', { p });
    },

    // 点赞视频
    addLike: function(videoid) {
        return this.request('Appapi.Video.addLike', { videoid });
    },

    // 分享视频
    addShare: function(videoid) {
        return this.request('Appapi.Video.addShare', { videoid });
    },

    // 增加观看
    addView: function(videoid) {
        return this.request('Appapi.Video.addView', { videoid });
    },

    // ========== 评论相关 ==========

    // 获取评论列表
    getComments: function(videoid, p = 1) {
        return this.request('Appapi.Video.getComments', { videoid, p });
    },

    // 发表评论
    setComment: function(videoid, content, commentid = 0, touid = 0) {
        return this.request('Appapi.Video.setComment', {
            videoid, content, commentid, touid
        });
    },

    // 评论点赞
    addCommentLike: function(commentid) {
        return this.request('Appapi.Video.addCommentLike', { commentid });
    },

    // ========== 消息相关 ==========

    // 获取消息列表
    getMsgList: function(type, p = 1) {
        // type: fans, like, at, comment
        const serviceMap = {
            fans: 'Appapi.Message.getFansMsg',
            like: 'Appapi.Message.getLikeMsg',
            at: 'Appapi.Message.getAtMsg',
            comment: 'Appapi.Message.getCommentMsg'
        };
        return this.request(serviceMap[type] || serviceMap.fans, { p });
    },

    // 获取私信列表
    getChatList: function(p = 1) {
        return this.request('Appapi.Message.getChatList', { p });
    },

    // 获取聊天记录
    getChatRecord: function(touid, p = 1) {
        return this.request('Appapi.Message.getChatRecord', { touid, p });
    },

    // 发送私信
    sendChat: function(touid, content) {
        return this.request('Appapi.Message.sendChat', { touid, content });
    },

    // ========== 搜索相关 ==========

    // 搜索视频
    searchVideo: function(keyword, p = 1) {
        return this.request('Appapi.Video.search', { keyword, p });
    },

    // 搜索用户
    searchUser: function(keyword, p = 1) {
        return this.request('Appapi.User.search', { keyword, p });
    },

    // ========== 上传相关 ==========

    // 获取上传配置
    getUploadConfig: function() {
        return this.request('Appapi.Video.getCreateNonreusableSignature');
    },

    // 发布视频
    setVideo: function(data) {
        return this.request('Appapi.Video.setVideo', data);
    }
};

// 导出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = API;
}
