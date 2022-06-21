<be-head>
    <?php
    if (strpos($this->article->description, '<pre><code>') !== false) {
        ?>
        <link rel="stylesheet" href="https://cdn.phpbe.com/lib/highlight.js/11.5.1/default.min.css">
        <link rel="stylesheet" href="https://cdn.phpbe.com/lib/highlight.js/11.5.1/styles/atom-one-light.css">
        <script src="https://cdn.phpbe.com/lib/highlight.js/11.5.1/highlight.min.js"></script>
        <script>
            hljs.highlightAll();
        </script>
        <?php
    }
    ?>
</be-head>

<be-center>
    <div class="be-center">
        <h1 class="be-h1 be-ta-center be-lh-300"><?php echo $this->article->title; ?></h1>
        <div class="be-mt-200 be-ta-center be-c-999">
            <span>作者：<?php echo $this->article->author; ?></span>
            <span class="be-ml-100">发布时间：<?php echo date('Y年n月j日 H:i', strtotime($this->article->publish_time)); ?></span>
            <span class="be-ml-100">浏览：<?php echo $this->article->hits; ?></span>
        </div>
        <div class="be-mt-200 be-lh-150">
            <?php echo $this->article->description; ?>
        </div>
    </div>
</be-center>