<?php


namespace app\admin\validate;

use think\Validate;

class AdminCheck extends Validate
{
    protected $regex = [ 'checkUser' => '/^[A-Za-z]{1}[A-Za-z0-9_-]{4,19}$/'];

    protected $rule = [
        'username' => 'require|regex:checkUser|unique:admin',
        'pwd' => 'require|min:6|confirm',
        'edit_pwd' => 'min:6|confirm',
        'mobile' => 'require|mobile',
        'nickname' => 'require|chsAlpha',
        'role_id' => 'require',
        'id' => 'require',
        'status' => 'require|checkStatus:-1,1',
        'old_pwd' => 'require|different:pwd',
    ];

    protected $message = [
        'username.require' => '登錄帳號不能為空',
        'username.regex' => '登錄帳號必須是以字母開頭，只能包含字母數字下劃線和減號，5到20位',
        'username.unique' => '同樣的登錄帳號已經存在',
        'pwd.require' => '密碼不能為空',
        'pwd.min' => '密碼至少要6個字元',
        'pwd.confirm' => '兩次密碼不一致',
        'edit_pwd.min' => '密碼至少要6個字元',
        'edit_pwd.confirm' => '兩次密碼不一致',
        'mobile.require' => '手機不能為空',
        'mobile.mobile' => '手機格式錯誤',
        'nickname.require' => '昵稱不能為空',
        'nickname.chsAlpha' => '昵稱只能是漢字和字母',
        'role_id.require' => '至少要選擇一個用戶角色',
        'id.require' => '缺少更新條件',
        'status.require' => '狀態為必選',
        'status.checkStatus' => '系統所有者不能被禁用',
        'old_pwd.require' => '請提供舊密碼',
        'old_pwd.different' => '新密碼不能和舊密碼一樣',
    ];

    protected $scene = [
        'add' => ['mobile', 'nickname', 'role_id', 'pwd', 'username', 'status'],
        'edit' => ['mobile', 'nickname', 'role_id', 'edit_pwd','id', 'username', 'status'],
        'editPersonal' => ['mobile', 'nickname'],
        'editpwd' => ['old_pwd', 'pwd'],
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
