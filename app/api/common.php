<?php

//读取文章分类列表
function get_article_cate()
{
    $cate = \think\facade\Db::name('ArticleCate')->order('created_at asc')->select()->toArray();
    return $cate;
}