<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\AdminMenuCheck;
use Carbon\Carbon;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class AdminMenuController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $rule = Db::name('admin_menu')->order('sort_order asc,id asc')->select();
            return output(0, '', $rule);
        } else {
            return view();
        }
    }

    //添加
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
			$param['src'] = preg_replace('# #','',$param['src']);
            if ($param['id'] > 0) {
                try {
                    validate(AdminMenuCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                Db::name('admin_menu')->strict(false)->field(true)->update($param);
                add_log('edit', $param['id'], $param);
            } else {
                try {
                    validate(AdminMenuCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['created_at'] = Carbon::now()->toDateString();
                $rid = Db::name('admin_menu')->strict(false)->field(true)->insertGetId($param);
                //自动为系统所有者管理组分配新增的节点
                $group = Db::name('admin_role')->find(1);
                if (!empty($group)) {
                    $newGroup['id'] = 1;
                    $newGroup['rules'] = $group['rules'] . ',' . $rid;
                    Db::name('admin_role')->strict(false)->field(true)->update($newGroup);
                    add_log('add', $rid, $param);
                }
            }
            // 删除后台节点缓存
            clear_cache('admin_menu');
            return output();
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            $pid = isset($param['pid']) ? $param['pid'] : 0;
            if($id>0){
                $detail = Db::name('admin_menu')->where('id',$id)->find();
                View::assign('detail', $detail);
            }
            View::assign('id', $id);
            View::assign('pid', $pid);
            return view();
        }
    }
    //删除
    public function delete()
    {
        $id = get_params("id");
        $count = Db::name('admin_menu')->where(["pid" => $id])->count();
        if ($count > 0) {
            return output(1, "该节点下还有子节点，无法删除");
        }
        if (Db::name('admin_menu')->delete($id) !== false) {
            clear_cache('admin_menu');
            add_log('delete', $id, []);
            return output(0, "删除节点成功");
        } else {
            return output(1, "删除失败");
        }
    }
}
