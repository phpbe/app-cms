<?php

namespace Be\App\Cms\Config\Page\Page;

class preview
{

    public int $west = 0;
    public int $center = 1;
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
