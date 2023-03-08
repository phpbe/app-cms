<?php

namespace Be\App\Cms\Section\GuessYouLike;

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

        $excludeArticleId = null;
        if ($request->getRoute() === 'Cms.Article.detail') {
            $excludeArticleId = $request->get('id', null);
        }

        $articles = Be::getService('App.Cms.Article')->getGuessYouLikeArticles($this->config->quantity, $excludeArticleId);
        if (count($articles) === 0) {
            return;
        }

        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-side-guess-you-like', $articles);
    }
}

