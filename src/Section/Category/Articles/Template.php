<?php

namespace Be\App\Cms\Section\Category\Articles;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'center'];

    public array $routes = ['Cms.Category.articles'];

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
            'categoryId' => $this->page->category->id,
            'orderBy' => ['is_on_top', 'publish_time'],
            'orderByDir' => ['desc', 'desc'],
            'pageSize' => $this->config->pageSize,
            'page' => $page,
        ];

        $result = Be::getService('App.Cms.Article')->search('', $params);

        echo Be::getService('App.Cms.Section')->makePagedArticlesSection($this, 'app-cms-category-articles', $result);
    }
}

