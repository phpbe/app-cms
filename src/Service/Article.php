<?php

namespace Be\App\Cms\Service;

use Be\App\ServiceException;
use Be\Be;

class Article
{

    /**
     * 获取文章伪静态页网址
     *
     * @param array $params
     * @return string
     * @throws ServiceException
     */
    public function getArticleUrl(array $params = []): string
    {
        $page = $this->getArticle($params['id']);
        return '/article/' . $page->url;
    }

    /**
     * 获取文章
     *
     * @param string $articleId 文章ID
     * @return \stdClass 文章对象
     * @throws ServiceException
     */
    public function getArticle(string $articleId): \stdClass
    {
        $cache = Be::getCache();

        $key = 'Cms:Article:' . $articleId;
        $article = $cache->get($key);
        if (!$article) {
            throw new ServiceException('文章不存在！');
        }
        return $article;
    }


    /**
     * 获取符合条件的文章列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getArticles(array $conditions = []): array
    {
        $configEs = Be::getConfig('App.Cms.Es');

        // 启用了 ES 搜索
        if ($configEs->enable) {

            return [];
        } else {
            // 数据库搜索

            $tableArticle = Be::newTable('cms_article');

            $where = $this->createArticleWhere($conditions);
            $tableArticle->where($where);

            if (isset($conditions['orderByString']) && $conditions['orderByString']) {
                $tableArticle->orderBy($conditions['orderByString']);
            } else {
                $orderBy = 'ordering';
                $orderByDir = 'DESC';
                if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
                if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
                $tableArticle->orderBy($orderBy, $orderByDir);
            }

            if (isset($conditions['offset']) && $conditions['offset']) $tableArticle->offset($conditions['offset']);
            if (isset($conditions['limit']) && $conditions['limit']) $tableArticle->limit($conditions['limit']);

            return $tableArticle->getObjects();
        }
    }

    /**
     * 获取符合条件的文章总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getArticleCount(array $conditions = []): int
    {
        $configEs = Be::getConfig('App.Cms.Es');

        // 启用了 ES 搜索
        if ($configEs->enable) {
            return 0;
        } else {
            return Be::newTable('cms_article')
                ->where($this->createArticleWhere($conditions))
                ->count();
        }
    }

    /**
     * 生成查询条件 where 数组
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createArticleWhere($conditions = [])
    {
        $where = [];

        if (isset($conditions['thumbnail'])) {
            if ($conditions['thumbnail'] == 1) {
                $where[] = ['thumbnail_s', '!=', ''];
            } else {
                $where[] = ['thumbnail_s', '=', ''];
            }
        }

        if (isset($conditions['categoryId']) && $conditions['categoryId'] != -1) {
            if ($conditions['categoryId'] == 0)
                $where[] = ['category_id', 0];
            elseif ($conditions['categoryId'] > 0) {
                $ids = Be::getService('Cms.Category')->getSubCategoryIds($conditions['categoryId']);
                if (count($ids) > 0) {
                    $ids[] = $conditions['categoryId'];
                    $where[] = ['category_id', 'in', $ids];
                } else {
                    $where[] = ['category_id', $conditions['categoryId']];
                }
            }
        }

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '%' . $conditions['key'] . '%'];
        }

        if (isset($conditions['thumbnail'])) {
            if ($conditions['thumbnail'] == 1) {
                $where[] = ['thumbnail_s', '!=', ''];
            } else {
                $where[] = ['thumbnail_s', '=', ''];
            }
        }

        if (isset($conditions['top'])) {
            if ($conditions['top'] == 0) {
                $where[] = ['top', '=', 0];
            } else {
                $where[] = ['top', '>', 0];
            }
        }

        if (isset($conditions['fromTime']) && is_numeric($conditions['fromTime'])) {
            $where[] = ['create_time', '>', $conditions['fromTime']];
        }

        if (isset($conditions['userId']) && is_numeric($conditions['userId'])) {
            $where[] = ['create_by_id', '>', $conditions['userId']];
        }

        if (isset($conditions['block']) && is_numeric($conditions['block']) && $conditions['block'] != -1) {
            $where[] = ['block', $conditions['block']];
        }

        return $where;
    }


    /**
     * 获取相似文章
     *
     * @param \Be\Db\Tuple | mixed $tupleArticle 当前文章
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    public function getSimilarArticles($tupleArticle, $n)
    {
        $similarArticles = [];

        // 按关键词查找类似文章
        if ($tupleArticle->metaKeywords != '') {
            $keywords = explode(' ', $tupleArticle->metaKeywords);
            $similarArticles = $this->_getSimilarArticles($tupleArticle, $keywords, $n);
        }

        if (count($similarArticles) > 0) return $similarArticles;

        if ($tupleArticle->title != '') {
            $libScws = Be::getLib('Pscws');
            $libScws->sendText($tupleArticle->title);
            $scwsKeywords = $libScws->getTops(3);
            $keywords = [];
            if ($scwsKeywords !== false) {
                foreach ($scwsKeywords as $scwsKeyword) {
                    $keywords[] = $scwsKeyword['word'];
                }
            }

            $similarArticles = $this->_getSimilarArticles($tupleArticle, $keywords, $n);
        }

        return $similarArticles;
    }

    /**
     * 获取相似文章
     *
     * @param \Be\Db\Tuple | mixed $tupleArticle 当前文章
     * @param array $keywords 关键词
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    private function _getSimilarArticles($tupleArticle, $keywords, $n)
    {
        $similarArticles = [];

        $keywordsCount = count($keywords);
        if ($keywordsCount > 0) {
            $tableArticle = Be::newTable('cms_article');
            $tableArticle->where('id', '!=', $tupleArticle->id);
            $tableArticle->where('(');
            for ($i = 0; $i < $keywordsCount; $i++) {
                $tableArticle->where('title', 'like', '%' . $keywords[$i] . '%');
                if ($i < ($keywordsCount - 1)) $tableArticle->where('OR');
            }
            $tableArticle->where(')');
            $tableArticle->where('block', 0);
            $tableArticle->orderBy('hits DESC, create_time DESC');
            $tableArticle->limit($n);
            $similarArticles = $tableArticle->getObjects();

            if (count($similarArticles) == 0) {
                $tableArticle->init();
                $tableArticle->where('id', '!=', $tupleArticle->id);
                $tableArticle->where('(');
                for ($i = 0; $i < $keywordsCount; $i++) {
                    $tableArticle->where('body', 'like', '%' . $keywords[$i] . '%');
                    if ($i < ($keywordsCount - 1)) $tableArticle->where('OR');
                }
                $tableArticle->where(')');
                $tableArticle->where('block', 0);
                $tableArticle->orderBy('hits DESC, create_time DESC');
                $tableArticle->limit($n);
                $similarArticles = $tableArticle->getObjects();
            }
        }

        return $similarArticles;
    }


    /**
     * 删除文章
     *
     * @param $ids
     * @throws \Exception
     */
    public function delete($ids)
    {
        $db = Be::getDb();
        $db->beginTransaction();
        try {
            $files = [];
            $array = explode(',', $ids);
            foreach ($array as $id) {

                $articleCommentIds = Be::newTable('cms_article_comment')->where('article_id', $id)->getArray('id');
                if (count($articleCommentIds)) {
                    Be::newTable('cms_article_vote_log')->where('comment_id', 'in', $articleCommentIds)->delete();
                    Be::newTable('cms_article_vote_log')->where('article_id', $id)->delete();
                    Be::newTable('cms_article_comment')->where('article_id', $id)->delete();
                }

                $tupleArticle = Be::newTuple('cms_article');
                $tupleArticle->load($id);

                if ($tupleArticle->thumbnail_l != '') $files[] = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/' . $tupleArticle->thumbnail_l;
                if ($tupleArticle->thumbnail_m != '') $files[] = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/' . $tupleArticle->thumbnail_m;
                if ($tupleArticle->thumbnail_s != '') $files[] = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/' . $tupleArticle->thumbnail_s;

                $tupleArticle->delete();
            }

            foreach ($files as $file) {
                @unlink($file);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * 顶
     *
     * @param int $articleId 文章编号
     * @throws \Exception
     */
    public function like($articleId)
    {
        $my = Be::getUser();
        if ($my->id == 0) {
            throw new \Exception('请先登陆！');
        }

        $tupleArticle = Be::newTuple('cms_article');
        $tupleArticle->load($articleId);
        if ($tupleArticle->id == 0 || $tupleArticle->block == 1) {
            throw new \Exception('文章不存在！');
        }

        $tupleArticleVoteLog = Be::newTuple('cms_article_vote_log');
        $tupleArticleVoteLog->load(['article_id' => $articleId, 'user_id' => $my->id]);
        if ($tupleArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleArticleVoteLog->article_id = $articleId;
            $tupleArticleVoteLog->user_id = $my->id;
            $tupleArticleVoteLog->save();

            $tupleArticle->increment('like', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    /**
     * 踩
     *
     * @param int $articleId 文章编号
     * @throws \Exception
     */
    public function dislike($articleId)
    {
        $my = Be::getUser();
        if ($my->id == 0) {
            throw new \Exception('请先登陆！');
        }

        $tupleArticle = Be::newTuple('cms_article');
        $tupleArticle->load($articleId);
        if ($tupleArticle->id == 0 || $tupleArticle->block == 1) {
            throw new \Exception('文章不存在！');
        }

        $tupleArticleVoteLog = Be::newTuple('cms_article_vote_log');
        $tupleArticleVoteLog->load(['article_id' => $articleId, 'user_id' => $my->id]);
        if ($tupleArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleArticleVoteLog->article_id = $articleId;
            $tupleArticleVoteLog->user_id = $my->id;
            $tupleArticleVoteLog->save();

            $tupleArticle->increment('dislike', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

}
