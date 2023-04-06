<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;

class Category
{

    /**
     * 获取分类列表
     *
     * @param int $n 数量
     * @return array
     */
    public function getCategories(int $n = 0): array
    {
        $cache = Be::getCache();

        $key = 'Cms:Categories';
        $categories = $cache->get($key);

        if (!$categories) {
            $table =  Be::getTable('cms_category');
            $table->where('is_delete', 0);
            $table->where('is_enable', 1);
            $table->orderBy('ordering', 'ASC');
            $categories = $table->getObjects();
            $cache->set($key, $categories, 600);
        }

        if ($n > 0 && $n < count($categories)) {
            ;$categories = array_slice($categories, 0, $n);
        }

        return $categories;
    }

    /**
     * 获取页面伪静态页网址
     *
     * @param array $params
     * @return array
     * @throws ServiceException
     */
    public function getCategoryUrl(array $params = []): array
    {
        $configCategory = Be::getConfig('App.Cms.Category');
        $category = $this->getCategory($params['id']);

        $params1 = ['id' => $params['id']];
        unset($params['id']);
        return [$configCategory->urlPrefix . $category->url, $params1, $params];
    }

    /**
     * 获取分类
     *
     * @param string $categoryId 分类ID
     * @return object
     */
    public function getCategory(string $categoryId): object
    {
        $cache = Be::getCache();
        $key = 'Cms:Category:' . $categoryId;
        $category = $cache->get($key);
        if (!$category) {
            throw new ServiceException('分类不存在！');
        }

        return $category;
    }

    /**
     * 获取分类
     *
     * @param string $pageId 页面ID
     * @return object 分类对象
     */
    public function getPageFromDb(string $categoryId): object
    {
        $tupleCategory = Be::getTuple('cms_page');
        try {
            $tupleCategory->load($categoryId);
        } catch (\Throwable $t) {
            throw new ServiceException('分类不存在！');
        }

        return $tupleCategory->toObject();
    }


}
