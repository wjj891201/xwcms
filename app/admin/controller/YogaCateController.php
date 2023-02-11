<?php

declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\YogaCateModel;
use app\admin\validate\YogaCateValidate;
use Carbon\Carbon;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class YogaCateController extends BaseController
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new YogaCateModel();
    }

    /**
     * 数据列表
     */
    public function cate_list()
    {

        if (request()->isAjax())
        {
            $list = $this->model->where('deleted_at', get_zero_time())->order('sort_order asc')->select();
            return output(0, '', $list);
        }
        else
        {
            return view();
        }
    }

    //获取子分类id.$is_self=1包含自己
    public function get_cate_son($id = 0, $is_self = 1)
    {
        $cates = $this->model->where('deleted_at', get_zero_time())->order('sort_order asc')->select()->toArray();
        $cates_list = get_data_node($cates, $id);
        $cates_array = array_column($cates_list, 'id');
        if ($is_self == 1)
        {
            //包括自己在内
            $cates_array[] = $id;
        }
        return $cates_array;
    }

    /**
     * 添加
     */
    public function add()
    {
        $param = get_params();
        if (request()->isAjax())
        {
            // 检验完整性
            try
            {
                validate(YogaCateValidate::class)->check($param);
            } catch (ValidateException $e)
            {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            $this->model->addYogaCate($param);
        }
        else
        {
            $pid = isset($param['pid']) ? $param['pid'] : 0;
            View::assign('pid', $pid);
            return view();
        }
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $param = get_params();

        if (request()->isAjax())
        {
            // 检验完整性
            try
            {
                validate(YogaCateValidate::class)->check($param);
            } catch (ValidateException $e)
            {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            $cate_array = $this->get_cate_son($param['id']);
            if (in_array($param['pid'], $cate_array))
            {
                return output(1, '上级分类不能是该分类本身或其子分类');
            }
            $this->model->editYogaCate($param);
        }
        else
        {
            $id = isset($param['id']) ? $param['id'] : 0;
            $detail = $this->model->getYogaCateById($id);
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
        $detail = $this->model->getArticleCateById($id);
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
        $count_cate = Db::name('ArticleCate')->where(["pid" => $id, "deleted_at" => 0])->count();
        if ($count_cate > 0)
        {
            return output(1, "该分类下还有子分类，无法删除");
        }
        $count_article = Db::name('Article')->where(["cate_id" => $id, "deleted_at" => 0])->count();
        if ($count_article > 0)
        {
            return output(1, "该分类下还有文章，无法删除");
        }
        $this->model->delYogaCateById($id, $type);
    }

}
