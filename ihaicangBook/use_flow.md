##接口使用

###示例地址

    http://fjtawww.cncn.net/app/api/add_sq?sign=xxx&ver=1.0&d=iphone

>从示例可以看出，接口地址地址由 `接口控制器 + 接口名称` 组成

####获取指定的key
向接口对接人索取接口账号（key），通过可以进行签名加密。
####请求参数
#####sing参数
签名是通过将相关参数通过des方法进行加密后生成的

		$data = array(
        	'time'	=>	time(),			//服务器时间，必传参数
            'uuid'	=>	device uuid，   //必传标示设备唯一性的ID，简称设备ID
            'token'	=>	50299xsdfw55234f, //登录成功后获取的token_org, token=MD5(token_org+time+uuid)
            ..................
        );
        $sign = $this->des->encrypt($data); //加密
        $data = $this->des->decrypt($sign); //解密
       
#####ver参数
>初始版本为1.0
>小跨度默认的增值为1.1，1.2等
>大跨度默认的增值为2.1,3.1等
>

#####d参数
>d=android	Android设备发出的请求
>d=iphone	iphone设备发出来的请求 

#####返回的数据规范
>明文直接使用
>加密的返回值通过DES.decryptDESO解密,通过接口对接人获取解密key


