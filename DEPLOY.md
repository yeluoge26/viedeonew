# 短视频项目宝塔部署文档

## 一、服务器环境要求

### 1.1 硬件配置（推荐）
- CPU: 2核及以上
- 内存: 4GB及以上
- 硬盘: 50GB及以上（视频存储需要更大空间）
- 带宽: 5Mbps及以上

### 1.2 系统要求
- 操作系统: CentOS 7.x / Ubuntu 18.04+
- 宝塔面板: 7.x 或更高版本

---

## 二、宝塔面板安装

### 2.1 安装宝塔面板

**CentOS:**
```bash
yum install -y wget && wget -O install.sh https://download.bt.cn/install/install_6.0.sh && sh install.sh ed8484bec
```

**Ubuntu/Debian:**
```bash
wget -O install.sh https://download.bt.cn/install/install-ubuntu_6.0.sh && sudo bash install.sh ed8484bec
```

### 2.2 登录宝塔面板
安装完成后，访问面板地址（安装时会显示），使用默认账号密码登录。

---

## 三、安装必要软件

登录宝塔面板后，进入【软件商店】，安装以下软件：

### 3.1 LNMP环境（推荐）
| 软件 | 版本要求 | 说明 |
|------|----------|------|
| Nginx | 1.20+ | Web服务器 |
| MySQL | 5.7+ / 8.0 | 数据库 |
| PHP | 7.4 / 8.0 | 后端运行环境 |
| Redis | 6.0+ | 缓存（可选） |
| phpMyAdmin | 最新版 | 数据库管理工具 |

### 3.2 PHP扩展安装
进入【软件商店】→【PHP设置】→【安装扩展】，安装以下扩展：
- fileinfo（文件信息）
- redis（如果使用Redis）
- imagemagick 或 gd（图片处理）
- opcache（性能优化）
- curl
- mbstring
- json

---

## 四、创建网站

### 4.1 创建API站点（PHP后端）

1. 进入【网站】→【添加站点】
2. 填写信息：
   - 域名: `api.yourdomain.com`（或使用IP:端口）
   - 根目录: `/www/wwwroot/shortvideo/api/public`
   - PHP版本: 选择已安装的PHP版本
   - 数据库: 创建新数据库

### 4.2 创建H5站点（前端）

1. 进入【网站】→【添加站点】
2. 填写信息：
   - 域名: `h5.yourdomain.com`（或 `www.yourdomain.com`）
   - 根目录: `/www/wwwroot/shortvideo/h5`
   - 纯静态: 选择"纯静态"

---

## 五、上传项目文件

### 5.1 上传方式

**方式一：通过宝塔文件管理器**
1. 进入【文件】
2. 导航到 `/www/wwwroot/`
3. 创建 `shortvideo` 目录
4. 上传项目压缩包并解压

**方式二：通过Git拉取**
```bash
cd /www/wwwroot/
git clone https://github.com/yeluoge26/viedeonew.git shortvideo
```

**方式三：通过SFTP/FTP**
使用FileZilla等工具上传

### 5.2 目录结构
```
/www/wwwroot/shortvideo/
├── api/                    # PHP后端
│   ├── public/            # 网站根目录（Nginx指向这里）
│   │   └── index.php
│   ├── application/       # 应用代码
│   ├── config/            # 配置文件
│   └── runtime/           # 运行时目录
├── h5/                     # H5前端
│   ├── index.html
│   ├── css/
│   ├── js/
│   └── ...
└── uploads/                # 上传文件目录
    ├── video/
    ├── image/
    └── avatar/
```

---

## 六、配置后端

### 6.1 数据库配置

1. 导入数据库SQL文件（如果有）：
   - 进入【数据库】→ 选择数据库 →【导入】
   - 上传并导入SQL文件

2. 修改数据库配置文件：
```bash
# 编辑配置文件（路径根据实际框架调整）
vi /www/wwwroot/shortvideo/api/config/database.php
```

配置内容示例：
```php
<?php
return [
    'type'     => 'mysql',
    'hostname' => '127.0.0.1',
    'database' => 'your_database_name',
    'username' => 'your_username',
    'password' => 'your_password',
    'hostport' => '3306',
    'charset'  => 'utf8mb4',
    'prefix'   => 'sv_',
];
```

### 6.2 目录权限设置

```bash
# 设置目录权限
cd /www/wwwroot/shortvideo

# 设置运行时目录权限
chmod -R 755 api/runtime
chown -R www:www api/runtime

# 设置上传目录权限
chmod -R 755 uploads
chown -R www:www uploads

# 如果使用ThinkPHP，还需要：
chmod -R 755 api/public/uploads
```

或在宝塔面板中操作：
1. 进入【文件】
2. 找到对应目录，右键 →【权限】
3. 设置权限为 755，所有者为 www

---

## 七、Nginx配置

### 7.1 API站点配置

