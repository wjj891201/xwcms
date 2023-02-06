<?php


namespace app\admin\validate;

use think\Validate;

class AdminMenuCheck extends Validate
{
    protected $rule = [
        'title' => 'require',
        'src' => 'unique:admin_menu',
        'id' => 'require',
    ];

    protected $message = [
        'title.require' => '節點名稱不能為空',
        'src.unique' => '同樣的節點URL已經存在',
        'id.require' => '缺少更新條件',
        'filed.require' => '缺少要更新的字段名',
    ];

    protected $scene = [
        'add' => ['title','src'],
        'edit_title' => ['id', 'title'],
        'edit_src' => ['id', 'src'],
    ];
}
