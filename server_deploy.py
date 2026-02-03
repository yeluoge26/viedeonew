#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
服务器端部署 - 从GitHub拉取代码
"""

import paramiko

SERVER = {
    'host': '139.180.192.2',
    'port': 22,
    'username': 'root',
    'password': ')r7S?(tbo-=gis9{'
}

GITHUB_REPO = 'https://github.com/yeluoge26/viedeonew.git'
REMOTE_PATH = '/var/www/techspace'
DB_NAME = 'techspace'
DB_USER = 'techspace'
DB_PASS = 'TechSpace@2024!'

class ServerDeploy:
    def __init__(self):
        self.ssh = None

    def connect(self):
        print(f"连接 {SERVER['host']}...")
        self.ssh = paramiko.SSHClient()
        self.ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
        self.ssh.connect(**{k: v for k, v in SERVER.items()}, timeout=30)
        print("OK")

    def run(self, cmd, timeout=300):
        print(f">>> {cmd[:80]}")
        stdin, stdout, stderr = self.ssh.exec_command(cmd, timeout=timeout)
        code = stdout.channel.recv_exit_status()
        out = stdout.read().decode('utf-8', errors='ignore')
        err = stderr.read().decode('utf-8', errors='ignore')
        if out:
            lines = out.strip().split('\n')
            for line in lines[-10:]:
                print(f"  {line[:100]}")
        if err and code != 0:
            print(f"  ERROR: {err[:200]}")
        return code, out

    def deploy(self):
        self.connect()

        print("\n=== 1. 从GitHub拉取代码 ===")
        self.run(f'rm -rf {REMOTE_PATH}/*')
        self.run(f'git clone {GITHUB_REPO} {REMOTE_PATH}', timeout=600)

        print("\n=== 2. 配置目录结构 ===")
        self.run(f'mv {REMOTE_PATH}/dspIM {REMOTE_PATH}/im 2>/dev/null || true')
        self.run(f'mkdir -p {REMOTE_PATH}/data/runtime {REMOTE_PATH}/api/runtime {REMOTE_PATH}/upload')

        print("\n=== 3. 配置数据库连接 ===")
        db_php = f'''<?php
return array(
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'localhost',
    'DB_NAME' => '{DB_NAME}',
    'DB_USER' => '{DB_USER}',
    'DB_PWD' => '{DB_PASS}',
    'DB_PORT' => '3306',
    'DB_PREFIX' => 'cmf_',
    'DB_CHARSET' => 'utf8mb4',
);'''
        self.run(f"mkdir -p {REMOTE_PATH}/data/conf")
        self.run(f"cat > {REMOTE_PATH}/data/conf/db.php << 'DBEOF'\n{db_php}\nDBEOF")

        api_php = f'''<?php
return array(
    'type' => 'mysql',
    'host' => 'localhost',
    'name' => '{DB_NAME}',
    'user' => '{DB_USER}',
    'password' => '{DB_PASS}',
    'port' => '3306',
    'charset' => 'utf8mb4',
);'''
        self.run(f"mkdir -p {REMOTE_PATH}/api/config")
        self.run(f"cat > {REMOTE_PATH}/api/config/dbs.php << 'DBEOF'\n{api_php}\nDBEOF")

        print("\n=== 4. 设置权限 ===")
        self.run(f'chown -R www-data:www-data {REMOTE_PATH}')
        self.run(f'chmod -R 755 {REMOTE_PATH}')
        self.run(f'chmod -R 777 {REMOTE_PATH}/data/runtime {REMOTE_PATH}/api/runtime {REMOTE_PATH}/upload')

        print("\n=== 5. 启动IM服务 ===")
        self.run(f'cd {REMOTE_PATH}/im && npm install --production 2>&1 | tail -3', timeout=300)
        self.run('pm2 delete techspace-im 2>/dev/null || true')
        self.run(f'cd {REMOTE_PATH}/im && pm2 start s1.js --name techspace-im')
        self.run('pm2 save')

        print("\n=== 6. 重启Web服务 ===")
        self.run('systemctl restart php7.4-fpm nginx')

        print("\n=== 7. 验证 ===")
        self.run('pm2 list')
        code, out = self.run(f'curl -s -o /dev/null -w "%{{http_code}}" http://localhost/')
        print(f"\n网站HTTP状态: {out.strip()}")

        print("\n" + "="*50)
        print("部署完成!")
        print("="*50)
        print(f'''
访问地址:
  http://{SERVER['host']}
  http://semonghuang.org (需解析域名)

数据库:
  mysql -u {DB_USER} -p'{DB_PASS}' {DB_NAME}

导入SQL (如果有):
  mysql {DB_NAME} < database.sql

配置SSL:
  certbot --nginx -d semonghuang.org -d api.semonghuang.org
''')

        self.ssh.close()

if __name__ == '__main__':
    ServerDeploy().deploy()
