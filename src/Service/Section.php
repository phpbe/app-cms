<?php

namespace Be\App\Cms\Service;

use Be\Be;

class Section
{

    /**
     * 生成文章列表部件
     *
     * @param object $section
     * @param string $class
     * @param array $articles
     * @param string $defaultMoreLink
     * @return string
     */
    public function makeArticlesSection(object $section, string $class, array $articles, string $defaultMoreLink = null): string
    {
        $html = '';
        $html .= '<style type="text/css">';
        $html .= $section->getCssBackgroundColor($class);
        $html .= $section->getCssPadding($class);
        $html .= $section->getCssMargin($class);

        $html .= '#' . $section->id . ' .' . $class . '-item {';
        //$html .= 'box-shadow: 0 0 10px var(--font-color-9);';
        $html .= 'box-shadow: 0 0 10px #eaf0f6;';
        $html .= 'transition: all 0.3s ease;';
        $html .= '}';

        $html .= '#' . $section->id . ' .' . $class . '-item:hover {';
        //$html .= 'box-shadow: 0 0 15px var(--font-color-8);';
        $html .= 'box-shadow: 0 0 15px #dae0e6;';
        $html .= '}';

        if (isset($section->config->itemBackgroundColor) && $section->config->itemBackgroundColor) {
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'background-color: ' . $section->config->itemBackgroundColor . ';';
            $html .= '}';
        }

        // 手机端
        if (isset($section->config->itemPaddingMobile) && $section->config->itemPaddingMobile !== '') {
            $html .= '@media (max-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'padding: ' . $section->config->itemPaddingMobile . ';';
            $html .= '}';
            $html .= '}';
        }

        // 平析端
        if (isset($section->config->itemPaddingTablet) && $section->config->itemPaddingTablet !== '') {
            $html .= '@media (min-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'padding: ' . $section->config->itemPaddingTablet . ';';
            $html .= '}';
            $html .= '}';
        }

        // 电脑端
        if (isset($section->config->itemPaddingDesktop) && $section->config->itemPaddingDesktop !== '') {
            $html .= '@media (min-width: 992px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'padding: ' . $section->config->itemPaddingDesktop . ';';
            $html .= '}';
            $html .= '}';
        }

        // 手机端
        if (isset($section->config->itemMarginMobile) && $section->config->itemMarginMobile !== '') {
            $html .= '@media (max-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'margin: ' . $section->config->itemMarginMobile . ';';
            $html .= '}';
            $html .= '}';
        }

        // 平析端
        if (isset($section->config->itemMarginTablet) && $section->config->itemMarginTablet !== '') {
            $html .= '@media (min-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'margin: ' . $section->config->itemMarginTablet . ';';
            $html .= '}';
            $html .= '}';
        }

        // 电脑端
        if (isset($section->config->itemMarginDesktop) && $section->config->itemMarginDesktop !== '') {
            $html .= '@media (min-width: 992px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'margin: ' . $section->config->itemMarginDesktop . ';';
            $html .= '}';
            $html .= '}';
        }

        $html .= '#' . $section->id . ' .' . $class . ' .article-image {';
        $html .= 'width: 100%;';
        $html .= '}';

        $html .= '#' . $section->id . ' .' . $class . ' .article-image img {';
        $html .= 'max-width: 100%;';
        $html .= '}';

        $html .= '@media (min-width: 768px) {';
        $html .= '#' . $section->id . ' .' . $class . ' .article-image {';
        $html .= 'width: 200px;';
        $html .= 'max-height: 200px;';
        $html .= 'overflow: hidden;';
        $html .= '}';
        $html .= '}';
        $html .= '</style>';

        $html .= '<div class="' . $class . '">';
        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '<div class="be-container">';
        }

        if (isset($section->config->title) && $section->config->title !== '') {
            $html .= $section->page->tag0('be-section-title', true);

            $html .= '<div class="' . $class . '-title">';
            $html .= '<h3 class="be-h3">' . $section->config->title . '</h3>';
            $html .= '</div>';

            $html .= $section->page->tag1('be-section-title', true);
        }

