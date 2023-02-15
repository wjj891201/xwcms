<?php

declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\YogaOrderModel;
use think\facade\Db;
use think\facade\View;

class YogaOrderController extends BaseController
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new YogaOrderModel();
        $this->uid = get_login_admin('id');
    }

    /**
     * 数据列表
     */
    public function order_list()
    {
        if (request()->isAjax())
        {
            $param = get_params();
            $where = [];
//            if (!empty($param['keywords']))
//            {
//                $where[] = ['a.id|a.title|a.desc|a.content|c.title', 'like', '%' . $param['keywords'] . '%'];
//            }
//            if (!empty($param['cate_id']))
//            {
//                $where[] = ['a.cate_id', '=', $param['cate_id']];
//            }
            $YogaOrderModel = new YogaOrderModel();
            $list = $YogaOrderModel->getOrderList($where, $param);
            $list = $list->toArray();

            $data = $list['data'];
            $temp = [];
            foreach ($data as $key => $vo)
            {
                $cateIdArr = Db::name('order_course')->group("cate_id")->where(['order_id' => $vo['id']])->column('cate_id');
                $itemNameArr = Db::name('yoga_cate')->where('id', 'in', $cateIdArr)->column('title');
                $itemName = implode(',', $itemNameArr);
                $vo['goods'] = $itemName;
                $temp[] = $vo;
            }
            $data = $temp;

            $list['data'] = $data;
            
            return table_output(0, '', $list);
        }
        else
        {
            return view();
        }
    }

}
