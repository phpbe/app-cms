<?php

namespace Be\App\Cms\Section\CategoryGuessYouLike;

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
        $categoryId = null;
        if ($request->getRoute() === 'Cms.Category.articles') {
            $categoryId = $request->get('id', null);
        }

        if ($categoryId === null) {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getCategoryGuessYouLikeArticles($categoryId, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'category-guess-you-like', $articles);
    }
}

