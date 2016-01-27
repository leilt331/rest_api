<?php
/**
 * This file is part of CNCN API SDK.
 * 
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @copyright Copyright (c) 2016 Xiamen Xinxin Information Technologies, Inc.
 */
namespace Cncn\Http;

/**
 * HTTP 请求的客户端
 */
class Client {
    /**
     * 调试级别(开启调试后，会填充 debug 信息到 $this->debug_info)，支持的值：
     *     0 - 不开启调试
     *     1 - 显示 http request 和 response
     *     2 - 显示详细的 curl debug 信息
     *
     * @var int
     */
    protected $debug_level = 1;

    /**
     * 完整的 HTTP 请求头
     *
     * @var string
     */
    protected $request_header = '';

    /**
     * 完整的 HTTP 请求 body
     *
     * @var string
     */
    protected $request_body = '';

    /**
     * debug 信息
     *
     * @var string
     */
    protected $debug_info = '';

    /**
     * http 请求头
     *
     * @var array
     */
    protected $request_headers = array();

    /**
     * http 响应头
     *
     * @var array
     */
    protected $response_headers = array();

    /**
     * http 响应头的各行
     *
     * @var array
     */
    protected $response_header_lines = array();

    /**
     * HTTP 响应代码
     *
     * @var int
     */
    public $status_code = null;

    /**
     * CURL 返回的错误代码
     *
     * @var int
     */
    protected $error_code = 0;

    /**
     * CURL 返回的错误信息
     *
     * @var string
     */
    protected $error_info = '';

    /**
     * CURL 用户自定义选项
     *
     * @var array
     */
    protected $options = array();

    /**
     * 构造函数
     *
     * @param int $debug_level 调试级别(开启调试后，会填充 debug 信息到 $this->debug_info)，支持的值：
     *     0 - 不开启调试
     *     1 - 显示 http request 和 response
     *     2 - 显示详细的 curl debug 信息
     */
    public function __construct($debug_level = 0) {
        $this->debug_level = $debug_level;
    }