        $html .= $section->page->tag0('be-section-content', true);


        if (count($articles) === 0) {
            $html .= '<div class="'.$class.'-item" style="margin-top: 0;">';
            $html .= '<div class="be-py-400 be-fs-150 be-ta-center">';
            $html .= beLang('App.Cms', 'ARTICLE.NO_MATCH_RESULT');
            $html .= '</div>';
            $html .= '</div>';
        } else {

            $isMobile = \Be\Be::getRequest()->isMobile();
            $i = 0;
            foreach ($articles as $article) {
                $html .= '<div class="'.$class.'-item"';
                if ($i === 0) {
                    $html .= ' style="margin-top: 0;"';
                }
                $html .= '>';

                $html .= '<div class="be-row">';

                $html .= '<div class="be-col-24 be-md-col-auto">';
                $html .= '<div class="article-image">';
                $html .= '<a class="be-d-inline-block" href="';
                $html .= beUrl('Cms.Article.detail', ['id'=> $article->id]);
                $html .= '" title="';
                $html .= $article->title;
                $html .= '"';
                if (!$isMobile) {
                    $html .= ' target="_blank"';
                }
                $html .= '>';
                $html .= '<img src="';
                if ($article->image === '') {
                    $html .= \Be\Be::getProperty('App.Cms')->getWwwUrl() . '/article/images/no-image-m.jpg';
                } else {
                    $html .= $article->image;
                }
                $html .= '" alt="';
                $html .= $article->title;
                $html .= '">';
                $html .= '</a>';
                $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="be-col-24 be-md-col-auto"><div class="be-pl-100 be-pt-100"></div></div>';
                $html .= '<div class="be-col-24 be-md-col">';

                $html .= '<div>';
                $html .= '<a class="be-fs-125 be-fw-bold be-lh-200" href="';
                $html .= beUrl('Cms.Article.detail', ['id'=> $article->id]);
                $html .= '" title="';
                $html .= $article->title;
                $html .= '"';
                if (!$isMobile) {
                    $html .= ' target="_blank"';
                }
                $html .= '>';
                $html .= $article->title;
                $html .= '</a>';
                $html .= '</div>';

                $html .= '<div class="be-mt-100 be-lh-175 be-c-font-3">';
                $html .= $article->summary;
                $html .= '</div>';

                $html .= '<div class="be-mt-100 be-c-font-6">';
                $html .= '<span>';
                $html .= date(beLang('App.Cms', 'ARTICLE.PUBLISH_TIME_YYYY_MM_DD'), strtotime($article->publish_time));
                $html .= '</span>';
                if ($article->author !== '') {
                    $html .= '<span class="be-ml-100">' . beLang('App.Cms', 'ARTICLE.AUTHOR') . ': ' . $article->author . '</span>';
                }
                $html .= '</div>';

                $html .= '</div>'; // be-col-

                $html .= '</div>'; // be-row
                $html .= '</div>'; // -item
                $i++;
            }

            if (isset($section->config->more) && $section->config->more !== '') {
                $moreLink = null;
                if (isset($section->config->moreLink) && $section->config->moreLink !== '') {
                    $moreLink = $section->config->moreLink;
                }

                if ($moreLink === null && $defaultMoreLink !== null) {
                    $moreLink = $defaultMoreLink;
                }

                if ($moreLink !== null) {
                    $html .= '<div class="be-mt-100 be-bt-eee be-pt-100 be-ta-right">';
                    $html .= '<a href="' . $moreLink . '"';
                    if (!$isMobile) {
                        $html .= ' target="_blank"';
                    }
                    $html .= '>' . $section->config->more . '</a>';
                    $html .= '</div>';
                }
            }
        }

