<?php
namespace App\Cms\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;

class Article extends Service
{
    /**
     * 获取符合条件的文章列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getArticles($conditions = [])
    {
        $tableArticle = Be::getTable('Cms', 'Article');

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

    /**
     * 获取符合条件的文章总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getArticleCount($conditions = [])
    {
        return Be::getTable('Cms', 'Article')
            ->where($this->createArticleWhere($conditions))
            ->count();
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
                $ids = Be::getService('Cms', 'Category')->getSubCategoryIds($conditions['categoryId']);
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
     * @param \Phpbe\System\Db\Row | mixed $rowArticle 当前文章
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    public function getSimilarArticles($rowArticle, $n)
    {
        $similarArticles = [];

        // 按关键词查找类似文章
        if ($rowArticle->metaKeywords != '') {
            $keywords = explode(' ', $rowArticle->metaKeywords);
            $similarArticles = $this->_getSimilarArticles($rowArticle, $keywords, $n);
        }

        if (count($similarArticles) > 0) return $similarArticles;

        if ($rowArticle->title != '') {
            $libScws = Be::getLib('Pscws');
            $libScws->sendText($rowArticle->title);
            $scwsKeywords = $libScws->getTops(3);
            $keywords = [];
            if ($scwsKeywords !== false) {
                foreach ($scwsKeywords as $scwsKeyword) {
                    $keywords[] = $scwsKeyword['word'];
                }
            }

            $similarArticles = $this->_getSimilarArticles($rowArticle, $keywords, $n);
        }

        return $similarArticles;
    }

    /**
     * 获取相似文章
     *
     * @param \Phpbe\System\Db\Row | mixed $rowArticle 当前文章
     * @param array $keywords 关键词
     * @param int $n 查询出最多多少条记录
     * @return array
     */
    private function _getSimilarArticles($rowArticle, $keywords, $n)
    {
        $similarArticles = [];

        $keywordsCount = count($keywords);
        if ($keywordsCount > 0) {
            $tableArticle = Be::getTable('Cms', 'Article');
            $tableArticle->where('id', '!=', $rowArticle->id);
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
                $tableArticle->where('id', '!=', $rowArticle->id);
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
     * 屏蔽文章
     *
     * @param $ids
     * @throws \Exception
     */
    public function unblock($ids)
    {
        Be::getTable('Cms', 'Article')->where('id', 'in', explode(',', $ids))->update(['block' => 0]);
    }

    /**
     * 公开文章
     *
     * @param $ids
     * @throws \Exception
     */
    public function block($ids)
    {
        Be::getTable('Cms', 'Article')->where('id', 'in', explode(',', $ids))->update(['block' => 1]);
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

                $articleCommentIds = Be::getTable('Cms', 'ArticleComment')->where('article_id', $id)->getArray('id');
                if (count($articleCommentIds)) {
                    Be::getTable('Cms', 'ArticleVoteLog')->where('comment_id', 'in', $articleCommentIds)->delete();
                    Be::getTable('Cms', 'ArticleVoteLog')->where('article_id', $id)->delete();
                    Be::getTable('Cms', 'ArticleComment')->where('article_id', $id)->delete();
                }

                $rowArticle = Be::getRow('Cms', 'Article');
                $rowArticle->load($id);

                if ($rowArticle->thumbnail_l != '') $files[] = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/' .  $rowArticle->thumbnail_l;
                if ($rowArticle->thumbnail_m != '') $files[] = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/' .  $rowArticle->thumbnail_m;
                if ($rowArticle->thumbnail_s != '') $files[] = Be::getRuntime()->getDataPath() . '/Cms/Article/Thumbnail/' .  $rowArticle->thumbnail_s;

                $rowArticle->delete();
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

        $rowArticle = Be::getRow('Cms', 'Article');
        $rowArticle->load($articleId);
        if ($rowArticle->id == 0 || $rowArticle->block == 1) {
            throw new \Exception('文章不存在！');
        }

        $rowArticleVoteLog = Be::getRow('Cms', 'ArticleVoteLog');
        $rowArticleVoteLog->load(['article_id' => $articleId, 'user_id' => $my->id]);
        if ($rowArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $rowArticleVoteLog->article_id = $articleId;
            $rowArticleVoteLog->user_id = $my->id;
            $rowArticleVoteLog->save();

            $rowArticle->increment('like', 1);

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

        $rowArticle = Be::getRow('Cms', 'Article');
        $rowArticle->load($articleId);
        if ($rowArticle->id == 0 || $rowArticle->block == 1) {
            throw new \Exception('文章不存在！');
        }

        $rowArticleVoteLog = Be::getRow('Cms', 'ArticleVoteLog');
        $rowArticleVoteLog->load(['article_id' => $articleId, 'user_id' => $my->id]);
        if ($rowArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $rowArticleVoteLog->article_id = $articleId;
            $rowArticleVoteLog->user_id = $my->id;
            $rowArticleVoteLog->save();

            $rowArticle->increment('dislike', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }


    /**
     * 活跃会员, 即参与评论最多的会员
     *
     * @param int $limit 获取多少个
     * @return array 用户对象数组
     */
    public function getActiveUsers($limit = 10)
    {
        $userIds = Be::getTable('Cms', 'ArticleComment')
            ->groupBy('user_id')
            ->orderBy('COUNT(*) DESC')
            ->limit($limit)
            ->getValues('user_id');

        $activeUsers = [];
        foreach ($userIds as $userId) {
            $activeUsers[] = Be::getUser($userId);
        }

        return $activeUsers;
    }
}
