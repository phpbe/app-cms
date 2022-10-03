<?php

namespace Be\App\Cms\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemImage;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Form\Item\FormItemSelect;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemSwitch;
use Be\AdminPlugin\Toolbar\Item\ToolbarItemButtonDropDown;
use Be\AdminPlugin\Toolbar\Item\ToolbarItemDropDown;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;
use Be\Request;
use Be\Response;

/**
 * @BeMenuGroup("文章")
 * @BePermissionGroup("文章")
 */
class ArticleComment extends Auth
{

    /**
     * 评论
     *
     * @BeMenu("评论", icon="bi-chat-left-text", ordering="1.2")
     * @BePermission("评论", ordering="1.2")
     */
    public function comments()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '文章评论',
            'table' => 'cms_article_comment',

            'grid' => [
                'title' => '文章评论列表',

                'filter' => [
                    ['is_delete', '=', '0'],
                    ['is_enable', '!=', '-1'],
                ],

                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',

                'tab' => [
                    'name' => 'is_enable',
                    'value' => Be::getRequest()->request('is_enable', '-100'),
                    'nullValue' => '-100',
                    'counter' => true,
                    'keyValues' => [
                        '-100' => '全部',
                        '1' => '已发布',
                        '0' => '未发布',
                    ],
                ],

                'titleToolbar' => [
                    'items' => [
                        [
                            'label' => '导出',
                            'driver' => ToolbarItemDropDown::class,
                            'ui' => [
                                'icon' => 'el-icon-download',
                            ],
                            'menus' => [
                                [
                                    'label' => 'CSV',
                                    'task' => 'export',
                                    'postData' => [
                                        'driver' => 'csv',
                                    ],
                                    'target' => 'blank',
                                ],
                                [
                                    'label' => 'EXCEL',
                                    'task' => 'export',
                                    'postData' => [
                                        'driver' => 'excel',
                                    ],
                                    'target' => 'blank',
                                ],
                            ]
                        ],
                    ]
                ],

                'tableToolbar' => [
                    'items' => [
                        [
                            'label' => '批量发布',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '1',
                            ],
                            'target' => 'ajax',
                            'confirm' => '确认要发布吗？',
                            'ui' => [
                                'icon' => 'el-icon-check',
                                'type' => 'success',
                            ]
                        ],
                        [
                            'label' => '批量取消发布',
                            'task' => 'fieldEdit',
                            'postData' => [
                                'field' => 'is_enable',
                                'value' => '0',
                            ],
                            'target' => 'ajax',
                            'confirm' => '确认要取消发布吗？',
                            'ui' => [
                                'icon' => 'el-icon-close',
                                'type' => 'warning',
                            ]
                        ],
                        [
                            'label' => '批量删除',
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'confirm' => '确认要删除吗？',
                            'postData' => [
                                'field' => 'is_delete',
                                'value' => '1',
                            ],
                            'ui' => [
                                'icon' => 'el-icon-delete',
                                'type' => 'danger'
                            ]
                        ],
                    ]
                ],


                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'article_id',
                            'label' => '文章',
                            'align' => 'left',
                            'value' => function ($row) {
                                $sql = 'SELECT title FROM cms_article WHERE id=?';
                                return Be::getDb()->getValue($sql, [$row['article_id']]);
                            },
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'task' => 'detail',
                            'drawer' => [
                                'width' => '80%'
                            ],
                            'width' => '160',
                        ],
                        [
                            'name' => 'email',
                            'label' => '邮箱',
                            'align' => 'left',
                            'width' => '160',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '发布',
                            'driver' => TableItemSwitch::class,
                            'target' => 'ajax',
                            'task' => 'fieldEdit',
                            'width' => '80',
                            'exportValue' => function ($row) {
                                return $row['is_enable'] ? '是' : '否';
                            },
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '80',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '删除',
                                'task' => 'fieldEdit',
                                'confirm' => '确认要删除么？',
                                'target' => 'ajax',
                                'postData' => [
                                    'field' => 'is_delete',
                                    'value' => 1,
                                ],
                                'ui' => [
                                    'type' => 'danger',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-delete',
                            ],
                        ]
                    ],
                ],
            ],

            'detail' => [
                'title' => '文章详情',
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                        [
                            'name' => 'email',
                            'label' => '邮箱',
                        ],
                        [
                            'name' => 'content',
                            'label' => '评论内容',
                            'driver' => DetailItemHtml::class,
                        ],
                        [
                            'name' => 'ip',
                            'label' => 'IP',
                        ],
                        [
                            'name' => 'is_enable',
                            'label' => '是否发布',
                            'driver' => DetailItemToggleIcon::class,
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                        ],
                    ]
                ],
            ],
        ])->execute();
    }


}
