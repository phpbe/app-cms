<?php

namespace Be\App\Cms\Config\Page\Article;

class search
{

    public int $west = 0;
    public int $center = 100;
    public int $east = 0;

    public array $centerSections = [
        [
            'name' => 'be-page-title',
        ],
        [
            'name' => 'be-page-content',
        ],
    ];

}
