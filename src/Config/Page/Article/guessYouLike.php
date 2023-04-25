<?php

namespace Be\App\Cms\Config\Page\Article;

class guessYouLike
{



    public int $west = 0;
    public int $center = 75;
    public int $east = 25;


    public array $centerSections = [
        [
            'name' => 'App.Cms.PageTitle',
        ],
        [
            'name' => 'App.Cms.GuessYouLike',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.Article.SearchFormSide',
        ],
        [
            'name' => 'App.Cms.Article.LatestTopNSide',
        ],
        [
            'name' => 'App.Cms.Article.HottestTopNSide',
        ],
        [
            'name' => 'App.Cms.Article.HotSearchTopNSide',
        ],
        [
            'name' => 'App.Cms.Article.GuessYouLikeTopNSide',
        ],
        [
            'name' => 'App.Cms.Article.TagTopNSide',
        ],
    ];

}
