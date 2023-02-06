<?php


namespace app\admin\validate;
use think\Validate;

class PagesValidate extends Validate
{
    protected $rule = [
        'title' => 'require',
        'content' => 'require',
        'name' => 'lower|min:3|unique:pages',
    ];

    protected $message = [
        'title.require' => '頁面名稱不能為空',
        'content.require' => '頁面內容不能為空',
        'name.lower' => 'URL檔案名稱只能是小寫字元',
        'name.min' => 'URL檔案名稱至少需要3個小寫字元',
        'name.unique' => '同樣的URL檔案名稱已經存在',
    ];
}
