<?php
/**
 * 景点信息
 * 
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2015-12-21 10:22:45
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2015-12-21 10:43:07
 */
namespace App\Model;

use Cncn\Encoding as Enc;

class Jingdian extends BaseModel {
    /**
     * {@inheritdoc}
     */
    protected static $table = 'jingdian.jingdian';

    public function get_list($params, $get_pic = false) {
        // 验证请求参数
        $fields = ['zone_id'];
        foreach ($fields as $field) {
            if (!isset($params[$field])) {
                return [-1, "缺少参数 {$field}"];
            } elseif (!ctype_digit($params[$field])) {
                return [-2, "参数 {$field} 类型错误"];
            }
        }

        $zone_id  = $params['zone_id'];
        $page     = $params['page'] ?? 1;
        $per_page = $params['per_page'] ?? 20;

        if ($page < 1) {
            return [-3, '请输入有效的 page 参数'];
        }

        if ($per_page > 100 || $per_page <= 0) {
            return [-4, 'per_page 参数值必须在 1~100 之间'];
        }

        if ($zone_id < 900000) {
            $prov_id = substr($zone_id, 0, 4);
            // 县的情况
            if (strlen($zone_id) == 6 && substr($zone_id, -2) != '00') {
                switch ($zone_id) {
                    case 90:
                        $where = " WHERE flag > 9 AND EN <> ''";
                        break;
                    default:
                        $where = " WHERE zone_id = '$zone_id' AND flag > 9 AND EN <> ''";
                        break;
                }
            } else {
                switch ($zone_id) {
                    case 90:
                        $where = " WHERE flag > 9 AND EN <> ''";
                        break;
                    case 5001:
                        //重庆的子城市有5002的情况，如500232 武隆，这里特殊处理下
                        $where = " WHERE zone_id LIKE '500%' AND flag > 9 AND EN <> ''";
                        break;
                    default:
                        $where = " WHERE zone_id LIKE '$prov_id%' AND flag > 9 AND EN <> ''";
                        break;
                }
            }
        } else {
            $where = " WHERE zone_id = '$zone_id' AND flag > 9";
        }

        if (isset($params['search_key']) && trim($params['search_key'])) {
            $title_h = trim($params['search_key']);
            $title_h = Enc::u2g($title_h);
            $where .= " AND title LIKE '%" . $title_h . "%' ";
        }

        $total_num = (int) $this->db->get_value("SELECT COUNT(*) 
            FROM " . self::$table . " $where ");
        $total_page = ceil($total_num / $per_page);
        $page = max(min($page, $total_page), 1);
        $start = ($page - 1) * $per_page;

        $jd_list = [];
        if ($total_num > 0) {
            $pic_field = $get_pic ? ', smallpic' : '';
            $result = $this->db->query("SELECT id, area_en, EN, CN, title, zone_id $pic_field 
                FROM " . self::$table . " $where 
                ORDER BY orderid LIMIT $start, $per_page");
            while ($arr = $this->db->fetch_array($result)) {
                if ($arr['zone_id'] > 900000 && $arr['title']) {
                    $arr['CN'] = $arr['title'];
                }
                if ($arr['CN']) {
                    if ($get_pic) {
                        $arr['smallpic'] = $this->photo_basepath() . '/' . $arr['smallpic'];
                    }

                    $jd_list[] = $arr;
                }
            }
        }

        $data = [
            'total_num'     => $total_num,
            'total_page'    => $total_page,
            'items'         => $jd_list,
        ];

        return [0, $data];
    }

    /**
     * 景点详情
     *
     * @param  int $id 景点 ID
     * @return array
     */
    public function detail($id) {
        if (!$id) {
            return [-1, '缺少参数 id'];
        }
        $id = intval($id);

        $row = $this->find($id, 'price, open_time, jiaotong, geo_lng, star, smallpic');
        if (!$row) {
            return [-2, '找不到对应的景点'];
        }
        $row['smallpic'] = $this->photo_basepath() . '/' . $row['smallpic'];

        $m_ja = new JdArchive();
        $jianjie = $m_ja->value('content', ['jd_id' => $id], 1);
        $row['jianjie'] = $jianjie ?? '';

        return [0, $row];
    }

    /**
     * 获取 basepath
     */
    protected function photo_basepath() {
        if (ENV == 'prod') {
            $host =  'http://c.cncnimg.cn';
        } else {
            $host = 'http://192.168.1.158:876/uploads/album';
        }
        return $host;
    }
}
