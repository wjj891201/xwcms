<?php

namespace app\front\service;

use app\front\model\UserModel as UserModel;

class User
{

    public $userModelObj = null;

    public function __construct()
    {
        $this->userModelObj = new UserModel();
    }

    public function login($data)
    {
        // 常规的做法：
        $user = $this->getUserByUsername($data['username']);

        if (!$user)
        {
            return show(0, "不存在该用户");
        }

        if ($user['password'] != md5($data['password'] . "_singwa_abc"))
        {
            return show(0, "输入的密码错误");
        }
        // 记录session
        session(config("user.session_front"), $user);
        // 设置模拟错误陷阱， 比如数据库内容错误等
        // 更新表的数据
        $res = $this->userModelObj->updateById($user['id'], ["last_login_time" => time()]);
        return $res;
    }

    public function getUserByUsername($username)
    {
        $user = $this->userModelObj->getUserByUsername($username);

        if (empty($user) || $user->status != 1)
        {
            return [];
        }

        $user = $user->toArray();
        return $user;
    }

    public static function updateById($id, $data)
    {
        
    }

}
