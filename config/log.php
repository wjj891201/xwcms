<?php

// +----------------------------------------------------------------------
// | 日誌設置
// +----------------------------------------------------------------------
return [
    // 默認日誌記錄通道
    'default'      => env('log.channel', 'file'),
    // 日誌記錄級別
    'level'        => [],
    // 日誌類型記錄的通道 ['error'=>'email',...]
    'type_channel' => [],
    // 關閉全局日誌寫入
    'close'        => false,
    // 全局日誌處理 支持閉包
    'processor'    => null,

    // 日誌通道列表
    'channels'     => [
        'file' => [
            // 日誌記錄方式
            'type'           => 'File',
            // 日誌保存目錄
            'path'           => '',
            // 單檔日誌寫入
            'single'         => false,
            // 獨立日誌級別
            'apart_level'    => [],
            // 最大日誌檔數量
            'max_files'      => 0,
            // 使用JSON格式記錄
            'json'           => false,
            // 日誌處理
            'processor'      => null,
            // 關閉通道日誌寫入
            'close'          => false,
            // 日誌輸出格式化
            'format'         => '[%s][%s] %s',
            // 是否即時寫入
            'realtime_write' => false,
        ],
        // 其他日誌通道配置
    ],
    'admin_action' => [
        'login'      => '登錄',
        'upload'     => '上傳',
        'down'       => '下載',
        'bak'        => '備份',
        'optimize'   => '優化',
        'repair'     => '修復',
        'reduction'  => '還原',
        'import'     => '導入',
        'export'     => '導出',
        'add'        => '新增',
        'edit'       => '編輯',
        'view'       => '查看',
        'save'       => '保存',
        'delete'     => '刪除',
        'send'       => '發送',
        'disable'    => '禁用',
        'recovery'   => '恢復',
        'reset'      => '重新設置',
    ],
    'user_action' => [
        'login'      => '登錄',
        'module'     => '拉取',
        'reg'        => '註冊',
        'upload'     => '上傳',
        'down'       => '下載',
        'import'     => '導入',
        'export'     => '導出',
        'add'        => '新增',
        'edit'       => '編輯',
        'view'       => '查看',
        'save'       => '保存',
        'delete'     => '刪除',
        'sign'       => '簽到',
        'join'       => '報名',
        'install'    => '安裝',
        'spider'     => '爬行',
        'search'     => '搜索',
        'api'        => 'API請求',
    ],
];
