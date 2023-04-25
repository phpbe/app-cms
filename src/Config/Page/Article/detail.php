<?php

namespace Be\App\Cms\Config\Page\Article;


class detail
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'App.Cms.Article.Detail.Main',
        ],
        [
            'name' => 'App.Cms.Article.Detail.Comments',
        ],
        [
            'name' => 'App.Cms.Article.Detail.CommentForm',
        ],
        [
            'name' => 'App.Cms.Article.Detail.Similar',
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
