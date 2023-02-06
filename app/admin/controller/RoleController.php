<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\RoleCheck;
use Carbon\Carbon;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class RoleController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $level = Db::name('member_role')->select();
            return output(0, '', $level);
        } else {
            return view();
        }
    }

    //添加新增/编辑
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
			$param['title'] = preg_replace('# #','',$param['title']);
            if ($param['id'] > 0) {
                try {
                    validate(RoleCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['updated_at'] = Carbon::now()->toDateTimeString();
                Db::name('member_role')->strict(false)->field(true)->update($param);
                add_log('edit', $param['id'], $param);
            } else {
                try {
                    validate(RoleCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['created_at'] = Carbon::now()->toDateTimeString();
                $mid = Db::name('member_role')->strict(false)->field(true)->insertGetId($param);
                add_log('add', $mid, $param);
            }
            return output();
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            if($id>0){
                $detail = Db::name('member_role')->where('id',$id)->find();
                View::assign('detail', $detail);
            }
            View::assign('id', $id);
            return view();
        }
    }

    //禁用/启用
    public function disable()
    {
        $param = get_params();
		$param['updated_at']= Carbon::now()->toDateTimeString();
		$res = Db::name('member_role')->strict(false)->field('status,updated_at')->update($param);
		if($res!==false){
			if($param['status'] == 0){
				add_log('disable', $param['id'], $param);
			}
			else if($param['status'] == 1){
				add_log('recovery', $param['id'], $param);
			}
			return output();
		}
		else{
			return output(1,'操作失败');
		}
    }
}
