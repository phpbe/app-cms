<?php

namespace Be\App\Cms\Section\Hottest;

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

        $moreLink = beUrl('Cms.Article.Hottest');
        echo Be::getService('App.Cms.Section')->makeArticlesSection($this, 'hottest', $articles, $moreLink);
    }
}

