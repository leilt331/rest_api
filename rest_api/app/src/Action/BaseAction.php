<?php
/**
 * Action ����
 * 
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @Date:   2015-12-17 11:09:21
 * @Last Modified by:   Joel Huang
 * @Last Modified time: 2016-01-04 20:35:42
 */
namespace App\Action;
require __DIR__.'./../include/Des2.php';

abstract class BaseAction {
    /**
     * DIC ����
     *
     * @var \Interop\Container\ContainerInterface;
     */
    protected $container = null;
    protected $key = null;

    /**
     * ���캯��
     *
     * @param \Interop\Container\ContainerInterface $container DIC ����
     */
    public function __construct($container) {
        $this->container = $container;
        //var_dump($this->get('key'));
        $this->des = new \Des([$this->get('key')]);
    }

    /**
     * ��ȡ�����е�����
     *
     * @param  mixed $service service ��ʶ��
     * @return mixed
     */
    protected function get($service) {
        return $this->container->get($service);
    }

    // ��֤����
    protected function valid_params($fields, $params) {
        foreach ($fields as $field) {
            if (!isset($params[$field])) {
                return false;
            }
        }

        return true;
    }
}
