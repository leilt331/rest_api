<?php
namespace Cncn;

/**
 * Encoding - 编码相关
 *
 * @author 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @since  2015-11-11 17:29:19
 */
class Encoding {
    /**
     * 编码转换(u2g, g2u没转换数组的键值，如需转换键值，需要用此方法)
     *
     * @param  array|string $data 要转换的数据
     * @param  string       $from 原始编码
     * @param  string       $to   转换到的编码
     * @return array|string
     */
    public static function convert($data, $from = 'GBK', $to = 'UTF-8') {
        if (is_array($data)) {
            $return = array();
            foreach ($data as $k => $v) {
                $k = mb_convert_encoding($k, $to, $from);
                if (is_array($v)) {
                    $v = self::convert($v, $from, $to);
                } elseif (is_string($v)) {
                    $v = mb_convert_encoding($v, $to, $from);
                }
                $return[$k] = $v;
            }
        } else {
            $return = mb_convert_encoding($data, $to, $from);
        }

        return $return;
    }

    /**
     * 字符串GBK转码为UTF-8，数字转换为数字。
     *
     * @param string|array $s
     * @return mixed
     */
    public static function g2u($s) {
        if (is_array($s)) {
            foreach ($s as $k => $v) {
                if (is_array($v)) {
                    $s[$k] = self::g2u($v);
                } elseif (is_string($v)) {
                    $s[$k] = mb_convert_encoding($v, 'UTF-8', 'GBK');
                }
            }
        } else {
            if (is_string($s)) {
                $s = mb_convert_encoding($s, 'UTF-8', 'GBK');
            }
        }

        return $s;
    }

    /**
     * 字符串UTF-8转码为GBK，数字转换为数字
     *
     * @param string|array $s
     * @return mixed
     */
    public static function u2g($s) {
        if (is_array($s)) {
            foreach ($s as $k => $v) {
                if (is_array($v)) {
                    $s[$k] = self::u2g($v);
                } elseif (is_string($v)) {
                    $s[$k] = mb_convert_encoding($v, 'GBK', 'UTF-8');
                }
            }
        } else {
            if (is_string($s)) {
                $s = mb_convert_encoding($s, 'GBK', 'UTF-8');
            }
        }

        return $s;
    }
}
