<?php

namespace Be\App\Cms\Section\Hottest;

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
        $params = [
            'orderBy' => 'hits',
            'orderByDir' => 'desc',
            'page' => $page,
        ];

        if ($this->config->pageSize > 0) {
            $params['pageSize'] = $this->config->pageSize;
        }

        $result = Be::getService('App.Cms.Article')->search('', $params);

        $paginationUrl = $request->getUrl();
        echo Be::getService('App.Cms.Section')->makePagedArticlesSection($this, 'app-cms-hottest', $result, $paginationUrl);
    }
}

