<?php

declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\AdminLogModel;
use app\admin\validate\AdminCheck;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Session;

class ApiController extends BaseController
{
    //上传文件
    public function upload()
    {
        $param = get_params();
		//var_dump($param);exit;
		$sourse = 'file';
		if(isset($param['sourse'])){
			$sourse = $param['sourse'];
		}
		if($sourse == 'file' || $sourse == 'tinymce'){
			if(request()->file('file')){
				$file = request()->file('file');
			}
			else{
				return output(1, '没有选择上传文件');
			}
		}
		else{
			if (request()->file('editormd-image-file')) {
				$file = request()->file('editormd-image-file');
			} else {
				return output(1, '没有选择上传文件');
			}
		}
        // 获取上传文件的hash散列值
        $sha1 = $file->hash('sha1');
        $md5 = $file->hash('md5');
        $rule = [
            'image' => 'jpg,png,jpeg,gif',
            'doc' => 'doc,docx,ppt,pptx,xls,xlsx,pdf',
            'file' => 'zip,gz,7z,rar,tar',
            'video' => 'mpg,mp4,mpeg,avi,wmv,mov,flv,m4v',
        ];
        $fileExt = $rule['image'] . ',' . $rule['doc'] . ',' . $rule['file'] . ',' . $rule['video'];
        //1M=1024*1024=1048576字节
        $fileSize = 100 * 1024 * 1024;
        if (isset($param['type']) && $param['type']) {
            $fileExt = $rule[$param['type']];
        }
        if (isset($param['size']) && $param['size']) {
            $fileSize = $param['size'];
        }
        $validate = \think\facade\Validate::rule([
            'image' => 'require|fileSize:' . $fileSize . '|fileExt:' . $fileExt,
        ]);
        $file_check['image'] = $file;
        if (!$validate->check($file_check)) {
            return output(1, $validate->getError());
        }
        // 日期前綴
        $dataPath = date('Ym');
        $use = 'thumb';
        $filename = \think\facade\Filesystem::disk('public')->putFile($dataPath, $file, function () use ($md5) {
            return $md5;
        });
        if ($filename) {
            //写入到附件表
            $data = [];
            $path = get_config('filesystem.disks.public.url');
            $data['filepath'] = $path . '/' . $filename;
            $data['name'] = $file->getOriginalName();
            $data['mimetype'] = $file->getOriginalMime();
            $data['fileext'] = $file->extension();
            $data['filesize'] = $file->getSize();
            $data['filename'] = $filename;
            $data['sha1'] = $sha1;
            $data['md5'] = $md5;
            $data['module'] = \think\facade\App::initialize()->http->getName();
            $data['action'] = app('request')->action();
            $data['uploadip'] = app('request')->ip();
            $data['created_at'] = time();
            $data['user_id'] = get_login_admin('id') ? get_login_admin('id') : 0;
            if ($data['module'] = 'admin') {
                //通过后台上传的文件直接审核通过
                $data['status'] = 1;
                $data['admin_id'] = $data['user_id'];
                $data['audit_time'] = time();
            }
            $data['use'] = request()->has('use') ? request()->param('use') : $use; //附件用处
            $res['id'] = Db::name('file')->insertGetId($data);
            $res['filepath'] = $data['filepath'];
            $res['name'] = $data['name'];
            $res['filename'] = $data['filename'];
            add_log('upload', $data['user_id'], $data);
			if($sourse == 'editormd'){
				//editormd编辑器上传返回
				return json(['success'=>1,'message'=>'上传成功','url'=>$data['filepath']]);
			}
			else if($sourse == 'tinymce'){
				//tinymce编辑器上传返回
				return json(['success'=>1,'message'=>'上传成功','location'=>$data['filepath']]);
			}
			else{
				//普通上传返回
				return output(0, '上传成功', $res);
			}            
        } else {
            return output(1, '上传失败，请重试');
        }
    }
	
    //获取权限树所需的节点列表
    public function get_rule()
    {
        $rule = get_admin_menu();
        $group = [];
        if (!empty(get_params('id'))) {
            $group = get_admin_role_info(get_params('id'))['rules'];
        }
        $list = create_tree_list(0, $rule, $group);
        return output(0, '', $list);
    }

    //获取关键字
    public function get_keyword_cate()
    {
        $keyword = get_keywords();
        return output(0, '', $keyword);
    }

    //获取话题
    public function get_topics_cate()
    {
        $topic = get_topics();
        return output(0, '', $topic);
    }

    //清空缓存
    public function cache_clear()
    {
        \think\facade\Cache::clear();
        return output(0, '系统缓存已清空');
    }

    //发送测试邮件
    public function email_to($email)
    {
        $name = empty(get_config('webconfig.admin_title')) ? '系统' : get_config('webconfig.admin_title');
        if (send_email($email, "一封来自{$name}的测试邮件。")) {
            return output(0, '发送成功，请注意查收');
        }
        return output(1, '发送失败');
    }

    //修改个人信息
    public function edit_personal()
    {
        return view('admin/edit_personal', [
            'admin' => get_login_admin(),
        ]);
    }

