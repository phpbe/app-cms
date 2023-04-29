<?php

namespace Be\App\Cms\Service\Admin;

use Be\Be;

class TaskPage
{

    /**
     * 页面同步到 Redis
     *
     * @param array $pages 页面列表
     */
    public function syncCache(array $pages)
    {
        if (count($pages) === 0) return;

        $cache = Be::getCache();
        $keyValues = [];
        foreach ($pages as $page) {
            $key = 'App:Cms:Page:' . $page->id;
            $page->is_delete = (int)$page->is_delete;

            if ($page->is_delete === 1) {
                $cache->delete($key);
            } else {
                if ($page->config) {
                    $config = unserialize($page->config);
                    if ($config) {
                        $page->config = $config;
                    } else {
                        $page->config = false;
                    }
                } else {
                    $page->config = false;
                }

                $keyValues[$key] = $page;
            }
        }

        if (count($keyValues) > 0) {
            $cache->setMany($keyValues);
        }
    }


}
