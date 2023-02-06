<?php


namespace app\admin\validate;

use think\Validate;

class ConfCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique:config,title^status',
        'name' => 'require|alphaDash|unique:config,name^status',
    ];

    protected $message = [
        'title.require' => '配置名稱不能為空',
        'title.unique' => '同樣的配置名稱已經存在',
        'name.require' => '配置標識不能為空',
        'name.alphaDash' => '配置標識只能是字母和數字，下劃線_及破折號-',
        'name.unique' => '同樣的配置標識已經存在',
    ];
}
