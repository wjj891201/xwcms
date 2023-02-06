<?php


namespace app\admin\validate;

use think\Validate;

class RoleCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique:member_role',
        'id' => 'require',
    ];

    protected $message = [
        'title.require' => '模組名稱不能為空',
        'title.unique' => '同樣的等級名稱已經存在',
        'id.require' => '缺少更新條件',
    ];

    protected $scene = [
        'add' => ['title'],
        'edit' => ['id','title'],
    ];
}
