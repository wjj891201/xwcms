<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\AdminPositionCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class AdminPositionController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $list = Db::name('admin_position')->where('status', '>=', 0)->order('created_at asc')->select()->toArray();
            $res['data'] = $list;
            return table_output(0, '', $res);
        } else {
            return view();
        }
    }

    //添加&編輯
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
            if (!empty($param['id']) && $param['id'] > 0) {
                try {
                    validate(AdminPositionCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 驗證失敗 輸出錯誤資訊
                    return output(1, $e->getError());
                }
                $res = Db::name('admin_position')->where(['id' => $param['id']])->strict(false)->field(true)->update($param);
                if($res!==false){
                    add_log('edit', $param['id'], $param);
                    return output();
                }
                else{
                    return output(1, '提交失敗');
                }
            } else {
                try {
                    validate(AdminPositionCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 驗證失敗 輸出錯誤資訊
                    return output(1, $e->getError());
                }

                $pid = Db::name('admin_position')->strict(false)->field(true)->insertGetId($param);
                if($pid>0){
                    add_log('add', $pid, $param);
                    return output();
                }
                else{
                    return output(1, '提交失敗');
                }
            }
        }
        else{
            $id = isset($param['id']) ? $param['id'] : 0;
            if ($id > 0) {
                $detail = Db::name('admin_position')->where(['id' => $id])->find();
                View::assign('detail', $detail);
            }
            View::assign('id', $id);
            return view();
        }
    }

    //刪除
    public function delete()
    {
        $id = get_params("id");
        if ($id == 1) {
            return output(0, "超級崗位，不能刪除");
        }
        $data['status'] = '-1';
        $data['id'] = $id;
        $data['updated_at'] = time();
        if (Db::name('admin_position')->update($data) !== false) {
            add_log('delete', $id);
            return output(0, "刪除崗位成功");
        } else {
            return output(1, "刪除失敗");
        }
    }
}
