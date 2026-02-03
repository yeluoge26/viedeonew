#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
直接部署代码到服务器 (不通过Git)
使用SFTP批量上传
"""

import paramiko
import os
import time
from concurrent.futures import ThreadPoolExecutor

SERVER = {
    'host': '139.180.192.2',
    'port': 22,
    'username': 'root',
    'password': ')r7S?(tbo-=gis9{'
}

LOCAL_PATH = r'e:\videos\new\web'
REMOTE_PATH = '/var/www/techspace'
DB_NAME = 'techspace'
DB_USER = 'techspace'
DB_PASS = 'TechSpace@2024!'

# 需要上传的目录
UPLOAD_DIRS = [
    'h5',
    'api',
    'admin',
    'application',
    'data',
    'simplewind',
    'themes',
    'PHPExcel',
    'dspIM',
]

# 需要上传的根文件
ROOT_FILES = ['index.php', 'robots.txt']

# 排除
EXCLUDE = {'.git', 'node_modules', '__pycache__', '.idea', '.vscode', 'mobile',
           '.claude', 'nul', 'deploy.py', 'deploy_fix.py', 'deploy_code.py',
           'upload_code.py', 'direct_deploy.py', 'DEPLOY.md'}
EXCLUDE_EXT = {'.pyc', '.pyo', '.log'}

class FastUploader:
    def __init__(self):
        self.ssh = None
        self.sftp = None
        self.count = 0
        self.errors = 0

    def connect(self):
        print(f"连接 {SERVER['host']}...")
        self.ssh = paramiko.SSHClient()
        self.ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self.ssh.connect(**{k: v for k, v in SERVER.items()}, timeout=30)
        self.sftp = self.ssh.open_sftp()
        print("连接成功!")

    def run(self, cmd, show=True):
        if show:
            print(f">>> {cmd[:80]}")
        stdin, stdout, stderr = self.ssh.exec_command(cmd, timeout=120)
        stdout.channel.recv_exit_status()
        out = stdout.read().decode('utf-8', errors='ignore')
        if show and out:
            print(out[:300])
        return out

    def should_skip(self, name):
        if name in EXCLUDE:
            return True
        if name.startswith('.'):
            return True
        _, ext = os.path.splitext(name)
        if ext in EXCLUDE_EXT:
            return True
        return False

    def ensure_dir(self, path):
        try:
            self.sftp.stat(path)
        except:
            parts = path.split('/')
            current = ''
            for part in parts:
                if not part:
                    continue
                current += '/' + part
                try:
                    self.sftp.stat(current)
                except:
                    try:
                        self.sftp.mkdir(current)
                    except:
                        pass

    def upload_file(self, local, remote):
        try:
            self.ensure_dir(os.path.dirname(remote).replace('\\', '/'))
            self.sftp.put(local, remote)
            self.count += 1
            if self.count % 100 == 0:
                print(f"  已上传 {self.count} 个文件...")
            return True
        except Exception as e:
            self.errors += 1
            if self.errors < 10:
                print(f"  错误: {local} - {e}")
            return False

    def upload_dir(self, local_dir, remote_dir):
        if not os.path.exists(local_dir):
            print(f"  目录不存在: {local_dir}")
            return

        for item in os.listdir(local_dir):
            if self.should_skip(item):
                continue

            local_path = os.path.join(local_dir, item)
            remote_path = f"{remote_dir}/{item}"

            if os.path.isfile(local_path):
                self.upload_file(local_path, remote_path)
            elif os.path.isdir(local_path):
                self.upload_dir(local_path, remote_path)

    def deploy(self):
        try:
            self.connect()

            print("\n" + "="*60)
            print("步骤1: 清理远程目录")
            print("="*60)
            self.run(f'rm -rf {REMOTE_PATH}/*')
            self.run(f'mkdir -p {REMOTE_PATH}')

            print("\n" + "="*60)
            print("步骤2: 上传文件")
            print("="*60)

            start_time = time.time()

            # 上传各目录
            for dirname in UPLOAD_DIRS:
                local_dir = os.path.join(LOCAL_PATH, dirname)
                remote_dir = f"{REMOTE_PATH}/{dirname}"
                print(f"\n上传 {dirname}/...")
                self.upload_dir(local_dir, remote_dir)

            # 上传根文件
            print("\n上传根目录文件...")
            for filename in ROOT_FILES:
                local_file = os.path.join(LOCAL_PATH, filename)
                if os.path.exists(local_file):
                    self.upload_file(local_file, f"{REMOTE_PATH}/{filename}")

            elapsed = time.time() - start_time
            print(f"\n上传完成: {self.count} 个文件, 耗时 {elapsed:.1f}秒")

            print("\n" + "="*60)
            print("步骤3: 配置项目")
            print("="*60)

            # 重命名IM目录
            self.run(f'mv {REMOTE_PATH}/dspIM {REMOTE_PATH}/im 2>/dev/null || true')

            # 创建运行时目录
            self.run(f'mkdir -p {REMOTE_PATH}/data/runtime')
            self.run(f'mkdir -p {REMOTE_PATH}/api/runtime')
            self.run(f'mkdir -p {REMOTE_PATH}/upload')

            # 配置数据库
            db_config = f'''<?php
return array(
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => '{DB_NAME}',
    'DB_USER' => '{DB_USER}',
    'DB_PWD' => '{DB_PASS}',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'cmf_',
    'DB_CHARSET' => 'utf8mb4',
);
'''
            self.run(f"mkdir -p {REMOTE_PATH}/data/conf")
            self.run(f"cat > {REMOTE_PATH}/data/conf/db.php << 'EOF'\n{db_config}\nEOF")

            # API数据库配置
            api_config = f'''<?php
return array(
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => '{DB_NAME}',
    'user' => '{DB_USER}',
    'password' => '{DB_PASS}',
    'port' => '3306',
    'charset' => 'utf8mb4',
);
'''
            self.run(f"mkdir -p {REMOTE_PATH}/api/config")
            self.run(f"cat > {REMOTE_PATH}/api/config/dbs.php << 'EOF'\n{api_config}\nEOF")

            print("\n" + "="*60)
            print("步骤4: 设置权限")
            print("="*60)
            self.run(f'chown -R www-data:www-data {REMOTE_PATH}')
            self.run(f'chmod -R 755 {REMOTE_PATH}')
            self.run(f'chmod -R 777 {REMOTE_PATH}/data/runtime')
            self.run(f'chmod -R 777 {REMOTE_PATH}/api/runtime')
            self.run(f'chmod -R 777 {REMOTE_PATH}/upload')

            print("\n" + "="*60)
            print("步骤5: 启动IM服务")
            print("="*60)
            self.run(f'cd {REMOTE_PATH}/im && npm install --production 2>&1 | tail -5', show=True)
            self.run('pm2 delete techspace-im 2>/dev/null || true')
            self.run(f'cd {REMOTE_PATH}/im && pm2 start s1.js --name techspace-im')
            self.run('pm2 save')

            print("\n" + "="*60)
            print("步骤6: 重启服务")
            print("="*60)
            self.run('systemctl restart php7.4-fpm')
            self.run('systemctl restart nginx')

            print("\n" + "="*60)
            print("步骤7: 测试")
            print("="*60)
            self.run('pm2 list')
            result = self.run(f'curl -s -o /dev/null -w "%{{http_code}}" http://localhost/', show=False)
            print(f"HTTP状态码: {result}")

            print("\n" + "="*60)
            print("部署完成!")
            print("="*60)
            print(f'''
【访问地址】
  http://{SERVER['host']}  (直接IP访问)
  http://semonghuang.org   (需解析域名)
  http://api.semonghuang.org

【数据库】
  名称: {DB_NAME}
  用户: {DB_USER}
  密码: {DB_PASS}

【导入数据库】
  找到SQL文件后执行:
  mysql {DB_NAME} < your_database.sql

【SSL配置】
  certbot --nginx -d semonghuang.org -d api.semonghuang.org
''')

        except Exception as e:
            print(f"错误: {e}")
            import traceback
            traceback.print_exc()
        finally:
            if self.sftp:
                self.sftp.close()
            if self.ssh:
                self.ssh.close()

if __name__ == '__main__':
    FastUploader().deploy()
