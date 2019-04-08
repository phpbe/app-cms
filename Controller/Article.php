<?php
namespace App\Cms\Controller;

use Phpbe\System\Be;
use Phpbe\System\Request;
use Phpbe\System\Response;
use Phpbe\System\Controller;

/**
 * @be-controller-name 文章
 */
class Article extends Controller
{

    /**
     *
     *
     * @be-action-name 首页
     * @be-action-permission 首页
     */
    public function home()
    {
        $serviceArticleCache = Be::getService('Cms', 'Article')->withCache(Be::getConfig('Cms', 'Article')->cacheExpire);

        // 最新带图文章
        $latestThumbnailArticles = $serviceArticleCache->getArticles([
            'block' => 0,
            'thumbnail' => 1,
            'orderBy' => 'create_time',
            'orderByDir' => 'DESC',
            'limit' => 6
        ]);

        $activeUsers = $serviceArticleCache->getActiveUsers();

        // 本月热点
        $monthHottestArticles = $serviceArticleCache->getArticles([
            'block' => 0,
            'orderBy' => 'hits',
            'orderByDir' => 'DESC',
            'fromTime' => time() - 86400 * 30,
            'limit' => 6
        ]);

        // 推荐文章
        $topArticles = $serviceArticleCache->getArticles([
            'block' => 0,
            'top' => 1,
            'orderBy' => 'top',
            'orderByDir' => 'DESC',
            'limit' => 6
        ]);

        $topCategories = array();

        $serviceCategoryCache = Be::getService('Cms', 'Category')->withCache(Be::getConfig('Cms', 'Article')->cacheExpire);
        $categories = $serviceCategoryCache->getCategories();
        foreach ($categories as $category) {
            if ($category->parentId > 0) continue;
            $topCategories[] = $category;

            $category->articles = $serviceArticleCache->getArticles([
                'block' => 0,
                'categoryId' => $category->id,
                'orderBy' => 'create_time',
                'orderByDir' => 'DESC',
                'limit' => 6
            ]);
        }

        $configSystem = Be::getConfig('System', 'System');

        Response::setTitle($configSystem->homeTitle);
        Response::setMetaKeywords($configSystem->homeMetaKeywords);
        Response::setMetaDescription($configSystem->homeMetaDescription);
        Response::set('latestThumbnailArticles', $latestThumbnailArticles);
        Response::set('activeUsers', $activeUsers);
        Response::set('monthHottestArticles', $monthHottestArticles);
        Response::set('topArticles', $topArticles);
        Response::set('categories', $topCategories);
        Response::display();
    }


    /**
     *
     *
     * @permission 文章列表
     */
    public function articles()
    {
        $categoryId = Request::get('categoryId', 0, 'int');
        Response::set('categoryId', $categoryId);

        $serviceCategoryCache = Be::getService('Cms', 'Category')->withCache(Be::getConfig('Cms', 'Article')->cacheExpire);
        $category = $serviceCategoryCache->getCategory($categoryId);

        if ($category->id == 0) Response::end('文章分类不存在！');

        Response::setTitle($category->name);
        Response::set('category', $category);

        if ($category->parentId > 0) {
            $parentCategory = $serviceCategoryCache->getTopParentCategory($category->parentId);
            Response::set('parentCategory', $parentCategory);

            $northMenu = Be::getMenu('north');
            $northMenuTree = $northMenu->getMenuTree();
            if (count($northMenuTree)) {
                //$menuExist = false;
                foreach ($northMenuTree as $menu) {
                    if (
                        isset($menu->params['app']) && $menu->params['app'] == 'Cms' &&
                        isset($menu->params['controller']) && $menu->params['controller'] == 'Article' &&
                        isset($menu->params['action']) && $menu->params['action'] == 'listing' &&
                        isset($menu->params['categoryId']) && $menu->params['categoryId'] == $parentCategory->id
                    ) {
                        Response::set('menuId', $menu->id);
                        break;
                    }
                }
            }
        } else {
            Response::set('parentCategory', $category);
        }

        $serviceArticleCache = Be::getService('Cms', 'Article')->withCache(Be::getConfig('Cms', 'Article')->cacheExpire);

        $option = array('categoryId' => $categoryId);

        $limit = 10;
        $pagination = Be::getUi('Pagination');
        $pagination->setLimit($limit);
        $pagination->setTotal($serviceArticleCache->getArticleCount($option));
        $pagination->setPage(Request::get('page', 1, 'int'));
        $pagination->seturl('Cms', 'Article', 'articles', ['categoryId' => $categoryId]);
        Response::set('pagination', $pagination);

        $option['offset'] = $pagination->getOffset();
        $option['limit'] = $limit;
        $option['orderByString'] = '`top` DESC, `ordering` DESC, `create_time` DESC';

        $articles = $serviceArticleCache->getArticles($option);
        Response::set('articles', $articles);

        // 热门文章
        $hottestArticles = $serviceArticleCache->getArticles([
            'block' => 0,
            'categoryId' => $categoryId,
            'orderBy' => 'hits',
            'orderByDir' => 'DESC',
            'limit' => 10
        ]);
        Response::set('hottestArticles', $hottestArticles);

        // 推荐文章
        $topArticles = $serviceArticleCache->getArticles(array('categoryId' => $categoryId, 'top' => 1, 'orderBy' => 'top', 'orderByDir' => 'DESC', 'limit' => 10));
        Response::set('topArticles', $topArticles);

        Response::display();
    }