    //保存个人信息修改
    public function personal_submit()
    {
        if (request()->isAjax()) {
            $param = get_params();
            try {
                validate(AdminCheck::class)->scene('editPersonal')->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            unset($param['username']);
            $uid = get_login_admin('id');
            Db::name('admin')->where([
                'id' => $uid,
            ])->strict(false)->field(true)->update($param);
            $session_admin = get_config('app.session_admin');
            Session::set($session_admin, Db::name('admin')->find($uid));
            return output();
        }
    }

    //修改密码
    public function edit_password()
    {
        return view('admin/edit_password', [
            'admin' => get_login_admin(),
        ]);
    }

    //保存密码修改
    public function password_submit()
    {
        if (request()->isAjax()) {
            $param = get_params();
            try {
                validate(AdminCheck::class)->scene('editpwd')->check($param);
            } catch (ValidateException $e) {
                // 验证失败 输出错误信息
                return output(1, $e->getError());
            }
            $admin = get_login_admin();
            if (set_password($param['old_pwd'], $admin['salt']) !== $admin['pwd']) {
                return output(1, '旧密码不正确!');
            }
            unset($param['username']);
            $param['salt'] = set_salt(20);
            $param['pwd'] = set_password($param['pwd'], $param['salt']);
            Db::name('admin')->where([
                'id' => $admin['id'],
            ])->strict(false)->field(true)->update($param);
            $session_admin = get_config('app.session_admin');
            Session::set($session_admin, Db::name('admin')->find($admin['id']));
            return output();
        }
    }

    // 测试邮件发送
    public function email_test()
    {
        $sender = get_params('email');
        //检查是否邮箱格式		
		$validate = \think\facade\Validate::rule([
			'email' => 'email'
		]);

		$data = [
			'email' => $sender
		];

		if (!$validate->check($data)) {
			return output(1, $validate->getError());
		}		
		
        $email_config = \think\facade\Db::name('config')->where('name', 'email')->find();
        $config = unserialize($email_config['content']);
        $content = $config['template'];
        //所有项目必须填写
        if (empty($config['smtp']) || empty($config['smtp_port']) || empty($config['smtp_user']) || empty($config['smtp_pwd'])) {
            return output(1, '请完善邮件配置信息！');
        }

        $send = send_email($sender, '测试邮件', $content);
        if ($send) {
            return output(0, '邮件发送成功！');
        } else {
            return output(1, '邮件发送失败！');
        }
    }

    //首页获取
    public function get_admin_list()
    {
        $content = Db::name('admin')
            ->where(['status' => 1])
            ->order('id desc')
            ->limit(10)
            ->select()->toArray();
        $res['data'] = $content;
        return table_output(0, '', $res);
    }

    //首页获取最新10位用户
    public function get_user_list()
    {
        $list = Db::name('User')
            ->where(['status' => 1])
            ->order('id desc')
            ->limit(10)
            ->select()->toArray();
        foreach ($list as $key => $val) {
            $list[$key]['last_login_time'] = date('Y-m-d H:i:s', $val['last_login_time']);
        }
        $res['data'] = $list;
        return table_output(0, '', $res);
    }

    //首页文章
    public function get_article_list()
    {
        $list = Db::name('Article')
            ->field('a.id,a.title,a.read,a.status,a.created_at,c.title as cate_title')
            ->alias('a')
            ->join('article_cate c', 'a.cate_id = c.id')
            ->where(['a.deleted_at' => 0])
            ->order('a.id desc')
            ->limit(10)
            ->select()->toArray();
        foreach ($list as $key => $val) {
            $list[$key]['created_at'] = date('Y-m-d H:i', $val['created_at']);
        }
        $res['data'] = $list;
        return table_output(0, '', $res);
    }

    //系统操作日志
    public function log_list()
    {
        return view('admin/log_list');
    }

    //获取系统操作日志
    public function get_log_list()
    {
        $param = get_params();
        $log = new AdminLogModel();
        $content = $log->get_log_list($param);
        return table_output(0, '', $content);
    }
	
    //获取访问记录
    public function get_view_data()
    {
        $param = get_params();
        $first_time = time();
        $second_time = $first_time - 86400;
        $three_time = $first_time - 86400*365;
        $begin_first = strtotime(date('Y-m-d', $first_time) . " 00:00:00");
        $end_first = strtotime(date('Y-m-d', $first_time) . " 23:59:59");
        $begin_second = strtotime(date('Y-m-d', $second_time) . " 00:00:00");
        $end_second = strtotime(date('Y-m-d', $second_time) . " 23:59:59");
		$begin_three = strtotime(date('Y-m-d', $three_time) . " 00:00:00");
        $data_first = Db::name('UserLog')->field('created_at')->whereBetween('created_at', "$begin_first,$end_first")->select();
        $data_second = Db::name('UserLog')->field('created_at')->whereBetween('created_at', "$begin_second,$end_second")->select();

		$data_three = Db::name('UserLog')->field('created_at')->whereBetween('created_at', "$begin_three,$end_first")->select();		
		
        return output(0, '', ['data_first' => hour_document($data_first), 'data_second' => hour_document($data_second), 'data_three'=>date_document($data_three)]);
    }

}
