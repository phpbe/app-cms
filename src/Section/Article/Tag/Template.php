<?php

namespace Be\App\Cms\Section\Article\Tag;

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

        $tag = $request->get('tag', '');
        $tag = trim($tag);
        if ($tag === '') {
            $response->error(beLang('App.Cms', 'ARTICLE.TAG_IS_MISSING'));
            return;
        }

        $page = $request->get('page', 1);
        if ($page > $this->config->maxPages) {
            $page = $this->config->maxPages;
        }
        $params = [
            'tag' => $tag,
            'page' => $page,
        ];

        if ($this->config->pageSize > 0) {
            $params['pageSize'] = $this->config->pageSize;
        }

        $result = Be::getService('App.Cms.Article')->search('', $params);

        echo Be::getService('App.Cms.Section')->makePagedArticlesSection($this, 'app-cms-article-tag', $result);
    }
}

