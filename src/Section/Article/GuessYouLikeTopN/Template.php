<?php

namespace Be\App\Cms\Section\Article\GuessYouLikeTopN;

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

        $articles = Be::getService('App.Cms.Article')->getGuessYouLikeTopNArticles($this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.guessYouLike');
        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'app-cms-article-guess-you-like-top-n', $articles, $defaultMoreLink);
    }

}

