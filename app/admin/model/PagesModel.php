<?php

namespace app\admin\model;
use Carbon\Carbon;
use think\model;
class PagesModel extends Model
{

    protected $table ="xw_pages";


    /**
    * 获取分页列表
    * @param $where
    * @param $param
    */
    public function getPagesList($where, $param)
    {
		$rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
		$order = empty($param['order']) ? 'id desc' : $param['order'];
		$list = $this->where($where)
		->field('a.*,u.nickname as admin_name')
        ->alias('a')
        ->leftJoin('admin u', 'a.admin_id = u.id')
		->order($order)
		->paginate($rows, false, ['query' => $param]);
		return $list;
    }

    /**
    * 添加数据
    * @param $param
    */
    public function addPages($param)
    {
		$insertId = 0;
        try {
			$param['created_at'] = Carbon::now()->toAtomString();
			$insertId = $this->strict(false)->field(true)->insertGetId($param);

			add_log('add', $insertId, $param);
        } catch(\Exception $e) {
			return output(1, '操作失败，原因：'.$e->getMessage());
        }
		return output(0,'操作成功',['aid'=>$insertId]);
    }

    /**
    * 编辑信息
    * @param $param
    */
    public function editPages($param)
    {
        try {
            $param['updated_at'] = time();
            $this->where('id', $param['id'])->strict(false)->field(true)->update($param);
			//关联关键字
			if (isset($param['keyword_names']) && $param['keyword_names']) {
				\think\facade\Db::name('PagesKeywords')->where(['aid'=>$param['id']])->delete();
				$keywordArray = explode(',', $param['keyword_names']);
				$res_keyword = $this->insertKeyword($keywordArray,$param['id']);
			}
			add_log('edit', $param['id'], $param);
        } catch(\Exception $e) {
			return output(1, '操作失败，原因：'.$e->getMessage());
        }
		return output();
    }
	

    /**
    * 根据id获取信息
    * @param $id
    */
    public function getPagesById($id)
    {
        $info = $this->where('id', $id)->find();
		return $info;
    }

    /**
    * 删除信息
    * @param $id
    * @return array
    */
    public function delPagesById($id,$type=0)
    {
        $info = $this->getPagesById($id);
        $info['deleted_at'] = Carbon::now()->toDateTimeString();
		if($type==0){
			//逻辑删除
			try {
				$this->where('id', $id)->update(['deleted_at'=>Carbon::now()->toDateString()]);
				add_log('delete', $id,$info);
			} catch(\Exception $e) {
				return output(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		else{
			//物理删除
			try {
				$this->where('id', $id)->delete();
				add_log('delete', $id,$info);
			} catch(\Exception $e) {
				return output(1, '操作失败，原因：'.$e->getMessage());
			}
		}
		return output();
    }
}

