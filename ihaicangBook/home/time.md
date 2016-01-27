#请求服务器时间

因为爱海沧API是使用签名加密验证的，所以app端首先要请求加密必须参数unid（服务器时间戳）
#### 请求接口

> GET  /api/get_time

#### 请求参数
      无

#### 响应参数
| 参数名称 |   参数描述       |参数类型 | 实例值      |
|:---------|:-----------------|:-----   |:------------|
| unix     | uuix时间戳       |string |  1441087840   |

#### 示例
* 接口调用示例
```php
http://192.168.1.158:611/api/get_time
```

*  请求
```php
GET http://192.168.1.158:611/api/get_time
```
* 响应
```php
HTTP/1.1 200 OK
Server: nginx/1.9.5
Date: Tue, 22 Dec 2015 05:21:49 GMT
Content-Type: application/json;charset=utf-8
Content-Length: 2661
Connection: keep-alive
{
    "code": 1,
    "msg": "ok",
    "data": {
        "unix": 1453802066
    }
}
```
