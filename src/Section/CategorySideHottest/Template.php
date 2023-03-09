<?php

namespace Be\App\Cms\Section\CategorySideHottest;

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

        if ($this->route !== 'Cms.Category.articles') {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getCategoryHottestArticles($this->page->category->id, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-category-hottest', $articles);
    }
}

