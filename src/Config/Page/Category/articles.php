<?php

namespace Be\App\Cms\Config\Page\Category;

class articles
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'App.Cms.CategoryArticles',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.CategorySideHottest',
        ],
    ];


}
