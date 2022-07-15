<be-head>
    <?php
    if (strpos($this->article->description, '<pre') !== false && strpos($this->article->description, '<code') !== false) {
        $wwwUrl = \Be\Be::getProperty('App.Cms')->getWwwUrl();
        ?>
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/default.min.css">
        <link rel="stylesheet" href="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/styles/atom-one-light.css">
        <script src="<?php echo $wwwUrl; ?>/lib/highlight.js/11.5.1/highlight.min.js"></script>
        <script>
            hljs.highlightAll();
        </script>
        <?php
    }
    ?>
</be-head>


<be-page-content>
    <div class="be-ta-center be-c-999">
        <?php
        if ($this->article->author !== '') {
            ?>
            <span>作者：<?php echo $this->article->author; ?></span>
            <?php
        }
        ?>
        <span class="be-ml-100">发布时间：<?php echo date('Y年n月j日 H:i', strtotime($this->article->publish_time)); ?></span>
        <span class="be-ml-100">浏览：<?php echo $this->article->hits; ?></span>
    </div>
    <div class="be-mt-200 be-lh-150">
        <?php echo $this->article->description; ?>
    </div>
</be-page-content>