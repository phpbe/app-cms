<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Db\DbException;
use Be\Runtime\RuntimeException;

class CollectArticle
{

    /**
     * 编辑采集的文章
     *
     * @param array $data 采集的文章数据
     * @return bool
     * @throws ServiceException
     * @throws DbException|RuntimeException
     */
    public function edit(array $data): bool
    {
        $db = Be::getDb();

        $isNew = true;
        $collectArticleId = null;
        if (isset($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $collectArticleId = $data['id'];
        }

        $tupleCollectArticle = Be::getTuple('cms_collect_article');
        if (!$isNew) {
            try {
                $tupleCollectArticle->load($collectArticleId);
            } catch (\Throwable $t) {
                throw new ServiceException('采集的文章（# ' . $collectArticleId . '）不存在！');
            }

            if ($tupleCollectArticle->article_id !== '') {
                throw new ServiceException('已导入的文章不可编辑！');
            }
        }


        if (!isset($data['unique_key']) || !is_string($data['unique_key'])) {
            $data['unique_key'] = '';
        }

        if ($data['unique_key'] !== '') {
            $loaded = false;
            try {
                $tupleCollectArticle->loadBy('unique_key', $data['unique_key']);
                $loaded = true;
            } catch (\Throwable $t) {
            }

            if ($loaded) {
                if ($tupleCollectArticle->article_id !== '') {
                    throw new ServiceException('唯一键值（unique_key=' . $data['unique_key'] . '）对应的文章已导入过！');
                }

                // 再次导入，覆盖
                $isNew = false;
                $collectArticleId = $tupleCollectArticle->id;
            }
        }

        if (!isset($data['image']) || !is_string($data['image'])) {
            $data['image'] = '';
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            throw new ServiceException('采集的文章标题未填写！');
        }
        $title = $data['title'];
        if (strlen($title) > 200) {
            throw new ServiceException('采集的文章标题（title）不得超过200个字符！');
        }

        if (!isset($data['summary']) || !is_string($data['summary'])) {
            $data['summary'] = '';
        } else {
            if ($data['summary'] && strlen($data['summary']) > 500) {
                throw new ServiceException('摘要（summary）不得超过500个字符！');
            }
        }

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['author']) || !is_string($data['author'])) {
            $data['author'] = '';
        } else {
            if ($data['author'] && strlen($data['author']) > 50) {
                throw new ServiceException('作者（author）不得超过50个字符！');
            }
        }

        if (!isset($data['publish_time']) || !is_string($data['publish_time']) || !strtotime($data['publish_time'])) {
            $data['publish_time'] = date('Y-m-d H:i:s');
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleCollectArticle->unique_key = $data['unique_key'];
            $tupleCollectArticle->image = $data['image'];
            $tupleCollectArticle->title = $title;
            $tupleCollectArticle->summary = $data['summary'];
            $tupleCollectArticle->description = $data['description'];
            $tupleCollectArticle->author = $data['author'];
            $tupleCollectArticle->publish_time = $data['publish_time'];
            $tupleCollectArticle->is_delete = 0;
            $tupleCollectArticle->update_time = $now;
            if ($isNew) {
                $tupleCollectArticle->article_id = '';
                $tupleCollectArticle->create_time = $now;
                $tupleCollectArticle->insert();
            } else {
                $tupleCollectArticle->update();
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '采集的文章发生异常！');
        }

        return true;
    }

    /**
     * 获取采集的文章
     *
     * @param string $collectArticleId
     * @param array $with
     * @return object
     * @throws ServiceException
     * @throws DbException|RuntimeException
     */
    public function getCollectArticle(string $collectArticleId, array $with = []): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM `cms_collect_article` WHERE id=?';
        $collectArticle = $db->getObject($sql, [$collectArticleId]);
        if (!$collectArticle) {
            throw new ServiceException('采集的文章（# ' . $collectArticleId . '）不存在！');
        }

        $collectArticle->is_delete = (int)$collectArticle->is_delete;

        return $collectArticle;
    }

    /**
     * 导入
     *
     * @param array $collectArticles 要导入文章数据
     * @return bool
     */
    public function import(array $collectArticles): bool
    {
        $serviceArticle = Be::getService('App.Cms.Admin.Article');
        foreach ($collectArticles as $collectArticle) {
            $tupleCollectArticle = Be::getTuple('cms_collect_article');
            try {
                $tupleCollectArticle->load($collectArticle['id']);
            } catch (\Throwable $t) {
                throw new ServiceException('采集的文章（# ' . $collectArticle['id'] . '）不存在！');
            }

            $articleData = [];
            $articleData['id'] = '';
            $articleData['image'] = $tupleCollectArticle->image;
            $articleData['title'] = $tupleCollectArticle->title;
            $articleData['summary'] = $tupleCollectArticle->summary;
            $articleData['description'] = $tupleCollectArticle->description;
            $articleData['author'] = $tupleCollectArticle->author;
            $articleData['publish_time'] = $tupleCollectArticle->publish_time;
            $articleData['category_ids'] = $collectArticle['category_ids'];

            $tupleArticle = $serviceArticle->edit($articleData);

            $tupleCollectArticle->article_id = $tupleArticle->id;
            $tupleCollectArticle->update_time = date('Y-m-d H:i:s');
            $tupleCollectArticle->update();
        }

        return true;
    }

}
