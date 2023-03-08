<?php

namespace Be\App\Cms\Config\Page\Article;

class search
{


    public int $west = 0;
    public int $center = 75;
    public int $east = 25;


    public array $centerSections = [
        [
            'name' => 'App.Cms.PageTitle',
        ],
        [
            'name' => 'App.Cms.Search',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.SearchForm',
        ],
        [
            'name' => 'App.Cms.SideHottest',
        ],
        [
            'name' => 'App.Cms.SideLatest',
        ],
        [
            'name' => 'App.Cms.SideTopSearch',
        ],
        [
            'name' => 'App.Cms.SideGuessYouLike',
        ],
        [
            'name' => 'App.Cms.SideTopTags',
        ],
    ];


}
