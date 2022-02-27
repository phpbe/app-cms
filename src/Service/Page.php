<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;

class Page
{

    /**
     * 获取页面伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getPageUrl(array $params = []): string
    {
        $page = $this->getPage($params['id']);
        return '/page/' . $page->url;
    }

    /**
     * 获取页面
     *
     * @param string $pageId 页面ID
     * @return \stdClass 页面对象
     * @throws ServiceException
     */
    public function getPage(string $pageId): \stdClass
    {
        $cache = Be::getCache();

        $key = 'Cms:Page:' . $pageId;
        $page = $cache->get($key);
        if (!$page) {
            throw new ServiceException('页面不存在！');
        }
        return $page;
    }

}
