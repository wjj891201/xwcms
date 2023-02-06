<?php

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\AdminPositionCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\View;

class CurrencyController extends BaseController
{
    public function index(){
        if (request()->isAjax()){
            $list =  Db::name("currency")->select()->toArray();
            return table_output(0, '', ["data"=>$list]);
        } else{
            return view();
        }
    }

    public function edit(){
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
                \think\facade\View::assign('detail', $detail);
            }
            View::assign('id', $id);
            return view();
        }
    }
}