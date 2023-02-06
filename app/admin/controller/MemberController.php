<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\MemberModel;
use Carbon\Carbon;
use dateset\Dateset;
use think\facade\Db;
use think\facade\View;

class MemberController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['nickname|username|name|mobile|province|city', 'like', '%' . $param['keywords'] . '%'];
            }

            //按时间检索
            $start_time = isset($param['start_time']) ? strtotime(urldecode($param['start_time'])) : 0;
            $end_time = isset($param['end_time']) ? strtotime(urldecode($param['end_time'])) : 0;

            if ($start_time > 0 && $end_time > 0) {
                if ($start_time === $end_time) {
                    $where[] = ['register_at', '=', $start_time];
                } else {
                    $where[] = ['register_at', '>=', $start_time];
                    $where[] = ['register_at', '<=', $end_time];
                }
            } elseif ($start_time > 0 && $end_time == 0) {
                $where[] = ['register_at', '>=', $start_time];
            } elseif ($start_time == 0 && $end_time > 0) {
                $where[] = ['register_at', '<=', $end_time];
            }

            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $content = MemberModel::where($where)
                ->order('id desc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
                    $item->role_name = Db::name('member_role')->where(['id' => $item->role_id])->value('title');
                });
            return table_output(0, '', $content);
        } else {
            return view();
        }
    }

    //编辑
    public function edit()
    {
        $param = get_params();
        if (request()->isAjax()) {
            if (!empty($param['id']) && $param['id'] > 0) {
                $param['updated_at'] = time();
                $res = Db::name('member')->where(['id' => $param['id']])->strict(false)->field(true)->update($param);
                if ($res !== false) {
                    add_log('edit', $param['id'], $param);
                    return output();
                } else {
                    return output(1, '提交失败');
                }
            }
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            $member = Db::name('member')->where(['id' => $id])->find();
            $role_list = Db::name('member_role')->where(['status' => 1])->select()->toArray();
            View::assign('member', $member);
            View::assign('role_list', $role_list);
            return view();
        }
    }

    //查看
    public function view()
    {
        $id = empty(get_params('id')) ? 0 : get_params('id');
        $member = Db::name('member')->where(['id' => $id])->find();
        $member['role_name'] = Db::name('member_role')->where(['id' => $member['role_id']])->value('title');
        add_log('view', get_params('id'));
        View::assign('user', $member);
        return view();
    }
    //禁用/启用
    public function disable()
    {
        $id = get_params("id");
        $data['status'] = get_params("status");
        $data['updated_at'] = Carbon::now()->toDateTimeString();
        $data['id'] = $id;
        if (Db::name('member')->update($data) !== false) {
            if ($data['status'] == 0) {
                add_log('disable', $id, $data);
            } else if ($data['status'] == 1) {
                add_log('recovery', $id, $data);
            }
            return output();
        } else {
            return output(1, "操作失败");
        }
    }

    //日志
    public function log()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['nickname|content|param_id', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['action'])) {
                $where[] = ['title', '=', $param['action']];
            }
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $content = DB::name('member_log')
                ->field("id,uid,nickname,title,content,ip,param_id,param,created_at")
                ->order('created_at desc')
                ->where($where)
                ->paginate($rows, false, ['query' => $param]);

            $content->toArray();
            foreach ($content as $k => $v) {
                $data = $v;
                $param_array = json_decode($v['param'], true);
                $param_value = '';
                foreach ($param_array as $key => $value) {
                    if (is_array($value)) {
                        $value = array_to_string($value);
                    }
                    $param_value .= $key . ':' . $value . '&nbsp;&nbsp;|&nbsp;&nbsp;';
                }
                $data['param'] = $param_value;
                $content->offsetSet($k, $data);
            }
            return table_output(0, '', $content);
        } else {
            $type_action = get_config('log.user_action');
            View::assign('type_action', $type_action);
            return view();
        }
    }


}
