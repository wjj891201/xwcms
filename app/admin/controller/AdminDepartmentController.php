<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\AdminDepartmentCheck;
use Carbon\Carbon;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class AdminDepartmentController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $list = Db::name('admin_department')
                ->field('d.*,a.nickname as leader')
                ->alias('d')
                ->join('Admin a', 'a.id = d.leader_id', 'LEFT')
                ->order('d.id asc')
                ->select();
            return output(0, '', $list);
        } else {
            return view();
        }
    }

    //添加部門
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
            if ($param['id'] > 0) {
                try {
                    validate(AdminDepartmentCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 驗證失敗 輸出錯誤資訊
                    return output(1, $e->getError());
                }
                $param['updated_at'] = Carbon::now()->toDateString();
                $department_array = get_department_son($param['id']);
                if (in_array($param['pid'], $department_array)) {
                    return output(1, '上級部門不能是該部門本身或其下屬部門');
                } else {
                    Db::name('admin_department')->strict(false)->field(true)->update($param);
                    add_log('edit', $param['id'], $param);
                    return output();
                }
            } else {
                try {
                    validate(AdminDepartmentCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 驗證失敗 輸出錯誤資訊
                    return output(1, $e->getError());
                }
                $did = Db::name('admin_department')->strict(false)->field(true)->insertGetId($param);
                add_log('add', $did, $param);
                return output();
            }
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            $pid = isset($param['pid']) ? $param['pid'] : 0;
            $department = set_recursion(get_department());
            if ($id > 0) {
                $detail = Db::name('admin_department')->where(['id' => $id])->find();
                $users = Db::name('admin')->where(['did' => $id, 'status' => 1])->select();
                View::assign('users', $users);
                View::assign('detail', $detail);
            }
            View::assign('department', $department);
            View::assign('pid', $pid);
            View::assign('id', $id);
            return view();
        }
    }

    //刪除
    public function delete()
    {
        $id = get_params("id");
        $count = Db::name('admin_department')->where([['pid', '=', $id], ['status', '>=', 0]])->count();
        if ($count > 0) {
            return output(1, "該部門下還有子部門，無法刪除");
        }
        $users = Db::name('admin')->where([['did', '=', $id], ['status', '>=', 0]])->count();
        if ($users > 0) {
            return output(1, "該部門下還有員工，無法刪除");
        }
        if (Db::name('admin_department')->delete($id) !== false) {
            add_log('delete', $id);
            return output(0, "刪除部門成功");
        } else {
            return output(1, "刪除失敗");
        }
    }
}
