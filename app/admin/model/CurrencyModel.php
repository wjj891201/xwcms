<?php

namespace app\admin\model;

use Carbon\Carbon;
use think\Model;

class CurrencyModel extends Model
{
    protected $table = "xw_currency";


    /**
     * 獲取分頁列表
     * @param $where
     * @param $param
     */
    public function getCurrencyList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'a.id desc' : $param['order'];
        $list = self::where($where)->order($order)->paginate($rows)->each(function ($item, $key) {
            $type = (int)$item->type;
            $item->type_str = self::$Type[$type];
        });
        return $list;
    }

    /**
     * 添加數據
     * @param $param
     */
    public function addArticle($param)
    {
        $insertId = 0;
        try {
            $param['created_at'] = Carbon::now()->toDateTimeString();
            $insertId = $this->strict(false)->field(true)->insertGetId($param);
            add_log('add', $insertId, $param);
        } catch(\Exception $e) {
            return output(1, '操作失敗，原因：'.$e->getMessage());
        }
        return output(0,'操作成功',['aid'=>$insertId]);
    }

    /**
     * 編輯資訊
     * @param $param
     */
    public function editArticle($param)
    {
        try {
            $param['updated_at'] = Carbon::now()->toDateTimeString();
            $this->where('id', $param['id'])->strict(false)->field(true)->update($param);
            add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
            return output(1, '操作失敗，原因：'.$e->getMessage());
        }
        return output();
    }


    /**
     * 根據id獲取資訊
     * @param $id
     */
    public function getArticleById($id)
    {
        $info = $this->where('id', $id)->find();
        return $info;
    }

    /**
     * 刪除資訊
     * @param $id
     * @return array
     */
    public function delArticleById($id,$type=0)
    {
        if($type==0){
            //邏輯刪除
            try {
                $param['deleted_at'] = Carbon::now()->toDateTimeString();
                $this->where('id', $id)->update(['deleted_at'=>time()]);
                add_log('delete', $id);
            } catch(\Exception $e) {
                return output(1, '操作失敗，原因：'.$e->getMessage());
            }
        }
        else{
            //物理刪除
            try {
                $this->where('id', $id)->delete();
                add_log('delete', $id);
            } catch(\Exception $e) {
                return output(1, '操作失敗，原因：'.$e->getMessage());
            }
        }
        return output();
    }
}
