#获取首页数据
#### 请求地址
> GET /api/get_homepage

####接口参数
| 参数名称 | 参数描述         | 必传 | 参数类型 | 实例值         |
|:---------|:-----------------|:-----|:---------|:---------------|
| time     | 服务器时间戳     | 是   | string   | 1441087840     |
| uuid     | 设备Id           | 是   | string   | 123456789      |
#### 返回参数
| 参数名称        | 参数描述         |参数类型 | 实例值      |
|:----------------|:-----------------|:-----   |:------------|
| knowhc          | 了解海沧     |string   |  http://    |
| weatherIcon     | 图标         |string   |  http://   |
| temperature     | 温度         |int   |   25°c   |
| foods           | 美食         |string   |  http：//   |
| ships           | 购物休闲     |string   |  http://    |
| travels 		  | 旅游    	   |string   |  http://    |
| hotels    	  | 酒店         |string   |  http://   |
| hcTraffic   	  | 海沧交通     |string   |  http://    |
| hcBicycle 	  | 自行车    	  |string   |  http://    |
| hcBicycleHelp   | 自行车帮助说明|string   |  http://   |
| hcBus           | 公交车       |string   |  http://    |
| hcTaxi 		  | 出租车    	  |string   |  http://    |
| affairs         | 政务         |string   |  http://   |
| appoint         | 办事预约     |string   |  http://    |
| offerNumber     | 排队取号     |string   |  http://    |
| chat            | 即时咨询     |string   |  http://   |
| news   		  | 新闻资讯     |string   |  http://    |
| hospitalAppoint | 医院预约     |string   |  http://    |
| telAppoint      | 预约电话     |string   |  http://      |
| sinaWeibo       | 微博互动     |string   |  http://    |
####示例
* 接口调用示例
```php
http://192.168.1.158:611/api/get_homepage
```

* 请求
```php
GET http://192.168.1.158:611/api/get_homepage?ver=1.0&time=1452496757&sign=2DAEA24AEDBFD1F71B4548FC1044F5F8121876BFECD5930BFD0AFF71506C0931&uuid=dffce830739e2092&d=android
```

* 响应
```php
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
        "knowhc": "http://u.eqxiu.com/s/NGOgihQM",
        "weatherIcon": "http://59.34.148.138:610/uploads/icon/14522157574750.png",
        "temperature": "10",
        "foods": "http://i.meituan.com/xiamen?cid=1&cateType=poi",
        "ships": "http://i.meituan.com/xiamen?cid=2&cateType=poi&stid_b=1&cevent=imt%2Fhomepage%2Fcategory2%2F2",
        "travels": "http://m.cncn.com/jingdian/xiamen/haicang",
        "hotels": "http://i.meituan.com/hotel/?stid_b=1&cevent=imt%2Fhomepage%2Fcategory1%2F20",
        "hcBicycle": "http://map.haicang.gov.cn/HCCICMS/map/hcpb.html?lat=24.48229&lng=118.03694&zoomlevel=13",
        "hcBicycleHelp": "",
        "hcBus": "http://mybus.xiamentd.com/",
        "hcTaxi": "http://wap.xm968890.com/Taxi/CallCar.aspx",
        "affairs": "http://weixin.haicang.gov.cn/weixin2.php/workservice/channel",
        "appoint": "http://weixin.haicang.gov.cn/weixin2.php/workreserve/",
        "offerNumber": "http://weixin.haicang.gov.cn/weixin2.php/app/detail/id/1.html",
        "chat": "http://www.haicang.gov.cn:8116/chatwindowforjszx?to=zhengwucenter",
        "news": "http://m.haicang.gov.cn/",
        "hospitalAppoint": "http://wechat.xmsmjk.com/zycapwxsehr/view/appointment/hospitalList.jsp",
        "telAppoint": "96166",
        "sinaWeibo": "http://m.weibo.cn/p/100808c486c4fbc1b210be80e7451823233479"
    }
}
```