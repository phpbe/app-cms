<?php
use Phpbe\System\Be;
?>
<!--{head}-->
<script type="text/javascript" language="javascript" src="/app/Cms/Template/Article/js/user.js"></script>
<link type="text/css" rel="stylesheet" href="/app/Cms/Template/Article/css/user.css">

<style type="text/css">
.theme-center .user-menu-bar li{background-color:<?php echo $this->getColor(3); ?>;}
.theme-center .user-menu-bar li.active{ background-color:<?php echo $this->getColor(); ?>;}

.theme-center .profile .profileItem{ background-color:<?php echo $this->getColor(); ?>;}
.theme-center .profile .profileItemValueBorder{border:<?php echo $this->getColor(5); ?> 1px solid; background-color:<?php echo $this->getColor(9); ?>;margin-top:5px;}
</style>
<!--{/head}-->


<!--{center}-->
<?php
$user = $this->get('user');
$articles = $this->get('articles');
$articleCount = $this->get('articleCount');
$comments = $this->get('comments');
$commentCount = $this->get('commentCount');

$configUser = Be::getConfig('System.User');
$configArticle = Be::getConfig('Cms.Article');
?>
<div style="border:#eee 1px solid; border-left:<?php echo $this->primaryColor; ?> 5px solid;  background-color:#FFFFFF; padding:20px; box-shadow:1px 1px 3px #ccc;">
    <div class="row">
        <div class="col-3 text-center">
            <img src="<?php echo Be::getRuntime()->getDataUrl().'/System/User/avatar/'.($user->avatarL == ''?('default/'.$configUser->defaultAvatarL):$user->avatarL); ?>" alt="<?php echo $user->name; ?>" />

        </div>
        <div class="col-17">
            <h4><?php echo $user->name; ?></h4>
            <p style="font-size:12px; color:#999;">注册于 <?php echo date('Y-m-d H:i', $user->registerTime); ?></p>
        </div>
    </div>
</div>

<div class="user-menu-bar" style="border-bottom:<?php echo $this->getColor(); ?> 1px solid;">
    <ul>
        <li class="active" data-content="articles">TA的文章 <sup><?php echo $articleCount; ?></sup></li><li data-content="comments">TA的评论 <sup><?php echo $commentCount; ?></sup></li><li data-content="profile">TA的资料</li>
    </ul>
</div>

<div class="user-tab user-tab-articles">
    <?php
    if ($articleCount == 0) {
    ?>
    <p class="text-muted text-center"><?php echo $user->name; ?> 未曾发表过文章</p>
    <?php
    } else {
        foreach ($articles as $article) {
        ?>
            <div class="article">
                <div class="article-thumbnail" style="width:<?php echo $configArticle->thumbnailSW; ?>px; height:<?php echo $configArticle->thumbnailSH; ?>px;">
                    <a href="<?php echo url('Cms.Article.detail', ['articleId' => $article->id]); ?>" title="<?php echo $comment->article->title; ?>" target="Blank">
                    <img src="<?php echo Be::getRuntime()->getDataUrl().'/Article/Thumbnail/'; ?><?php echo $article->thumbnailS == ''?('default/'.$configArticle->defaultThumbnailS):$article->thumbnailS; ?>" alt="<?php echo $comment->article->title; ?>" />
                    </a>
                </div>

                <div style="margin-left:<?php echo $configArticle->thumbnailSW; ?>px;">
                    <h4 class="article-title"><a href="<?php echo url('Cms.Article.detail', ['articleId' => $article->id]); ?>" title="<?php echo $comment->article->title; ?>" target="Blank"><?php echo $article->title; ?></a></h4>
                    <div class="article-time"><?php echo date('Y-m-d H:i:s', $article->createTime); ?></div>
                    <div class="article-summary"><?php echo $article->summary; ?></div>
                </div>
            </div>

            <div class="clearLeft"></div>
        <?php
        }
    }
    ?>
</div>

<div class="user-tab user-tab-comments" style="display:none;">
    <?php
    if ($commentCount == 0) {
    ?>
    <p class="text-muted text-center"><?php echo $user->name; ?> 未曾发表过评论</p>
    <?php
    } else {
        foreach ($comments as $comment) {
        ?>
        <div class="comment">
            <h4 class="article-title">评论文章：<a href="<?php echo url('Cms.Article.detail', ['articleId' => $comment->article->id]); ?>" title="<?php echo $comment->article->title; ?>" target="Blank"><?php echo $comment->article->title; ?></a></h4>
            <div class="comment-time"><?php echo date('Y-m-d H:i:s', $comment->createTime); ?></div>
            <div class="comment-body"><?php echo $comment->body; ?></div>
        </div>
        <?php
        }
    }
    ?>
</div>

<div class="user-tab user-tab-profile" style="display:none;">
    <div class="profile">

        <table>
            <tbody>
                <tr>
                    <td>
                        <div class="profileItem">用户名: </div>
                    </td>
                    <td>
                        <div class="profileItemValueBorder">
                            <div class="profileItemValue"><?php echo $user->username; ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profileItem">名称: </div>
                    </td>
                    <td>
                        <div class="profileItemValueBorder">
                            <div class="profileItemValue"><?php echo $user->name == ''?'-':$user->name; ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profileItem">性别: </div>
                    </td>
                    <td>
                        <div class="profileItemValueBorder">
                            <div class="profileItemValue">
                                <?php
                                if ($user->gender == 0) {
                                    echo '女';
                                }
                                elseif ($user->gender == 1) {
                                    echo '男';
                                } else {
                                    echo '未知';
                                }
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profileItem">QQ: </div>
                    </td>
                    <td>
                        <div class="profileItemValueBorder">
                            <div class="profileItemValue"><?php echo $user->qq == ''?'-':$user->qq; ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profileItem">注册于: </div>
                    </td>
                    <td>
                        <div class="profileItemValueBorder">
                            <div class="profileItemValue"><?php echo date('Y-m-d H:i', $user->registerTime); ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="profileItem">上次登陆时间: </div>
                    </td>
                    <td>
                        <div class="profileItemValueBorder">
                            <div class="profileItemValue"><?php echo date('Y-m-d H:i', $user->lastVisitTime); ?></div>
                        </div>
                    </td>
                </tr>

            </tbody>
        </table>

    </div>
</div>
<!--{/center}-->