<?php

namespace Be\App\Cms\Config\Page\Category;

class articles
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'App.Cms.Category.Articles',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.Category.TopNSide',
        ],
        [
            'name' => 'App.Cms.Category.LatestTopNSide',
        ],
        [
            'name' => 'App.Cms.Category.HottestTopNSide',
        ],
        [
            'name' => 'App.Cms.Category.HotSearchTopNSide',
        ],
        [
            'name' => 'App.Cms.Category.GuessYouLikeTopNSide',
        ],
        [
            'name' => 'App.Cms.Article.TagTopNSide',
        ],
    ];


}
