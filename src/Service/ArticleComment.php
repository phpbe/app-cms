<?php
namespace Be\App\Cms\Service;

use Be\Be;
use Be\Service;

class ArticleComment extends Service
{

    /**
     * 获取评论列表
     *
     * @param array $conditions 查询条件
     * @return array
     */
    public function getComments($conditions = [])
    {
        $tableArticleComment= Be::getTable('cms_article_comment');

        $where = $this->createCommentWhere($conditions);
        $tableArticleComment->where($where);

        if (isset($conditions['orderByString']) && $conditions['orderByString']) {
            $tableArticleComment->orderBy($conditions['orderByString']);
        } else {
            $orderBy = 'create_time';
            $orderByDir = 'DESC';
            if (isset($conditions['orderBy']) && $conditions['orderBy']) $orderBy = $conditions['orderBy'];
            if (isset($conditions['orderByDir']) && $conditions['orderByDir']) $orderByDir = $conditions['orderByDir'];
            $tableArticleComment->orderBy($orderBy, $orderByDir);
        }

        if (isset($conditions['offset']) && $conditions['offset']) $tableArticleComment->offset($conditions['offset']);
        if (isset($conditions['limit']) && $conditions['limit']) $tableArticleComment->limit($conditions['limit']);

        return $tableArticleComment->getObjects();
    }

    /**
     * 获取评论总数
     *
     * @param array $conditions 查询条件
     * @return int
     */
    public function getCommentCount($conditions = [])
    {
        return Be::getTable('cms_article_comment')
            ->where($this->createCommentWhere($conditions))
            ->count();
    }

    /**
     * 生成评论条件where
     *
     * @param array $conditions 查询条件
     * @return array
     */
    private function createCommentWhere($conditions = [])
    {
        $where = [];

        if (isset($conditions['articleId']) && is_numeric($conditions['articleId']) && $conditions['articleId'] > 0) {
            $where[] = ['article_id', $conditions['articleId']];
        }

        if (isset($conditions['userId']) && is_numeric($conditions['userId'])) {
            $where[] = ['user_id', $conditions['userId']];
        }

        if (isset($conditions['key']) && $conditions['key']) {
            $where[] = ['title', 'like', '\'%' . $conditions['key'] . '%\''];
        }

        if (isset($conditions['block']) && is_numeric($conditions['block']) && $conditions['block'] != -1) {
            $where[] = ['block', $conditions['block']];
        }

        return $where;
    }

    /**
     * 提交评论
     *
     * @param int $articleId 文章编号
     * @param string $commentBody 评论内容
     * @throws \Exception
     */
    public function comment($articleId, $commentBody)
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

        $commentBody = trim($commentBody);
        $commentBodyLength = strlen($commentBody);
        if ($commentBodyLength == 0) {
            throw new \Exception('请输入评论内容！');
        }

        if ($commentBodyLength > 2000) {
            throw new \Exception('评论内容过长！');
        }

        $tupleArticleComment = Be::getTuple('cms_article_comment');
        $tupleArticleComment->article_id = $articleId;
        $tupleArticleComment->user_id = $my->id;
        $tupleArticleComment->user_name = $my->name;
        $tupleArticleComment->body = $commentBody;
        $tupleArticleComment->ip = $_SERVER['REMOTE_ADDR'];
        $tupleArticleComment->create_time = time();

        $configArticle = Be::getConfig('Cms.Article');
        $tupleArticleComment->block = ($configArticle->commentPublic == 1 ? 0 : 1);

        $tupleArticleComment->save();
    }

    /**
     * 顶
     *
     * @param int $commentId 文章评论编号
     * @throws \Exception
     */
    public function commentLike($commentId)
    {

        $my = Be::getUser();
        if ($my->id == 0) {
            throw new \Exception('请先登陆！');
        }

        $tupleArticleComment = Be::getTuple('cms_article_comment');
        $tupleArticleComment->load($commentId);
        if ($tupleArticleComment->id == 0 || $tupleArticleComment->block == 1) {
            throw new \Exception('评论不存在！');
        }

        $tupleArticleVoteLog = Be::getTuple('cms_article_vote_log');
        $tupleArticleVoteLog->load(['comment_id' => $commentId, 'user_id' => $my->id]);
        if ($tupleArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleArticleVoteLog->comment_id = $commentId;
            $tupleArticleVoteLog->userId = $my->id;
            $tupleArticleVoteLog->save();

            $tupleArticleComment->increment('like', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    /**
     * 踩
     *
     * @param int $commentId 文章评论编号
     * @throws \Exception
     */
    public function commentDislike($commentId)
    {
        $my = Be::getUser();
        if ($my->id == 0) {
            throw new \Exception('请先登陆！');
        }

        $tupleArticleComment = Be::getTuple('cms_article_comment');
        $tupleArticleComment->load($commentId);
        if ($tupleArticleComment->id == 0 || $tupleArticleComment->block == 1) {
            throw new \Exception('评论不存在！');
        }

        $tupleArticleVoteLog = Be::getTuple('cms_article_vote_log');
        $tupleArticleVoteLog->load(['comment_id' => $commentId, 'user_id' => $my->id]);
        if ($tupleArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $tupleArticleVoteLog->comment_id = $commentId;
            $tupleArticleVoteLog->userId = $my->id;
            $tupleArticleVoteLog->save();

            $tupleArticleComment->increment('dislike', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    public function commentsUnblock($ids)
    {
        Be::getTable('cms_article_comment')->where('id', 'in', explode(',', $ids))->update(['block' => 0]);
    }

    public function commentsBlock($ids)
    {
        Be::getTable('cms_article_comment')->where('id', 'in', explode(',', $ids))->update(['block' => 1]);
    }

    public function commentsDelete($ids)
    {
        Be::getTable('cms_article_comment')->where('id', 'in', explode(',', $ids))->delete();
    }

}
