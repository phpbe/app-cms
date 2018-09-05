<?php
use Phpbe\System\Be;

?>

<!--{head}-->
<?php
$config = Be::getConfig('System', 'System');
$configArticle = Be::getConfig('Cms', 'Article');
?>
<link type="text/css" rel="stylesheet" href="<?php echo url(); ?>/app/Cms/Template/Article/css/bjqs.css">
<script type="text/javascript" language="javascript" src="<?php echo url(); ?>/app/Cms/Template/Article/js/bjqs-1.3.min.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo url(); ?>/app/Cms/Template/Article/css/home.css">
<script type="text/javascript" language="javascript" src="<?php echo url(); ?>/app/Cms/Template/Article/js/home.js"></script>

<style type="text/css">
    ol.bjqs-markers li a {
        padding: 2px 6px;
        background: <?php echo $this->getColor(3); ?>;
        color: #fff;
        margin: 2px;
        text-decoration: none;
    }

    ol.bjqs-markers li.active-marker a,
    ol.bjqs-markers li a:hover {
        background: <?php echo $this->getColor(); ?>;
    }
</style>
<script type="text/javascript" language="javascript">
    jQuery(document).ready(function ($) {

        $('#banner-fade').bjqs({
            height: <?php echo 280 * $configArticle->thumbnailLH / $configArticle->thumbnailLW; ?>,
            width: 280,
            responsive: true,
            showcontrols: false
        });
    });
</script>
<!--{/head}-->

<!--{center}-->
<?php
$latestThumbnailArticles = $this->latestThumbnailArticles;
$topArticles = $this->topArticles;
$categories = $this->categories;
?>
<div class="row">
    <div class="col-8">

        <div id="banner-fade">
            <ul class="bjqs">
                <?php
                foreach ($latestThumbnailArticles as $article) {
                    ?>
                    <li>
                        <a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                           title="<?php echo $article->title; ?>">
                            <img src="<?php echo Be::getRuntime()->getDataUrl() . '/Article/Thumbnail/' . $article->thumbnailL; ?>"
                                 alt="<?php echo $article->title; ?>" title="<?php echo $article->title; ?>"
                                 style="max-width:100%;">
                        </a>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>

    </div>
    <div class="col-12">

        <div class="top-articles">
            <?php
            if (count($topArticles)) {
                $article = $topArticles[0];
                ?>
                <h4><a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                       title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h4>
                <div class="summary"><?php echo $article->summary; ?><a
                            href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                            title="<?php echo $article->title; ?>">详细 &gt;</a></div>
                <ul>
                    <?php
                    foreach ($topArticles as $article) {
                        ?>
                        <li>
                            <a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                               title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
            }
            ?>
        </div>

    </div>
    <div class="clear-left"></div>
</div>
<?php
foreach ($categories as $category) {
    if (count($category->articles) == 0) continue;
    ?>
    <div class="theme-box-container">
        <div class="theme-box">
            <div class="theme-box-title"><?php echo $category->name; ?><a
                        href="<?php echo url('Cms', 'Article', 'articles', ['categoryId' => $category->id]); ?>"
                        class="more" style="float:right;">更多...</a></div>
            <div class="theme-box-body">


                <?php
                $categoryThumbnailArticle = null;
                foreach ($category->articles as $article) {
                    if ($article->thumbnailL != '') {
                        $categoryThumbnailArticle = $article;
                        break;
                    }
                }
                ?>
                <div class="category-articles">
                    <div class="row">
                        <?php
                        if ($categoryThumbnailArticle == null) {
                            ?>
                            <div class="col-20">
                                <ul>
                                    <?php
                                    foreach ($category->articles as $article) {
                                        ?>
                                        <li>
                                            <span class="article-time"><?php echo date('m-d', $article->createTime); ?></span><a
                                                    href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                                                    title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                        } else {
                            ?>
                            <div class="col-5 text-center">
                                <a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $categoryThumbnailArticle->id]); ?>"
                                   title="<?php echo $categoryThumbnailArticle->title; ?>">
                                    <img src="<?php echo Be::getRuntime()->getDataUrl() . '/Article/Thumbnail/' . $categoryThumbnailArticle->thumbnailM; ?>"
                                         alt="<?php echo $categoryThumbnailArticle->title; ?>"/>
                                </a>
                            </div>
                            <div class="col-15">
                                <ul>
                                    <?php
                                    foreach ($category->articles as $article) {
                                        ?>
                                        <li>
                                            <span class="article-time"><?php echo date('m-d', $article->createTime); ?></span><a
                                                    href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                                                    title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
<!--{/center}-->


<!--{east}-->
<?php
$activeUsers = $this->activeUsers;
$monthHottestArticles = $this->monthHottestArticles;

$configArticle = Be::getConfig('Cms', 'Article');

if (count($activeUsers)) {
    $configUser = Be::getConfig('System', 'User');
    ?>
    <div class="theme-box-container">
        <div class="theme-box">
            <div class="theme-box-title">活跃会员</div>
            <div class="theme-box-body">

                <div class="active-users">
                    <ul>
                        <?php
                        foreach ($activeUsers as $activeUser) {
                            ?>
                            <li style="width:<?php echo $configUser->avatarMW; ?>px;">
                                <div class="active-user-avatar">
                                    <a href="<?php echo url('Cms', 'Article', 'user', ['userId' => $activeUser->id]); ?>"
                                       title="查看 <?php echo $activeUser->name; ?> 的动态">
                                        <img src="<?php echo Be::getRuntime()->getDataUrl() . '/user/avatar/' . (isset($activeUser->avatarM) ? $activeUser->avatarM : ('default/' . $configUser->defaultAvatarM)); ?>"
                                             alt="<?php echo $activeUser->name; ?>"/>
                                    </a>
                                </div>
                                <div class="active-user-name">
                                    <a href="<?php echo url('Cms', 'Article', 'user', ['userId' => $activeUser->id]); ?>"
                                       title="查看 <?php echo $activeUser->name; ?> 的动态">
                                        <?php echo $activeUser->name; ?>
                                    </a>
                                </div>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>

            </div>
        </div>
    </div>
    <?php
}


if (count($monthHottestArticles)) {
    ?>
    <div class="theme-box-container">
        <div class="theme-box">
            <div class="theme-box-title">本月热点</div>
            <div class="theme-box-body">

                <?php
                foreach ($monthHottestArticles as $article) {
                    ?>
                    <div class="month-hottest-article">

                        <div class="month-hottest-article-thumbnail"
                             style="width:<?php echo $configArticle->thumbnailSW; ?>px; height:<?php echo $configArticle->thumbnailSH; ?>px;">
                            <a href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                               title="<?php echo $article->title; ?>">
                                <img src="<?php echo Be::getRuntime()->getDataUrl() . '/Article/Thumbnail/'; ?><?php echo $article->thumbnailS == '' ? ('default/' . $configArticle->defaultThumbnailS) : $article->thumbnailS; ?>"
                                     alt="<?php echo $article->title; ?>">
                            </a>
                        </div>

                        <div style="margin-left:<?php echo $configArticle->thumbnailSW; ?>px;">
                            <h5 class="month-hottest-article-title"><a
                                        href="<?php echo url('Cms', 'Article', 'detail', ['articleId' => $article->id]); ?>"
                                        title="<?php echo $article->title; ?>"><?php echo $article->title; ?></a></h5>
                            <div class="month-hottest-article-time"><?php echo date('Y-m-d H:i:s', $article->createTime); ?></div>
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
