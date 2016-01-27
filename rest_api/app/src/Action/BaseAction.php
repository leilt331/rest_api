<?php
/**
 * Action 基类
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-17 11:09:21
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-04 20:35:42
 */
namespace App\Action;
require __DIR__.'./../include/Des2.php';

abstract class BaseAction {
    /**
     * DIC 容器
     *
     * @var \Interop\Container\ContainerInterface;
     */
    protected $container = null;
    protected $key = null;

    /**
     * 构造函数
     *
     * @param \Interop\Container\ContainerInterface $container DIC 容器
     */
    public function __construct($container) {
        $this->container = $container;
        //var_dump($this->get('key'));
        $this->des = new \Des([$this->get('key')]);
    }

    /**
     * 获取容器中的内容
     *
     * @param  mixed $service service 标识符
     * @return mixed
     */
    protected function get($service) {
        return $this->container->get($service);
    }

    // 验证参数
    protected function valid_params($fields, $params) {
        foreach ($fields as $field) {
            if (!isset($params[$field])) {
                return false;
            }
        }

        return true;
    }
}
