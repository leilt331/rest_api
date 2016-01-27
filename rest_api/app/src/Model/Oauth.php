<?php
/**
 * Oauth ���Ȩ����֤�߼�
 *
 * @Author: �ƾ���(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2015-12-18 09:05:32
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2015-12-21 14:37:23
 */
namespace App\Model;

class Oauth extends BaseModel {
    /**
     * ���� token ���õ���Կ
     */
    const TOKEN_KEY = 'cnCn';

    /**
     * ����ʱ��
     */
    const CACHE_TIME = 3600;

    /**
     * ��ȡ����Ȩ����֤�� token
     *
     * @param  string $username   �û���
     * @param  string $password   ����
     * @param  string $grant_type ��֤����
     * @return array
     */
    public function get_token($username, $password, $grant_type) : array {
        if ($grant_type !== 'client_credentials') {
            return ['error' => 'unsupported_grant_type', 'error_description' => '��֧�ָ�����֤����'];
        }

        if (!$username || !$password) {
            return ['error' => 'invalid_request', 'error_description' => '�û��������벻��Ϊ��'];
        }

        $m_au     = new ApiUser();
        $api_user = $m_au->find(
            ['username' => $username, 'password' => $password],
            'uid, allowed_ip'
        );
        if (!$api_user) {
            return ['error' => 'invalid_client', 'error_description' => '�û������������'];

        }

        $ip = $_SERVER['REMOTE_ADDR'];
        if ($api_user['allowed_ip'] && strpos($api_user['allowed_ip'], $ip) === false) {
            return ['error' => 'unauthorized_client', 'error_description' => '����IP��Ȩ�޷��ʽӿ�'];
        }

        // ���� token
        $dateline  = time();
        $uid       = $api_user['uid'];
        $m_al      = new ApiLogin();
        $api_login = $m_al->find(
            ['uid' => $uid, 'dateline >=' => $dateline - self::CACHE_TIME],
            'token, dateline'
        );
        if ($api_login) {
            // �ӿ���ȡ�� token
            $token    = $api_login['token'];
            $dateline = $api_login['dateline'];
        } else {
            // ���� token�������
            $token = hash_hmac('md5', $uid . $dateline, self::TOKEN_KEY);
            $m_al->add([
                'uid'      => $uid,
                'token'    => $token,
                'dateline' => $dateline,
            ]);
        }

        mem_set('api_' . $token, $uid, self::CACHE_TIME);  // ���� memcache

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => self::CACHE_TIME,
            'expires_at'   => $dateline + self::CACHE_TIME,
        ];
    }

    /**
     * ��� token ��Ӧ���û��Ƿ���Ȩ�޷��ʽӿ�
     *
     * @param  string            $token  ����APIȨ����֤�� token
     * @param  string            $action ����������������(�����������ռ�)
     * @param  \App\Http\Request $req    HTTP �������
     * @return array
     */
    public function valid_token($token, $action, &$req = null) : array {
        if (!$token || strlen($token) !== 32) {
            return [-101, '���ṩ��Ч�� token'];
        }

        $dateline = time();
        $uid      = mem_get('api_' . $token);
        if ($uid === false) {
            $m_al      = new ApiLogin();
            $api_login = $m_al->find(
                ['token' => $token, 'dateline >=' => $dateline - self::CACHE_TIME],
                'uid, token, dateline'
            );
            if ($api_login) {
                $uid = $api_login['uid'];
                mem_set('api_' . $token, $uid, self::CACHE_TIME);
            } else {
                return [-102, 'token��ƥ��'];
            }
        }

        // ���Ȩ��
        $key_rights     = 'api_rights_' . $uid;
        $key_allowed_ip = 'api_allowed_ip_' . $uid;
        $uid_rights     = mem_get($key_rights);
        $allowed_ip     = mem_get($key_allowed_ip);
        if ($uid_rights === false) {
            $m_au     = new ApiUser();
            $api_user = $m_au->find(
                ['uid' => $uid],
                'rights, allowed_ip'
            );
            if (!$api_user) {
                return [-103, 'token ��Ӧ���û�������'];
            }
            $uid_rights = $api_user['rights'];
            $allowed_ip = $api_user['allowed_ip'];

            mem_set($key_rights, $uid_rights, self::CACHE_TIME);
            mem_set($key_allowed_ip, $allowed_ip, self::CACHE_TIME);
        }

        list($controller, $method) = explode(':', $action, 2);
        if (!$this->check_rights($uid_rights, $controller, $method)) {
            return [-104, '��û��Ȩ�޷��ʸýӿ�'];
        }

        // ���IP�Ƿ�����
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($allowed_ip && strpos($allowed_ip, $ip) === false) {
            return [-105, '����IP��Ȩ�޷��ʽӿ�'];
        }

        $req = $this->set_extra_args($req, $uid_rights, $action);

        return [0, $uid];
    }

    // ���ö������
    protected function set_extra_args($req, $uid_rights, $action) {
        if ($req) {  // ���ṩ HTTP Request ����
            $pos = strpos($uid_rights, "|$action#");
            if ($pos !== false) {  // �������Զ������
                $pos1 = strpos($uid_rights, '#', $pos + 1);
                $pos2 = strpos($uid_rights, '|', $pos1 + 1);
                $extra_args = substr($uid_rights, $pos1 + 1, $pos2 - $pos1 - 1);

                if ($extra_args !== false) {
                    $req = $req->withAttribute('extra_args', explode(',', $extra_args));
                }
            }
        }

        return $req;
    }

    /**
     * ����Ƿ���Ȩ��
     *
     * @param  string $uid_rights Ȩ�������ַ���
     * @param  string $controller ��������
     * @param  string $method     ������
     * @return bool
     */
    protected function check_rights($uid_rights, $controller, $method) {
        if (!$uid_rights) {  // Ϊ����Ĭ����Ȩ��
            return false;
        }

        // ��������Ȩ�޵ķ�ʽ������ "|$controller|" ������Ȩ�޷������еķ���
        // "|$controller:$method#a,b,c" ������������������������ zone_id=xx ��
        // a,b,c �Ƕ������������Ӧ�Ŀ����������Զ����京��
        if (strpos($uid_rights, "|$controller|") === false &&
            strpos($uid_rights, "|$controller:$method|") === false &&
            strpos($uid_rights, "|$controller:$method#") === false
        ) {
            return false;
        }

        // ��ȥ��ĳ��������Ȩ��
        if (strpos($uid_rights, "|-$controller:$method|") !== false) {
            return false;
        }

        return true;
    }
}
