<?php

namespace Be\App\Cms\Section\Category\LatestTopNSide;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['west', 'east'];

    public array $routes = ['Cms.Category.articles'];

    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getCategoryLatestTopNArticles($this->page->category->id, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.latest');
        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-category-latest-top-n-side', $articles, $defaultMoreLink);
    }
}

