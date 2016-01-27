## 获取访问令牌
使用 HTTP Basic 认证方式，发送 Oauth 2.0 客户端模式的令牌申请。
#### 请求地址

> POST /v2/token

#### 请求参数
HTTP请求头包含 `<Client-Id>:<Client-Secret>` 作为 HTTP Basic 认证的凭证。`<Client-Id>`为用户名，`<Client-Secret>`为密码的md5值。

| 参数名称   | 参数描述 | 参数类型 | 必传 | 备注                        |
|:-----------|:---------|:---------|:-----|:----------------------------|
| grant_type | 授权类型 | string   | 是   | 值固定为 client_credentials |

#### 返回参数

成功时返回参数：

| 参数名称     | 参数描述     | 参数类型 | 备注                                                     |
|:-------------|:-------------|:---------|:---------------------------------------------------------|
| access_token | 访问令牌     | string   | 长度为32的字符串                                         |
| token_type   | 令牌类型     | string   | 值固定为 Bearer                                          |
| expires_in   | 缓存时间     | int      | 单位为秒，默认为1小时，即 3600秒                         |
| expires_at   | 缓存过期时间 | int      | 缓存在哪个时间点过期，即 当前UNIX时间戳 + <br>expires_in |

失败时返回参数：

| 参数名称          | 参数描述 | 参数类型 | 备注           |
|:------------------|:---------|:---------|:---------------|
| error             | 错误代码 | string   |                |
| error_description | 错误信息 | string   | 详细的中文说明 |

可能的错误代码及其值有：

| error                  | error_description    |
|:-----------------------|:---------------------|
| unsupported_grant_type | 不支持该种验证类型   |
| invalid_request        | 用户名和密码不能为空 |
| invalid_client         | 用户名或密码错误     |
| unauthorized_client    | 您的IP无权限访问接口 |


#### 示例
* 请求

```http
POST /v2/token HTTP/1.1
Host: 192.168.1.158:883
Authorization: Basic am9lbGh5OmE2MDIzMGI1OTA3M2FkODMwODBhNGE1MzExODViYWRi
User-Agent: CNCN OAuth 2.0 API client
Accept: application/json
Content-Length: 29
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
```

* 响应

成功响应：

```http
HTTP/1.1 200 OK
Server: nginx/1.8.0
Date: Tue, 22 Dec 2015 05:21:49 GMT
Content-Type: application/json;charset=utf-8
Content-Length: 140
Connection: keep-alive
Cache-Control: no-store
Pragma: no-cache

{
    "access_token": "435ff6c5415d33ab2c3dbe053adf378e",
    "token_type": "Bearer",
    "expires_in": 3600,
    "expires_at": 1450765309
}
```

失败响应：

```http
HTTP/1.1 400 Bad Request
Server: nginx/1.8.0
Date: Tue, 22 Dec 2015 11:46:27 GMT
Content-Type: application/json;charset=utf-8
Content-Length: 86
Connection: keep-alive

{
    "error": "invalid_client",
    "error_description": "用户名或密码错误"
}
```
