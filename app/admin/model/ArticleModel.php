<?php

namespace app\admin\model;

use Carbon\Carbon;
use think\model;
use app\admin\model\Keywords;

class ArticleModel extends Model
{

    protected $table = "xw_article";
    public static $Type = ['普通', '精华', '热门', '推荐'];

    /**
     * 获取分页列表
     * @param $where
     * @param $param
     */
    public function getArticleList($where, $param)
    {
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $order = empty($param['order']) ? 'a.id desc' : $param['order'];
        $list = self::where($where)
                ->field('a.*,c.id as cate_id,c.title as cate_title,u.nickname as admin_name')
                ->alias('a')
                ->join('article_cate c', 'a.cate_id = c.id')
                ->join('admin u', 'a.admin_id = u.id')
                ->order($order)
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
            $type = (int) $item->type;
            $item->type_str = self::$Type[$type];
        });
        return $list;
    }

    /**
     * 添加数据
     * @param $param
     */
    public function addArticle($param)
    {
        $insertId = 0;
        try
        {
            $param['created_at'] = Carbon::now()->toDateTimeString();
            $insertId = $this->strict(false)->field(true)->insertGetId($param);
            add_log('add', $insertId, $param);
        } catch (\Exception $e)
        {
            return output(1, '操作失败，原因：' . $e->getMessage());
        }
        return output(0, '操作成功', ['aid' => $insertId]);
    }

    /**
     * 编辑信息
     * @param $param
     */
    public function editArticle($param)
    {
        try
        {
            $param['updated_at'] = Carbon::now()->toDateTimeString();
            $this->where('id', $param['id'])->strict(false)->field(true)->update($param);
            add_log('edit', $param['id'], $param);
        } catch (\Exception $e)
        {
            return output(1, '操作失败，原因：' . $e->getMessage());
        }
        return output();
    }

    /**
     * 根据id获取信息
     * @param $id
     */
    public function getArticleById($id)
    {
        $info = $this->where('id', $id)->find();
        return $info;
    }

    /**
     * 删除信息
     * @param $id
     * @return array
     */
    public function delArticleById($id, $type = 0)
    {
        if ($type == 0)
        {
            //逻辑删除
            try
            {
                $param['deleted_at'] = Carbon::now()->toDateTimeString();
                $this->where('id', $id)->update(['deleted_at' => time()]);
                add_log('delete', $id);
            } catch (\Exception $e)
            {
                return output(1, '操作失败，原因：' . $e->getMessage());
            }
        }
        else
        {
            //物理删除
            try
            {
                $this->where('id', $id)->delete();
                add_log('delete', $id);
            } catch (\Exception $e)
            {
                return output(1, '操作失败，原因：' . $e->getMessage());
            }
        }
        return output();
    }

}
