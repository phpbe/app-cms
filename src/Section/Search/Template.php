<?php

namespace Be\App\Cms\Section\Search;

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
        $response = Be::getResponse();

        $keyword = $request->get('keyword', '');
        $keyword = trim($keyword);
        if ($keyword === '') {
            $response->error(beLang('App.Cms', 'ARTICLE.SEARCH_KEYWORDS_IS_MISSING'));
            return;
        }

        $page = $request->get('page', 1);
        $params = [
            'page' => $page,
        ];

        if ($this->config->pageSize > 0) {
            $params['pageSize'] = $this->config->pageSize;
        }

        $result = Be::getService('App.Cms.Article')->search($keyword, $params);
        $paginationUrl = $request->getUrl();
        echo Be::getService('App.Cms.Section')->makePagedArticlesSection($this, 'app-cms-search', $result, $paginationUrl);
    }
}