进入【网站】→ 选择API站点 →【设置】→【配置文件】，修改配置：

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    root /www/wwwroot/shortvideo/api/public;
    index index.php index.html;

    # 上传文件大小限制（视频上传需要较大限制）
    client_max_body_size 500M;

    # URL重写（ThinkPHP/Laravel等框架需要）
    location / {
        if (!-e $request_filename) {
            rewrite ^(.*)$ /index.php?s=$1 last;
            break;
        }
    }

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi-74.sock;  # 根据PHP版本调整
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态资源缓存
    location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|mp4|mp3)$ {
        expires 30d;
        access_log off;
    }

    # 禁止访问敏感文件
    location ~ /\.(ht|git|svn) {
        deny all;
    }

    # 日志
    access_log /www/wwwlogs/api.yourdomain.com.log;
    error_log /www/wwwlogs/api.yourdomain.com.error.log;
}
```

### 7.2 H5站点配置

进入【网站】→ 选择H5站点 →【设置】→【配置文件】：

```nginx
server {
    listen 80;
    server_name h5.yourdomain.com;
    root /www/wwwroot/shortvideo/h5;
    index index.html;

    # 单页应用路由支持
    location / {
        try_files $uri $uri/ /index.html;
    }

    # 静态资源缓存
    location ~ .*\.(js|css|gif|jpg|jpeg|png|bmp|swf|ico|woff|woff2|ttf)$ {
        expires 7d;
        access_log off;
    }

    # Gzip压缩
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    access_log /www/wwwlogs/h5.yourdomain.com.log;
    error_log /www/wwwlogs/h5.yourdomain.com.error.log;
}
```

### 7.3 配置HTTPS（推荐）

1. 进入【网站】→ 选择站点 →【SSL】
2. 选择【Let's Encrypt】免费证书
3. 点击【申请】
4. 开启【强制HTTPS】

---

## 八、H5前端配置

### 8.1 修改API地址

编辑H5前端的API配置文件：

```bash
vi /www/wwwroot/shortvideo/h5/js/api.js
# 或
vi /www/wwwroot/shortvideo/h5/config.js
```

修改API地址：
```javascript
// 将API地址改为你的服务器地址
const API_BASE_URL = 'https://api.yourdomain.com/';
// 或
const API_BASE_URL = 'http://你的服务器IP:端口/api/public/';
```

### 8.2 上传目录映射（如需要）

如果上传文件存储在独立目录，需要配置Nginx映射：

```nginx
# 在H5站点配置中添加
location /uploads {
    alias /www/wwwroot/shortvideo/uploads;
    expires 30d;
}
```

---

## 九、防火墙设置

### 9.1 宝塔防火墙

进入【安全】，放行以下端口：
- 80 (HTTP)
- 443 (HTTPS)
- 3306 (MySQL，如需远程访问)

### 9.2 系统防火墙

```bash
# CentOS 7
firewall-cmd --permanent --add-port=80/tcp
firewall-cmd --permanent --add-port=443/tcp
firewall-cmd --reload

# Ubuntu
ufw allow 80/tcp
ufw allow 443/tcp
```

---

## 十、跨域配置（重要）

如果H5和API不在同一域名下，需要配置跨域。

### 10.1 Nginx配置跨域

在API站点配置中添加：

```nginx
location / {
    # 跨域配置
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS, PUT, DELETE';
    add_header Access-Control-Allow-Headers 'DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization,token';

    if ($request_method = 'OPTIONS') {
        return 204;
    }

    # 原有的rewrite规则
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=$1 last;
    }
}
```

### 10.2 PHP代码配置跨域

在PHP入口文件添加：
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, token');
```

---

## 十一、定时任务（可选）

如需定时清理临时文件或执行其他任务：

1. 进入【计划任务】→【添加任务】
2. 配置示例：

```bash
# 每天凌晨清理临时文件
0 3 * * * find /www/wwwroot/shortvideo/api/runtime/temp -mtime +7 -delete

# 每天凌晨清理日志
0 4 * * * find /www/wwwroot/shortvideo/api/runtime/log -mtime +30 -delete
```

---

## 十二、性能优化

### 12.1 PHP优化

进入【软件商店】→【PHP设置】→【性能调整】：
- max_execution_time: 300
- max_input_time: 300
- memory_limit: 256M
- post_max_size: 500M
- upload_max_filesize: 500M

### 12.2 MySQL优化

进入【软件商店】→【MySQL设置】→【性能调整】：
- 根据服务器内存选择预设方案
- 或手动调整 innodb_buffer_pool_size

### 12.3 开启OPcache

进入【软件商店】→【PHP设置】→【安装扩展】→ 安装 opcache

---

## 十三、常见问题

### Q1: 404错误
- 检查Nginx配置的URL重写规则
- 检查网站根目录是否正确

### Q2: 500错误
- 查看PHP错误日志：`/www/wwwlogs/站点名.error.log`
- 检查目录权限
- 检查PHP扩展是否安装

### Q3: 上传失败
- 检查 upload_max_filesize 和 post_max_size 配置
- 检查 Nginx 的 client_max_body_size
- 检查上传目录权限

### Q4: 跨域错误
- 检查跨域配置是否正确
- 确认API地址是否正确

### Q5: 数据库连接失败
- 检查数据库账号密码
- 检查数据库是否启动
- 检查防火墙是否放行3306端口

---

## 十四、Android App配置

编译Android App时，需要修改API地址：

文件位置：`mobile/shortvideo/app/src/main/java/com/techspace/shortvideo/util/Constants.kt`

```kotlin
object Constants {
    // 修改为你的服务器API地址
    const val BASE_URL = "https://api.yourdomain.com/"
}
```

然后重新编译APK：
```bash
cd mobile/shortvideo
./gradlew assembleRelease
```

---

## 十五、部署检查清单

- [ ] 宝塔面板已安装
- [ ] LNMP环境已安装
- [ ] PHP扩展已安装（fileinfo等）
- [ ] 数据库已创建并导入
- [ ] 项目文件已上传
- [ ] 目录权限已设置
- [ ] Nginx配置已修改
- [ ] API地址已配置
- [ ] HTTPS已开启（推荐）
- [ ] 跨域已配置
- [ ] 防火墙已放行
- [ ] 网站可正常访问

---

## 联系支持

如遇到问题，请检查：
1. 宝塔面板日志
2. Nginx错误日志
3. PHP错误日志
4. 浏览器控制台错误

GitHub仓库: https://github.com/yeluoge26/viedeonew
