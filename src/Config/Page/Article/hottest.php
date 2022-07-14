<?php

namespace Be\App\Cms\Config\Page\Article;

class hottest
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'be-page-content',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.Latest',
        ],
    ];


}
