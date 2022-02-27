<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Category
{

    /**
     * 获取文章分类列表
     *
     * @return array
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function getCategories(): array
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $sql = 'SELECT * FROM cms_category WHERE is_delete = 0 ORDER BY ordering ASC';
        $categories = Be::getDb($configCms->db)->getObjects($sql);
        return $categories;
    }

    /**
     * 获取文章分类
     *
     * @param string $categoryId
     * @return \stdClass
     * @throws ServiceException
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function getCategory(string $categoryId): \stdClass
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $sql = 'SELECT * FROM cms_category WHERE id=? AND is_delete = 0';
        $category = Be::getDb($configCms->db)->getObject($sql, [$categoryId]);
        if (!$category) {
            throw new ServiceException('文章分类（# ' . $categoryId . '）不存在！');
        }

        $category->seo = (int)$category->seo;
        $category->ordering = (int)$category->ordering;

        return $category;
    }

    /**
     * 获取文章分类键值对
     *
     * @return array
     * @throws \Be\Db\DbException
     * @throws \Be\Runtime\RuntimeException
     */
    public function getCategoryKeyValues(): array
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $sql = 'SELECT id, `name` FROM cms_category WHERE is_delete = 0 ORDER BY ordering ASC';
        return Be::getDb($configCms->db)->getKeyValues($sql);
    }

    /**
     * 编辑文章分类
     *
     * @param array $data 文章分类数据
     * @return bool
     * @throws \Throwable
     */
    public function edit(array $data): bool
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $db = Be::getDb($configCms->db);

        $isNew = true;
        $categoryId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $categoryId = $data['id'];
        }

        $tupleCategory = Be::newTuple('cms_category', $configCms->db);
        if (!$isNew) {
            try {
                $tupleCategory->load($categoryId);
            } catch (\Throwable $t) {
                throw new ServiceException('文章分类（# ' . $categoryId . '）不存在！');
            }

            if ($tupleCategory->is_delete === 1) {
                throw new ServiceException('文章分类（# ' . $categoryId . '）不存在！');
            }
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('文章分类名称未填写！');
        }
        $name = $data['name'];

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        $url = null;
        if (!isset($data['url']) || !is_string($data['url'])) {
            $url = strtolower($name);
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
                $urlExist = Be::newTable('cms_category', $configCms->db)
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::newTable('cms_category', $configCms->db)
                        ->where('url', $urlUnique)
                        ->where('id', '!=', $categoryId)
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
            $data['seo_title'] = $name;
        }

        if (!isset($data['seo_description']) || !is_string($data['seo_description'])) {
            $data['seo_description'] = $data['description'];
        }

        if (!isset($data['seo_keywords']) || !is_string($data['seo_keywords'])) {
            $data['seo_keywords'] = '';
        }

        if (!isset($data['ordering']) || !is_numeric($data['ordering'])) {
            $data['ordering'] = 0;
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleCategory->name = $name;
            $tupleCategory->description = $data['description'];
            $tupleCategory->url = $url;
            $tupleCategory->seo = $data['seo'];
            $tupleCategory->seo_title = $data['seo_title'];
            $tupleCategory->seo_description = $data['seo_description'];
            $tupleCategory->seo_keywords = $data['seo_keywords'];
            $tupleCategory->ordering = $data['ordering'];
            $tupleCategory->update_time = $now;
            if ($isNew) {
                $tupleCategory->is_delete = 0;
                $tupleCategory->create_time = $now;
                $tupleCategory->insert();
            } else {
                $tupleCategory->update();
            }

            $db->commit();

            $this->onUpdate([$tupleCategory->id]);

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '文章分类发生异常！');
        }

        return true;
    }

    /**
     * 在文章分类下添加文章
     *
     * @param string $categoryId 文章分类ID
     * @param array $articleIds 文章ID列表
     * @return bool
     */
    public function addArticle(string $categoryId, array $articleIds): bool
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $db = Be::getDb($configCms->db);
        $sql = 'SELECT * FROM cms_category WHERE id=? AND is_delete=0';
        $category = $db->getObject($sql, [$categoryId]);
        if (!$category) {
            throw new ServiceException('文章分类（# ' . $categoryId . '）不存在！');
        }

        $sql = 'SELECT article_id FROM cms_article_category WHERE category_id=?';
        $existArticleIds = $db->getValues($sql, [$categoryId]);
        if (is_array($existArticleIds) && count($existArticleIds) > 0) {
            $articleIds = array_diff($articleIds, $existArticleIds);
        }

        if (count($articleIds) > 0) {
            $existArticleIds = Be::newTable('cms_article', $configCms->db)
                ->where('id', 'IN', $articleIds)
                ->getValues('id');

            if ($existArticleIds === false) {
                $existArticleIds = [];
            }

            if (count($existArticleIds) != count($articleIds)) {
                $diffArticleIds = array_diff($articleIds, $existArticleIds);
                throw new ServiceException('（#' . implode(', #', $diffArticleIds) . '）不存在！');
            }

            foreach ($articleIds as $articleId) {
                $tupleArticleCategory = Be::newTuple('cms_article_category', $configCms->db);
                $tupleArticleCategory->article_id = $articleId;
                $tupleArticleCategory->category_id = $categoryId;
                $tupleArticleCategory->insert();
            }

            Be::getService('App.Cms.Admin.Article')->onUpdate($articleIds);
        }

        return true;
    }

    /**
     * 将文章从文章分类中删除
     *
     * @param string $categoryId 文章分类ID
     * @param array $articleIds 文章ID列表
     * @return bool
     */
    public function deleteArticle(string $categoryId, array $articleIds): bool
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $db = Be::getDb($configCms->db);
        $sql = 'SELECT * FROM cms_category WHERE id=? AND is_delete=0';
        $category = $db->getObject($sql, [$categoryId]);
        if (!$category) {
            throw new ServiceException('文章分类（# ' . $categoryId . '）不存在！');
        }

        Be::newTable('cms_article_category', $configCms->db)
            ->where('category_id', $categoryId)
            ->where('article_id', 'IN', $articleIds)
            ->delete();

        Be::getService('App.Cms.Article')->onUpdate($articleIds);

        return true;
    }

    /**
     * 文章分类更新
     *
     * @param array $categoryIds 文章分类ID列表
     */
    public function onUpdate(array $categoryIds)
    {
        $configCms = Be::getConfig('App.Cms.Cms');

        $articleIds = Be::newTable('cms_article_category', $configCms->db)
            ->where('category_id', 'IN',  $categoryIds)
            ->getValues('article_id');
        if (count($articleIds) > 0) {
            Be::getService('App.Cms.Article')->onUpdate($articleIds);
        }

    }


}
