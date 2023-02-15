<?php

namespace app\admin\model;

use think\Model;

class YogaOrderModel extends Model
{

    protected $table = "xw_order";

    /**
     * 获取分页列表
     * @param $where
     * @param $param
     */
    public function getOrderList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'o.id desc' : $param['order'];
        $list = self::where($where)
                ->field('o.*,u.username,u.email')
                ->alias('o')
                ->join('user u', 'o.user_id = u.id')
                ->order($order)
                ->paginate($rows, false, ['query' => $param]);
        return $list;
    }

}
