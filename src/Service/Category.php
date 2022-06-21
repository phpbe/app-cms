<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;

class Category
{

    /**
     * 获取页面伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getCategoryUrl(array $params = []): string
    {
        $category = $this->getCategory($params['id']);
        return '/articles/' . $category->url;
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
