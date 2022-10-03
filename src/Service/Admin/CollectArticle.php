<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class CollectArticle
{

    /**
     * 发布
     *
     * @param array $articles 要发布的文章数据
     */
    public function publish(array $articles)
    {
        if (count($articles) === 0) return;

        $db = Be::getDb();
        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            foreach ($articles as $article) {
                $tupleArticle = Be::getTuple('cms_article');
                try {
                    $tupleArticle->loadBy([
                        'id' => $article['id'],
                        'is_delete' => 0,
                    ]);
                } catch (\Throwable $t) {
                    throw new ServiceException('采集的文章（# ' . $article['id'] . '）不存在！');
                }

                $tupleArticle->is_enable = 1;
                $tupleArticle->update_time = $now;
                $tupleArticle->update();
            }

            $db->commit();

            Be::getService('App.System.Task')->trigger('Cms.ArticleSyncEsAndCache');

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException('发布采集的文章发生异常！');
        }
    }

    
    /**
     * 删除
     *
     * @param array $articleIds 要删除的文章ID
     */
    public function delete(array $articleIds)
    {
        if (count($articleIds) === 0) return;

        $db = Be::getDb();
        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            foreach ($articleIds as $articleId) {
                $tupleArticle = Be::getTuple('cms_article');
                try {
                    $tupleArticle->loadBy([
                        'id' => $articleId,
                        'is_delete' => 0,
                    ]);
                } catch (\Throwable $t) {
                    throw new ServiceException('采集的文章（# ' . $articleId . '）不存在！');
                }

                if ($tupleArticle->is_enable === -1) { // 未曾发布，直接物理删除

                    // 删除文章分类
                    Be::getTable('cms_article_category_id')
                        ->where('article_id', $articleId)
                        ->delete();

                    // 删除商品款式
                    Be::getTable('cms_article__tag')
                        ->where('article_id', $articleId)
                        ->delete();

                    if ($tupleArticle->collect_article_id !== '') {
                        Be::getTuple('cms_collect_article')
                            ->delete($tupleArticle->collect_article_id);
                    }

                    // 最后删除文章主表
                    $tupleArticle->delete();

                } else {

                    if ($tupleArticle->collect_article_id !== '') {
                        Be::getTuple('cms_collect_article')
                            ->delete($tupleArticle->collect_article_id);
                    }

                    $tupleArticle->collect_article_id = '';
                    $tupleArticle->update_time = $now;
                    $tupleArticle->update();
                }
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException('删除采集的文章发生异常！');
        }
    }


}
