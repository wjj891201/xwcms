<?php


declare (strict_types = 1);

namespace app\admin\middleware;

use think\facade\Cache;
use think\facade\Db;
use think\facade\Session;

class Auth
{
    public function handle($request, \Closure $next)
    {
        //獲取模組名稱
        $controller = app('http')->getName();
        $pathInfo = str_replace('.' . $request->ext(), '', $request->pathInfo());
        $action = explode('/', $pathInfo)[0];
        //var_dump($pathInfo);exit;
        if ($pathInfo == '' || $action == '') {
            redirect('/admin/index/index')->send();
            exit;
        }
        //驗證用戶登錄
        if ($action !== 'login') {
            $session_admin = get_config('app.session_admin');
            if (!Session::has($session_admin)) {
                if ($request->isAjax()) {
                    return output(404, '請先登錄');
                } else {
                    redirect('/admin/login/index')->send();
                    exit;
                }
            }
            $uid = Session::get($session_admin)['id'];
            // 驗證用戶訪問許可權
            if ($action !== 'index' && $action !== 'api') {


                if (!$this->checkAuth($controller, $pathInfo, $action, $uid)) {

                    if ($request->isAjax()) {
                        return output(202, '你沒有許可權,請聯繫超級管理員！');
                    } else {
                        echo '<div style="text-align:center;color:red;margin-top:20%;">您沒有許可權,請聯繫超級管理員</div>';exit;
                    }
                }
            }
        }
        return $next($request);
    }

    /**
     * 驗證用戶訪問許可權
     * @DateTime 2020-12-21
     * @param    string $controller 當前訪問控制器
     * @param    string $action 當前訪問方法
     * @param    string $uid 當前用戶id
     * @return   [type]
     */
    protected function checkAuth($controller, $pathInfo, $action, $uid)
    {
        //Cache::delete('RulesSrc' . $uid);
        if (!Cache::get('RulesSrc' . $uid) || !Cache::get('RulesSrc0')) {
            //用戶所在許可權組及所擁有的許可權
            // 執行查詢
            $user_groups = Db::name('admin_role_access')
                ->alias('a')
                ->join("admin_role g", "a.role_id=g.id", 'LEFT')
                ->where("a.uid='{$uid}' and g.status='1'")
                ->select()
                ->toArray();
            $groups = $user_groups ?: [];

            $ids = []; //保存用戶所屬用戶組設置的所有權限規則id
            foreach ($groups as $g) {
                $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
            }
            $ids = array_unique($ids);
            //讀取所有權限規則
            $rules_all = Db::name('admin_menu')->field('src')->select();
            //讀取用戶組所有權限規則
            $rules = Db::name('admin_menu')->where('id', 'in', $ids)->field('src')->select();
            //迴圈規則，判斷結果。
            $auth_list_all = [];
            $auth_list = [];
            foreach ($rules_all as $rule_all) {
                $auth_list_all[] = strtolower($rule_all['src']);
            }
            foreach ($rules as $rule) {
                $auth_list[] = strtolower($rule['src']);
            }
            //規則列表結果保存到Cache
            Cache::tag('admin_menu')->set('RulesSrc0', $auth_list_all, 36000);
            Cache::tag('admin_menu')->set('RulesSrc' . $uid, $auth_list, 36000);
        } else {
            $auth_list_all = Cache::get('RulesSrc0');
            $auth_list = Cache::get('RulesSrc' . $uid);
        }

        $pathUrl = $controller . '/' . $pathInfo;
        if (!in_array($pathUrl, $auth_list)) {
            return false;
        } else {
            return true;
        }
    }
}
