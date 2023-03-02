<?php

namespace Be\App\Cms\Config\Page\Article;


class detail
{

    public int $west = 0;
    public int $center = 75;
    public int $east = 25;

    public array $centerSections = [
        [
            'name' => 'Theme.System.PageTitle',
        ],
        [
            'name' => 'Theme.System.PageContent',
        ],
        [
            'name' => 'App.Cms.ArticleComments',
        ],
        [
            'name' => 'App.Cms.ArticleCommentForm',
        ],
        [
            'name' => 'App.Cms.Similar',
        ],
    ];

    public array $eastSections = [
        [
            'name' => 'App.Cms.Latest',
        ],
        [
            'name' => 'App.Cms.Hottest',
        ],
        [
            'name' => 'App.Cms.GuessYouLike',
        ],
    ];

}
