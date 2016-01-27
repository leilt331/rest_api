<?php
/**
 * @Author: ����ͨ <850846416@qq.com>
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

    // ��ȡ������ʱ��
    public function get_time(req $req, res $res, $args){
        $data = array('unix'=>time());
        $res->success($data);
    }

    /**
     * ��ȡ��ҳ����
     *
     * @access public
     * @param  string time	������ʱ���
     * @param  string unid	�豸id
     * @return string $data
     */
    public function get_homepage(req $req, res $res, $args){
        if (isset($_GET['time']) && isset($_GET['uuid'])) {
            //��֤sign
            $time = $_GET['time'];
            $uuid = $_GET['uuid'];
            $str = $time . $uuid;
            $a = $this->des->encrypt($str);
            $sign = $req->getQueryParam('sign');
            if ($sign) {
                $signs = $this->des->decrypt($sign);
                if ($str !== $signs) {
                    return $res->error(-101, 'signֵ��Ч');
                }
            } else {
                return $res->error(-104, 'signֵ����Ϊ��');
            }

        //�ӵ������ӿڳ���ȡ�����й���Ϣ
        $url = file_get_contents('http://apistore.baidu.com/microservice/weather?cityid=101230201');
        $xiamen = json_decode($url, TRUE);
        $weather = iconv("UTF-8", "GBK//IGNORE", $xiamen['retData']['weather']);
        $temp = $xiamen['retData']['temp'];
        $pic = new \App\Model\Pics();
        $result = $pic->check_icon($weather);
        $icon = $result['icon_img'];
            $data = array(
                'knowhc'			=>	'http://u.eqxiu.com/s/NGOgihQM',	//�˽⺣��
                'weatherIcon'		=>	'http://59.34.148.138:610/uploads/icon/'.$icon,//����
                'temperature'		=>	$temp,//����
                'foods'		  		=>	'http://i.meituan.com/xiamen?cid=1&cateType=poi',//��ʳ
                'ships'		  		=>	'http://i.meituan.com/xiamen?cid=2&cateType=poi&stid_b=1&cevent=imt%2Fhomepage%2Fcategory2%2F2',//��������
                'travels'	  		=>	'http://m.cncn.com/jingdian/xiamen/haicang',//����
                'hotels'	  		=>	'http://i.meituan.com/hotel/?stid_b=1&cevent=imt%2Fhomepage%2Fcategory1%2F20',//�Ƶ�

                //'hcTraffic'     	=>	'',//���׽�ͨ
                'hcBicycle'     	=>	'http://map.haicang.gov.cn/HCCICMS/map/hcpb.html?lat=24.48229&lng=118.03694&zoomlevel=13',//���г�
                'hcBicycleHelp' 	=>	'',//���г�����˵��
                'hcBus'				=>	'http://mybus.xiamentd.com/',//������
                'hcTaxi'			=>	'http://wap.xm968890.com/Taxi/CallCar.aspx',//���⳵

                'affairs'			=>	'http://weixin.haicang.gov.cn/weixin2.php/workservice/channel',//����
                'appoint'			=>	'http://weixin.haicang.gov.cn/weixin2.php/workreserve/',//����ԤԼ
                'offerNumber'		=>	'http://weixin.haicang.gov.cn/weixin2.php/app/detail/id/1.html',
                'chat'				=>	'http://www.haicang.gov.cn:8116/chatwindowforjszx?to=zhengwucenter',//��ʱ��ѯ

                'news'				=>	'http://m.haicang.gov.cn/',//news
                'hospitalAppoint'	=>	'http://wechat.xmsmjk.com/zycapwxsehr/view/appointment/hospitalList.jsp',//ҽԺԤԼ
                'telAppoint'		=>	'96166',//ԤԼ�绰
                'sinaWeibo'			=>	'http://m.weibo.cn/p/100808c486c4fbc1b210be80e7451823233479',//΢������
            );

            return $res->success($data);
        }else{
            return $res->error(-1, 'time��uuid����Ϊ��');
        }
    }

    /**
     * �����£�android��
     *
     * @param	string	time
     * @param	string	uuid
     * @return  string	$data
     */
    public function app_upgrade(req $req, res $res, $args){
        if(isset($_GET['time']) && isset( $_GET['uuid'])){
            //��֤sign
            $time = $_GET['time'];
            $uuid = $_GET['uuid'];
            $str = $time.$uuid;
            $sign = $req->getQueryParam('sign');
            if($sign){
                $signs = $this->des->decrypt($sign);
                if($str !== $signs){
                    return $res->error(-101, 'signֵ��Ч');
                }
            }else{
                return $res->error(-104, 'signֵ����Ϊ��');
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
            return $res->error(-1, 'time��uuid����Ϊ��');
        }
    }

    /**
     * ��ȡͼƬ���б�
     *
     * @param  string time
     * @param  string uuid
     * @param  string page_Index ��ǰҳ��
     * @param  string pageSize	 ÿҳ��ʾҳ��
     * @return string
     */
    public function get_pic_list(req $req, res $res, $args){
        if(isset($_GET['time']) && isset( $_GET['uuid']) && isset( $_GET['pageIndex']) && isset($_GET['pageSize'])){
            //��֤sign
            $time = $_GET['time'];
            $uuid = $_GET['uuid'];
            $str = $time.$uuid;
            $sign = $req->getQueryParam('sign');
            if($sign){
                $signs = $this->des->decrypt($sign);
                if($str !== $signs){
                    return $res->error(-101, 'signֵ��Ч');
                }
            }else{
                return $res->error(-1004, 'signֵ����Ϊ��');
            }

            $pic = new \App\Model\Pics();
            $page_Index = $req->getQueryParam('pageIndex');
            $page_Size	= $req->getQueryParam('pageSize');
            if($result['list'] = $pic->pic_list($page_Index, $page_Size)){
                return $res->success($result);
            }else{
                return $res->error(-1004, '��������Ϊ��');
            }

        }else{
            return $res->error(-1, 'time��uuid��pageIndex��pageSize����Ϊ��');
        }
    }
}