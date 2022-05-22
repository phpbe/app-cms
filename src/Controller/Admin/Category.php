<?php

namespace Be\App\Cms\Controller\Admin;


use Be\AdminPlugin\Detail\Item\DetailItemHtml;
use Be\AdminPlugin\Detail\Item\DetailItemToggleIcon;
use Be\AdminPlugin\Table\Item\TableItemImage;
use Be\AdminPlugin\Table\Item\TableItemLink;
use Be\AdminPlugin\Table\Item\TableItemSelection;
use Be\App\ControllerException;
use Be\Be;

/**
 * @BeMenuGroup("内容管理")
 * @BePermissionGroup("内容管理")
 */
class Category
{

    /**
     * 文章分类
     *
     * @BeMenu("文章分类", icon="el-icon-folder", ordering="1.2")
     * @BePermission("文章分类", ordering="1.2")
     */
    public function categories()
    {
        Be::getAdminPlugin('Curd')->setting([

            'label' => '文章分类',
            'table' => 'cms_category',

            'grid' => [
                'title' => '文章分类',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'orderBy' => 'ordering',
                'orderByDir' => 'ASC',

                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                    ],
                ],

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '新建文章分类',
                            'action' => 'create',
                            'target' => 'self', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
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
                            'name' => 'name',
                            'label' => '名称',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'task' => 'detail',
                            'drawer' => [
                                'width' => '80%'
                            ],
                        ],
                        [
                            'name' => 'article_count',
                            'label' => '文章数量',
                            'align' => 'center',
                            'width' => '120',
                            'driver' => TableItemLink::class,
                            'value' => function ($row) {
                                $sql = 'SELECT COUNT(*) FROM cms_article_category WHERE category_id = ?';
                                $count = Be::getDb()->getValue($sql, [$row['id']]);
                                return $count;
                            },
                            'action' => 'goArticles',
                            'target' => 'self',
                        ],
                        [
                            'name' => 'ordering',
                            'label' => '排序',
                            'width' => '120',
                            'sortable' => true,
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
                'title' => '文章分类详情',
                'theme' => 'CmsBlank',
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
                            'name' => 'description',
                            'label' => '描述',
                            'driver' => DetailItemHtml::class,
                        ],
                        [
                            'name' => 'url',
                            'label' => '网址',
                            'value' => function ($row) {
                                // return Be::getRequest()->getRootUrl() . '/article/category/' . $row['url'];
                                return beUrl('Cms.Category.articles', ['id' => $row['id']]);
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
                            'name' => 'ordering',
                            'label' => '排序',
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
                        $postData = Be::getRequest()->json();

                        $categoryIds = [];
                        if (isset($postData['selectedRows'])) {
                            foreach ($postData['selectedRows'] as $row) {
                                $categoryIds[] = $row['id'];
                            }
                        } elseif (isset($postData['row'])) {
                            $categoryIds[] = $postData['row']['id'];
                        }

                        $articleIds = Be::getTable('cms_article_category')
                            ->where('category_id', 'IN',  $categoryIds)
                            ->getValues('article_id');
                        if (count($articleIds) > 0) {
                            Be::getService('App.Cms.Admin.Article')->onUpdate($articleIds);
                        }

                        Be::getService('App.Cms.Admin.Category')->onUpdate($categoryIds);
                    },
                ],
            ],

        ])->execute();
    }

    /**
     * 新建文章分类
     *
     * @BePermission("新建", ordering="1.21")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Cms.Admin.Category')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '新建文章分类成功！');
                $response->json();
            } catch (\Throwable $t) {
                $response->set('success', false);
                $response->set('message', $t->getMessage());
                $response->json();
            }
        } else {
            $response->set('category', false);
            $response->set('title', '新建文章分类');
            $response->display('App.Cms.Admin.Category.edit');
        }
    }

    /**
     * 编辑
     *
     * @BePermission("编辑", ordering="1.32")
     */
    public function edit()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        if ($request->isAjax()) {
            try {
                Be::getService('App.Cms.Admin.Category')->edit($request->json('formData'));
                $response->set('success', true);
                $response->set('message', '编辑文章分类成功！');
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
                    $response->redirect(beAdminUrl('Cms.Category.edit', ['id' => $postData['row']['id']]));
                }
            }
        } else {
            $pageId = $request->get('id', '');
            $category = Be::getService('App.Cms.Admin.Category')->getCategory($pageId);
            $response->set('category', $category);
            $response->set('title', '编辑文章分类');
            $response->display('App.Cms.Admin.Category.edit');
        }
    }

    /**
     * 预览
     *
     * @return void
     */
    public function preview() {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beUrl('Cms.Category.articles', ['id' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 指定文章分类下的文章分类文章管理
     *
     * @BePermission("文章分类文章管理", ordering="1.33")
     */
    public function goArticles()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $postData = $request->post('data', '', '');
        if ($postData) {
            $postData = json_decode($postData, true);
            if (isset($postData['row']['id']) && $postData['row']['id']) {
                $response->redirect(beAdminUrl('Cms.Category.articles', ['id' => $postData['row']['id']]));
            }
        }
    }

    /**
     * 指定文章分类下的文章分类文章管理
     *
     * @BePermission("文章分类文章管理")
     */
    public function articles()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $categoryId = $request->get('id', '');
        $category = Be::getService('App.Cms.Admin.Category')->getCategory($categoryId);

        $filter = [
            ['is_delete', '=', '0'],
        ];

        $articleIds = Be::getTable('cms_article_category')
            ->where('category_id', $categoryId)
            ->getValues('article_id');
        if (count($articleIds) > 0) {
            $filter[] = [
                'id', 'IN', $articleIds
            ];
        } else {
            $filter[] = [
                'id', '=', ''
            ];
        }

        Be::getAdminPlugin('Curd')->setting([
            'label' => '文章分类 ' . $category->name . ' 下的文章',
            'table' => 'cms_article',
            'grid' => [
                'title' => '文章分类 ' . $category->name . ' 下的文章管理',

                'filter' => $filter,

                'titleRightToolbar' => [
                    'items' => [
                        [
                            'label' => '返回',
                            'url' => beAdminUrl('Cms.Category.categories'),
                            'target' => 'self',
                            'ui' => [
                                'icon' => 'el-icon-back'
                            ]
                        ],
                        [
                            'label' => '添加文章',
                            'url' => beAdminUrl('Cms.Category.addArticle', ['id' => $categoryId]),
                            'target' => 'drawer', // 'ajax - ajax请求 / dialog - 对话框窗口 / drawer - 抽屉 / self - 当前页面 / blank - 新页面'
                            'drawer' => [
                                'width' => '60%',
                            ],
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
                            'label' => '批量从此文章分类中移除',
                            'task' => 'fieldEdit',
                            'target' => 'ajax',
                            'confirm' => '确认要从此文章分类中移除吗？',
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

                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'image',
                            'label' => '封面图片',
                            'width' => '90',
                            'driver' => TableItemImage::class,
                            'ui' => [
                                'style' => 'max-width: 60px; max-height: 60px'
                            ],
                            'value' => function($row) {
                                if ($row['image'] === '') {
                                    return Be::getProperty('App.Cms')->getUrl() . '/Template/Article/images/no-image.jpg';
                                }
                                return $row['image'];
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '文章标题',
                            'driver' => TableItemLink::class,
                            'align' => 'left',
                            'url' => beAdminUrl('Cms.Article.articles', ['task'=>'detail']),
                            'drawer' => [
                                'width' => '80%'
                            ],
                        ],
                    ],

                    'operation' => [
                        'label' => '操作',
                        'width' => '150',
                        'items' => [
                            [
                                'label' => '',
                                'tooltip' => '预览',
                                'url' => beAdminUrl('Cms.Article.preview'),
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
                                'tooltip' => '从此文章分类中移除',
                                'url' => beAdminUrl('Cms.Category.deleteArticle', ['id' => $categoryId]),
                                'confirm' => '确认要从此文章分类中移除么？',
                                'target' => 'ajax',
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
        ])->execute();
    }

    /**
     * 指定文章分类下的文章 - 添加
     *
     * @BePermission("文章分类文章管理")
     */
    public function addArticle()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $categoryId = $request->get('id', '');
        $category = Be::getService('App.Cms.Admin.Category')->getCategory($categoryId);

        $filter = [
            ['is_delete', '=', '0'],
        ];

        $articleIds = Be::getTable('cms_article_category')
            ->where('category_id', $categoryId)
            ->getValues('article_id');
        if (count($articleIds) > 0) {
            $filter[] = [
                'id', 'NOT IN', $articleIds
            ];
        }

        Be::getAdminPlugin('Curd')->setting([
            'label' => '向文章分类 ' . $category->name . ' 添加文章',
            'table' => 'cms_article',
            'opLog' => false,
            'grid' => [
                'title' => '向文章分类 ' . $category->name . ' 添加文章',
                'theme' => 'Blank',

                'filter' => $filter,

                'form' => [
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '文章标题',
                        ],
                    ],
                ],

                'tableToolbar' => [
                    'items' => [
                        [
                            'label' => '添加到文章分类 ' . $category->name . ' 中',
                            'url' => beAdminUrl('Cms.Category.addArticleSave', ['id' => $categoryId]),
                            'target' => 'ajax',
                            'ui' => [
                                'icon' => 'el-icon-plus',
                                'type' => 'primary'
                            ]
                        ],
                    ]
                ],

                'table' => [

                    'items' => [
                        [
                            'driver' => TableItemSelection::class,
                            'width' => '50',
                        ],
                        [
                            'name' => 'image',
                            'label' => '封面图片',
                            'width' => '90',
                            'driver' => TableItemImage::class,
                            'ui' => [
                                'style' => 'max-width: 60px; max-height: 60px'
                            ],
                            'value' => function($row) {
                                if ($row['image'] === '') {
                                    return Be::getProperty('App.Cms')->getUrl() . '/Template/Article/images/no-image.jpg';
                                }
                                return $row['image'];
                            },
                        ],
                        [
                            'name' => 'title',
                            'label' => '文章标题',
                            'align' => 'left',
                        ],
                    ],
                ],
            ],
        ])->execute();
    }

    /**
     * 指定文章分类下的文章 - 添加
     *
     * @BePermission("文章分类文章管理")
     */
    public function addArticleSave()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $categoryId = $request->get('id', '');
            $selectedRows = $request->json('selectedRows');
            if (!is_array($selectedRows) || count($selectedRows) == 0) {
                throw new ControllerException('请选择文章！');
            }

            $articleIds = [];
            foreach ($selectedRows as $selectedRow) {
                $articleIds[] = $selectedRow['id'];
            }

            Be::getService('App.Cms.Admin.Category')->addArticle($categoryId, $articleIds);
            $response->set('success', true);
            $response->set('message', '编辑文章分类成功！');
            $response->set('callback', 'parent.closeDrawerAndReload();');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

    /**
     * 指定文章分类下的文章 - 删除
     *
     * @BePermission("文章分类文章管理")
     */
    public function deleteArticle()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();
        try {
            $categoryId = $request->get('id', '');
            $articleIds = [];
            $postData = $request->json();
            if (isset($postData['selectedRows'])) {
                if (is_array($postData['selectedRows']) && count($postData['selectedRows']) > 0) {
                    foreach ($postData['selectedRows'] as $selectedRow) {
                        $articleIds[] = $selectedRow['id'];
                    }
                }
            } elseif (isset($postData['row'])) {
                $articleIds[] = $postData['row']['id'];
            }

            if (count($articleIds) == 0) {
                throw new ControllerException('请选择文章！');
            }

            Be::getService('App.Cms.Admin.Category')->deleteArticle($categoryId, $articleIds);
            $response->set('success', true);
            $response->set('message', '编辑文章分类成功！');
            $response->json();
        } catch (\Throwable $t) {
            $response->set('success', false);
            $response->set('message', $t->getMessage());
            $response->json();
        }
    }

}