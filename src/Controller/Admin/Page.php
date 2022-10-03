<?php

namespace Be\App\Cms\Controller\Admin;

use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\App\System\Controller\Admin\Auth;
use Be\Be;

/**
 * 自定义页面
 */
class Page extends Auth
{

    /**
     * 自定义页面列表
     *
     * @BeMenu("自定义页面", icon="el-icon-document", ordering="3")
     * @BePermission("自定义页面", ordering="3")
     */
    public function pages()
    {
        Be::getAdminPlugin('Curd')->setting([
            'label' => '自定义页面',
            'table' => 'cms_page',
            'grid' => [
                'title' => '自定义页面',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
                    ],
                ],

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '新建自定义页面',
                            'action' => 'create',
                            'target' => 'self', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前自定义页面 / blank - 新自定义页面'
                            'ui' => [
                                'icon' => 'el-icon-plus',
                                'type' => 'primary',
                            ]
                        ],
                    ]
                ],

                'tableToolbar' => [
                    'items' => [
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
                        'width' => '180',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '预览',
                                'task' => 'preview',
                                'target' => '_blank',
                                'ui' => [
                                    'type' => 'success',
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-view',
                            ],
                            [
                                'label' => '',
                                'tooltip' => '编辑',
                                'action' => 'edit',
                                'target' => 'self',
                                'ui' => [
                                    ':underline' => 'false',
                                    'style' => 'font-size: 20px;',
                                ],
                                'icon' => 'el-icon-edit',
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
                                ],
                                'icon' => 'el-icon-delete',
                            ],
                        ]
                    ],
                ],
            ],

            'detail' => [
                'title' => '自定义页面详情',
                'form' => [
                    'items' => [
                        [
                            'name' => 'id',
                            'label' => 'ID',
                        ],
                        [
                            'name' => 'title',
                            'label' => '标题',
                        ],
                        [
                            'name' => 'description',
                            'label' => '描述',
                            'driver' => DetailItemHtml::class,
                        ],
                        [
                            'name' => 'url',
                            'label' => '网址',
                            'value' => function ($row) {
                                // return Be::getRequest()->getRootUrl() . '/page/' . $row['url'];
                                return beUrl('Cms.Page.detail', ['id' => $row['id']]);
                            }
                        ],
                        [
                            'name' => 'seo',
                            'label' => 'SEO 独立编辑',
                            'driver' => DetailItemToggleIcon::class,
                        ],
                        [
                            'name' => 'seo_title',
                            'label' => 'SEO 标题',
                        ],
                        [
                            'name' => 'seo_description',
                            'label' => 'SEO 描述',
                        ],
                        [
                            'name' => 'seo_keywords',
                            'label' => 'SEO 关键词',
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
                        $postData = Be::getRequest()->json();
                        $field = $postData['postData']['field'];
                        if ($field === 'is_delete') {
                            $value = $postData['postData']['value'];
                            if ($value === 1) {
                                $tuple->url = $tuple->url . '-' . $tuple->id;
                            }
                        }

                        $tuple->update_time = date('Y-m-d H:i:s');
                    },
                    'success' => function () {
                        Be::getService('App.System.Task')->trigger('Cms.PageSyncRedis');
                    },
                ],
            ],

        ])->execute();
    }

    /**
     * 新建自定义页面
     *
     * @BePermission("新建", ordering="3.11")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                $page = Be::getService('App.Cms.Admin.Page')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建自定义页面成功！');
                $response->set('page', $page);
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            $response->set('page', false);

            $response->set('title', '新建自定义页面');

            $configPage = Be::getConfig('App.Cms.Page');
            $response->set('configPage', $configPage);

            $response->display('App.Cms.Admin.Page.edit');
        }
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                $page = Be::getService('App.Cms.Admin.Page')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑自定义页面成功！');
                $response->set('page', $page);
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
                    $response->redirect(beAdminUrl('Cms.Page.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $response->set('title', '编辑自定义页面');

            $pageId = $request->get('id', '');
            $page = Be::getService('App.Cms.Admin.Page')->getPage($pageId);
            $response->set('page', $page);

            $configPage = Be::getConfig('App.Cms.Page');
            $response->set('configPage', $configPage);

            $response->display('App.Cms.Admin.Page.edit');
        }
    }

    /**
     * 预览
     *
     * @BePermission("*")
     * @return void
     */
    public function preview()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beUrl('Cms.Page.detail', ['id' => $postData['row']['id']]));
            }
        }
    }


}