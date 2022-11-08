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

                'reload' => 10,

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
                            'target' => 'blank', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前自定义页面 / blank - 新自定义页面'
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
                                'action' => 'goSetting',
                                'target' => 'blank',
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
     * 编辑
     *
     * @BePermission("编辑", ordering="3.11")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        $page = Be::getService('App.Cms.Admin.Page')->create();
        $response->redirect(beAdminUrl('Cms.Page.setting', ['pageId' => $page->id]));
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function goSetting()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Cms.Page.setting', ['pageId' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function setting()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $response->set('title', '编辑自定义页面');

        $pageId = $request->get('pageId', '');
        $response->set('pageId', $pageId);

        $page = Be::getService('App.Cms.Admin.Page')->getPage($pageId);
        $response->set('page', $page);

        $pageDefault = Be::getService('App.System.Admin.Theme')->getPage(Be::getConfig('App.System.Theme')->default, 'default');
        $response->set('pageDefault', $pageDefault);

        $response->display();
    }

    /**
     * 配置页面
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function editPage()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $service = Be::getService('App.Cms.Admin.Page');

        if ($request->isAjax()) {
            $service->editPage($pageId, $request->json('formData'));
            $response->set('success', true);
            $response->json();
        } else {
            $drivers = $service->getPageDrivers($pageId);
            $response->set('drivers', $drivers);

            $response->set('editUrl', beAdminUrl('Cms.Page.editPage', ['pageId' => $pageId]));
            $response->set('resetUrl', beAdminUrl('Cms.Page.resetPage', ['pageId' => $pageId]));

            $response->display('App.Cms.Admin.Page.edit', 'Blank');
        }
    }

    /**
     * 页面 恢复默认值
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function resetPage()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->resetPage($pageId);

        $response->success('重置成功！');
    }


    /**
     * 配置方位
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function editPosition()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');
        $position = $request->get('position', '');

        $service = Be::getService('App.Cms.Admin.Page');
        $serviceTheme = Be::getService('App.System.Admin.Theme');

        if ($request->isAjax()) {
            $service->editPosition($pageId, $position, $request->json('formData'));
            $response->set('success', true);
            $response->json();
        } else {
            $response->set('pageId', $pageId);
            $response->set('position', $position);

            $positionDescription = $serviceTheme->getPositionDescription($position);
            $response->set('positionDescription', $positionDescription);

            $page = Be::getService('App.Cms.Admin.Page')->getPage($pageId);
            $response->set('page', $page);

            $response->display('App.Cms.Admin.Page.editPosition', 'Blank');
        }
    }

    /**
     * 方位 恢复默认值
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function resetPosition()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->get('position', '');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->resetPosition($pageId, $position);

        $response->success('重置成功！');
    }

    /**
     * 编辑 部件
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function editSection()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->get('position', '');
        $sectionIndex = $request->get('sectionIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');

        if ($request->isAjax()) {
            $service->editSection($pageId, $position, $sectionIndex, $request->json('formData'));
            $response->set('success', true);
            $response->json();
        } else {
            $drivers = $service->getSectionDrivers($pageId, $position, $sectionIndex);
            $response->set('drivers', $drivers);

            $response->set('editUrl', beAdminUrl('Cms.Page.editSection', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex]));
            $response->set('resetUrl', beAdminUrl('Cms.Page.resetSection', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex]));

            $response->display('App.Cms.Admin.Page.edit', 'Blank');
        }
    }

    /**
     * 新增部件
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function addSection()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->json('position', '');

        $sectionName = $request->json('sectionName', '');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->addSection($pageId, $position, $sectionName);

        $page = $service->getPage($pageId);
        $response->set('page', $page);

        $response->success('保存成功！');
    }

    /**
     * 删除部件
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function deleteSection()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->json('position', '');

        $sectionIndex = $request->json('sectionIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->deleteSection($pageId, $position, $sectionIndex);

        $page = $service->getPage($pageId);
        $response->set('page', $page);

        $response->success('保存成功！');
    }

    /**
     * 部件排序
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function sortSection()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->json('position', '');

        $oldIndex = $request->json('oldIndex', -1, 'int');
        $newIndex = $request->json('newIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->sortSection($pageId, $position, $oldIndex, $newIndex);

        $page = $service->getPage($pageId);
        $response->set('page', $page);

        $response->success('保存成功！');
    }

    /**
     * 部件 恢复默认值
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function resetSection()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->get('position', '');
        $sectionIndex = $request->get('sectionIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->resetSection($pageId, $position, $sectionIndex);

        $response->success('重置成功！');

        Be::getRuntime()->reload();
    }

    /**
     * 编辑部件子项
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function editSectionItem()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->get('position', '');
        $sectionIndex = $request->get('sectionIndex', -1, 'int');

        $itemIndex = $request->get('itemIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');

        if ($request->isAjax()) {
            $service->editSectionItem($pageId, $position, $sectionIndex, $itemIndex, $request->json('formData'));
            $response->set('success', true);
            $response->json();
        } else {
            $drivers = $service->getSectionItemDrivers($pageId, $position, $sectionIndex, $itemIndex);
            $response->set('drivers', $drivers);

            $response->set('editUrl', beAdminUrl('Cms.Page.editSectionItem', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex, 'itemIndex' => $itemIndex]));
            $response->set('resetUrl', beAdminUrl('Cms.Page.resetSectionItem', ['pageId' => $pageId, 'position' => $position, 'sectionIndex' => $sectionIndex, 'itemIndex' => $itemIndex]));

            $response->display('App.Cms.Admin.Page.edit', 'Blank');
        }
    }

    /**
     * 新增部件子项
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function addSectionItem()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->get('position', '');
        $sectionIndex = $request->get('sectionIndex', -1, 'int');

        $itemName = $request->get('itemName', '');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->addSectionItem($pageId, $position, $sectionIndex, $itemName);

        $page = $service->getPage($pageId);
        $response->set('page', $page);

        $response->success('保存成功！');
    }

    /**
     * 删除部件子项
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function deleteSectionItem()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->json('position', '');
        $sectionIndex = $request->json('sectionIndex', -1, 'int');

        $itemIndex = $request->json('itemIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->deleteSectionItem($pageId, $position, $sectionIndex, $itemIndex);

        $page = $service->getPage($pageId);
        $response->set('page', $page);

        $response->success('保存成功！');
    }

    /**
     * 部件子项排序
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function sortSectionItem()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->json('position', '');

        $sectionIndex = $request->json('sectionIndex', -1, 'int');

        $oldIndex = $request->json('oldIndex', -1, 'int');
        $newIndex = $request->json('newIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->sortSectionItem($pageId, $position, $sectionIndex, $oldIndex, $newIndex);

        $page = $service->getPage($pageId);
        $response->set('page', $page);

        $response->success('保存成功！');
    }

    /**
     * 部件子项恢复默认值
     *
     * @BePermission("编辑", ordering="3.12")
     */
    public function resetSectionItem()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageId = $request->get('pageId', '');

        $position = $request->get('position', '');
        $sectionIndex = $request->get('sectionIndex', -1, 'int');

        $itemIndex = $request->get('itemIndex', -1, 'int');

        $service = Be::getService('App.Cms.Admin.Page');
        $service->resetSectionItem($pageId, $position, $sectionIndex, $itemIndex);

        $response->success('重置成功！');
    }

    /**
     * 预览
     *
     * @BePermission("*")
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