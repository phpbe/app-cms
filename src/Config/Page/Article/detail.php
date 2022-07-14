<?php

namespace Be\App\Cms\Config\Page\Article;

class detail
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'be-page-title',
        ],
        [
            'name' => 'be-page-content',
        ],
        [
            'name' => 'App.Cms.Related',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.Latest',
        ],
        [
            'name' => 'App.Cms.Hottest',
        ],
    ];

}
