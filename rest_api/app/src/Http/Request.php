<?php
/**
 * HTTP ����
 * 
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-17 19:34:11
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-19 18:20:27
 */

namespace App\Http;

class Request extends \Slim\Http\Request {
    /**
     * ��ȡȨ�޿��Ʋ���
     *
     * @return array
     */
    public function getExtraArgs() {
        return $this->getAttribute('extra_args', []);
    }

    /**
     * ��ȡ HTTP body �н�����Ĳ���
     *
     * @param  string $key     ��ֵ
     * @param  mixed  $default Ĭ��ֵ
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
     * ��ȡ HTTP body �����н�����Ĳ���
     *
     * @return array|object
     */
    public function getInputs() {
        return $this->getParsedBody();
    }

    /**
     * ��ȡ $_SERVER ����
     *
     * @param  string $key    ��ֵ
     * @param  mixed $default Ĭ��ֵ
     * @return mixed
     */
    public function getServerParam($key, $default = null) {
        return $this->serverParams[$key] ?? $default;
    }

    /**
     * ��ȡ HTTP ��uthorization ����ͷ
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
