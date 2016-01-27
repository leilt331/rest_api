<?php
/**
 *
 *
 * @Author: ����ͨ  <850846416@qq.com>
 * @Date  :   2015-12-21 10:22:45
 * @Last  Modified by:   leilingtong
 * @Last  Modified time: 2015-1-27 10:43:07
 */
namespace App\Model;

class Pics extends BaseModel {
    /**
     * ����ͼ��
     * @access public
     * @param string $weather
     * @return array
     */
    public function check_icon($weather){
        $name = addslashes($weather);
        $row = $this->db->get_row("SELECT icon_img FROM hc_icon where name='$name' limit 1");
        return $row;

    }

    /**
     * ��ѯ���°汾��
     *
     * @access public
     * @return array
     */
    public function apk_list(){
        $result = $this->db->get_row("select appname,apkname,vername,vercode,url,info from hc_files order by time desc limit 1");
        $file = array();
        $file = array(
            'appname' => $result['appname'],
            'apkname' => $result['apkname'],
            'vername' => $result['vername'],
            'vercode' => $result['vercode'],
            'url' => 'http://59.34.148.138:610/uploads/apk/'.$result['url'],
            'infoUrl' => $result['info'],
        );

        return $file;
    }

    /**
     * ��ѯ���е�ͼƬ��
     *
     * @access public
     * @return array
     */
    public function pic_list($page_Index, $page_Size){
        $page = intval($page_Index); //��ǰҳ��
        $page_size = intval($page_Size);// ÿҳ����
        //������
        $row = $this->db->get_value("select count(*) as num  from hc_pics"); //��ҳ��
        $counts = (int)($row / $page_size) + 1;
        if ($page <= 1 || $page == '') $page = 1;

        if ($page > $counts) {
            return '';
        } else {
            $page = ($page-1) * $page_size;
            $result = $this->db->get_rows("SELECT `id`, `name`, `image` FROM (`hc_pics`) JOIN `hc_thumb` ON `hc_pics`.`id`=`hc_thumb`.`pid` GROUP BY `id` LIMIT {$page},{$page_size}". MYSQLI_ASSOC);
            $file = array();
            foreach($result as $v){
                $data = array(
                    'id' => $v['id'],
                    'title' => $v['name'],
                    'smallpic' => 'http://59.34.148.138:610/uploads/thumb/'.$v['image'],
                    'bigpic' => 'http://59.34.148.138:610/uploads/thumb/'.$v['image'],
                );
                $file[] = $data;
            }
            return $file;
        }
    }
}