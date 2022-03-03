<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;

class CollectArticle
{

    /**
     * 获取采集的文章
     *
     * @param string $articleId 采集的文章ID
     * @return \stdClass 文章对象
     * @throws ServiceException
     */
    public function getArticle(string $articleId): \stdClass
    {
        $tupleArticle = Be::newtuple('cms_collect_article');
        try {
            $tupleArticle->load($articleId);
        } catch (\Throwable $t) {
            throw new ServiceException('采集的文章不存在！');
        }
        return $tupleArticle->toObject();
    }

}
