<?php
/**
 * DES加密类
 *
 * 本类用于实现des算法的加密及解密
 *
 * 调用方法
 *     $des = new Des('key值');  // key只能是八位
 *     $xx = $des->decrypt('xxxxx');    // 解密
 *     $aa = $des->encrypt('xxxaa');    // 加密
 */
class Des {
    public $key='';

    // protected $iv = null;
    
    //key长度8例如:1234abcd
    function __construct($params) {
        $key = empty($params[0]) ? '': $params[0];
        $this->key = $key;
    }

    function iv() {
        return "\0\0\0\0\0\0\0\0";
    }
    
    function encrypt($encrypt) {
        $encrypt = $this->pkcs5_pad($encrypt);
        //$iv_size = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        //$this->iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
        $this->iv = $this->iv();

        $passcrypt = mcrypt_encrypt(MCRYPT_DES, $this->key, $encrypt, MCRYPT_MODE_CBC, $this->iv);
        return strtoupper(bin2hex(/*$this->iv . */$passcrypt));
    }

    function decrypt($decrypt) {
        $decoded = pack("H*", $decrypt);
        //$iv_size = mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        //$iv_dec = substr($decoded, 0, $iv_size);
        //$decoded = substr($decoded, $iv_size);
        $decrypted = mcrypt_decrypt(MCRYPT_DES, $this->key, $decoded, MCRYPT_MODE_CBC, $this->iv());
        return $this->pkcs5_unpad($decrypted);
    }

    function pkcs5_unpad($text) {
        $pad = ord($text{strlen($text)-1});    
        if ($pad > strlen($text)) {
            return $text;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return $text;
        }
        
        return substr($text, 0, -1 * $pad);
    }

    function pkcs5_pad($text) {
        $len = strlen($text);
        $mod = $len % 8;
        $pad = 8 - $mod;
        return $text.str_repeat(chr($pad),$pad);
    }

    //截中文英文子串
    function msubstr($str, $start, $len) {
        $tmpstr = "";
        $strlen = $start + $len;
        for($i = 0; $i < $strlen; $i++) {
            if(ord(substr($str, $i, 1)) > 0xa0) {
                $tmpstr .= substr($str, $i, 2);
                $i++;
            } else
                $tmpstr .= substr($str, $i, 1);
        }
        return $tmpstr;
    }
}
