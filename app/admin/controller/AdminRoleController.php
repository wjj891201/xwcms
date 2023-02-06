<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\AdminRoleModel;
use app\admin\validate\AdminRoleCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class AdminRoleController extends BaseController
{
    public function index()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['id|title|desc', 'like', '%' . $param['keywords'] . '%'];
            }
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $group = AdminRoleModel::where($where)
                ->order('created_at asc')
                ->paginate($rows, false, ['query' => $param]);
            return table_output(0, '', $group);
        } else {
            return view();
        }
    }

    //添加&編輯
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
            $ruleData = isset($param['rule']) ? $param['rule'] : 0;
            $param['rules'] = implode(',', $ruleData);
            if (!empty($param['id']) && $param['id'] > 0) {
                try {
                    validate(AdminRoleCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 驗證失敗 輸出錯誤資訊
                    return output(1, $e->getError());
                }
                //為了系統安全id為1的系統所有者管理組不允許修改
                if ($param['id'] == 1) {
                    return output(1, '為了系統安全,該管理組不允許修改');
                }
                Db::name('admin_role')->where(['id' => $param['id']])->strict(false)->field(true)->update($param);
                add_log('edit', $param['id'], $param);
            } else {
                try {
                    validate(AdminRoleCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 驗證失敗 輸出錯誤資訊
                    return output(1, $e->getError());
                }
                $gid = Db::name('admin_role')->strict(false)->field(true)->insertGetId($param);
                add_log('add', $gid, $param);
            }
            //清除菜單\許可權緩存
            clear_cache('adminMenu');
            return output();
        } else {
            $id = isset($param['id']) ? $param['id'] : 0;
            $rule = get_admin_menu();
            if ($id > 0) {
                $rules = get_admin_role_info($id);
                $role_rule = create_tree_list(0, $rule, $rules);
                $role = Db::name('admin_role')->where(['id' => $id])->find();
                View::assign('role', $role);
            } else {
                $role_rule = create_tree_list(0, $rule, []);
            }
            View::assign('role_rule', $role_rule);
            View::assign('id', $id);
            return view();
        }
    }

    //刪除
    public function delete()
    {
        $id = get_params("id");
        if ($id == 1) {
            return output(1, "該組是系統所有者，無法刪除");
        }
        if (Db::name('admin_role')->delete($id) !== false) {
            add_log('delete', $id, []);
            return output(0, "刪除角色成功");
        } else {
            return output(1, "刪除失敗");
        }
    }
}
