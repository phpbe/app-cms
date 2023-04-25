<?php

namespace Be\App\Cms\Section\Category\GuessYouLikeTopNSide;

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

        $request = Be::getRequest();
        if ($request->getRoute() !== 'Cms.Category.articles') {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getCategoryGuessYouLikeTopNArticles($this->page->category->id, $this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.guessYouLike');
        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-category-guess-you-like-top-n-side', $articles, $defaultMoreLink);
    }

}

