<?php

// 應用公共檔,內置主要的數據處理方法
use Carbon\Carbon;
use think\facade\Db;
use think\facade\Session;

//獲取後臺模組當前登錄用戶的資訊
function get_login_admin($key = "")
{
    $session_admin = get_config('app.session_admin');
    if (Session::has($session_admin))
    {
        $xuwen_admin = Session::get($session_admin);
        if (!empty($key))
        {
            if (isset($xuwen_admin[$key]))
            {
                return $xuwen_admin[$key];
            }
            else
            {
                return '';
            }
        }
        else
        {
            return $xuwen_admin;
        }
    }
    else
    {
        return '';
    }
}

/**
 * 截取摘要
 *  @return bool
 */
function getDescriptionFromContent($content, $count)
{
    $content = preg_replace("@<script(.*?)</script>@is", "", $content);
    $content = preg_replace("@<iframe(.*?)</iframe>@is", "", $content);
    $content = preg_replace("@<style(.*?)</style>@is", "", $content);
    $content = preg_replace("@<(.*?)>@is", "", $content);
    $content = str_replace(PHP_EOL, '', $content);
    $space = array(" ", "　", "  ", " ", " ");
    $go_away = array("", "", "", "", "");
    $content = str_replace($space, $go_away, $content);
    $res = mb_substr($content, 0, $count, 'UTF-8');
    if (mb_strlen($content, 'UTF-8') > $count)
    {
        $res = $res . "...";
    }
    return $res;
}

/**
 * PHP格式化位元組大小
 * @param number $size      位元組數
 * @param string $delimiter 數字和單位分隔符號
 * @return string            格式化後的帶單位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++)
    {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[$i];
}

function create_tree_list($pid, $arr, $group, &$tree = [])
{
    foreach ($arr as $key => $vo)
    {
        if ($key == 0)
        {
            $vo['spread'] = true;
        }
        if (!empty($group) and in_array($vo['id'], $group))
        {
            $vo['checked'] = true;
        }
        else
        {
            $vo['checked'] = false;
        }
        if ($vo['pid'] == $pid)
        {
            $child = create_tree_list($vo['id'], $arr, $group);
            if ($child)
            {
                $vo['children'] = $child;
            }
            $tree[] = $vo;
        }
    }
    return $tree;
}

//遞歸排序，用於分類選擇
function set_recursion($result, $pid = 0, $level = -1)
{
    /* 記錄排序後的類別數組 */
    static $list = array();
    static $space = ['', '├─', '§§├─', '§§§§├─', '§§§§§§├─'];
    $level++;
    foreach ($result as $k => $v)
    {
        if ($v['pid'] == $pid)
        {
            if ($pid != 0)
            {
                $v['title'] = $space[$level] . $v['title'];
            }
            /* 將該類別的數據放入list中 */
            $list[] = $v;
            set_recursion($result, $v['id'], $level);
        }
    }
    return $list;
}

/**
 * 根據id遞歸返回子數據
 * @param  $data 數據
 * @param  $pid 父節點id
 */
function get_data_node($data = [], $pid = 0)
{
    $dep = [];
    foreach ($data as $k => $v)
    {
        if ($v['pid'] == $pid)
        {
            $node = get_data_node($data, $v['id']);
            array_push($dep, $v);
            if (!empty($node))
            {
                $dep = array_merge($dep, $node);
            }
        }
    }
    return array_values($dep);
}

//獲取指定管理員的資訊
function get_admin($id)
{
    $admin = Db::name('admin')->where(['id' => $id])->find();
    $admin['role_id'] = Db::name('admin_role_access')->where(['uid' => $id])->column('role_id');
    return $admin;
}

//讀取許可權節點列表
function get_admin_menu()
{
    $rule = Db::name('admin_menu')->where(['status' => 1])->order('sort_order asc,id asc')->select()->toArray();
    return $rule;
}

//讀取許可權分組列表
function get_admin_role()
{
    $group = Db::name('admin_role')->order('created_at asc')->select()->toArray();
    return $group;
}

//讀取指定許可權分組詳情
function get_admin_role_info($id)
{
    $rule = Db::name('admin_role')->where(['id' => $id])->value('rules');
    $rules = explode(',', $rule);
    return $rules;
}

//讀取部門列表
function get_department()
{
    $department = Db::name('admin_department')->where(['status' => 1])->select()->toArray();
    return $department;
}

//獲取某部門的子部門id.$is_self時候包含自己
function get_department_son($did = 0, $is_self = 1)
{
    $department = get_department();
    $department_list = get_data_node($department, $did);
    $department_array = array_column($department_list, 'id');
    if ($is_self == 1)
    {
        //包括自己在內
        $department_array[] = $did;
    }
    return $department_array;
}

//讀取員工所在部門的負責人
function get_department_leader($uid = 0, $pid = 0)
{
    $did = get_admin($uid)['did'];
    if ($pid == 0)
    {
        $leader = Db::name('admin_department')->where(['id' => $did])->value('leader_id');
    }
    else
    {
        $pdid = Db::name('admin_department')->where(['id' => $did])->value('pid');
        if ($pdid == 0)
        {
            $leader = 0;
        }
        else
        {
            $leader = Db::name('admin_department')->where(['id' => $pdid])->value('leader_id');
        }
    }
    return $leader;
}

//讀取職位
function get_position()
{
    $position = Db::name('admin_position')->where(['status' => 1])->select()->toArray();
    return $position;
}

//讀取文章分類列表
function get_article_cate()
{
    $cate = Db::name('article_cate')->where(['deleted_at' => get_zero_time()])->order('created_at asc')->select()->toArray();
    return $cate;
}

/**
 * 管理員操作日誌
 * @param string $type 操作類型 login add edit view delete
 * @param int    $param_id 操作類型
 * @param array  $param 提交的參數
 */
function add_log($type, $param_id = '', $param = [])
{
    $action = '未知操作';
    $type_action = get_config('log.admin_action');
    if ($type_action[$type])
    {
        $action = $type_action[$type];
    }
    if ($type == 'login')
    {
        $login_admin = Db::name('admin')->where(array('id' => $param_id))->find();
    }
    else
    {
        $session_admin = get_config('app.session_admin');
        $login_admin = Session::get($session_admin);
    }
    $data = [];
    $data['uid'] = $login_admin['id'];
    $data['nickname'] = $login_admin['nickname'];
    $data['type'] = $type;
    $data['action'] = $action;
    $data['param_id'] = $param_id;
    $data['param'] = json_encode($param);
    $data['module'] = strtolower(app('http')->getName());
    $data['controller'] = uncamelize(app('request')->controller());
    $data['function'] = strtolower(app('request')->action());
    $parameter = $data['module'] . '/' . $data['controller'] . '/' . $data['function'];
    $rule_menu = Db::name('admin_menu')->where(array('src' => $parameter))->find();
    if ($rule_menu)
    {
        $data['title'] = $rule_menu['title'];
        $data['subject'] = $rule_menu['name'];
    }
    else
    {
        $data['title'] = '';
        $data['subject'] = '系統';
    }
    $content = $login_admin['nickname'] . '在' . date('Y-m-d H:i:s') . $data['action'] . '了' . $data['subject'];
    $data['content'] = $content;
    $data['ip'] = app('request')->ip();
    $data['created_at'] = Carbon::now()->toDateTimeString();
    Db::name('admin_log')->strict(false)->field(true)->insert($data);
}
