<?php

namespace Be\App\Cms\Controller;

use Be\App\ControllerException;
use Be\Be;

/**
 * 文章
 */
class Article
{

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

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $response->display();
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

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $response->display();
    }

    /**
     * 搜索
     *
     * @BeMenu("搜索")
     * @BeRoute("/article/search")
     */
    public function search()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $keywords = $request->get('keywords', '');
        $keywords = trim($keywords);

        /*
        if ($keywords === '') {
            $response->error(beLang('App.Cms', 'ARTICLE.SEARCH_KEYWORDS_IS_MISSING'));
            return;
        }
        */

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $title = beLang('App.Cms', 'ARTICLE.SEARCH_X_RESULT', $keywords);

        $response->set('title', $title);
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $title);
        $response->display();
    }

    /**
     * 搜索
     *
     * @BeMenu("搜索")
     * @BeRoute("/article/tag")
     */
    public function tag()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $tag = $request->get('tag', '');
        $tag = trim($tag);
        if ($tag === '') {
            $response->error(beLang('App.Cms', 'ARTICLE.TAG_IS_MISSING'));
            return;
        }

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $title = beLang('App.Cms', 'ARTICLE.TAG_X_RESULT', $tag);

        $response->set('title', $title);
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $title);
        $response->display();
    }

    /**
     * 热搜文章
     *
     * @BeMenu("热搜文章")
     * @BeRoute("/article/hot-search")
     */
    public function hotSearch()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $response->display();
    }

    /**
     * 猜你喜欢
     * @BeMenu("猜你喜欢")
     * @BeRoute("/article/guess-you-like")
     */
    public function guessYouLike()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $response->display();
    }

    /**
     * 文章明细
     *
     * @BeMenu("文章详情", picker="return \Be\Be::getService('App.Cms.Admin.Article')->getArticleMenuPicker()")
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
                throw new ControllerException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
            }

            $article = $service->hit($id);

            $response->set('title', $article->seo_title);
            $response->set('metaDescription', $article->seo_description);
            $response->set('metaKeywords', $article->seo_keywords);
            $response->set('pageTitle', $article->title);

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
                throw new ControllerException(beLang('App.Cms', 'ARTICLE.NOT_EXIST'));
            }

            $article = $service->getArticleFromDb($id);

            $response->set('title', $article->seo_title);
            $response->set('metaDescription', $article->seo_description);
            $response->set('metaKeywords', $article->seo_keywords);
            $response->set('pageTitle', $article->title);

            $response->set('article', $article);

            $response->display();
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }



}
