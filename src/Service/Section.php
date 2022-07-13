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
     * @param string $moreLink
     * @return string
     */
    public function makeArticlesSection(object $section, string $class, array $articles, string $moreLink = null): string
    {
        $html = '';
        $html .= '<style type="text/css">';
        $html .= $section->getCssBackgroundColor($class);
        $html .= $section->getCssPadding($class);
        $html .= $section->getCssMargin($class);
        $html .= '</style>';

        $html .= '<div class="' . $class . '">';
        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '<div class="be-container">';
        }

        if ($section->config->title !== '') {
            $html .= $section->pageTemplate->tag0('be-section-title', true);
            $html .= $section->config->title;
            $html .= $section->pageTemplate->tag1('be-section-title', true);
        }

        $html .= $section->pageTemplate->tag0('be-section-content', true);

        foreach ($articles as $article) {
            $html .= '<div class="be-py-20">';
            $html .= '<a class="be-d-block be-t-ellipsis" href="' . beUrl('Cms.Article.detail', ['id' => $article->id]) . '" title="'.$article->title.'">';
            $html .= $article->title;
            $html .= '</a>';
            $html .= '</div>';
        }

        $html .= $section->pageTemplate->tag1('be-section-content', true);

        if ($moreLink !== null && isset($section->config->more) && $section->config->more !== '') {
            $html .= '<div class="be-mt-100 be-bt-eee be-pt-100 be-ta-right">';
            $html .= '<a href="' . $moreLink . '"';
            if (!Be::getRequest()->isMobile()) {
                $html .= ' target="_blank"';
            }
            $html .= '>' . $section->config->more . '</a>';
            $html .= '</div>';
        }

        if ($section->position === 'middle' && $section->config->width === 'default') {
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

}
