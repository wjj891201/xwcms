<?php


namespace app\admin\validate;
use think\Validate;

class YogaCourseValidate extends Validate
{
    protected $rule = [
        'cate_id' => 'require',
        'title' => 'require',
        'course_url' => 'require',
    ];

    protected $message = [
        'cate_id.require' => '所屬分類不能為空',
        'title.require' => '文章標題不能為空',
        'course_url.require' => '課程鏈接不能為空',
    ];
}
