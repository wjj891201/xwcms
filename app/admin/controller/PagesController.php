<?php

 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\PagesModel;
use app\admin\validate\PagesValidate;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class PagesController extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new PagesModel();
		$this->uid = get_login_admin('id');
    }
    /**
    * 数据列表
    */
    public function datalist()
    {
        if (request()->isAjax()) {
			$param = get_params();
			$where = [];
			if (!empty($param['keywords'])) {
                $where[] = ['a.id|a.title|a.desc|a.content', 'like', '%' . $param['keywords'] . '%'];
            }
            $where[] = ['a.status', '=', 1];
            $list = $this->model->getPagesList($where, $param);
            return table_output(0, '', $list);
        }
        else{
            return view();
        }
    }

    /**
    * 添加
    */
    public function add()
    {
        if (request()->isAjax()) {		
			$param = get_params();	
			
            // 检验完整性
            try {
                validate(PagesValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
			$param['admin_id'] = $this->uid;
            $this->model->addPages($param);
        }else{
			$templates = get_file_list(CMS_ROOT . '/app/home/view/pages/');
			View::assign('templates', $templates);
			return view();
		}
    }
	

    /**
    * 编辑
    */
    public function edit()
    {
		$param = get_params();
		
        if (request()->isAjax()) {			
            // 检验完整性
            try {
                validate(PagesValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            $this->model->editPages($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $this->model->getPagesById($id);
			if (!empty($detail)) {
				View::assign('detail', $detail);
				return view();
			}
			else{
				throw new HttpException(404, '找不到页面');
			}			
		}
    }


    /**
    * 查看信息
    */
    public function read()
    {
        $param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$detail = $this->model->getPagesById($id);
		if (!empty($detail)) {
			View::assign('detail', $detail);
			return view();
		}
		else{
			throw new HttpException(404, '找不到页面');
		}
    }

    /**
    * 删除
    */
    public function del()
    {
		$param = get_params();
		$id = isset($param['id']) ? $param['id'] : 0;
		$type = isset($param['type']) ? $param['type'] : 0;

        $this->model->delPagesById($id,1);
   }
}
