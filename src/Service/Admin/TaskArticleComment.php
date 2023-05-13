<?php

namespace Be\App\Cms\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class TaskArticleComment
{

    /**
     * 同步到 ES
     *
     * @param array $articleComments
     */
    public function syncEs(array $articleComments)
    {
        if (count($articleComments) === 0) return;

        $configEs = Be::getConfig('App.Cms.Es');
        if (!$configEs->enable) {
            return;
        }

        $es = Be::getEs();

        $batch = [];
        foreach ($articleComments as $articleComment) {

            $articleComment->is_enable = (int)$articleComment->is_enable;

            if ($articleComment->is_enable === -1) {
                continue;
            }

            $articleComment->is_delete = (int)$articleComment->is_delete;

            $batch[] = [
                'index' => [
                    '_index' => $configEs->indexArticleComment,
                    '_id' => $articleComment->id,
                ]
            ];

            if ($articleComment->is_delete === 1) {
                $batch[] = [
                    'id' => $articleComment->id,
                    'is_delete' => true
                ];
            } else {

                $batch[] = [
                    'id' => $articleComment->id,
                    'article_id' => $articleComment->article_id,
                    'name' => $articleComment->name,
                    'email' => $articleComment->email,
                    'content' => $articleComment->content,
                    'ip' => $articleComment->ip,
                    'is_enable' => $articleComment->is_enable === 1,
                    'is_delete' => $articleComment->is_delete === 1,
                    'create_time' => $articleComment->create_time,
                    'update_time' => $articleComment->update_time,
                ];
            }
        }

        $response = $es->bulk(['body' => $batch]);
        if ($response['errors'] > 0) {
            $reason = '';
            if (isset($response['items']) && count($response['items']) > 0) {
                foreach ($response['items'] as $item) {
                    if (isset($item['index']['error']['reason'])) {
                        $reason = $item['index']['error']['reason'];
                        break;
                    }
                }
            }
            throw new ServiceException('文章评论全量量同步到ES出错：' . $reason);
        }
    }

    /**
     * 文章同步到缓存
     *
     * @param array $articleComments
     */
    public function syncCache(array $articleComments)
    {

    }


}
