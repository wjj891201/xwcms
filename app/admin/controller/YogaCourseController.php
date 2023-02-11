<?php

declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\YogaCourseModel;
use app\admin\validate\YogaCourseValidate;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class YogaCourseController extends BaseController
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new YogaCourseModel();
        $this->uid = get_login_admin('id');
    }

    /**
     * 数据列表
     */
    public function course_list()
    {
        if (request()->isAjax())
        {
            $param = get_params();
            $where = [];
            if (!empty($param['keywords']))
            {
                $where[] = ['a.id|a.title|a.desc|a.content|c.title', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['cate_id']))
            {
                $where[] = ['a.cate_id', '=', $param['cate_id']];
            }
            $where[] = ['a.deleted_at', '=', get_zero_time()];
            $YogaCourseModel = new YogaCourseModel();
            $list = $YogaCourseModel->getArticleList($where, $param);
            return table_output(0, '', $list);
        }
        else
        {
            return view();
        }
    }

    /**
     * 添加
     */
    public function add()
    {
        if (request()->isAjax())
        {
            $param = get_params();
            $param['admin_id'] = $this->uid;
            // 检验完整性
            try
            {
                validate(YogaCourseValidate::class)->check($param);
            } catch (ValidateException $e)
            {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }

            $YogaCourseModel = new YogaCourseModel();
            $YogaCourseModel->addYogaCourse($param);
        }
        else
        {
            return view();
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $param = get_params();
        $YogaCourseModel = new YogaCourseModel();

        if (request()->isAjax())
        {
            // 检验完整性
            try
            {
                validate(YogaCourseValidate::class)->check($param);
            } catch (ValidateException $e)
            {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            $YogaCourseModel->editYogaCourse($param);
        }
        else
        {
            $id = isset($param['id']) ? $param['id'] : 0;
            $detail = $YogaCourseModel->getYogaCourseById($id);
            if (!empty($detail))
            {
                View::assign('detail', $detail);
                return view();
            }
            else
            {
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
        $YogaCourseModel = new YogaCourseModel();
        $detail = $YogaCourseModel->getYogaCourseById($id);
        if (!empty($detail))
        {
            View::assign('detail', $detail);
            return view();
        }
        else
        {
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

        $YogaCourseModel = new YogaCourseModel();
        $YogaCourseModel->delYogaCourseById($id, 1);
    }

}
