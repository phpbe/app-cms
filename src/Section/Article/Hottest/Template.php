<?php

namespace Be\App\Cms\Section\Article\Hottest;

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

        $request = Be::getRequest();
        $page = $request->get('page', 1);
        if ($page > $this->config->maxPages) {
            $page = $this->config->maxPages;
        }
        $params = [
            'orderBy' => 'hits',
            'orderByDir' => 'desc',
            'pageSize' => $this->config->pageSize,
            'page' => $page,
        ];

        $result = Be::getService('App.Cms.Article')->search('', $params);

        echo Be::getService('App.Cms.Section')->makePagedArticlesSection($this, 'app-cms-article-hottest', $result);
    }
}

