<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use malkusch\lock\mutex\PHPRedisMutex;
use think\facade\App;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

class IndexController extends BaseController
{
    public function index()
    {
//        $id = get_params("id");
//        $redis = new  \Redis();
//        $redis->connect("127.0.0.1");
//
//        $bankAccount = "account";
//        $amount = 10;
//
//        $mutex = new PHPRedisMutex([$redis], "balance");
//
//        $mutex->check(function (): bool {
//            return false;
//        });
//
//        $mutex->synchronized(function () use ($bankAccount, $amount) {
//           var_dump($bankAccount,$amount);
//            usleep(5000000);
//        });
//
//        return "hello";


		$admin = get_login_admin();
		if (get_cache('menu' . $admin['id'])) {
			$list = get_cache('menu' . $admin['id']);
		} else {
			$adminGroup = Db::name('admin_role_access')->where(['uid' => get_login_admin('id')])->column('role_id');
			$adminMenu = Db::name('admin_role')->where('id', 'in', $adminGroup)->column('rules');
			$adminMenus = [];
			foreach ($adminMenu as $k => $v) {
				$v = explode(',', $v);
				$adminMenus = array_merge($adminMenus, $v);
			}
			$menu = Db::name('admin_menu')->where(['menu' => 1,'status'=>1])->where('id', 'in', $adminMenus)->order('sort_order asc')->select()->toArray();
			$list = list_to_tree($menu);
			Cache::tag('adminMenu')->set('menu' . $admin['id'], $list);
		}
		$theme = Db::name('admin')->where('id',$admin['id'])->value('theme');
		View::assign('theme',$theme);
        View::assign('menu', $list);
        return View();
    }

    public function main()
    {
        $adminCount = Db::name('admin')->where('status', '1')->count();
        $userCount = Db::name('member')->where('status', '1')->count();
        $articleCount = Db::name('article')->where('status', '1')->count();
        $fileCount = Db::name('file')->count();
        if (file_exists(CMS_ROOT . 'app/install')) {
            $install = true;
        }
        View::assign('adminCount', $adminCount);
        View::assign('userCount', $userCount);
        View::assign('articleCount', $articleCount);
        View::assign('fileCount', $fileCount);
        View::assign('TP_VERSION', App::version());
        return View();
    }
	
	//设置theme
	public function set_theme()
    {
        if (request()->isAjax()) {
            $param = get_params();
			$admin = get_login_admin();
			Db::name('admin')->where('id',$admin['id'])->update(['theme'=>$param['theme']]);
            return output();
        }
		else{
			return output(1,'操作错误');
		}
    }
}
