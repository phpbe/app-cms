<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Db\DbException;
use Be\Runtime\RuntimeException;
use Be\Util\Str\Pinyin;

class Page
{


    /**
     * 获取自定义页面列表
     *
     * @return array
     * @throws DbException|RuntimeException
     */
    public function getPages(): array
    {
        $sql = 'SELECT * FROM cms_page WHERE is_delete = 0';
        $categories = Be::getDb()->getObjects($sql);
        return $categories;
    }

    /**
     * 获取自定义页面键值对
     *
     * @return array
     * @throws DbException|RuntimeException
     */
    public function getPageKeyValues(): array
    {
        $sql = 'SELECT id, title FROM cms_page WHERE is_delete = 0';
        return Be::getDb()->getKeyValues($sql);
    }


    /**
     * 获取自定义页面
     *
     * @param string $pageId 自定义页面ID
     * @return \stdClass 自定义页面对象
     * @throws ServiceException
     */
    public function getPage(string $pageId): \stdClass
    {
        $tuplePage = Be::newTuple('cms_page');
        try {
            $tuplePage->load($pageId);
        } catch (\Throwable $t) {
            throw new ServiceException('自定义页面不存在！');
        }

        return $tuplePage->toObject();
    }


    /**
     * 编辑自定义页面
     *
     * @param array $data 自定义页面数据
     * @return bool
     * @throws DbException
     * @throws RuntimeException
     * @throws ServiceException
     */
    public function edit(array $data): bool
    {
        $db = Be::getDb();

        $isNew = true;
        $pageId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $pageId = $data['id'];
        }

        $tuplePage = Be::newTuple('cms_page');
        if (!$isNew) {
            try {
                $tuplePage->load($pageId);
            } catch (\Throwable $t) {
                throw new ServiceException('自定义页面（# ' . $pageId . '）不存在！');
            }

            if ($tuplePage->is_delete === 1) {
                throw new ServiceException('自定义页面（# ' . $pageId . '）不存在！');
            }
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            throw new ServiceException('标题未填写！');
        }
        $title = $data['title'];

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        $url = null;
        if (!isset($data['url']) || !is_string($data['url'])) {
            $urlTitle = strtolower($title);
            $url = Pinyin::convert($urlTitle, '-');
            if (strlen($url) > 200) {
                $url = Pinyin::convert($urlTitle, '-', true);
                if (strlen($url) > 200) {
                    $url = Pinyin::convert($urlTitle, '', true);
                }
            }
        } else {
            $url = $data['url'];
        }
        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            if ($isNew) {
                $urlExist = Be::newTable('cms_page')
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::newTable('cms_page')
                        ->where('url', $urlUnique)
                        ->where('id', '!=', $pageId)
                        ->getValue('COUNT(*)') > 0;
            }

            if ($urlExist) {
                $urlIndex++;
                $urlUnique = $url . '-' . $urlIndex;
            }
        } while ($urlExist);
        $url = $urlUnique;

        if (!isset($data['seo']) || $data['seo'] !== 1) {
            $data['seo'] = 0;
        }

        if (!isset($data['seo_title']) || !is_string($data['seo_title'])) {
            $data['seo_title'] = $title;
        }

        if (!isset($data['seo_description']) || !is_string($data['seo_description'])) {
            $data['seo_description'] = $data['description'];
        }

        if (!isset($data['seo_keywords']) || !is_string($data['seo_keywords'])) {
            $data['seo_keywords'] = '';
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tuplePage->title = $title;
            $tuplePage->description = $data['description'];
            $tuplePage->url = $url;
            $tuplePage->seo = $data['seo'];
            $tuplePage->seo_title = $data['seo_title'];
            $tuplePage->seo_description = $data['seo_description'];
            $tuplePage->seo_keywords = $data['seo_keywords'];
            $tuplePage->update_time = $now;
            if ($isNew) {
                $tuplePage->is_delete = 0;
                $tuplePage->create_time = $now;
                $tuplePage->insert();
            } else {
                $tuplePage->update();
            }

            $db->commit();

            $this->onUpdate([$tuplePage->id]);

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '自定义页面发生异常！');
        }

        return true;
    }

    /**
     * 删除自定义页面
     *
     * @param string $pageId 自定义页面ID
     * @return true
     */
    public function deletePage(string $pageId): bool
    {
        $tuplePage = Be::newTuple('cms_page');
        try {
            $tuplePage->load($pageId);
        } catch (\Throwable $t) {
            throw new ServiceException('自定义页面不存在！');
        }

        if ($tuplePage->is_delete === 1) {
            throw new ServiceException('自定义页面不存在！');
        }

        $now = date('Y-m-d H:i:s');

        $tuplePage->is_delete = 1;
        $tuplePage->update_time = $now;
        $tuplePage->update();

        $this->onUpdate([$pageId]);
        return true;
    }


    /**
     * 页面更新
     *
     * @param array $pageIds 页面ID列表
     * @throws ServiceException|RuntimeException
     */
    public function onUpdate(array $pageIds)
    {
        $configRedis = Be::getConfig('App.Cms.Redis');
        if ($configRedis->enable) {
            $this->syncRedis($pageIds);
        }
    }

    /**
     * 页面同步到 Redis
     *
     * @param array $pageIds 页面ID列表
     * @throws ServiceException
     * @throws RuntimeException
     */
    public function syncRedis(array $pageIds)
    {
        $configRedis = Be::getConfig('App.Cms.Redis');
        if ($configRedis->enable) {
            $keyValues = [];
            foreach ($pageIds as $pageId) {
                $key = 'Cms:Page:' . $pageId;
                $page = $this->getPage($pageId);
                $keyValues[$key] = serialize($page);
            }

            $redis = Be::getRedis();
            $redis->mset($keyValues);
        }
    }


    /**
     * 获取菜单参数选择器
     *
     * @return array
     */
    public function getPageMenuPicker():array
    {
        return [
            'name' => 'id',
            'value' => '自定义页面：{title}',
            'table' => 'cms_page',
            'grid' => [
                'title' => '选择一个自定义页面',

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

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'name' => 'title',
                            'label' => '标题',
                            'align' => 'left'
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
                ],
            ]
        ];
    }
}
