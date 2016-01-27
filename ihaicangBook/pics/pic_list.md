# 海沧图集列表

#### 请求地址



> GET /api/get_pic_list



#### 请求参数



| 参数名称 | 参数描述         | 必传 | 参数类型 | 备注           |
|:---------|:-----------------|:-----|:---------|:---------------|
| time     | 服务器时间戳     | 是   | string   | &nbsp;      |
| uuid     | 设备ID           | 是   | string   | &nbsp;      |
| zondId   | 区域ID           | 否   | string   | 不传则默认为海沧|
|pageIndex | 当前的页数       | 是   | int      |默认为第一页开始|
|pageSize  |每页显示的条数	   | 是	 | int		|&nbsp;			 |



####返回参数

| 参数名称 | 参数描述         |参数类型 | 实例值      |
|:---------|:-----------------|:-----   |:------------|
| id 	   | 图集id    	    |int      |  http://    |
| smallpic | 缩略图地址    	 |string   |  http://    |
| title    | 图集名称      	   |string   |  海沧新城   |
| bigpic   | 大图地址         |string   |  http://    |



#### 示例

* 接口调用示例

```php

http://192.168.1.158:611/api/get_pic_list

```



* 请求

```php

GET http://192.168.1.158:611/api/get_pic_list?ver=1.0&time=1452496757&sign=2DAEA24AEDBFD1F71B4548FC1044F5F8121876BFECD5930BFD0AFF71506C0931&pageSize=60&pageIndex=1&uuid=dffce830739e2092&d=android

```

* 响应

```PHP

HTTP/1.1 200 OK

Server:  nginx/1.4.2

Date:  Wed, 27 Jan 2016 02:02:12 GMT

Content-Type:  application/json;charset=utf-8

Content-Length:  1436

Connection:  keep-alive

{

    "code": 1,

    "msg": "ok",

    "data": {

        "list": [

            {

                "id": "43",

                "title": "海沧新城区",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528371377125.JPG",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528371377125.JPG"

            },

            {

                "id": "44",

                "title": "海沧行政中心",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528372354937.jpg",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528372354937.jpg"

            },

            {

                "id": "45",

                "title": "莲塘大厝",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528375197833.jpg",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528375197833.jpg"

            },

            {

                "id": "46",

                "title": "沧湾乐道",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528376753139.jpg",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528376753139.jpg"

            },

            {

                "id": "47",

                "title": "日月瑶池",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528377905592.jpg",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528377905592.jpg"

            },

            {

                "id": "48",

                "title": "天竺流翠",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528378351938.jpg",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528378351938.jpg"

            },

            {

                "id": "49",

                "title": "乡约院前",

                "smallpic": "http://59.34.148.138:610/uploads/thumb/14528378844846.jpg",

                "bigpic": "http://59.34.148.138:610/uploads/thumb/14528378844846.jpg"

            }

        ]

    }

}

```