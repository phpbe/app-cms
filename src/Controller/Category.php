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

            $category = Be::getService('App.Cms.Category')->getCategory($id);

            $response->set('title', $category->seo_title);
            $response->set('metaDescription', $category->seo_description);
            $response->set('metaKeywords', $category->seo_keywords);
            $response->set('pageTitle', $category->name);

            $response->display();

        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

}
