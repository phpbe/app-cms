<?php
namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 采集的文章
 */
class CollectArticle
{

    /**
     * 文章明细
     *
     * @BeRoute("/article/collect")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $service = Be::getService('App.Cms.CollectArticle');
            $id = $request->get('id', '');
            if ($id === '') {
                throw new ControllerException('文章不存在！');
            }

            $article = $service->getArticle($id);
            $response->set('title', $article->title);
            $response->set('article', $article);
            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

}
