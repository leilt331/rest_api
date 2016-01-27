<?php
/**
 * Oauth 相关权限验证逻辑
 *
 * @Author: 黄景祥(Joel Huang) <joelhy@gmail.com>
 * @Date  :   2015-12-18 09:05:32
 * @Last  Modified by:   Joel Huang
 * @Last  Modified time: 2015-12-21 14:37:23
 */
namespace App\Model;

class Oauth extends BaseModel {
    /**
     * 生成 token 所用的密钥
     */
    const TOKEN_KEY = 'cnCn';

    /**
     * 缓存时间
     */
    const CACHE_TIME = 3600;

    /**
     * 获取用于权限验证的 token
     *
     * @param  string $username   用户名
     * @param  string $password   密码
     * @param  string $grant_type 验证类型
     * @return array
     */
    public function get_token($username, $password, $grant_type) : array {
        if ($grant_type !== 'client_credentials') {
            return ['error' => 'unsupported_grant_type', 'error_description' => '不支持该种验证类型'];
        }

        if (!$username || !$password) {
            return ['error' => 'invalid_request', 'error_description' => '用户名和密码不能为空'];
        }

        $m_au     = new ApiUser();
        $api_user = $m_au->find(
            ['username' => $username, 'password' => $password],
            'uid, allowed_ip'
        );
        if (!$api_user) {
            return ['error' => 'invalid_client', 'error_description' => '用户名或密码错误'];

        }

        $ip = $_SERVER['REMOTE_ADDR'];
        if ($api_user['allowed_ip'] && strpos($api_user['allowed_ip'], $ip) === false) {
            return ['error' => 'unauthorized_client', 'error_description' => '您的IP无权限访问接口'];
        }

        // 生成 token
        $dateline  = time();
        $uid       = $api_user['uid'];
        $m_al      = new ApiLogin();
        $api_login = $m_al->find(
            ['uid' => $uid, 'dateline >=' => $dateline - self::CACHE_TIME],
            'token, dateline'
        );
        if ($api_login) {
            // 从库里取出 token
            $token    = $api_login['token'];
            $dateline = $api_login['dateline'];
        } else {
            // 生成 token，并入库
            $token = hash_hmac('md5', $uid . $dateline, self::TOKEN_KEY);
            $m_al->add([
                'uid'      => $uid,
                'token'    => $token,
                'dateline' => $dateline,
            ]);
        }

        mem_set('api_' . $token, $uid, self::CACHE_TIME);  // 存入 memcache

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => self::CACHE_TIME,
            'expires_at'   => $dateline + self::CACHE_TIME,
        ];
    }

    /**
     * 检查 token 对应的用户是否有权限访问接口
     *
     * @param  string            $token  用于API权限验证的 token
     * @param  string            $action 控制器类名及方法(不包含命名空间)
     * @param  \App\Http\Request $req    HTTP 请求对象
     * @return array
     */
    public function valid_token($token, $action, &$req = null) : array {
        if (!$token || strlen($token) !== 32) {
            return [-101, '请提供有效的 token'];
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
                return [-102, 'token不匹配'];
            }
        }

        // 检查权限
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
                return [-103, 'token 对应的用户不存在'];
            }
            $uid_rights = $api_user['rights'];
            $allowed_ip = $api_user['allowed_ip'];

            mem_set($key_rights, $uid_rights, self::CACHE_TIME);
            mem_set($key_allowed_ip, $allowed_ip, self::CACHE_TIME);
        }

        list($controller, $method) = explode(':', $action, 2);
        if (!$this->check_rights($uid_rights, $controller, $method)) {
            return [-104, '您没有权限访问该接口'];
        }

        // 检查IP是否允许
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($allowed_ip && strpos($allowed_ip, $ip) === false) {
            return [-105, '您的IP无权限访问接口'];
        }

        $req = $this->set_extra_args($req, $uid_rights, $action);

        return [0, $uid];
    }

    // 设置额外参数
    protected function set_extra_args($req, $uid_rights, $action) {
        if ($req) {  // 有提供 HTTP Request 对象
            $pos = strpos($uid_rights, "|$action#");
            if ($pos !== false) {  // 有设置自定义参数
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
     * 检查是否有权限
     *
     * @param  string $uid_rights 权限配置字符串
     * @param  string $controller 控制器名
     * @param  string $method     方法名
     * @return bool
     */
    protected function check_rights($uid_rights, $controller, $method) {
        if (!$uid_rights) {  // 为空则默认无权限
            return false;
        }

        // 几种设置权限的方式，其中 "|$controller|" 代表有权限访问所有的方法
        // "|$controller:$method#a,b,c" 代表方法后有限制条件，例如 zone_id=xx 等
        // a,b,c 是额外参数，由相应的控制器方法自定义其含义
        if (strpos($uid_rights, "|$controller|") === false &&
            strpos($uid_rights, "|$controller:$method|") === false &&
            strpos($uid_rights, "|$controller:$method#") === false
        ) {
            return false;
        }

        // 有去除某个方法的权限
        if (strpos($uid_rights, "|-$controller:$method|") !== false) {
            return false;
        }

        return true;
    }
}
