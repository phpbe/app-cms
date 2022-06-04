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
     * @param $categoryId
     * @return \Be\Db\Tuple
     */
    public function getCategory($categoryId) {
        $tupleCategory = Be::getTuple('cms_category');
        $tupleCategory->load($categoryId);
        return $tupleCategory;
    }


}