        $html .= $section->page->tag1('be-section-content', true);

        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * 生成分页章列表部件
     *
     * @param object $section
     * @param string $class
     * @param array $result
     * @return string
     */
    public function makePagedArticlesSection(object $section, string $class, array $result): string
    {
        $html = '';
        $html .= '<style type="text/css">';
        $html .= $section->getCssBackgroundColor($class);
        $html .= $section->getCssPadding($class);
        $html .= $section->getCssMargin($class);

        $html .= '#' . $section->id . ' .' . $class . '-item {';
        //$html .= 'box-shadow: 0 0 10px var(--font-color-9);';
        $html .= 'box-shadow: 0 0 10px #eaf0f6;';
        $html .= 'transition: all 0.3s ease;';
        $html .= '}';

        $html .= '#' . $section->id . ' .' . $class . '-item:hover {';
        //$html .= 'box-shadow: 0 0 15px var(--font-color-8);';
        $html .= 'box-shadow: 0 0 15px #dae0e6;';
        $html .= '}';

        if (isset($section->config->itemBackgroundColor) && $section->config->itemBackgroundColor) {
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'background-color: ' . $section->config->itemBackgroundColor . ';';
            $html .= '}';
        }

        // 手机端
        if (isset($section->config->itemPaddingMobile) && $section->config->itemPaddingMobile !== '') {
            $html .= '@media (max-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'padding: ' . $section->config->itemPaddingMobile . ';';
            $html .= '}';
            $html .= '}';
        }

        // 平析端
        if (isset($section->config->itemPaddingTablet) && $section->config->itemPaddingTablet !== '') {
            $html .= '@media (min-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'padding: ' . $section->config->itemPaddingTablet . ';';
            $html .= '}';
            $html .= '}';
        }

        // 电脑端
        if (isset($section->config->itemPaddingDesktop) && $section->config->itemPaddingDesktop !== '') {
            $html .= '@media (min-width: 992px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'padding: ' . $section->config->itemPaddingDesktop . ';';
            $html .= '}';
            $html .= '}';
        }

        // 手机端
        if (isset($section->config->itemMarginMobile) && $section->config->itemMarginMobile !== '') {
            $html .= '@media (max-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'margin: ' . $section->config->itemMarginMobile . ';';
            $html .= '}';
            $html .= '}';
        }


        // 平析端
        if (isset($section->config->itemMarginTablet) && $section->config->itemMarginTablet !== '') {
            $html .= '@media (min-width: 768px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'margin: ' . $section->config->itemMarginTablet . ';';
            $html .= '}';
            $html .= '}';
        }

        // 电脑端
        if (isset($section->config->itemMarginDesktop) && $section->config->itemMarginDesktop !== '') {
            $html .= '@media (min-width: 992px) {';
            $html .= '#' . $section->id . ' .' . $class . '-item {';
            $html .= 'margin: ' . $section->config->itemMarginDesktop . ';';
            $html .= '}';
            $html .= '}';
        }

        $html .= '#' . $section->id . ' .' . $class . ' .article-image {';
        $html .= 'width: 100%;';
        $html .= '}';

        $html .= '#' . $section->id . ' .' . $class . ' .article-image img {';
        $html .= 'max-width: 100%;';
        $html .= '}';

        $html .= '@media (min-width: 768px) {';
        $html .= '#' . $section->id . ' .' . $class . ' .article-image {';
        $html .= 'width: 200px;';
        $html .= 'max-height: 200px;';
        $html .= 'overflow: hidden;';
        $html .= '}';
        $html .= '}';
        $html .= '</style>';

        $html .= '<div class="' . $class . '">';
        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '<div class="be-container">';
        }

        if (isset($section->config->title) && $section->config->title !== '') {
            $html .= $section->page->tag0('be-section-title', true);

            $html .= '<div class="' . $class . '-title">';
            $html .= '<h3 class="be-h3">' . $section->config->title . '</h3>';
            $html .= '</div>';

            $html .= $section->page->tag1('be-section-title', true);
        }

        $html .= $section->page->tag0('be-section-content', true);