    /**
     * @permission 文章明细
     */
    public function detail()
    {
        $articleId = Request::get('articleId', 0, 'int');
        if ($articleId == 0) Response::end('参数(articleId)缺失！');

        $tupleArticle = Be::getTuple('Cms', 'Article');
        $tupleArticle->load($articleId);
        $tupleArticle->increment('hits', 1); // 点击量加 1

        $serviceArticleCache = Be::getService('Cms', 'Article')->withCache(Be::getConfig('Cms', 'Article')->cacheExpire);

        $similarArticles = $serviceArticleCache->getSimilarArticles($tupleArticle, 10);

        // 热门文章
        $hottestArticles = $serviceArticleCache->getArticles([
            'block' => 0,
            'categoryId' => $tupleArticle->category_id,
            'orderBy' => 'hits',
            'orderByDir' => 'DESC',
            'limit' => 10
        ]);

        // 推荐文章
        $topArticles = $serviceArticleCache->getArticles([
            'block' => 0,
            'categoryId' => $tupleArticle->category_id,
            'top' => 1,
            'orderBy' => 'top',
            'orderByDir' => 'DESC',
            'limit' => 10
        ]);


        $serviceArticleCommentCache = Be::getService('Cms', 'ArticleComment')->withCache(Be::getConfig('Cms', 'Article')->cacheExpire);
        $comments = $serviceArticleCommentCache->getComments([
            'articleId' => $articleId
        ]);

        Response::setTitle($tupleArticle->title);
        Response::setMetaKeywords($tupleArticle->metaKeywords);
        Response::setMetaDescription($tupleArticle->metaDescription);

        $northMenu = Be::getMenu('north');
        $northMenuTree = $northMenu->getMenuTree();
        if (count($northMenuTree)) {
            $menuExist = false;
            foreach ($northMenuTree as $menu) {
                if (
                    isset($menu->params['app']) && $menu->params['app'] == 'Cms' &&
                    isset($menu->params['controller']) && $menu->params['controller'] == 'Article' &&
                    isset($menu->params['action']) && $menu->params['action'] == 'detail' &&
                    isset($menu->params['articleId']) && $menu->params['articleId'] == $articleId
                ) {
                    Response::set('menuId', $menu->id);
                    if ($menu->home == 1) Response::set('home', 1);
                    $menuExist = true;
                    break;
                }
            }

            if (!$menuExist) {
                foreach ($northMenuTree as $menu) {
                    if (
                        isset($menu->params['app']) && $menu->params['app'] == 'Cms' &&
                        isset($menu->params['controller']) && $menu->params['controller'] == 'Article' &&
                        isset($menu->params['action']) && $menu->params['action'] == 'listing' &&
                        isset($menu->params['categoryId']) && $menu->params['categoryId'] == $tupleArticle->categoryId
                    ) {
                        Response::set('menuId', $menu->id);
                        //$menuExist = true;
                        break;
                    }
                }
            }
        }

        Response::set('article', $tupleArticle);
        Response::set('similarArticles', $similarArticles);
        Response::set('hottestArticles', $hottestArticles);
        Response::set('topArticles', $topArticles);
        Response::set('comments', $comments);
        Response::display();
    }

}
