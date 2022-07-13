<?php

namespace Be\App\Cms\Config\Page\Article;

class home
{

    public int $west = 0;
    public int $center = 100;
    public int $east = 0;

    public array $centerSections = [
        [
            'name' => 'App.Cms.Latest',
        ],
        [
            'name' => 'App.Cms.Hottest',
        ],
    ];

}
