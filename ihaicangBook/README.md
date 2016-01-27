# 爱海沧API v1.0
#### 概述
* 爱海沧API v1.0 采用 HTTP 动词(GET、POST、PATCH、DELETE)
* 使用签名加密验证的方式。
* 接口返回JSON格式的数据，接口请求采用 foo=xx&bar=yy 形式传递参数。

#### 返回数据格式
服务端返回的数据格式为JSON格式，JSON格式的结构如下：

失败时，返回如下格式的数据：

```json
{"code": -1, "msg":"失败"}
```

成功时，返回如下格式的数据：

``` json
{"code": 1, "data": "成功"}
```

参数说明：

| 参数名称 | 参数描述   | 参数类型               | 备注                                                                 |
|:---------|:-----------|:-----------------------|:---------------------------------------------------------------------|
| code     | 状态代码   | int                    | 成功时为1，失败时不为0，<br>用于区别错误信息                         |
| msg      | 错误信息   | string                 | 对错误代码的解释，出错时有该参数，<br>成功时无该参数                 |
| data     | 返回的数据 | string/int/float/array | 成功时返回的数据，失败时无此参数，<br>成功且无需返回数据时，无此参数 |

> **注:** 测试环境下，返回的 JSON 数据是格式化后的，正式环境下返回的是未格式化的。例如：
测试环境下的 JSON 示例：

```json
{
    "code": -1,
    "msg": "缺少uuid与time"
}
```

正式环境下的 JSON 示例：

```json
{"code":-1,"msg":"time\u548cuuid\u4e0d\u80fd\u4e3a\u7a7a"}
```



#### 接口地址
* 测试接口地址： http://192.168.1.158:611/
* 正式接口地址： http://59.34.148.138:611/

#### 编码
接口请求和响应均采用 UTF-8 编码。



[1]: http://en.wikipedia.org/wiki/Representational_state_transfer
[2]: http://www.ruanyifeng.com/blog/2014/05/oauth_2_0.html
[3]: http://tools.ietf.org/html/rfc6750
[4]: http://www.ruanyifeng.com/blog/2014/05/restful_api.html
[5]: https://en.wikipedia.org/wiki/HTTP_ETag