    /**
     * 获取 CURL 默认选项
     *
     * @var array
     */
    protected function default_options() {
        return array(
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.1; rv:43.0) Gecko/20100101 Firefox/43.0',
            CURLOPT_MAXREDIRS      => 5,  // 最大重定向次数
            CURLOPT_TIMEOUT        => 30,  // 接口请求的超时时间
            CURLOPT_FOLLOWLOCATION => true,  // 是否继续请求 Location header 指向的 URL 地址
            CURLOPT_HEADER         => false,  // 在输出中包含 HTTP头
            CURLOPT_RETURNTRANSFER => true,  // 以字符串形式返回 HTTP 响应，而不是在页面直接输出内容
            CURLOPT_FAILONERROR    => false,  // 在发生错误时，不返回错误页面（例如 404页面）
            CURLOPT_CONNECTTIMEOUT => 15,  // 连接超时时间
            CURLOPT_SSL_VERIFYHOST => 2,  // 2 - 检查公用名是否存在，并且是否与提供的主机名匹配
            CURLOPT_SSL_VERIFYPEER => 1,  // 网站SSL证书验证，不推荐设为0或false，设为0或false不能抵挡中间人攻击
                // ref. http://cn2.php.net/manual/en/function.curl-setopt.php#110457
                // Turning off CURLOPT_SSL_VERIFYPEER allows man in the middle (MITM) attacks, which you don't want!
            CURLOPT_CAINFO         => __DIR__ . '/cacert.pem',  // CA证书
            // 如需更新，从 http://curl.haxx.se/ca/cacert.pem 获取
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
            // ref. http://www.cnblogs.com/cfinder010/p/3192380.html
            // 如果开启了IPv6，curl默认会优先解析 IPv6，在对应域名没有 IPv6 的情况下，
            // 会等待 IPv6 dns解析失败 timeout 之后才按以前的正常流程去找 IPv4。
            // curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4) 
            // 只有在php版本5.3及以上版本，curl版本7.10.8及以上版本时，以上设置才生效。
            CURLOPT_HEADERFUNCTION => array($this, 'parse_response_header'),
            // CURLOPT_FAILONERROR == true 时，会导致出错时 parse_response_header() 不会调用
        );
    }

    /**
     * HTTP 响应头解析
     *
     * @param resource $ch CURL 资源
     * @param string $header_line HTTP 响应行
     * @return int
     */
    protected function parse_response_header($ch, $header_line) {
        if (strpos($header_line, ':') !== false) {
            $parts = explode(':', $header_line, 2);
            $parts = array_map('trim', $parts);
            list($name, $value) = $parts;

            if (isset($this->response_headers[$name])) {  // 已经存在该HTTP请求头
                if (is_array($this->response_headers[$name])) {
                    $this->response_headers[$name][] = $value;
                } else {  // 转换该请求头的值为数组形式
                    $old_value                     = $this->response_headers[$name];
                    $this->response_headers[$name] = [$old_value, $value];
                }
            } else {
                $this->response_headers[$name] = $value;
            }
        }
        $this->response_header_lines[] = $header_line;

        return strlen($header_line);  // 必须返回该行的字节数
    }

    /**
     * 设置连接超时时间
     *
     * @param int $seconds 多少秒之后超时
     * @return Client
     */
    public function set_timeout($seconds) {
        $this->options[CURLOPT_TIMEOUT] = $seconds;

        return $this;
    }

    /**
     * 设置 CURL 连接参数
     *
     * @param int   $name  参数常量
     * @param mixed $value 参数值
     * @return Client
     */
    public function set_option($name, $value) {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * 设置多个 CURL 连接参数
     *
     * @param array $options 参数数组
     * @return Client
     */
    public function set_options($options) {
        foreach ($options as $name => $value) {
            $this->options[$name] = $value;
        }

        return $this;
    }

    /**
     * HTTP Basic 权限验证
     *
     * @param  string $username 用户名
     * @param  string $password 密码
     * @return Client
     */
    public function auth_basic($username, $password) {
        $credentials                     = "$username:$password";
        $this->options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        $this->options[CURLOPT_USERPWD]  = $credentials;

        return $this;
    }

    /**
     * 设置　HTTP Authorization Bearer　头
     *
     * @param  string $access_token 访问用token
     * @return Client
     */
    public function auth_bearer($access_token) {
        $this->set_header('Authorization', 'Bearer ' . $access_token);

        return $this;
    }

    /**
     * 确保 header 的名称 是符合规范的 首字母大写由连字符分割的字符串，
     *     例如： 把不符合 HTTP 规范的 content-type 转换成 符合规范的 Content-Type
     *
     * @param  string $name
     * @return string
     */
    protected function normalize_header($name) {
        $string = ucwords(str_replace('-', ' ', $name));

        return str_replace(' ', '-', $string);
    }

    /**
     * 获取一个设置的 HTTP 请求的头
     *
     * @param  string $name
     * @return string|null
     */
    public function header($name) {
        $name = $this->normalize_header($name);
        if (isset($this->request_headers[$name])) {
            return $this->request_headers[$name];
        } else {
            return null;
        }
    }

    /**
     * 获取所有的 HTTP 请求头 键值对数组
     *
     * @return array
     */
    public function headers() {
        return $this->request_headers;
    }

    /**
     * 设置一个 HTTP 请求头
     *
     * @param  string $name
     * @param  string $value
     * @return Client
     */
    public function set_header($name, $value) {
        $name                         = $this->normalize_header($name);
        $this->request_headers[$name] = $value;

        return $this;
    }

    /**
     * 设置多个 HTTP 请求头
     *
     * @param  array $headers
     * @return Client
     */
    public function set_headers(array $headers) {
        foreach ($headers as $name => $value) {
            $name                         = $this->normalize_header($name);
            $this->request_headers[$name] = $value;
        }

        return $this;
    }

    /**
     * 获取 HTTP 请求头的各行
     *
     * @return array
     */
    public function header_lines() {
        $headers = array();
        foreach ($this->request_headers as $name => $value) {
            $headers[] = $name . ': ' . $value;
        }

        return $headers;
    }

    /**
     * 获取 HTTP 响应头
     *
     * @return string
     */
    public function response_header() {
        return implode('', $this->response_header_lines);
    }

    /**
     * 设置 HTTP referer
     *
     * @param  string $value
     * @return Client
     */
    public function referer($value) {
        $this->set_header('Referer', $value);

        return $this;
    }

    /**
     * 设置 HTTP 请求的 user agent
     *
     * @param  string $value
     * @return Client
     */
    public function user_agent($value) {
        $this->options[CURLOPT_USERAGENT] = $value;

        return $this;
    }

    /**
     * 设置 HTTP 请求的方法
     *
     * @param resource $ch          CURL资源
     * @param string   $http_method 请求方法
     */
    protected function set_request_method($ch, $http_method) {
        switch (strtoupper($http_method)) {
            case 'HEAD':
                curl_setopt($ch, CURLOPT_NOBODY, true);
                break;
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);
        }
    }

    /**
     * 发起 HTTP 请求
     *
     * @param  string       $url     请求地址
     * @param  array|string $params  HTTP body 中传递的参数数组
     * @param  array        $options 自定义 curl 参数
     * @return string|false 返回 http 请求的 body
     */
    public function request($http_method, $url,
                            $params = null, $options = array()
    ) {
        // 重置错误代码和错误信息
        $this->error_code = 0;
        $this->error_info = '';
        $this->debug_info = '';

        // 重置 request header, body
        $this->request_header = '';
        $this->request_body = '';
        $this->response_header_lines = array();

        if ($this->debug_level) {
            if ($this->debug_level == 1) {
                $this->set_option(CURLINFO_HEADER_OUT, true);
            } else {
                // CURLINFO_HEADER_OUT 和 CURLOPT_VERBOSE 有冲突，只能采用一种
                // ref. https://bugs.php.net/bug.php?id=65348
                $this->set_option(CURLOPT_VERBOSE, true);
                $fp = fopen('php://temp', 'r+');
                $this->set_option(CURLOPT_STDERR, $fp);
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        $opts = $options + $this->options + $this->default_options();
        curl_setopt_array($ch, $opts);
        $this->set_request_method($ch, $http_method);

        // HTTP body 参数传递
        if (is_array($params)) {  // $params 有可能是字符串
            $params = http_build_query($params);
        }
        if ($params) {
            $this->request_body = $params;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }

        // 设置 HTTP 头
        $header_lines = $this->header_lines();
        if ($header_lines) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header_lines);
        }
        $this->request_headers = array();  // 重置HTTP请求头

        $response          = curl_exec($ch);
        $this->status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $this->error_code = curl_errno($ch);
            $this->error_info = curl_error($ch);
        }

        if ($this->debug_level) {
            if ($this->debug_level === 1) {
                $this->request_header = curl_getinfo($ch, CURLINFO_HEADER_OUT);
                $this->debug_info = 
                    "request:\n" . $this->request_header .
                        $this->request_body . "\n\n\n" .
                    "response:\n" . $this->response_header() .
                    $response . "\n";
            } else {
                rewind($fp);
                $this->debug_info = stream_get_contents($fp);
                fclose($fp);
            }
        }

        curl_close($ch);

        return $response;
    }

    /**
     * 发起 HTTP GET 请求，并返回 HTTP 响应的 body
     *
     * @param  string $url
     * @param  array  $options 自定义 curl 参数
     * @return string|false
     */
    public function get($url) {
        return $this->request('GET', $url);
    }

    /**
     * 发起 HTTP POST 请求，并返回 HTTP 响应的 body
     *
     * @param  string       $url
     * @param  array|string $params
     * @return string|false
     */
    public function post($url, $params = array()) {
        return $this->request('POST', $url, $params);
    }

    /**
     * 发起 HTTP HEAD 请求，并返回 HTTP 响应头
     *
     * @param  string $url
     * @param  array  $options 自定义 curl 参数
     * @return string|false
     */
    public function head($url) {
        $response = $this->request('HEAD', $url);

        return $response === false ? $response :
            implode("\r\n", $this->response_header_lines);
    }

    /**
     * 发起 HTTP PUT 请求，并返回 HTTP 响应的 body
     *
     * @param  string       $url
     * @param  array|string $params
     * @return string|false
     */
    public function put($url, $params = array()) {
        return $this->request('PUT', $url, $params);
    }

    /**
     * 发起 HTTP DELETE 请求，并返回 HTTP 响应的 body
     *
     * @param  string $url
     * @return string|false
     */
    public function delete($url) {
        return $this->request('DELETE', $url);
    }

    /**
     * 发起 HTTP PATCH 请求，并返回 HTTP 响应的 body
     *
     * @param  string       $url
     * @param  array|string $params
     * @return string|false
     */
    public function patch($url, $params = array()) {
        return $this->request('PATCH', $url, $params);
    }

    /**
     * 获取HTTP响应的状态码
     *
     * @return int
     */
    public function status_code() {
        return $this->status_code;
    }

    /**
     * 获取 CURL 返回的错误代码
     *
     * @return string
     */
    public function error_info() {
        return $this->error_info;
    }

    /**
     * 获取 CURL 返回的错误代码
     *
     * @return int
     */
    public function error_code() {
        return $this->error_code;
    }

    /**
     * 获取 debug 信息
     *
     * @return string
     */
    public function debug_info() {
        return  $this->debug_info;
    }
}
