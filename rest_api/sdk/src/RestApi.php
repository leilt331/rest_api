<?php
/**
 * This file is part of CNCN API SDK.
 * 
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @copyright Copyright (c) 2016 Xiamen Xinxin Information Technologies, Inc.
 */
namespace Cncn;

use InvalidArgumentException;

/**
 * 欣欣 OAuth2 grant_type=client_credentials 授权类型的客户端
 */
class RestApi {
    /**
     * 发送 HTTP 请求时使用的浏览器用户代理
     */
    const USER_AGENT = 'CNCN OAuth 2.0 API client';

    /**
     * 调试级别(开启调试后，会在当前页面输出 debug 信息)，支持的值：
     * + 0: 不开启调试
     * + 1: 显示 http request 和 response
     * + 2: 显示详细的 cURL debug 信息
     *
     * @var int
     */
    protected $debug_level = 0;

    /**
     * HTTP 请求客户端
     *
     * @var RestApi
     */
    protected $http_client = null;

    /**
     * API访问地址
     *
     * @var string
     */
    public $api_site = 'https://api.cncn.com/v2';

    /**
     * 访问令牌
     *
     * @var string
     */
    public $access_token = '';

    /**
     * token 存储
     *
     * @var array|object
     */
    protected $token_storage = null;

    /**
     * 用户名
     *
     * @var string
     */
    protected $username = '';

    /**
     * 密码的 md5 值
     *
     * @var string
     */
    protected $password_hash = '';

    /**
     * 构造函数
     *
     * @param array $config 配置项，支持的参数如下：
     *    - env: 可选参数，默认值为 prod，正式接口使用 prod，测试接口使用 dev
     *    - debug_level: 可选参数，调试级别(开启调试后，会在当前页面输出 debug 信息)，支持的值：
     *    - client_id: 用户名
     *    - client_secret: 密码的 md5 值
     *        + 0: 不开启调试
     *        + 1: 显示 http request 和 response
     *        + 2: 显示详细的 cURL debug 信息
     *    - version: 可选参数，API 接口版本，默认值为 1
     * @throws InvalidArgumentException 参数错误时，抛出该异常
     */
    public function __construct($config) {
        // 检查参数是否传全
        if (!isset($config['client_id']) || !isset($config['client_secret'])) {
            $class = __CLASS__;
            throw new InvalidArgumentException("class $class constructor lack of parameter(s)");
        }

        // 设置默认值
        $this->options = array_replace(array(
            'env'               => 'prod',
            'debug_level'       => 0,
            'version'           => 1,  // API 接口版本
        ), $config);

        $this->username         = $config['client_id'];
        $this->password_hash    = $config['client_secret'];
        $this->debug_level      = $this->options['debug_level'];

        $env = $this->options['env'];
        $this->api_site = $config['urls'][$env];

        $this->http_client = new Http\Client($this->debug_level);
    }

    /**
     * 设置 token 存储
     *
     * @param array $storage 获取 token 所需的 getter 和 setter
     */
    public function set_token_storage($storage) {
        $this->token_storage = $storage;
    }

    /**
     * 获取 token 存储
     */
    protected function get_token_storage() {
        if ($this->token_storage === null) {
            $this->token_storage = array(
                'get'   => function () {
                    if (session_id() === '') {  // session 还没启用
                        session_start();
                    }

                    return isset($_SESSION['cncn_api_token_data']) ?
                        $_SESSION['cncn_api_token_data'] : array();
                },
                'set'   => function ($token_data) {
                    if (session_id() === '') {  // session 还没启用
                        session_start();
                    }
                    $_SESSION['cncn_api_token_data'] = $token_data;
                }
            );
        }

        return $this->token_storage;
    }

    /**
     * 获取 token
     *
     * @return string|bool
     */
    protected function get_access_token() {
        $token_storage = $this->get_token_storage();
        $get_func = $token_storage['get'];
        $token_data = $get_func();

        if (!$token_data || !isset($token_data['expires_at']) ||
            $token_data['expires_at'] < time()) {
            $token_data = $this->set_access_token();

            if (is_array($token_data) && isset($token_data['access_token'])) {
                $set_func = $token_storage['set'];
                $set_func($token_data);
            } else {
                return false;
            }
        }

        return $token_data['access_token'];
    }

