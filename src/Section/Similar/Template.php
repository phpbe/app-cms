<?php

namespace Be\App\Cms\Section\Similar;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'west', 'center', 'east'];

    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $request = Be::getRequest();
        $route = $request->getRoute();

        // 仅详情页，预览页可用
        if (!in_array($route, ['Cms.Article.detail', 'Cms.Article.preview'])) {
            return;
        }

        // 无文章数据时不显示
        if (!isset($this->page->article) || !$this->page->article) {
            return;
        }

        $article = $this->page->article;

        $articles = Be::getService('App.Cms.Article')->getSimilarArticles($article->id, $article->title, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'similar', $articles);
    }
}

