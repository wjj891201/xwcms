<?php


namespace app\admin\validate;

use think\Validate;

class AdminPositionCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique: admin_position',
        'id' => 'require'
    ];

    protected $message = [
        'title.require' => '崗位名稱不能為空',
        'title.unique' => '同樣的崗位名稱已經存在',
        'id.require' => '缺少更新條件',
    ];

    protected $scene = [
        'add' => ['title', 'role_id'],
        'edit' => ['title', 'role_id', 'id'],
    ];
}
