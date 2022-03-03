<?php
namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 文章分类
 */
class Category
{

    /**
     * 分类文章列表
     *
     * @BeMenu("文章分类", picker="return \Be\Be::getService('App.Cms.Admin.Category')->getCategoryMenuPicker()")
     * @BeRoute("\Be\Be::getService('App.Cms.Category')->getCategoryUrl($params)")
     */
    public function articles()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $id = $request->get('id', '');
            if ($id === '') {
                throw new ControllerException('文章分类不存在！');
            }

            $service = Be::getService('App.Cms.Article');
            $article = $service->getArticle($id);
            $response->set('title', $article->seo_title);
            $response->set('meta_keywords', $article->seo_keywords);
            $response->set('meta_description', $article->seo_description);
            $response->set('article', $article);
            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

}
