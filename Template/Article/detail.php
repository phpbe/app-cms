<?php
use Be\System\Be;
$config = Be::getConfig('System.System');
$configArticle = Be::getConfig('Cms.Article');
?>
<!--{head}-->
<link type="text/css" rel="stylesheet" href="<?php echo url(); ?>/app/Cms/Template/Article/css/listing.css">
	
<script type="text/javascript" language="javascript" src="<?php echo url(); ?>/app/Cms/Template/Article/js/detail.js"></script>
<link type="text/css" rel="stylesheet" href="<?php echo url(); ?>/app/Cms/Template/Article/css/detail.css">
<!--{/head}-->



<!--{center}-->
<?php
$article = $this->article;
$similarArticles = $this->similarArticles;
$comments = $this->comments;

$configArticle = Be::getConfig('Cms.Article');
$configUser = Be::getConfig('System.User');

$my = Be::getUser();
?>
<h3 class="title"><?php echo $article->title; ?></h3>
<div class="sub-title"><span>作者：<?php echo Be::getUser($article->create_by_id)->name; ?></span><span>发布时间：<?php echo date('Y-m-d H:i:s', $article->create_time); ?></span><span>访问量：<?php echo $article->hits; ?></span></div>
<div class="body">
    <?php echo $article->body; ?>
</div>

<div class="article-vote">
    <div class="row">
        <div class="col-3">
            <a class="article-like" href="javascript:void(0);" title="喜欢" onclick="like(<?php echo $article->id; ?>);"><?php echo $article->like; ?></a>
        </div>
        <div class="col-3">
            <a class="article-dislike" href="javascript:void(0);" title="不喜欢" onclick="dislike(<?php echo $article->id; ?>);"><?php echo $article->dislike; ?></a>
        </div>

        <div class="col-14">

            <!-- Baidu Button BEGIN -->
            <div id="bdshare" class="bdshareT bdsTools get-codes-bdshare" style="float:right;">
            <span class="bdsMore">分享到：</span>
            <a class="bdsQzone"></a>
            <a class="bdsTsina"></a>
            <a class="bdsTqq"></a>
            <a class="bdsRenren"></a>
            <a class="bdsT163"></a>
            </div>
            <script type="text/javascript" id="bdshare_js" data="type=tools&amp;mini=1&amp;uid=0" ></script>
            <script type="text/javascript" id="bdshell_js"></script>
            <script type="text/javascript">
            document.getElementById("bdshellJs").src = "http://bdimg.share.baidu.com/static/js/shellV2.js?cdnversion=" + Math.ceil(new Date()/3600000)
            </script>
            <!-- Baidu Button END -->
        </div>
    </div>
</div>

<?php
if (count($similarArticles)>0) {
?>
<div class="similarArticles">
    <div class="similarArticles-title"><div>您可能感兴趣的文章</div></div>
    <ul>
    <?php
    foreach ($similarArticles as $similarArticle) {
    ?>
    <li class="similarArticle">
        <a href="<?php echo url('Cms.Article.detail', ['articleId' => $similarArticle->id]); ?>">
            <?php echo $similarArticle->title; ?>
        </a>
    </li>
    <?php
    }
    ?>
    </ul>
</div>
<?php
}
?>

<!--{/center}-->
