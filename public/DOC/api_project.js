define({
  "name": "TASK",
  "version": "1.0.0",
  "description": "",
  "title": "API",
  "header": {
    "title": "HEADER",
    "content": "<h3>RESPONSE CODE</h3>\n<p><strong>/api/v1/user/login &amp; /api/v1/user/register' 除以上接口,其它接口参数中header都需带上TOKEN参数</strong></p>\n<ol>\n<li>\n<p>API_RESPONSE_CODE   1000以下HTTP常用    1000以上当前系统定义</p>\n<ul>\n<li>200 正常响应返回数据</li>\n<li>302 重定向操作</li>\n<li>400 错误返回</li>\n<li>403 权限拒绝,非法请求接口操作. FLAG标记用户</li>\n<li>404 NOT FOUND</li>\n<li>500 服务器异常</li>\n</ul>\n</li>\n<li>\n<p>API_RESPONSE_SYSTEM_CODE</p>\n<ul>\n<li>1000 用户登录态过期,跳转到登录页面处理</li>\n<li>1001 手机短信发送间隔处理</li>\n</ul>\n</li>\n<li>\n<p>API_WEB_SOCKET_RESPONSE_CODE websocket长链接返回消息定义 ws://192.168.3.34:9601/?token=x&amp;phone=x</p>\n<ul>\n<li>\n<p>200 链接成功</p>\n</li>\n<li>\n<p>400 token错误或者其它. 重试操作</p>\n</li>\n<li>\n<p>{&quot;title&quot;:&quot;register_give&quot;,&quot;content&quot;:&quot;register give success 100&quot;,&quot;created_at&quot;:&quot;2021-01-18 16:12:03&quot;,&quot;announce_id&quot;:10001}</p>\n</li>\n</ul>\n<p>3.1 announce_id  10001全局弹窗处理</p>\n</li>\n</ol>\n"
  },
  "url": "http://192.168.3.34:9501",
  "sampleUrl": false,
  "defaultVersion": "0.0.0",
  "apidoc": "0.3.0",
  "generator": {
    "name": "apidoc",
    "time": "2021-01-18T09:50:58.315Z",
    "url": "https://apidocjs.com",
    "version": "0.25.0"
  }
});
