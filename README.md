# Task 任务API接口 [Install](https://www.easyswoole.com/QuickStart/install.html)  C
- easyswoole PHP7.3 ->  swoole     [online](https://api.xxxxx.xyz/api/v1)
    - composer install & composer update
- websocket PHP7.3 ->  swoole  EasySwooleEvent_websocket.php 另开服务
  - wss://api.xxxxx.xyz/?token=xxx&phone=xxx

## 启动与重启 start & restart & stop & -d 常驻 
- php easyswoole server -h
- php easyswoole server start --mode=dev 
- php easyswoole server restart --mode=dev -force

## 生成接口文档 
1. **apidoc -> npm install -g apidoc** 
    - apidoc -i App/HttpController/ -o public/DOC/
## DOMAIN 域名
- https://api.xxxxx.xyz/api/v1 接口线上地址 & wss://api.xxxxx.xyz WEBSOCKET线上地址
- https://invite.xxxxx.xyz/  邀请码页面
  - FTP  119.28.92.238  dc   dc123456   /data/php/project/h5
- https://h5.xxxxx.xyz/
  - FTP  119.28.92.238  dc2   dc123456   /data/php/project/h5_2

- https://imagea.xxxxx.xyz/ & laravel图片显示   nginx配置指定对应目录上
- https://imageb.xxxxx.xyz/ & swoole项目图片 nginx配置指定对应目录上



### NGINX 配置参考

```
upstream easyswoole_ws {
    # 将负载均衡模式设置为IP hash，作用：不同的客户端每次请求都会与同一节点进行交互。
    ip_hash;
    server 10.0.0.3:9601;
}

upstream easyswoole_http {
    # 将负载均衡模式设置为IP hash，作用：不同的客户端每次请求都会与同一节点进行交互。
    ip_hash;
    server 10.0.0.3:9501;
}
server {

    server_name api.xxxxx.xyz;
    if ($ssl_protocol = "") { return 301 https://$host$request_uri; }
    location /api/v1 {
        client_max_body_size 100M;
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
        add_header Access-Control-Allow-Headers 'DNT,X-Mx-ReqToken,Keep-Alive,token,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Authorization';
        if ($request_method = 'OPTIONS') {
            return 204;
        }
        # 将客户端host及ip信息转发到对应节点 
        proxy_set_header Host $http_host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        # 转发Cookie，设置 SameSite
        proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";
        # 代理访问真实服务器
        proxy_pass http://easyswoole_http;
    }

    location / {
        # websocket的header
        proxy_http_version 1.1;
        # 升级http1.1到websocket协议
        proxy_set_header Upgrade websocket;
        proxy_set_header Connection "Upgrade";
        # 将客户端host及ip信息转发到对应节点  
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        # 客户端与服务端60s之内无交互，将自动断开连接。
        proxy_read_timeout 60s ;
        proxy_pass http://easyswoole_ws;
    }
    location ~ /\.ht {
        deny all;
    }



}
```