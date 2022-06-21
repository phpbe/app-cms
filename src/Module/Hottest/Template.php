<?php
if ($sectionData['enable']) {
    $articles = \Be\Be::getService('App.Cms.Article')->getHottestArticles($sectionData['quantity']);
    if (count($articles) > 0) {
        $moreLink = beUrl('Cms.Article.hottest');
        echo \Be\Be::getService('App.Cms.Article')->makeArticleSection('hottest', $sectionType, $sectionKey, $sectionData, $articles, $moreLink);
    }
}
