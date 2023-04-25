<?php

namespace Be\App\Cms\Section\Article\HottestTopN;

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

        $articles = Be::getService('App.Cms.Article')->getHottestTopNArticles($this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.hottest');
        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'app-cms-article-hottest-top-n', $articles, $defaultMoreLink);
    }

}

