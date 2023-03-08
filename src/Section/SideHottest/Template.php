<?php

namespace Be\App\Cms\Section\SideHottest;

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

        $articles = Be::getService('App.Cms.Article')->getHottestArticles($this->config->quantity);
        if (count($articles) === 0) {
            return;
        }

        $defaultMoreLink = beUrl('Cms.Article.hottest');
        echo Be::getService('App.Cms.Section')->makeSideArticlesSection($this, 'app-cms-side-hottest', $articles, $defaultMoreLink);
    }
}

