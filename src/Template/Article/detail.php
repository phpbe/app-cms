<be-head>
    <?php
    if (strpos($this->article->description, '<pre') !== false && strpos($this->article->description, '<code') !== false) {
        $wwwUrl = \Be\Be::getProperty('App.Cms')->getWwwUrl();
        ?>
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/default.min.css">
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/styles/atom-one-light.css">

        <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/highlight.min.js"></script>

        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.css">
        <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/highlightjs-line-numbers.min.js"></script>

        <script src="<?php echo $wwwUrl; ?>/lib/clipboard/clipboard.min.js"></script>

        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/css/article/detail.code.css">
        <script src="<?php echo $wwwUrl; ?>/js/article/detail.code.js"></script>
        <?php
    }
    ?>
</be-head>


<be-page-content>
    <div class="be-ta-center be-c-999">
        <?php
        if ($this->article->author !== '') {
            ?>
            <span><?php echo beLang('App.Cms', 'ARTICLE.AUTHOR') . ': ' .  $this->article->author; ?></span>
            <?php
        }
        ?>
        <span class="be-ml-100"><?php echo beLang('App.Cms', 'ARTICLE.PUBLISH_TIME') . ': '. date(beLang('App.Cms', 'ARTICLE.PUBLISH_TIME_YYYY_MM_DD_HH_II'), strtotime($this->article->publish_time)); ?></span>
        <span class="be-ml-100"><?php echo beLang('App.Cms', 'ARTICLE.HITS') . ': '. $this->article->hits; ?></span>
    </div>
    <div class="be-mt-200 be-lh-200 be-fs-110">
        <?php echo $this->article->description; ?>
    </div>

    <div class="be-mt-200 be-bt-eee be-pt-100">
        <?php
        foreach ($this->article->tags as $tag) {
            ?>
            <a class="be-mt-50 be-mr-50 be-btn be-btn-main be-btn-outline be-btn-sm" href="<?php echo beUrl('Cms.Article.search', ['tag'=> $tag]); ?>" title="<?php echo $tag; ?>">
                #<?php echo $tag; ?>
            </a>
            <?php
        }
        ?>
    </div>
</be-page-content>