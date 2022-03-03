<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Db\DbException;
use Be\Runtime\RuntimeException;

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
        $sql = 'SELECT * FROM cms_category WHERE is_delete = 0 ORDER BY ordering ASC';
        $categories = Be::getDb()->getObjects($sql);
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
        $sql = 'SELECT * FROM cms_category WHERE id=? AND is_delete = 0';
        $category = Be::getDb()->getObject($sql, [$categoryId]);
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
        $sql = 'SELECT id, `name` FROM cms_category WHERE is_delete = 0 ORDER BY ordering ASC';
        return Be::getDb()->getKeyValues($sql);
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
        $db = Be::getDb();

        $isNew = true;
        $categoryId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $categoryId = $data['id'];
        }

        $tupleCategory = Be::newTuple('cms_category');
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
                $urlExist = Be::newTable('cms_category')
                        ->where('url', $urlUnique)
                        ->getValue('COUNT(*)') > 0;
            } else {
                $urlExist = Be::newTable('cms_category')
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
        $db = Be::getDb();
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
            $existArticleIds = Be::newTable('cms_article')
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
                $tupleArticleCategory = Be::newTuple('cms_article_category');
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
        $db = Be::getDb();
        $sql = 'SELECT * FROM cms_category WHERE id=? AND is_delete=0';
        $category = $db->getObject($sql, [$categoryId]);
        if (!$category) {
            throw new ServiceException('文章分类（# ' . $categoryId . '）不存在！');
        }

        Be::newTable('cms_article_category')
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
        $articleIds = Be::newTable('cms_article_category')
            ->where('category_id', 'IN',  $categoryIds)
            ->getValues('article_id');
        if (count($articleIds) > 0) {
            Be::getService('App.Cms.Article')->onUpdate($articleIds);
        }

        $configRedis = Be::getConfig('App.Cms.Redis');
        if ($configRedis->enable) {
            $this->syncRedis($categoryIds);
        }
    }

    /**
     * 文章分类同步到 Redis
     *
     * @param array $categoryIds
     * @throws ServiceException
     * @throws RuntimeException|DbException
     */
    public function syncRedis(array $categoryIds)
    {
        $keyValues = [];
        foreach ($categoryIds as $categoryId) {
            $key = 'Cms:Category:' . $categoryId;
            $category = $this->getCategory($categoryId);
            $keyValues[$key] = serialize($category);
        }

        $configRedis = Be::getConfig('App.Cms.Redis');
        $redis = Be::getRedis($configRedis->db);
        $redis->mset($keyValues);
    }

    /**
     * 获取菜单参数选择器
     *
     * @return array
     */
    public function getCategoryMenuPicker():array
    {
        return [
            'name' => 'id',
            'value' => '文章分类：{name}',
            'table' => 'cms_category',
            'grid' => [
                'title' => '选择一个分类',

                'filter' => [
                    ['is_delete', '=', '0'],
                ],

                'form' => [
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
                        ],
                    ],
                ],

                'table' => [

                    // 未指定时取表的所有字段
                    'items' => [
                        [
                            'name' => 'name',
                            'label' => '名称',
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
