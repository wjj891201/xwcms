<?php


namespace app\admin\validate;

use think\Validate;

class AdminRoleCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique:admin_role',
        'id' => 'require',
        'status' => 'require|checkStatus:-1,1',
    ];

    protected $message = [
        'title.require' => '名稱不能為空',
        'title.unique' => '同樣的記錄已經存在',
        'id.require' => '缺少更新條件',
        'status.require' => '狀態為必選',
        'status.checkStatus' => '系統所有者組不能被禁用',
    ];

    protected $scene = [
        'add' => ['title', 'status'],
        'edit' => ['id', 'title', 'status'],
    ];

    // 自定義驗證規則
    protected function checkStatus($value, $rule, $data)
    {
        if ($value == -1 and $data['id'] == 1) {
            return $rule == false;
        }
        return $rule == true;
    }
}
