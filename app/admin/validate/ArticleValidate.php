<?php


namespace app\admin\validate;
use think\Validate;

class ArticleValidate extends Validate
{
    protected $rule = [
        'cate_id' => 'require',
        'title' => 'require',
        'thumb' => 'require',
        'content' => 'require',
    ];

    protected $message = [
        'cate_id.require' => '所屬分類不能為空',
        'title.require' => '文章標題不能為空',
        'thumb.require' => '縮略圖不能為空',
        'content.require' => '文章內容不能為空',
    ];
}
