<?php
if ($sectionData['enable']) {
    $articles = \Be\Be::getService('App.Cms.Article')->getLatestArticles($sectionData['quantity']);
    if (count($articles) > 0) {
        $moreLink = beUrl('Cms.Article.latest');
        echo \Be\Be::getService('App.Cms.Article')->makeArticleSection('latest', $sectionType, $sectionKey, $sectionData, $products, $moreLink);
    }
}
