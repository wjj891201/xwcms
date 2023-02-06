<?php


namespace app\admin\validate;

use think\Validate;

class AdminDepartmentCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique:admin_department',
        'id' => 'require',
    ];

    protected $message = [
        'title.require' => '部門名稱不能為空',
        'title.unique' => '同樣的部門名稱已經存在',
        'id.require' => '缺少更新條件',
    ];

    protected $scene = [
        'add' => ['title'],
        'edit' => ['id', 'title'],
    ];
}
