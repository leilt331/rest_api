<?php
/**
 * HTTP 请求
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-17 19:34:11
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-19 18:20:27
 */

namespace App\Http;

class Request extends \Slim\Http\Request {
    /**
     * 获取权限控制参数
     *
     * @return array
     */
    public function getExtraArgs() {
        return $this->getAttribute('extra_args', []);
    }

    /**
     * 获取 HTTP body 中解析后的参数
     *
     * @param  string $key     键值
     * @param  mixed  $default 默认值
     * @return mixed
     */
    public function getInput($key, $default = null) {
        $postParams = $this->getParsedBody();
        $result = $default;
        if (is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (is_object($postParams) && property_exists($postParams, $key)) {
            $result = $postParams->$key;
        }

        return $result;
    }

    /**
     * 获取 HTTP body 中所有解析后的参数
     *
     * @return array|object
     */
    public function getInputs() {
        return $this->getParsedBody();
    }

    /**
     * 获取 $_SERVER 变量
     *
     * @param  string $key    键值
     * @param  mixed $default 默认值
     * @return mixed
     */
    public function getServerParam($key, $default = null) {
        return $this->serverParams[$key] ?? $default;
    }

    /**
     * 获取 HTTP Ａuthorization 请求头
     *
     * @return string|null
     */
    public function getAccessToken() {
        $authorization = current($this->getHeader('HTTP_AUTHORIZATION'));
        if (!$authorization) {
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                $authorization = $headers['Authorization'] ?? null;
            }
        }

        if (!$authorization) {
            return null;
        }

        if (stripos($authorization, 'Bearer') !== 0) {
            return null;
        }

        return substr($authorization, strlen('Bearer '));
    }
}
