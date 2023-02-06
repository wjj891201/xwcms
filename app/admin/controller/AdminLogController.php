<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use think\facade\Db;
use think\facade\View;

class AdminLogController extends BaseController
{
    //管理员操作日志
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['nickname|content|param_id', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['action'])) {
                $where['action'] = $param['action'];
            }
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $content = DB::name('admin_log')
                ->order('created_at desc')
                ->where($where)
                ->paginate($rows, false, ['query' => $param]);
            $content->toArray();
            foreach ($content as $k => $v) {
                $data = $v;
                $param_array = json_decode($v['param'], true);
				if(is_array($param_array)){
					$param_value = '';
					foreach ($param_array as $key => $value) {
						if (is_array($value)) {
							$value = implode(',', $value);
						}
						$param_value .= $key . ':' . $value . '&nbsp;&nbsp;|&nbsp;&nbsp;';
					}
					$data['param'] = $param_value;
				}
				else{
					$data['param'] = $param_array;
				}
                $content->offsetSet($k, $data);
            }
            return table_output(0, '', $content);
        } else {
			$type_action = get_config('log.admin_action');
			View::assign('type_action', $type_action);
            return view();
        }
    }
}
