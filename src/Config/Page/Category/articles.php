<?php

namespace Be\App\Cms\Config\Page\Category;

class articles
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'Theme.System.PageContent',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.CategoryHottest',
        ],
        [
            'name' => 'App.Cms.GuessYouLike',
        ],
    ];


}
