欣欣 REST API 接口 SDK
===========
#### 系统要求
* PHP 5.3 或以上
* 启用 curl 扩展

> **注:** PHP 5.3 的使用者请自行将示例代码中的 [] 换成 array() 形式。
也可以根据接口文档自行封装调用代码，PHP SDK不是使用接口必须的。
使用 PHP SDK **不**用手动获取 token。

#### 基本用法

```php
require 'src/init.php';
$client = new RestApi([
    'env'           => 'dev',  // 可选参数，默认值为 prod，正式接口使用 prod，测试接口使用 dev
    'debug_level'   => 1,  // 可选参数，调试级别(开启调试后，会在当前页面输出 debug 信息)
    'version'       => 2,  // 可选参数，API接口版本，默认值为 1
    'client_id'     => 'your_username',  // 用户名
    'client_secret' => md5('your_password'),  // 密码的 md5 值
    'urls'          => [
        'dev'   => 'https://192.168.1.158/v2',  // 测试环境地址
        'prod'  => 'https://api.cncn.com/v2',  // 正式环境地址
    ],
]);

// 调用接口
$client->get('/jingdians/13545');

// 带参数的 HTTP GET 请求
$params = [
    'zone_id'       => 3504,
    'page'          => 2,  // 可选参数
    'per_page'      => 10,  // 可选参数
    'search_key'    => '山',  // 可选参数
];
$client->get('/jingdians', $params);

// HTTP POST 请求
$params = [
    'mobile'    => 'John Doe',
    'password'  => 'cncn123456',
];
$client->post('/users/register', $params);
```

#### 高级用法
##### 自定义 token 存储
默认情况下 SDK 采用 session 作为 访问令牌的存储方式，为了方便自定义 token 存储，SDK 提供 设置 token 存储的方式。建议采用 memcache 等缓存方式存储 token。

其调用方式如下：

```php
$client->set_token_storage([
    'set'   => $set_token_func,
    'get'   => $get_token_func,
]);
```

`$set_token_func` 的函数签名如下：

```php
void function (array $token_data)
```

`$get_token_func` 的函数签名如下：

```php
array function ()
```

* 设置 session 存储方式示例
```php
$client->set_token_storage([
    'set'   => function ($token_data) {
        if (session_id() === '') {  // session 还没启用
            session_start();
        }
        $_SESSION['cncn_api_token_data'] = $token_data;
    },
    'get'   => function () {
        if (session_id() === '') {  // session 还没启用
            session_start();
        }

        return isset($_SESSION['cncn_api_token_data']) ?
            $_SESSION['cncn_api_token_data'] : [];
    },
]);
```

* 设置 memcache 存储方式示例
```php
$client->set_token_storage([
    'set'   => function ($token_data) {
        mem_set('cncn_api_token_data', $token_data);
        // mem_set 为自定义的 memcache 函数
    },
    'get'   => function () {
        return mem_get('cncn_api_token_data');
        // mem_get 为自定义的 memcache 函数
    },
]);
```

* CodeIgniter 框架缓存 access_token

如果使用的是[CodeIgniter](https://ellislab.com/codeigniter/user-guide/index.html)框架，则可使用如下方式设置 access_token 缓存：

```php
$this->load->driver('cache');
$cache = $this->cache;

$client->set_token_storage([
  'set'   => function ($token_data) use ($cache) {
      $cache->save('cncn_api_token_data', $token_data, 3600);
  },
  'get'   => function () use ($cache) {
      return $cache->get('cncn_api_token_data');
  },
]);
```
