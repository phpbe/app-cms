
<be-center>
    <div class="be-container">
        <h1 class="be-h1 be-ta-center"><?php echo $this->article->title; ?></h1>
        <div class="be-mt-200 be-ta-center be-c-999"><span>作者：<?php echo $this->article->author; ?></span><span class="be-ml-200">发布时间：<?php echo $this->article->publish_time; ?></span></div>
        <div class="be-mt-200 be-lh-150">
            <?php echo $this->article->description; ?>
        </div>
    </div>
</be-center>
