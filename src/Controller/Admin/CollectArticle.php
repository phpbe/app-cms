<?php

namespace Be\App\Cms\Controller\Admin;


use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemImage;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\AdminPlugin\Table\Item\TableItemToggleIcon;
use Be\AdminPlugin\Toolbar\Item\ToolbarItemDropDown;
use Be\Be;

/**
 * @BeMenuGroup("采集", icon="el-icon-download", ordering="2")
 * @BePermissionGroup("采集", ordering="2")
 */
class CollectArticle
{

    /**
     * 文章
     *
     * @BeMenu("采集的文章", icon="el-icon-tickets", ordering="2.1")
     * @BePermission("采集的文章", ordering="2.1")
     */
    public function collectArticles()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '采集的文章',
            'table' => 'cms_collect_article',

            'grid' => [
                'title' => '采集的文章',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'tab' => [
                    'name' => 'status',
                    'value' => '-1',
                    'nullValue' => '-1',
                    'keyValues' => [
                        '-1' => '全部',
                        '0' => '未导入',
                        '1' => '已导入',
                    ],
                    'counter' => true,
                    'buildSql' => function ($dbName, $formData) {
                        if (isset($formData['status'])) {
                            if ($formData['status'] === '0') {
                                return ['article_id', '=', ''];
                            } elseif ($formData['status'] === '1') {
                                return ['article_id', '!=', ''];
                            }
                        }
                        return '';
                    },
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
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
                            'label' => '批量导入',
                            'action' => 'import',
                            'drawer' => [
                                'title' => '批量导入',
                                'width' => '80%'
                            ],
                            'ui' => [
                                'icon' => 'el-icon-upload2',
                                'type' => 'success',
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
                            'ui' => [
                                'table-column' => [
                                    ':selectable' => 'function(row, index){return row.article_id === \'\';}',
                                ],
                            ],
                        ],
                        [
                            'name' => 'image',
                            'label' => '封面图片',
                            'width' => '90',
                            'driver' => TableItemImage::class,
                            'ui' => [
                                'style' => 'max-width: 60px; max-height: 60px'
                            ],
                            'value' => function ($row) {
                                if ($row['image'] === '') {
                                    return Be::getProperty('App.Cms')->getUrl() . '/Template/Article/images/no-image.jpg';
                                }
                                return $row['image'];
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'task' => 'detail',
                            'drawer' => [
                                'width' => '80%'
                            ],
                        ],
                        [
                            'name' => 'status',
                            'label' => '是否已导入',
                            'driver' => TableItemToggleIcon::class,
                            'width' => '90',
                            'value' => function ($row) {
                                return $row['article_id'] === '' ? '0' : '1';
                            },
                            'exportValue' => function ($row) {
                                return $row['article_id'] === '' ? '未导入' : '已导入';
                            },
                        ],
                        [
                            'name' => 'create_time',
                            'label' => '创建时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                        [
                            'name' => 'update_time',
                            'label' => '更新时间',
                            'width' => '180',
                            'sortable' => true,
                        ],
                    ],
                    'operation' => [
                        'label' => '操作',
                        'width' => '240',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '预览',
                                'action' => 'preview',
                                'target' => '_blank',
                                'ui' => [
                                    'type' => 'success',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                    ':disabled' => 'scope.row.article_id !== \'\'',
                                    'icon' => 'el-icon-view',
                                ],
                            ],
                            [
                                'label' => '',
                                'tooltip' => '导入',
                                'action' => 'import',
                                'drawer' => [
                                    'title' => '导入',
                                    'width' => '80%'
                                ],
                                'ui' => [
                                    'type' => 'warning',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                    ':disabled' => 'scope.row.article_id !== \'\'',
                                    'icon' => 'el-icon-upload2',
                                ],
                            ],
                            [
                                'label' => '',
                                'tooltip' => '编辑',
                                'action' => 'edit',
                                'target' => 'self',
                                'ui' => [
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                    ':disabled' => 'scope.row.article_id !== \'\'',
                                    'icon' => 'el-icon-edit',
                                ],
                            ],
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
                                    'icon' => 'el-icon-delete',
                                ],
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
                            'name' => 'unique_key',
                            'label' => '唯一键',
                        ],
                        [
                            'name' => 'image',
                            'label' => '封面图片',
                            'driver' => DetailItemImage::class,
                            'value' => function ($row) {
                                if ($row['image'] === '') {
                                    return Be::getProperty('App.Cms')->getUrl() . '/Template/Article/images/no-image.jpg';
                                }
                                return $row['image'];
                            },
                            'ui' => [
                                'style' => 'max-width: 128px;',
                            ],
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
                        [
                            'name' => 'summary',
                            'label' => '摘要',
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                            'driver' => DetailItemHtml::class,
                        ],
                        [
                            'name' => 'author',
                            'label' => '作者',
                        ],
                        [
                            'name' => 'publish_time',
                            'label' => '发布时间',
                        ],
                        [
                            'name' => 'article_id',
                            'label' => '导入的文章',
                            'driver' => DetailItemHtml::class,
                            'value' => function ($row) {
                                if ($row['article_id'] === '') {
                                    return '尚未导入';
                                } else {
                                    try {
                                        $article = Be::getService('App.Cms.Admin.Article')->getArticle($row['article_id']);
                                        return 'ID：' . $row['article_id'] . '<br>标题：' . $article->title;
                                    } catch (\Throwable $t) {
                                        return '导入的文章已删除！';
                                    }
                                }
                            }
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

            'fieldEdit' => [
                'events' => [
                    'before' => function ($tuple) {
                        $tuple->update_time = date('Y-m-d H:i:s');
                    },
                ],
            ],

        ])->execute();
    }

    /**
     * 编辑采集的文章
     *
     * @BePermission("编辑", ordering="2.12")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        if ($request->isAjax()) {
            try {
                Be::getService('App.Cms.Admin.CollectArticle')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑采集的文章成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } elseif ($request->isPost()) {
            $postData = $request->post('data', '', '');
            if ($postData) {
                $postData = json_decode($postData, true);
                if (isset($postData['row']['id']) && $postData['row']['id']) {
                    $response->redirect(beAdminUrl('Cms.CollectArticle.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $collectArticleId = $request->get('id', '');
            $collectArticle = Be::getService('App.Cms.Admin.CollectArticle')->getCollectArticle($collectArticleId);

            if ($collectArticle->article_id !== '') {
                $response->error('已导入的文章禁止编辑！');
                return;
            }

            $response->set('collectArticle', $collectArticle);
            $response->set('title', '编辑采集的文章');
            $response->display();
        }
    }

    /**
     * 预览
     *
     * @BePermission("*")
     */
    public function preview()
    {
        $request = Be::getRequest();
        $data = $request->post('data', '', '');
        $data = json_decode($data, true);
        Be::getResponse()->redirect(beUrl('Cms.CollectArticle.detail', ['id' => $data['row']['id']]));
    }

    /**
     * 导入
     *
     * @BePermission("导入", ordering="2.13")
     */
    public function import()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $data = $request->post('data', '', '');
        $data = json_decode($data, true);

        $collectArticles = [];
        if (isset($data['row'])) {
            $collectArticles[] = $data['row'];
        } elseif (isset($data['selectedRows'])) {
            $collectArticles = $data['selectedRows'];
        }

        if (count($collectArticles) === 0) {
            $response->error('您未选择文章！');
            return;
        }

        foreach ($collectArticles as &$collectArticle) {
            $collectArticle['category_ids'] = [];
        }
        unset($article);

        $response->set('title', '导入');
        $response->set('collectArticles', $collectArticles);

        $categoryKeyValues = Be::getService('App.Cms.Admin.Category')->getCategoryKeyValues();
        $response->set('categoryKeyValues', $categoryKeyValues);

        $response->display(null, 'Blank');
    }

    /**
     * 导入
     *
     * @BePermission("导入", ordering="2.13")
     */
    public function importSave()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $formData = $request->json('formData');
            $collectArticles = $formData['collectArticles'];
            Be::getService('App.Cms.Admin.CollectArticle')->import($collectArticles);
            $response->set('success', true);
            $response->set('message', '导入成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }



}
