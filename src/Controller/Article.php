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
     * 首页
     *
     * @BeMenu("文章首页")
     * @BeRoute("/article/home")
     */
    public function home()
    {
        $request = Be::getRequest();
        $response = Be::getResponse();

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $page = $request->get('page', 1);
        $result = Be::getService('App.Cms.Article')->search('', [
            'isPushHome' => 1,
            'orderBy' => ['is_on_top', 'publish_time'],
            'orderByDir' => ['desc', 'desc'],
            'page' => $page,
        ]);
        $response->set('result', $result);
        $paginationUrl = beUrl('Cms.Article.home');
        $response->set('paginationUrl', $paginationUrl);

        $response->display('App.Cms.Article.articles');
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

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

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

        $pageConfig = $response->getPageConfig();
        $response->set('pageConfig', $pageConfig);

        $response->set('title', $pageConfig->title ?: '');
        $response->set('metaDescription', $pageConfig->metaDescription ?: '');
        $response->set('metaKeywords', $pageConfig->metaKeywords ?: '');
        $response->set('pageTitle', $pageConfig->pageTitle ?: ($pageConfig->title ?: ''));

        $page = $request->get('page', 1);
        $result = Be::getService('App.Cms.Article')->search('', [
            'orderBy' => 'hits',
            'orderByDir' => 'desc',
            'page' => $page,
        ]);
        $response->set('result', $result);

        $paginationUrl = beUrl('Cms.Article.hottest');
        $response->set('paginationUrl', $paginationUrl);

        $response->display('App.Cms.Article.articles');
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

        $keyword = $request->get('keyword', '');
        $page = $request->get('page', 1);
        $result = Be::getService('App.Cms.Article')->search($keyword, [
            'page' => $page,
        ]);
        $response->set('result', $result);

        $paginationUrl = beUrl('Cms.Article.hottest');
        $response->set('paginationUrl', $paginationUrl);

        $response->set('title', '搜索 ' . $keyword . ' 的结果');

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
            $response->set('metaDescription', $article->seo_description);
            $response->set('metaKeywords', $article->seo_keywords);
            $response->set('pageTitle', $article->title);

            $response->set('article', $article);

            $configArticle = Be::getConfig('App.Cms.Article');
            $response->set('configArticle', $configArticle);

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
            $response->set('metaDescription', $article->seo_description);
            $response->set('metaKeywords', $article->seo_keywords);
            $response->set('pageTitle', $article->title);

            $response->set('article', $article);

            $configArticle = Be::getConfig('App.Cms.Article');
            $response->set('configArticle', $configArticle);

            $response->display('App.Cms.Article.detail');
        } catch (\Throwable $t) {
            $response->error($t->getMessage());
        }
    }
}