        if ($result['total'] === 0) {
            $html .= '<div class="'.$class.'-item" style="margin-top: 0;">';
            $html .= '<div class="be-py-400 be-fs-150 be-ta-center">';
            $html .= beLang('App.Cms', 'ARTICLE.NO_MATCH_RESULT');
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $isMobile = \Be\Be::getRequest()->isMobile();
            $i = 0;
            foreach ($result['rows'] as $article) {
                $html .= '<div class="'.$class.'-item"';
                if ($i === 0) {
                    $html .= ' style="margin-top: 0;"';
                }
                $html .= '>';
                $html .= '<div class="be-row">';

                $html .= '<div class="be-col-24 be-md-col-auto">';
                $html .= '<div class="article-image">';
                $html .= '<a class="be-d-inline-block" href="';
                $html .= beUrl('Cms.Article.detail', ['id'=> $article->id]);
                $html .= '" title="';
                $html .= $article->title;
                $html .= '"';
                if (!$isMobile) {
                    $html .= ' target="_blank"';
                }
                $html .= '>';
                $html .= '<img src="';
                if ($article->image === '') {
                    $html .= \Be\Be::getProperty('App.Cms')->getWwwUrl() . '/article/images/no-image-m.jpg';
                } else {
                    $html .= $article->image;
                }
                $html .= '" alt="';
                $html .= $article->title;
                $html .= '">';
                $html .= '</a>';
                $html .= '</div>';
                $html .= '</div>';

                $html .= '<div class="be-col-24 be-md-col-auto"><div class="be-pl-100 be-pt-100"></div></div>';
                $html .= '<div class="be-col-24 be-md-col">';

                $html .= '<div>';
                $html .= '<a class="be-fs-125 be-fw-bold be-lh-200" href="';
                $html .= beUrl('Cms.Article.detail', ['id'=> $article->id]);
                $html .= '" title="';
                $html .= $article->title;
                $html .= '"';
                if (!$isMobile) {
                    $html .= ' target="_blank"';
                }
                $html .= '>';
                $html .= $article->title;
                $html .= '</a>';
                $html .= '</div>';

                $html .= '<div class="be-mt-100 be-lh-175 be-c-font-3">';
                $html .= $article->summary;
                $html .= '</div>';

                $html .= '<div class="be-mt-100 be-c-font-6">';
                $html .= '<span>';
                $html .= date(beLang('App.Cms', 'ARTICLE.PUBLISH_TIME_YYYY_MM_DD'), strtotime($article->publish_time));
                $html .= '</span>';
                if ($article->author !== '') {
                    $html .= '<span class="be-ml-100">' . beLang('App.Cms', 'ARTICLE.AUTHOR') . ': ' . $article->author . '</span>';
                }
                $html .= '</div>';

                $html .= '</div>'; // be-col-

                $html .= '</div>'; // be-row
                $html .= '</div>'; // -item
                $i++;
            }
        }

