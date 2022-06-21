<?php

namespace Be\App\Cms\Service\Admin;


use Be\Be;

class TaskCategory
{

    /**
     * 分类同步到 Redis
     *
     * @param array $categories
     */
    public function syncCache(array $categories)
    {
        if (count($categories) === 0) return;

        $cache = Be::getCache();
        $keyValues = [];
        foreach ($categories as $category) {
            $key = 'Cms:Category:' . $category->id;

            $category->is_delete = (int)$category->is_delete;

            if ($category->is_delete === 1) {
                $cache->delete($key);
            } else {
                $category->seo = (int)$category->seo;
                $category->ordering = (int)$category->ordering;
                $category->is_enable = (int)$category->is_enable;

                $keyValues[$key] = $category;
            }
        }

        if (count($keyValues) > 0) {
            $cache->setMany($keyValues);
        }
    }

}
