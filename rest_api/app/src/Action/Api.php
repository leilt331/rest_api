<?php
/**
 * @Author: 雷铃通 <850846416@qq.com>
 * @Date:   2015-12-21 10:19:21
 * @Last Modified by:   leilingtong
 * @Last Modified time: 2016-01-27 10:35:44
 */

namespace App\Action;

class Api extends BaseAction{
    public function __construct($container){
        parent::__construct($container);
        $this->get('db');
    }

    // 获取服务器时间
    public function get_time(req $req, res $res, $args){
        $data = array('unix'=>time());
        $res->success($data);
    }

    /**
     * 获取首页数据
     *
     * @access public
     * @param  string time	服务器时间戳
     * @param  string unid	设备id
     * @return string $data
     */
    public function get_homepage(req $req, res $res, $args){
        if (isset($_GET['time']) && isset($_GET['uuid'])) {
            //验证sign
            $time = $_GET['time'];
            $uuid = $_GET['uuid'];
            $str = $time . $uuid;
            $a = $this->des->encrypt($str);
            $sign = $req->getQueryParam('sign');
            if ($sign) {
                $signs = $this->des->decrypt($sign);
                if ($str !== $signs) {
                    return $res->error(-101, 'sign值无效');
                }
            } else {
                return $res->error(-104, 'sign值不能为空');
            }

        //从第三方接口出获取天气有关信息
        $url = file_get_contents('http://apistore.baidu.com/microservice/weather?cityid=101230201');
        $xiamen = json_decode($url, TRUE);
        $weather = iconv("UTF-8", "GBK//IGNORE", $xiamen['retData']['weather']);
        $temp = $xiamen['retData']['temp'];
        $pic = new \App\Model\Pics();
        $result = $pic->check_icon($weather);
        $icon = $result['icon_img'];
            $data = array(
                'knowhc'			=>	'http://u.eqxiu.com/s/NGOgihQM',	//了解海沧
                'weatherIcon'		=>	'http://59.34.148.138:610/uploads/icon/'.$icon,//天气
                'temperature'		=>	$temp,//气温
                'foods'		  		=>	'http://i.meituan.com/xiamen?cid=1&cateType=poi',//美食
                'ships'		  		=>	'http://i.meituan.com/xiamen?cid=2&cateType=poi&stid_b=1&cevent=imt%2Fhomepage%2Fcategory2%2F2',//购物休闲
                'travels'	  		=>	'http://m.cncn.com/jingdian/xiamen/haicang',//旅游
                'hotels'	  		=>	'http://i.meituan.com/hotel/?stid_b=1&cevent=imt%2Fhomepage%2Fcategory1%2F20',//酒店

                //'hcTraffic'     	=>	'',//海沧交通
                'hcBicycle'     	=>	'http://map.haicang.gov.cn/HCCICMS/map/hcpb.html?lat=24.48229&lng=118.03694&zoomlevel=13',//自行车
                'hcBicycleHelp' 	=>	'',//自行车帮助说明
                'hcBus'				=>	'http://mybus.xiamentd.com/',//公交车
                'hcTaxi'			=>	'http://wap.xm968890.com/Taxi/CallCar.aspx',//出租车

                'affairs'			=>	'http://weixin.haicang.gov.cn/weixin2.php/workservice/channel',//政务
                'appoint'			=>	'http://weixin.haicang.gov.cn/weixin2.php/workreserve/',//办事预约
                'offerNumber'		=>	'http://weixin.haicang.gov.cn/weixin2.php/app/detail/id/1.html',
                'chat'				=>	'http://www.haicang.gov.cn:8116/chatwindowforjszx?to=zhengwucenter',//即时查询

                'news'				=>	'http://m.haicang.gov.cn/',//news
                'hospitalAppoint'	=>	'http://wechat.xmsmjk.com/zycapwxsehr/view/appointment/hospitalList.jsp',//医院预约
                'telAppoint'		=>	'96166',//预约电话
                'sinaWeibo'			=>	'http://m.weibo.cn/p/100808c486c4fbc1b210be80e7451823233479',//微博互动
            );

            return $res->success($data);
        }else{
            return $res->error(-1, 'time和uuid不能为空');
        }
    }

    /**
     * 检查更新（android）
     *
     * @param	string	time
     * @param	string	uuid
     * @return  string	$data
     */
    public function app_upgrade(req $req, res $res, $args){
        if(isset($_GET['time']) && isset( $_GET['uuid'])){
            //验证sign
            $time = $_GET['time'];
            $uuid = $_GET['uuid'];
            $str = $time.$uuid;
            $sign = $req->getQueryParam('sign');
            if($sign){
                $signs = $this->des->decrypt($sign);
                if($str !== $signs){
                    return $res->error(-101, 'sign值无效');
                }
            }else{
                return $res->error(-104, 'sign值不能为空');
            }

            $apk = new \App\Model\Pics();
            $data = $apk->apk_list();

            $fileurl = "./uploads/apkinfo/info.txt";
            $txt = preg_replace("/\t/","",$data['infoUrl']);
            file_put_contents($fileurl, $txt);
            $url = "http://59.34.148.138:611/uploads/apkinfo/info.txt";
            $data['infoUrl'] = $url;

            return $res->success($data);
        }else{
            return $res->error(-1, 'time和uuid不能为空');
        }
    }

    /**
     * 获取图片集列表
     *
     * @param  string time
     * @param  string uuid
     * @param  string page_Index 当前页数
     * @param  string pageSize	 每页显示页数
     * @return string
     */
    public function get_pic_list(req $req, res $res, $args){
        if(isset($_GET['time']) && isset( $_GET['uuid']) && isset( $_GET['pageIndex']) && isset($_GET['pageSize'])){
            //验证sign
            $time = $_GET['time'];
            $uuid = $_GET['uuid'];
            $str = $time.$uuid;
            $sign = $req->getQueryParam('sign');
            if($sign){
                $signs = $this->des->decrypt($sign);
                if($str !== $signs){
                    return $res->error(-101, 'sign值无效');
                }
            }else{
                return $res->error(-1004, 'sign值不能为空');
            }

            $pic = new \App\Model\Pics();
            $page_Index = $req->getQueryParam('pageIndex');
            $page_Size	= $req->getQueryParam('pageSize');
            if($result['list'] = $pic->pic_list($page_Index, $page_Size)){
                return $res->success($result);
            }else{
                return $res->error(-1004, '请求数据为空');
            }

        }else{
            return $res->error(-1, 'time和uuid与pageIndex跟pageSize不能为空');
        }
    }
}