        $total = $result['total'];
        $pageSize = $result['pageSize'];
        $pages = ceil($total / $pageSize);
        if ($pages > 1) {

            $page = $result['page'];

            $request = Be::getRequest();
            $route = $request->getRoute();
            $params = $request->get();

            if ($page > $pages) $page = $pages;

            $html .= '<nav class="be-mt-200">';
            $html .= '<ul class="be-pagination" style="justify-content: center;">';
            $html .= '<li>';
            if ($page > 1) {
                $params['page'] = $page - 1;
                $html .= '<a href="' . beUrl($route, $params) . '">' . beLang('App.Cms', 'PAGINATION.PREVIOUS'). '</a>';
            } else {
                $html .= '<span>' . beLang('App.Cms', 'PAGINATION.PREVIOUS'). '</span>';
            }
            $html .= '</li>';

            $from = null;
            $to = null;
            if ($pages < 9) {
                $from = 1;
                $to = $pages;
            } else {
                $from = $page - 4;
                if ($from < 1) {
                    $from = 1;
                }

                $to = $from + 8;
                if ($to > $pages) {
                    $to = $pages;
                }
            }

            if ($from > 1) {
                $html .= '<li><span>...</span></li>';
            }

            for ($i = $from; $i <= $to; $i++) {
                if ($i == $page) {
                    $html .= '<li class="active">';
                    $html .= '<span>' . $i . '</span>';
                    $html .= '</li>';
                } else {
                    $params['page'] = $i;
                    $html .= '<li>';
                    $html .= '<a href="' . beUrl($route, $params) . '">' . $i . '</a>';
                    $html .= '</li>';
                }
            }

            if ($to < $pages) {
                $html .= '<li><span>...</span></li>';
            }

            $html .= '<li>';
            if ($page < $pages) {
                $params['page'] = $page + 1;
                $html .= '<a href="' . beUrl($route, $params) . '">' . beLang('App.Cms', 'PAGINATION.NEXT'). '</a>';
            } else {
                $html .= '<span>' . beLang('App.Cms', 'PAGINATION.NEXT'). '</span>';
            }
            $html .= '</li>';
            $html .= '</ul>';
            $html .= '</nav>';
        }

        $html .= $section->page->tag1('be-section-content', true);

        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }


    /**
     * 生成文章列表部件
     *
     * @param object $section
     * @param string $class
     * @param array $articles
     * @param string $defaultMoreLink
     * @return string
     */
    public function makeSideArticlesSection(object $section, string $class, array $articles, string $defaultMoreLink = null): string
    {
        $html = '';
        $html .= '<style type="text/css">';
        $html .= $section->getCssBackgroundColor($class);
        $html .= $section->getCssPadding($class);
        $html .= $section->getCssMargin($class);

        $html .= '#' . $section->id . ' .' . $class . ' {';
        //$html .= 'box-shadow: 0 0 10px var(--font-color-9);';
        $html .= 'box-shadow: 0 0 10px #eaf0f6;';
        $html .= 'transition: all 0.3s ease;';
        $html .= '}';

        $html .= '#' . $section->id . ' .' . $class . ':hover {';
        //$html .= 'box-shadow: 0 0 15px var(--font-color-8);';
        $html .= 'box-shadow: 0 0 15px #dae0e6;';
        $html .= '}';

        $html .= '</style>';

        $html .= '<div class="' . $class . '">';
        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '<div class="be-container">';
        }

        if (isset($section->config->title) && $section->config->title !== '') {
            $html .= $section->page->tag0('be-section-title');
            $html .= $section->config->title;
            $html .= $section->page->tag1('be-section-title');
        }

        $html .= $section->page->tag0('be-section-content');

        $isMobile = \Be\Be::getRequest()->isMobile();
        foreach ($articles as $article) {
            $html .= '<div class="be-py-20">';
            $html .= '<a class="be-d-block be-t-ellipsis" href="' . beUrl('Cms.Article.detail', ['id' => $article->id]) . '" title="' . $article->title . '"';
            if (!$isMobile) {
                $html .= ' target="_blank"';
            }
            $html .= '>';
            $html .= $article->title;
            $html .= '</a>';
            $html .= '</div>';
        }

        if (isset($section->config->more) && $section->config->more !== '') {

            $moreLink = null;
            if (isset($section->config->moreLink) && $section->config->moreLink !== '') {
                $moreLink = $section->config->moreLink;
            }

            if ($moreLink === null && $defaultMoreLink !== null) {
                $moreLink = $defaultMoreLink;
            }

            if ($moreLink !== null) {
                $html .= '<div class="be-mt-100 be-bt-eee be-pt-100 be-ta-right">';
                $html .= '<a href="' . $moreLink . '"';
                if (!$isMobile) {
                    $html .= ' target="_blank"';
                }
                $html .= '>' . $section->config->more . '</a>';
                $html .= '</div>';
            }
        }

        $html .= $section->page->tag1('be-section-content');

        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

}
