<?php


namespace app\admin\validate;

use think\Validate;

class BannerCheck extends Validate
{
    protected $rule = [
        'title' => 'require|unique:banner',
        'name' => 'require|unique:banner',
        'id' => 'require',
        'status' => 'require',
        'img' => 'require',
        'cate_id' => 'require',
    ];

    protected $message = [
        'title.require' => '標題不能為空',
        'title.unique' => '同樣的標題已經存在',
        'name.require' => '標識不能為空',
        'name.unique' => '同樣的標識已經存在',
        'id.require' => '缺少更新條件',
        'status.require' => '狀態為必選',
        'img.require' => '請上傳圖片',
        'cate_id.require' => '缺少換燈組ID',
    ];

    protected $scene = [
        'add' => ['title', 'name', 'status'],
        'edit' => ['id', 'title', 'name', 'status'],
        'addInfo' => ['title', 'img', 'status', 'cate_id'],
        'editInfo' => ['title', 'img', 'status', 'id'],
    ];
}
