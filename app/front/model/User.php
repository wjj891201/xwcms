<?php

namespace app\front\model;

use think\Model;
use Carbon\Carbon;

class User extends Model
{

    public function getUserByUsername($username)
    {
        if (empty($username))
        {
            return false;
        }

        $where = [
            "username" => trim($username),
        ];

        $result = $this->where($where)->find();
        return $result;
    }

    /**
     * 根据主键ID更新数据表中的数据
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateById($id, $data)
    {
        $id = intval($id);
        if (empty($id) || empty($data) || !is_array($data))
        {
            return false;
        }

        $where = [
            "id" => $id,
        ];

        return $this->where($where)->save($data);
    }

    public function insertUser($data)
    {
        $now = (string) Carbon::now();
        $result = $this->insert(['username' => $data['username'], 'password' => md5($data['password']), 'status' => 1, 'create_time' => $now]);
        return $result;
    }

}
