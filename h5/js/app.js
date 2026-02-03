/**
 * TECHSPACE短视频 H5 - 通用功能
 */

const App = {
    // ==================== 安全函数 ====================

    /**
     * HTML转义 - 防止XSS攻击
     * @param {string} str 需要转义的字符串
     * @returns {string} 转义后的安全字符串
     */
    escapeHtml: function(str) {
        if (str === null || str === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    },

    /**
     * 安全的URL参数 - 防止注入
     * @param {string} str URL参数值
     * @returns {string} 编码后的安全字符串
     */
    escapeUrl: function(str) {
        if (str === null || str === undefined) return '';
        return encodeURIComponent(String(str));
    },

    /**
     * 验证是否为安全的内部URL
     * @param {string} url 需要验证的URL
     * @returns {boolean} 是否安全
     */
    isSafeUrl: function(url) {
        if (!url) return false;
        // 只允许相对路径或同域名URL
        if (url.startsWith('/') || url.startsWith('./') || url.startsWith('../')) return true;
        if (url.match(/^[a-zA-Z0-9_-]+\.html/)) return true;
        try {
            const urlObj = new URL(url, window.location.origin);
            return urlObj.origin === window.location.origin;
        } catch (e) {
            return false;
        }
    },

    /**
     * 安全跳转 - 防止开放重定向
     * @param {string} url 目标URL
     */
    safeRedirect: function(url) {
        if (this.isSafeUrl(url)) {
            location.href = url;
        } else {
            console.warn('Blocked unsafe redirect:', url);
            location.href = 'index.html';
        }
    },

    // ==================== 用户认证 ====================

    // 检查登录状态
    checkLogin: function() {
        const token = localStorage.getItem('token');
        return !!token;
    },

    // 获取当前用户信息
    getUserInfo: function() {
        const userStr = localStorage.getItem('userInfo');
        return userStr ? JSON.parse(userStr) : null;
    },

    // 退出登录
    logout: function() {
        localStorage.removeItem('token');
        localStorage.removeItem('userInfo');
        location.href = 'login.html';
    },

    // 需要登录才能操作
    requireLogin: function(callback) {
        if (this.checkLogin()) {
            callback && callback();
        } else {
            if (confirm('请先登录')) {
                location.href = 'login.html';
            }
        }
    },

    // 数字格式化
    formatNumber: function(num) {
        if (num >= 100000000) {
            return (num / 100000000).toFixed(1) + '亿';
        } else if (num >= 10000) {
            return (num / 10000).toFixed(1) + 'w';
        }
        return num;
    },

    // 时间格式化
    formatTime: function(timestamp) {
        const now = Date.now() / 1000;
        const diff = now - timestamp;

        if (diff < 60) {
            return '刚刚';
        } else if (diff < 3600) {
            return Math.floor(diff / 60) + '分钟前';
        } else if (diff < 86400) {
            return Math.floor(diff / 3600) + '小时前';
        } else if (diff < 604800) {
            return Math.floor(diff / 86400) + '天前';
        } else {
            const date = new Date(timestamp * 1000);
            return (date.getMonth() + 1) + '-' + date.getDate();
        }
    },

    // 显示Toast提示
    toast: function(msg, duration = 2000) {
        let toast = document.getElementById('_toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = '_toast';
            toast.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                padding: 12px 24px;
                background: rgba(0,0,0,0.8);
                color: #fff;
                border-radius: 8px;
                font-size: 14px;
                z-index: 10000;
                max-width: 80%;
                text-align: center;
            `;
            document.body.appendChild(toast);
        }

        toast.textContent = msg;
        toast.style.display = 'block';

        setTimeout(() => {
            toast.style.display = 'none';
        }, duration);
    },

    // 显示加载中
    showLoading: function(msg = '加载中...') {
        let loading = document.getElementById('_loading');
        if (!loading) {
            loading = document.createElement('div');
            loading.id = '_loading';
            loading.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                    <div style="background: #1f1f1f; padding: 20px 30px; border-radius: 12px; text-align: center;">
                        <div class="loading-spinner" style="width: 30px; height: 30px; border: 2px solid #333; border-top-color: #fe2c55; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto;"></div>
                        <div style="margin-top: 12px; color: #fff; font-size: 14px;" id="_loadingText">加载中...</div>
                    </div>
                </div>
            `;
            document.body.appendChild(loading);

            // 添加动画样式
            if (!document.getElementById('_spinStyle')) {
                const style = document.createElement('style');
                style.id = '_spinStyle';
                style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
                document.head.appendChild(style);
            }
        }

        document.getElementById('_loadingText').textContent = msg;
        loading.style.display = 'block';
    },

    // 隐藏加载中
    hideLoading: function() {
        const loading = document.getElementById('_loading');
        if (loading) {
            loading.style.display = 'none';
        }
    },

    // 下拉刷新
    initPullRefresh: function(element, callback) {
        let startY = 0;
        let pulling = false;

        element.addEventListener('touchstart', (e) => {
            if (element.scrollTop === 0) {
                startY = e.touches[0].clientY;
                pulling = true;
            }
        });

        element.addEventListener('touchmove', (e) => {
            if (!pulling) return;
            const currentY = e.touches[0].clientY;
            const diff = currentY - startY;

            if (diff > 0 && diff < 100) {
                e.preventDefault();
            }
        });

        element.addEventListener('touchend', (e) => {
            if (!pulling) return;
            const currentY = e.changedTouches[0].clientY;
            const diff = currentY - startY;

            if (diff > 60) {
                callback && callback();
            }

            pulling = false;
        });
    },

    // 上拉加载更多
    initLoadMore: function(element, callback) {
        element.addEventListener('scroll', () => {
            if (element.scrollTop + element.clientHeight >= element.scrollHeight - 50) {
                callback && callback();
            }
        });
    },

    // 图片懒加载
    lazyLoadImages: function() {
        const images = document.querySelectorAll('img[data-src]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => observer.observe(img));
    },

    // 复制到剪贴板
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.toast('复制成功');
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            this.toast('复制成功');
        }
    },

    // 分享
    share: function(title, url) {
        if (navigator.share) {
            navigator.share({ title, url });
        } else {
            this.copyToClipboard(url);
            this.toast('链接已复制，快去分享吧');
        }
    },

    // 获取URL参数
    getUrlParam: function(name) {
        const params = new URLSearchParams(window.location.search);
        return params.get(name);
    },

    // 设置页面标题
    setTitle: function(title) {
        document.title = title;
    },

    // 防抖
    debounce: function(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    // 节流
    throttle: function(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// 全局错误处理
window.onerror = function(msg, url, line) {
    console.error('Error:', msg, 'at', url, 'line', line);
    return false;
};

// 导出
if (typeof module !== 'undefined' && module.exports) {
    module.exports = App;
}
