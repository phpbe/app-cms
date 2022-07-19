<?php

namespace Be\App\Cms\Section\CategoryHottest;

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
        if ($request->getRoute() !== 'Cms.Category.articles') {
            return;
        }

        $categoryId = $request->get('id');

        $articles = Be::getService('App.Cms.Article')->getCategoryHottestArticles($categoryId, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'category-hottest', $articles);
    }
}

