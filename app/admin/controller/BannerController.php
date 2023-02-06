<?php


declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\model\BannerCateModel;
use app\admin\model\BannerModel;
use app\admin\validate\BannerCheck;
use Carbon\Carbon;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\View;

class BannerController extends BaseController
{
    public function cate_list()
    {
        if (request()->isAjax()) {
            $param = get_params();
            $where = array();
            if (!empty($param['keywords'])) {
                $where[] = ['id|name|title|desc', 'like', '%' . $param['keywords'] . '%'];
            }
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $slide = BannerCateModel::where($where)
                ->order('created_at asc')
                ->paginate($rows, false, ['query' => $param]);
            return table_output(0, '', $slide);
        } else {
            return view();
        }
    }

    //添加
    public function cate_add()
    {
        $param = get_params();
        if (request()->isAjax()) {
            if (!empty($param['id']) && $param['id'] > 0) {
                try {
                    validate(BannerCheck::class)->scene('edit')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['updated_at'] = Carbon::now()->toDateString();
                $res = BannerCateModel::where('id', $param['id'])->strict(false)->field(true)->update($param);
                if ($res) {
                    add_log('edit', $param['id'], $param);
                }

                clear_cache('banner');
                return output();
            } else {
                try {
                    validate(BannerCheck::class)->scene('add')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['created_at'] = Carbon::now()->toDateString();
                $sid = BannerCateModel::strict(false)->field(true)->insertGetId($param);
                if ($sid) {
                    add_log('add', $sid, $param);
                }

                // 删除banner缓存
                clear_cache('banner');
                return output();
            }
        }
		else{
			$id = isset($param['id']) ? $param['id'] : 0;
			if ($id > 0) {
				$banner_cate = Db::name('banner_cate')->where(['id' => $id])->find();
				View::assign('banner_cate', $banner_cate);
			}
			View::assign('id', $id);
			return view();
		}
    }

    //删除
    public function cate_delete()
    {
        $id = get_params("id");
        $count = Db::name('banner')->where([
            'cate_id' => $id,
        ])->count();
        if ($count > 0) {
            return output(1, '该组下还有Banner，无法删除');
        }
        if (Db::name('banner')->delete($id) !== false) {
            add_log('delete', $id);
            clear_cache('banner');
            return output(0, "删除成功");
        } else {
            return output(1, "删除失败");
        }
    }

    //管理幻灯片
    public function info()
    {
        $param = get_params();
        if (request()->isAjax()) {
            $where = array();
            $where[] = ['s.cate_id', '=', $param['id']];
            $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
            $slideInfoList = BannerModel::where($where)
                ->alias('s')
                ->join('File f', 's.img=f.id', 'LEFT')
                ->field('s.*,f.filepath')
                ->order('s.sort desc, s.id desc')
                ->paginate($rows, false, ['query' => $param]);
            return table_output(0, '', $slideInfoList);
        } else {
            return view('', [
                'cate_id' => $param['id'],
            ]);
        }
    }

    //幻灯片列表
    public function list()
    {
        $param = get_params();
        $where = array();
        $where[] = ['s.cate_id', '=', $param['id']];
        $rows = empty($param['limit']) ? get_config('app.page_size') : $param['limit'];
        $slideInfoList = BannerModel::where($where)
            ->alias('s')
            ->join('File f', 's.img=f.id', 'LEFT')
            ->field('s.*,f.filepath')
            ->order('s.sort desc, s.id desc')
            ->paginate($rows, false, ['query' => $param]);
        return table_output(0, '', $slideInfoList);
    }

    //添加幻灯片
    public function add()
    {
        $param = get_params();
        if (request()->isAjax()) {
            if (!empty($param['id']) && $param['id'] > 0) {
                try {
                    validate(BannerCheck::class)->scene('editInfo')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['updated_at'] = Carbon::now()->toDateString();
                $res = BannerModel::where(['id' => $param['id']])->strict(false)->field(true)->update($param);
                if ($res) {
                    add_log('edit', $param['id'], $param);
                }

                // 删除缓存
                clear_cache('banner');
                return output();
            } else {
                try {
                    validate(BannerCheck::class)->scene('addInfo')->check($param);
                } catch (ValidateException $e) {
                    // 验证失败 输出错误信息
                    return output(1, $e->getError());
                }
                $param['created_at'] = Carbon::now()->toDateString();
                $sid = BannerModel::strict(false)->field(true)->insertGetId($param);
                if ($sid) {
                    add_log('add', $sid, $param);
                }

                // 删除缓存
                clear_cache('banner');
                return output();
            }
        }
		else{
            $banner = array();
			$id = isset($param['id']) ? $param['id'] : 0;
            $cate_id = isset($param['sid']) ? $param['sid'] : 0;
			if ($id > 0) {
				$banner = Db::name('banner')->where(['id' => $id])->find();
				$cate_id = $banner['cate_id'];
			}
            View::assign('banner', $banner);
			View::assign('id', $id);
			View::assign('cate_id', $cate_id);
			return view();
		}
    }

    //删除幻灯片
    public function delete()
    {
        $id = get_params("id");
        if (Db::name('banner')->delete($id) !== false) {
            add_log('delete', $id);
            clear_cache('banner');
            return output(0, "删除成功");
        } else {
            return output(1, "删除失败");
        }
    }
}
