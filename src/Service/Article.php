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
        $configRedis = Be::getConfig('App.Cms.Redis');
        if ($configRedis->enable) {
            $redis = Be::getRedis($configRedis->db);

            $key = 'Cms:Article:' . $articleId;
            $article = $redis->get($key);
            if ($article) {
                $article = json_decode($article);
            }

            if (!$article) {
                throw new ServiceException('文章不存在！');
            }

            return $article;
        } else {
            $tupleArticle = Be::newtuple('cms_article');
            try {
                $tupleArticle->load($articleId);
            } catch (\Throwable $t) {
                throw new ServiceException('文章不存在！');
            }
            return $tupleArticle->toObject();
        }
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

            $tableArticle = Be::getTable('cms_article');

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
            return Be::getTable('cms_article')
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

        if (isset($conditions['categoryId']) && $conditions['categoryId']) {
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
            $tableArticle = Be::getTable('cms_article');
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

        $tupleArticle = Be::getTuple('cms_article');
        $tupleArticle->load($articleId);
        if ($tupleArticle->id == 0 || $tupleArticle->block == 1) {
            throw new \Exception('文章不存在！');
        }

        $tupleArticleVoteLog = Be::getTuple('cms_article_vote_log');
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

        $tupleArticle = Be::getTuple('cms_article');
        $tupleArticle->load($articleId);
        if ($tupleArticle->id == 0 || $tupleArticle->block == 1) {
            throw new \Exception('文章不存在！');
        }

        $tupleArticleVoteLog = Be::getTuple('cms_article_vote_log');
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