    /**
     * 获取 access token
     *
     * @param  string $username      用户名
     * @param  string $password_hash 密码的 md5 值
     * @return string|false
     */
    public function set_access_token($username = null, $password_hash = null) {
        if ($username && $password_hash) {
            $this->username = $username;
            $this->password_hash = $password_hash;
        }
        $result = $this->http_client->user_agent(self::USER_AGENT)
            ->auth_basic($this->username, $this->password_hash)
            ->set_header('Accept', 'application/json')
            ->post(
                $this->url('/token'), 
                array('grant_type' => 'client_credentials')
            );

        if ($this->debug_level) {
            echo $this->http_client->debug_info();
        }

        if ($result === false) {
            return false;
        } else {
            $data = json_decode($result, true);
            if (json_last_error()) {
                return 'Json decode error - ' . json_last_error_msg() . 
                    ', server response: ' . $result;
            }
            if (!isset($data['error']) && !isset($data['message'])) {
                $this->access_token = $data['access_token'];
            }

            return $data;
        }
    }

    /**
     * 发起 HTTP 请求
     *
     * @param  string       $http_method HTTP请求方法
     * @param  string       $path        HTTP 请求地址的访问路径
     * @param  array|string $params      HTTP body 的参数
     * @param  int          $retry       重试次数，默认为0
     * @return array|string
     */
    public function request($http_method, $path, $params = array(), $retry = 1) {
        $result = $this->http_client->user_agent(self::USER_AGENT)
            ->auth_bearer($this->get_access_token())
            ->set_header('Accept', 'application/json')
            ->request($http_method, $this->url($path), $params);
        $result = $this->parse_response($result);

        // token 验证失败时，自动重新调用 token 获取函数
        if ($result === false) {
            // 网络连接失败时，自动重试
            // http://cn2.php.net/manual/en/curl.constants.php#117928
            // 从 PHPv5.5+ 引入了常量 CURLE_OPERATION_TIMEDOUT，与
            // CURLE_OPERATION_TIMEOUTED 相同值
            if ($retry > 0 && $this->http_client->error_code() === CURLE_OPERATION_TIMEOUTED) {
                $retry--;
                return $this->request($http_method, $path, $params, $retry);
            }
        }

        return $result;
    }

    /**
     * 发起 HTTP GET 请求
     *
     * @param  string       $path        HTTP 请求地址的访问路径
     * @param  array|string $params      HTTP query string 参数
     * @return array|string
     */
    public function get($path, $params = array()) {
        if ($params) {
            $query_string = http_build_query($params);
            $connector = strpos($path, '?') !== false ? '&' : '?';
            $path .= $connector . $query_string;
        }
        return $this->request('GET', $path);
    }

    /**
     * 发起 HTTP POST 请求
     *
     * @param  string       $path        HTTP 请求地址的访问路径
     * @param  array|string $params      HTTP body 的参数
     * @return array|string
     */
    public function post($path, $params = array()) {
        return $this->request('POST', $path, $params);
    }

    /**
     * 发起 HTTP PUT 请求
     *
     * @param  string       $path        HTTP 请求地址的访问路径
     * @param  array|string $params      HTTP body 的参数
     * @return array|string
     */
    public function put($path, $params = array()) {
        return $this->request('PUT', $path, $params);
    }

    /**
     * 发起 HTTP DELETE 请求
     *
     * @param  string       $path        HTTP 请求地址的访问路径
     * @param  array|string $params      HTTP body 的参数
     * @return array|string
     */
    public function delete($path, $params = array()) {
        return $this->request('DELETE', $path, $params);
    }

    /**
     * 解析 HTTP 响应
     *
     * @param  string|false $result HTTP 响应
     * @return array|string
     */
    protected function parse_response($result) {
        if ($this->debug_level) {
            echo $this->http_client->debug_info();
            if ($result === false) {
                echo "\ncurl error: " . $this->http_client->error_info();
            }
        }

        if ($result === false) {
            return $this->http_client->error_info();
        } else {
            $data = json_decode($result, true);
            if (json_last_error()) {
                return 'Json decode error - ' . json_last_error_msg();
            } else {
                return $data;
            }
        }
    }

    /**
     * 获取 url
     *
     * @param  string $path 请求地址的访问路径
     * @return string
     */
    protected function url($uri) {
        return $this->api_site . $uri;
    }
}
