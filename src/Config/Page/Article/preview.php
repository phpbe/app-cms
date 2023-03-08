<?php

namespace Be\App\Cms\Config\Page\Article;

class preview
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'Theme.System.PageTitle',
        ],
        [
            'name' => 'Theme.System.PageContent',
        ],
        [
            'name' => 'App.Cms.Similar',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.SearchForm',
        ],
        [
            'name' => 'App.Cms.SideLatest',
        ],
        [
            'name' => 'App.Cms.SideHottest',
        ],
        [
            'name' => 'App.Cms.SideGuessYouLike',
        ],
        [
            'name' => 'App.Cms.SideTopTags',
        ],
    ];

}
