<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Db\DbException;
use Be\Runtime\RuntimeException;

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
        $configCms = Be::getConfig('App.Cms.Cms');
        $sql = 'SELECT * FROM cms_page WHERE is_delete = 0';
        $categories = Be::getDb($configCms->db)->getObjects($sql);
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
        $configCms = Be::getConfig('App.Cms.Cms');
        $sql = 'SELECT id, title FROM cms_page WHERE is_delete = 0';
        return Be::getDb($configCms->db)->getKeyValues($sql);
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
        $configCms = Be::getConfig('App.Cms.Cms');
        $tuplePage = Be::newTuple('cms_page', $configCms->db);
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
        $configCms = Be::getConfig('App.Cms.Cms');
        $db = Be::getDb($configCms->db);

        $isNew = true;
        $pageId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $pageId = $data['id'];
        }

        $tuplePage = Be::newTuple('cms_page', $configCms->db);
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
            $url = strtolower($title);
            $url = str_replace(' ', '', $url);
            $url = preg_replace('/[^a-z0-9\-]/', '', $url);
        } else {
            $url = $data['url'];
        }
        $urlUnique = $url;
        $urlIndex = 0;
        $urlExist = null;
        do {
            if ($isNew) {
                $urlExist = Be::newTable('cms_page', $configCms->db)
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::newTable('cms_page', $configCms->db)
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
        $configCms = Be::getConfig('App.Cms.Cms');
        $tuplePage = Be::newTuple('cms_page', $configCms->db);
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
        $keyValues = [];
        foreach ($pageIds as $pageId) {
            $key = 'Cms:Page:' . $pageId;
            $page = $this->getPage($pageId);
            $keyValues[$key] = $page;
        }

        $cache = Be::getCache();
        $cache->setMany($keyValues);
    }


}
