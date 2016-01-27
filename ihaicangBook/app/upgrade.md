#检查app版本信息
#### 请求地址

>	GET /api/get_upgrade

####请求参数
| 参数名称 | 参数描述         | 必传 | 参数类型 | 备注           |
|:---------|:-----------------|:-----|:---------|:---------------|
| time     | 服务器时间戳     | 是   | string   | &nbsp;      |
| uuid     | 设备Id           | 是   | string   | &nbsp;      |

####返回参数
| 参数名称 | 参数描述    |参数类型 | 实例值    |
|:---------|:------------|:-----   |:----------|
| appname  | 应用名称    |string   |  xxxx     |
| apkname  | apk名称     |string   |  xxxx     |
| verName  | 版本名称    |string   |  1.1      |
| verCode  | 版本号      |string   |  1   		|
| url      | 下载地址    |string   |  http://  |
| infoUrl  | 下载内容    |string   |  http://  |

####示例
* 接口调用示例
```PHP
http://192.168.1.158:611/api/app_upgrade
```
* 请求
```php
GET http://192.168.1.158:611/api/app_upgrade?ver=1.0&time=1452503248&sign=1C3FA0A77C8330E4F778C449E642CE733F4BC40B4D540746A71DA9C21F50DFA6&uuid=d1cc033c9b36effc&d=android
```
* 响应
```php
HTTP/1.1 200 OK
Server:  nginx/1.9.5
Date:  Wed, 27 Jan 2016 02:27:27 GMT
Content-Type:  application/json;charset=utf-8
Content-Length:  251
Connection:  keep-alive
{
    "code": 1,
    "msg": "ok",
    "data": {
        "appname": "i海沧",
        "apkname": "ihaicang",
        "vername": "1.0.1",
        "vercode": "1",
        "url": "http://59.34.148.138:610/uploads/apk/ihaicang(Bate)_1.0_1.apk",
        "infoUrl": "http://59.34.148.138:611/uploads/apkinfo/info.txt"
    }
}
````
