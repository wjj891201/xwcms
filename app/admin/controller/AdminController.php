<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\AdminModel;
use app\admin\validate\AdminCheck;
use avatars\MDAvatars;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class AdminController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['id|username|nickname|desc|mobile', 'like', '%' . $param['keywords'] . '%'];
            }
            $where[] = ['status','>=',0];
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $admin = AdminModel::where($where)
                ->order('created_at asc')
                ->paginate($rows, false, ['query' => $param])
                ->each(function ($item, $key) {
                    $groupId = Db::name('admin_role_access')->where(['uid' => $item->id])->column('role_id');
                    $groupName = Db::name('admin_role')->where('id', 'in', $groupId)->column('title');
                    $item->groupName = implode(',', $groupName);
                    $item->last_login_time = empty($item->last_login_time) ? '-' : date('Y-m-d H:i', $item->last_login_time);
                });
            return table_output(0, '', $admin);
        } else {
            return view();
        }
    }

    //添加
    public function add()
    {	
		if (request()->isAjax()) {
			$param = get_params();
            if (!empty($param['id']) && $param['id'] > 0) {
                try {
                    validate(AdminCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                if (!empty($param['edit_pwd'])) {
                    //重置密码
                    if (empty($param['edit_pwd_confirm']) or $param['edit_pwd_confirm'] !== $param['edit_pwd']) {
                        return output(1, '两次密码不一致');
                    }
                    $param['salt'] = set_salt(20);
                    $param['pwd'] = set_password($param['edit_pwd'], $param['salt']);
                }

                // 启动事务
                Db::startTrans();
                try {
                    Db::name('admin')->where(['id' => $param['id']])->strict(false)->field(true)->update($param);
                    Db::name('admin_role_access')->where(['uid' => $param['id']])->delete();
                    foreach ($param['role_id'] as $k => $v) {
                        //为了系统安全，只有系统所有者才可创建id为1的管理员分组
                        if ($v == 1 and get_login_admin('id') !== 1) {
                            throw new ValidateException("你没有权限创建系统所有者", 1);
                        }
                        $data[$k] = [
                            'uid' => $param['id'],
                            'role_id' => $v,
                        ];
                    }
                    Db::name('admin_role_access')->strict(false)->field(true)->insertAll($data);
                    if (!isset($param['thumb']) || $param['thumb'] == '') {
                        $char = mb_substr($param['nickname'], 0, 1, 'utf-8');
                        Db::name('admin')->where('id', $param['id'])->update(['thumb' => $this->to_avatars($char)]);
                    }
                    add_log('edit', $param['id'], $param);
                    //清除菜单\权限缓存
                    clear_cache('adminMenu');
                    clear_cache('adminRules');
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return output(1, '提交失败:' . $e->getMessage());
                }
            } else {
                try {
                    validate(AdminCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['salt'] = set_salt(20);
                $param['pwd'] = set_password($param['pwd'], $param['salt']);
                // 启动事务
                Db::startTrans();
                try {
                    $uid = Db::name('admin')->strict(false)->field(true)->insertGetId($param);
                    foreach ($param['role_id'] as $k => $v) {
                        //为了系统安全，只有系统所有者才可创建id为1的管理员分组
                        if ($v == 1 and get_login_admin('id') !== 1) {
                            throw new ValidateException("你没有权限创建系统所有者", 1);
                        }
                        $data[$k] = [
                            'uid' => $uid,
                            'role_id' => $v,
                        ];
                    }
                    Db::name('admin_role_access')->strict(false)->field(true)->insertAll($data);
                    if (!isset($param['thumb']) || $param['thumb'] == '') {
                        $char = mb_substr($param['nickname'], 0, 1, 'utf-8');
                        Db::name('admin')->where('id', $uid)->update(['thumb' => $this->to_avatars($char)]);
                    }
                    add_log('add', $uid, $param);
                    // 提交事务
                    Db::commit();
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return output(1, '提交失败:' . $e->getMessage());
                }
            }
            return output();
		}
		else{
			$id = empty(get_params('id')) ? 0 : get_params('id');
			$department = set_recursion(get_department());
            $position = Db::name('admin_position')->where('status', '>=', 0)->order('created_at asc')->select();
			if ($id > 0) {
				$admin = get_admin(get_params('id'));
				View::assign('admin', $admin);
			}
			View::assign('department', $department);
            View::assign('position', $position);
			View::assign('id', $id);
			return view();
		}
    }

    public function to_avatars($char)
    {
        $defaultData = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
            'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'S', 'Y', 'Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            '零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '拾',
            '一', '二', '三', '四', '五', '六', '七', '八', '九', '十');
        if (isset($char)) {
            $Char = $char;
        } else {
            $Char = $defaultData[mt_rand(0, count($defaultData) - 1)];
        }
        $OutputSize = min(512, empty($_GET['size']) ? 36 : intval($_GET['size']));

        $Avatar = new MDAvatars($Char, 256, 1);
        $avatar_name = '/avatars/avatar_256_' . set_salt(10) . time() . '.png';
        $path = get_config('filesystem.disks.public.url') . $avatar_name;
        $res = $Avatar->Save('.' . $path, 256);
        $Avatar->Free();
        /*
        if ($res) {
        //写入到附件表
        $data = [];
        $data['filepath'] = $path;
        $data['name'] = $Char;
        $data['mimetype'] = 'image/png';
        $data['fileext'] = 'png';
        $data['filesize'] = 0;
        $data['filename'] = $avatar_name;
        $data['sha1'] = '';
        $data['md5'] = '';
        $data['module'] = \think\facade\App::initialize()->http->getName();
        $data['action'] = app('request')->action();
        $data['uploadip'] = app('request')->ip();
        $data['created_at'] = time();
        $data['user_id'] = get_login_admin('id') ? get_login_admin('id') : 0;
        if ($data['module'] = 'admin') {
        //通过后台上传的文件直接审核通过
        $data['status'] = 1;
        $data['admin_id'] = $data['user_id'];
        $data['audit_time'] = time();
        }
        $data['use'] = 'avatar'; //附件用处
        $fid = Db::name('file')->insertGetId($data);
        return $fid;
        }
         */
        return $path;
    }

    //查看
    public function view()
    {		
		$id = get_params('id');
		$rule = get_admin_menu();
		
		$user_groups = Db::name('admin_role_access')
                ->alias('a')
                ->join("admin_role g", "a.role_id=g.id", 'LEFT')
                ->where("a.uid='{$id}' and g.status='1'")
                ->select()
                ->toArray();
		$groups = $user_groups ?: [];

		$rules = []; 
		foreach ($groups as $g) {
			$rules = array_merge($rules, explode(',', trim($g['rules'], ',')));
		}
		$rules = array_unique($rules);		
		
		$role_rule = create_tree_list(0, $rule, $rules);
		$department = get_department();
        $position = Db::name('admin_position')->where('status', '>=', 0)->order('created_at asc')->select();
		View::assign('department', $department);
        View::assign('position', $position);
        View::assign('role_rule', $role_rule);
        View::assign('admin', get_admin($id));		
		add_log('view', get_params('id'));
        return view('', ['admin' => get_admin(get_params('id'))]);
    }
    //删除
    public function delete()
    {
        $id = get_params("id");
        if($id == 1){
            return output(0, "超级管理员，不能删除");
        }
        $data['status'] = '-1';
        $data['id'] = $id;
        $data['updated_at'] = time();
        if (Db::name('admin')->update($data) !== false) {
            add_log('delete', $id);
            return output(0, "删除管理员成功");
        } else {
            return output(1, "删除失败");
        }
    }

    //管理员操作日志
    public function log()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['nickname|rule_menu|param_id', 'like', '%' . $param['keywords'] . '%'];
            }
            if (!empty($param['title_cate'])) {
                $where['title'] = $param['title_cate'];
            }
            if (!empty($param['rule_menu'])) {
                $where['rule_menu'] = $param['rule_menu'];
            }
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $content = DB::name('AdminLog')
                ->field("id,uid,nickname,title,content,rule_menu,ip,param_id,param,FROM_UNIXTIME(created_at,'%Y-%m-%d %H:%i:%s') created_at")
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
            return view();
        }
    }
}
