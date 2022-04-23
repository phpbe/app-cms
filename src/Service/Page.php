<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;
use Be\Runtime\RuntimeException;

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
     * @throws ServiceException|RuntimeException
     */
    public function getPage(string $pageId): \stdClass
    {
        $configRedis = Be::getConfig('App.Cms.Redis');
        if ($configRedis->enable) {
            $redis = Be::getRedis($configRedis->db);

            $key = 'Cms:Page:' . $pageId;
            $page = $redis->get($key);
            if ($page) {
                $page = json_decode($page);
            }

            if (!$page) {
                throw new ServiceException('页面不存在！');
            }

            return $page;
        } else {
            $tuplePage = Be::newtuple('cms_page');
            try {
                $tuplePage->load($pageId);
            } catch (\Throwable $t) {
                throw new ServiceException('页面不存在！');
            }
            return $tuplePage->toObject();
        }
    }

}
