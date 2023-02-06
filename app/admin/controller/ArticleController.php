<?php

 
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\ArticleModel;
use app\admin\validate\ArticleValidate;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class ArticleController extends BaseController

{
	/**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new ArticleModel();
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
                $where[] = ['a.id|a.title|a.desc|a.content|c.title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['cate_id'])) {
                $where[] = ['a.cate_id', '=', $param['cate_id']];
            }
            $where[] = ['a.deleted_at', '=', get_zero_time()];
            $ArticleModel = new ArticleModel();
            $list = $ArticleModel->getArticleList($where, $param);
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
			if (isset($param['table-align'])) {
				unset($param['table-align']);
			}
			if (isset($param['content'])) {
				$param['md_content'] = '';
			}
			if (isset($param['docContent-html-code'])) {
				$param['content'] = $param['docContent-html-code'];
				$param['md_content'] = $param['docContent-markdown-doc'];
				unset($param['docContent-html-code']);
				unset($param['docContent-markdown-doc']);
			}
			$param['admin_id'] = $this->uid;
            // 检验完整性
            try {
                validate(ArticleValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
			
			$ArticleModel = new ArticleModel();	
            $ArticleModel->addArticle($param);
        }else{
			View::assign('editor', get_system_config('base','editor'));
			return view();
		}
    }
	

    /**
    * 编辑
    */
    public function edit()
    {
		$param = get_params();
        $ArticleModel = new ArticleModel();
		
        if (request()->isAjax()) {		
			if (isset($param['table-align'])) {
				unset($param['table-align']);
			}
			if (isset($param['content'])) {
				$param['md_content'] = '';
			}
			if (isset($param['docContent-html-code'])) {
				$param['content'] = $param['docContent-html-code'];
				$param['md_content'] = $param['docContent-markdown-doc'];
				unset($param['docContent-html-code']);
				unset($param['docContent-markdown-doc']);
			}		
            // 检验完整性
            try {
                validate(ArticleValidate::class)->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            $ArticleModel->editArticle($param);
        }else{
			$id = isset($param['id']) ? $param['id'] : 0;
			$detail = $ArticleModel->getArticleById($id);
			View::assign('editor', get_system_config('base','editor'));
			if (!empty($detail)) {
				if(!empty($article['md_content'])){
                    View::assign('editor',1);
                }
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
		$ArticleModel = new ArticleModel();
		$detail = $ArticleModel->getArticleById($id);
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

        $ArticleModel = new ArticleModel();
        $ArticleModel->delArticleById($id,1);
   }
}
