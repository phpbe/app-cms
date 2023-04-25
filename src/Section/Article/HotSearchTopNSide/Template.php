<?php

namespace Be\App\Cms\Section\Article\HotSearchTopNSide;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['west', 'east'];
    
    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getHotSearchTopNArticles($this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.hotSearch');
        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-article-hot-search-top-n-side', $articles, $defaultMoreLink);
    }

}

