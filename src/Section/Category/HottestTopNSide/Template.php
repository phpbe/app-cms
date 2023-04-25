<?php

namespace Be\App\Cms\Section\Category\HottestTopNSide;

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

        if ($this->route !== 'Cms.Category.articles') {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getCategoryHottestTopNArticles($this->page->category->id, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-category-hottest-top-n-side', $articles);
    }
}

