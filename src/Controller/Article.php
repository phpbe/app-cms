<?php
namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;
use Be\Response;

/**
 * 文章
 */
class Article
{

    /**
     * 首页
     *
     * @BeMenu("文章首页")
     * @BeRoute("/article/home")
     */
    public function home()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $response->display();
    }

    /**
     * 最新文章
     *
     * @BeMenu("最新文章")
     * @BeRoute("/article/latest")
     */
    public function latest()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $page = $request->get('page', 1);
        $result = Be::getService('App.Cms.Article')->search('', [
            'orderBy' => 'publish_time',
            'orderByDir' => 'desc',
            'page' => $page,
        ]);
        $response->set('result', $result);

        $paginationUrl = beUrl('Cms.Article.latest');
        $response->set('paginationUrl', $paginationUrl);

        $response->display('App.Cms.Article.articles');
    }

    /**
     * 热门文章
     *
     * @BeMenu("热门文章")
     * @BeRoute("/article/hottest")
     */
    public function hottest()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $page = $request->get('page', 1);
        $result = Be::getService('App.Cms.Article')->search('', [
            'orderBy' => 'publish_time',
            'orderByDir' => 'desc',
            'page' => $page,
        ]);
        $response->set('result', $result);

        $paginationUrl = beUrl('Cms.Article.hottest');
        $response->set('paginationUrl', $paginationUrl);

        $response->display('App.Cms.Article.articles');
    }

    /**
     * 文章明细
     *
     * @BeMenu("指定一篇文章", picker="return \Be\Be::getService('App.Cms.Admin.Article')->getArticleMenuPicker()")
     * @BeRoute("\Be\Be::getService('App.Cms.Article')->getArticleUrl($params)")
     */
    public function detail()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $service = Be::getService('App.Cms.Article');
            $id = $request->get('id', '');
            if ($id === '') {
                throw new ControllerException('文章不存在！');
            }

            $article = $service->hit($id);
            $response->set('title', $article->seo_title);
            $response->set('meta_keywords', $article->seo_keywords);
            $response->set('meta_description', $article->seo_description);
            $response->set('article', $article);
            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }

    /**
     * 博客预览
     *
     * @BeRoute("/article/preview")
     */
    public function preview()
    {
        // 限速最快 1 秒调用 1 次
        if (Be::getRuntime()->isSwooleMode()) {
            \Swoole\Coroutine::sleep(1);
        }

        $request = Be::getRequest();
        $response = Be::getResponse();

        try {
            $service = Be::getService('App.Cms.Article');
            $id = $request->get('id', '');
            if ($id === '') {
                throw new ControllerException('文章不存在！');
            }

            $article = $service->getArticleFromDb($id);

            $response->set('title', $article->seo_title);
            $response->set('meta_keywords', $article->seo_keywords);
            $response->set('meta_description', $article->seo_description);
            $response->set('article', $article);
            $response->display('App.Cms.Article.detail');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }
}
