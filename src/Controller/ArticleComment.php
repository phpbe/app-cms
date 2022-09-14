<?php

namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 文章评论
 */
class ArticleComment
{

    /**
     * 新增评论
     *
     * @BeRoute("/article/comment/create")
     */
    public function create()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $service = Be::getService('App.Cms.ArticleComment');
            $service->create($request->post());

            $response->success(beLang('App.Cms', 'ARTICLE.COMMENT.CREATE_SUCCESS'));
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }


}
