<?php
namespace App\Cms\Service;

use Phpbe\System\Be;
use Phpbe\System\Service;

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
        $tableArticleComment= Be::getTable('Cms', 'ArticleComment');

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
        return Be::getTable('Cms', 'ArticleComment')
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

        $rowArticle = Be::getRow('Cms', 'article');
        $rowArticle->load($articleId);
        if ($rowArticle->id == 0 || $rowArticle->block == 1) {
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

        $rowArticleComment = Be::getRow('Cms', 'ArticleComment');
        $rowArticleComment->article_id = $articleId;
        $rowArticleComment->user_id = $my->id;
        $rowArticleComment->user_name = $my->name;
        $rowArticleComment->body = $commentBody;
        $rowArticleComment->ip = $_SERVER['REMOTE_ADDR'];
        $rowArticleComment->create_time = time();

        $configArticle = Be::getConfig('Cms', 'Article');
        $rowArticleComment->block = ($configArticle->commentPublic == 1 ? 0 : 1);

        $rowArticleComment->save();
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

        $rowArticleComment = Be::getRow('Cms', 'ArticleComment');
        $rowArticleComment->load($commentId);
        if ($rowArticleComment->id == 0 || $rowArticleComment->block == 1) {
            throw new \Exception('评论不存在！');
        }

        $rowArticleVoteLog = Be::getRow('Cms', 'ArticleVoteLog');
        $rowArticleVoteLog->load(['comment_id' => $commentId, 'user_id' => $my->id]);
        if ($rowArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $rowArticleVoteLog->comment_id = $commentId;
            $rowArticleVoteLog->userId = $my->id;
            $rowArticleVoteLog->save();

            $rowArticleComment->increment('like', 1);

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

        $rowArticleComment = Be::getRow('Cms', 'ArticleComment');
        $rowArticleComment->load($commentId);
        if ($rowArticleComment->id == 0 || $rowArticleComment->block == 1) {
            throw new \Exception('评论不存在！');
        }

        $rowArticleVoteLog = Be::getRow('Cms', 'ArticleVoteLog');
        $rowArticleVoteLog->load(['comment_id' => $commentId, 'user_id' => $my->id]);
        if ($rowArticleVoteLog->id > 0) {
            throw new \Exception('您已经表过态啦！');
        }

        $db = Be::getDb();
        $db->beginTransaction();
        try {

            $rowArticleVoteLog->comment_id = $commentId;
            $rowArticleVoteLog->userId = $my->id;
            $rowArticleVoteLog->save();

            $rowArticleComment->increment('dislike', 1);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    public function commentsUnblock($ids)
    {
        Be::getTable('Cms', 'ArticleComment')->where('id', 'in', explode(',', $ids))->update(['block' => 0]);
    }

    public function commentsBlock($ids)
    {
        Be::getTable('Cms', 'ArticleComment')->where('id', 'in', explode(',', $ids))->update(['block' => 1]);
    }

    public function commentsDelete($ids)
    {
        Be::getTable('Cms', 'ArticleComment')->where('id', 'in', explode(',', $ids))->delete();
    }

}
