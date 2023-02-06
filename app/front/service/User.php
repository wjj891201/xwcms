<?php

namespace app\front\service;

use think\Exception;
use app\front\model\User as UserModel;

class User
{

    public $userModelObj = null;

    public function __construct()
    {
        $this->userModelObj = new UserModel();
    }

    public function login($data)
    {
        $user = $this->getUserByUsername($data['username']);

        if (!$user)
        {
            throw new Exception('不存在该用户');
        }

        if ($user['password'] != md5($data['password']))
        {
            throw new Exception('密码错误');
        }
        // 更新表的数据
        $res = $this->userModelObj->updateById($user['id'], ["last_login_time" => time()]);
        // 记录session
        session(config("user.session_front"), $user);
        return true;
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
