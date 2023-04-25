<?php

namespace Be\App\Cms\Section\Article\LatestTopN;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'center'];

    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $articles = Be::getService('App.Cms.Article')->getLatestTopNArticles($this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.latest');
        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'app-cms-article-latest-top-n', $articles, $defaultMoreLink);
    }

}

