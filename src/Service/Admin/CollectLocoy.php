<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;
use Be\Db\DbException;
use Be\Runtime\RuntimeException;

class CollectLocoy
{

    /**
     * 火车采
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
        $articleId = null;
        if (isset($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $articleId = $data['id'];
        }

        $tupleArticle = Be::newTuple('cms_collect_article');
        if (!$isNew) {
            try {
                $tupleArticle->load($articleId);
            } catch (\Throwable $t) {
                throw new ServiceException('采集的文章（# ' . $articleId . '）不存在！');
            }
        }

        if (!isset($data['title']) || !is_string($data['title'])) {
            throw new ServiceException('采集的文章标题未填写！');
        }
        $title = $data['title'];

        if (!isset($data['summary']) || !is_string($data['summary'])) {
            $data['summary'] = '';
        }

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['image']) || !is_string($data['image'])) {
            $data['image'] = '';
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleArticle->image = $data['image'];
            $tupleArticle->title = $title;
            $tupleArticle->summary = $data['summary'];
            $tupleArticle->description = $data['description'];
            $tupleArticle->is_delete = 0;
            $tupleArticle->update_time = $now;
            if ($isNew) {
                $tupleArticle->create_time = $now;
                $tupleArticle->insert();
            } else {
                $tupleArticle->update();
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
     * @param string $articleId
     * @param array $with
     * @return object
     * @throws ServiceException
     * @throws DbException|RuntimeException
     */
    public function getArticle(string $articleId, array $with = []): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM `cms_collect_article` WHERE id=?';
        $article = $db->getObject($sql, [$articleId]);
        if (!$article) {
            throw new ServiceException('采集的文章（# ' . $articleId . '）不存在！');
        }
        
        $article->is_delete = (int)$article->is_delete;
        
        return $article;
    }

}
