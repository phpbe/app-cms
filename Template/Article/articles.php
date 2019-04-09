<?php
use Phpbe\System\Be;
?>

<!--{extends:\App\Cms\Article\detail}-->

<!--{head}-->
<link type="text/css" rel="stylesheet" href="/app/Cms/Template/Article/css/articles.css">
<!--{/head}-->

<!--{middle}-->
<div class="row">

    <div class="col" style="width:<?php echo (!$west && !$east)?100:70; ?>%;">

        <div class="theme-center-container">
            <div class="theme-center">

                <!--{center}-->
                <?php
                $categoryId = $this->categoryId;
                $articles = $this->articles;

                $pagination = $this->pagination;

                $configArticle = Be::getConfig('Cms.Article');

                if (count($articles)) {
                    $configArticle = Be::getConfig('Cms.Article');

                    if ($pagination->getPage() == 1) {
                        $article = array_shift($articles);
                        ?>
                        <h4 class="head-article-title"><a href="<?php echo url('Cms.Article.detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h4>
                        <div class="head-article-summary"><?php echo $article->summary; ?></div>
                        <div class="head-article-thumbnail">
                            <a href="<?php echo url('Cms.Article.detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>">
                                <img src="<?php echo Be::getRuntime()->getDataUrl().'/Article/Thumbnail/'; ?><?php echo $article->thumbnailL == ''?('default/'.$configArticle->defaultThumbnailL):$article->thumbnailL; ?>" alt="<?php echo $article->title; ?>" />
                            </a>
                        </div>
                        <?php
                    }
                }
                ?>

                <?php
                if (count($articles)) {
                    foreach ($articles as $article) {
                        ?>
                        <div class="article">
                            <div class="article-thumbnail" style="width:<?php echo $configArticle->thumbnailMW; ?>px; height:<?php echo $configArticle->thumbnailMH; ?>px;">
                                <a href="<?php echo url('Cms.Article.detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>">
                                    <img src="<?php echo Be::getRuntime()->getDataUrl().'/Article/Thumbnail/'; ?><?php echo $article->thumbnailM == ''?('default/'.$configArticle->defaultThumbnailM):$article->thumbnailM; ?>" alt="<?php echo $article->title; ?>" />
                                </a>
                            </div>

                            <div style="margin-left:<?php echo $configArticle->thumbnailMW; ?>px;">
                                <h4 class="article-title"><a href="<?php echo url('Cms.Article.detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h4>
                                <div class="article-time"><?php echo date('Y-m-d H:i:s', $article->createTime); ?></div>
                                <div class="article-summary"><?php echo $article->summary; ?></div>
                            </div>
                        </div>

                        <div class="clear-left"></div>
                        <?php
                    }
                }
                ?>
                <div style="padding:10px 0;"><?php $pagination->display(); ?></div>
                <!--{/center}-->

            </div>
        </div>

    </div>


    <div class="col" style="width:30%;">
        <div class="theme-east-container">
            <div class="theme-east">

                <!--{east}-->
                <?php
                $hottestArticles = $this->hottestArticles;
                $topArticles = $this->topArticles;

                $configArticle = Be::getConfig('Cms', 'Article');

                if (count($hottestArticles)) {
                    ?>
                    <div class="theme-box-container">
                        <div class="theme-box">
                            <div class="theme-box-title">热门文章</div>
                            <div class="theme-box-body">

                                <?php
                                foreach ($hottestArticles as $article) {
                                    ?>
                                    <div class="hottest-article">

                                        <div class="hottest-article-thumbnail" style="width:<?php echo $configArticle->thumbnailSW; ?>px; height:<?php echo $configArticle->thumbnailSH; ?>px;">
                                            <a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>">
                                                <img src="<?php echo url().'/'.DATA.'/Article/Thumbnail/'; ?><?php echo $article->thumbnailS == ''?('default/'.$configArticle->defaultThumbnailS):$article->thumbnailS; ?>" alt="<?php echo $article->title; ?>" />
                                            </a>
                                        </div>

                                        <div style="margin-left:<?php echo $configArticle->thumbnailSW; ?>px;">
                                            <h5 class="hottest-article-title"><a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h5>
                                            <div class="hottest-article-time"><?php echo date('Y-m-d H:i:s', $article->createTime); ?></div>
                                        </div>
                                    </div>

                                    <div class="clear-left"></div>
                                    <?php
                                }
                                ?>


                            </div>
                        </div>
                    </div>
                    <?php
                }


                if (count($topArticles)) {
                    ?>
                    <div class="theme-box-container">
                        <div class="theme-box">
                            <div class="theme-box-title">推荐文章</div>
                            <div class="theme-box-body">

                                <?php
                                foreach ($topArticles as $article) {
                                    ?>
                                    <div class="top-article">

                                        <div class="top-article-thumbnail" style="width:<?php echo $configArticle->thumbnailSW; ?>px; height:<?php echo $configArticle->thumbnailSH; ?>px;">
                                            <a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>">
                                                <img src="<?php echo url().'/'.DATA.'/Article/Thumbnail/'; ?><?php echo $article->thumbnailS == ''?('default/'.$configArticle->defaultThumbnailS):$article->thumbnailS; ?>" alt="<?php echo $article->title; ?>" />
                                            </a>
                                        </div>

                                        <div style="margin-left:<?php echo $configArticle->thumbnailSW; ?>px;">
                                            <h5 class="top-article-title"><a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>" title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h5>
                                            <div class="top-article-time"><?php echo date('Y-m-d H:i:s', $article->createTime); ?></div>
                                        </div>
                                    </div>

                                    <div class="clear-left"></div>
                                    <?php
                                }
                                ?>

                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <!--{/east}-->

            </div>
        </div>
    </div>

    <div class="clear-left"></div>
</div>
<!--{/middle}-->
