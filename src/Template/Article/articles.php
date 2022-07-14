<be-head>
    <style type="text/css">
        .article-image {
            width: 200px;
            text-align: center;
        }

        .article-image img {
            max-width: 100%;
        }
    </style>
</be-head>

<be-page-content>
    <?php
    foreach ($this->result['rows'] as $article) {
        ?>
        <div class="be-mb-300 be-row">

            <div class="be-col-auto">
                <div class="article-image">
                    <a class="be-d-inline-block" href="<?php echo beUrl('Cms.Article.detail', ['id'=> $article->id]); ?>" title="<?php echo $article->title; ?>">
                    <img src="<?php
                    if ($article->image === '') {
                        echo \Be\Be::getProperty('App.Cms')->getWwwUrl() . '/article/images/no-image-m.jpg';
                    } else {
                        echo $article->image;
                    }
                    ?>" alt="<?php echo $article->title; ?>">
                    </a>
                </div>
            </div>

            <div class="be-col">
                <div class="be-pl-100">
                    <a class="be-fs-175 be-fw-bold be-lh-300" href="<?php echo beUrl('Cms.Article.detail', ['id'=> $article->id]); ?>" title="<?php echo $article->title; ?>">
                        <?php echo $article->title; ?>
                    </a>
                    <div class="be-mt-150 be-lh-150 be-c-666">
                        <?php echo $article->summary; ?>
                    </div>
                    <div class="be-mt-150 be-c-999">
                        <span><?php echo date('Y年n月j日', strtotime($article->publish_time)); ?></span>
                        <?php
                        if ($article->author) {
                            echo '<span class="be-ml-100">作者：' . $article->author . '</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>

        </div>
        <?php
    }


    $total = $this->result['total'];
    $pageSize = $this->result['pageSize'];
    $pages = ceil($total / $pageSize);
    if ($pages > 1) {
        $page = $this->result['page'];
        if ($page > $pages) $page = $pages;

        $paginationUrl = $this->paginationUrl;
        $paginationUrl .= strpos($paginationUrl, '?') === false ? '?' : '&';

        $html = '<nav class="be-mt-300">';
        $html .= '<ul class="be-pagination" style="justify-content: center;">';
        $html .= '<li>';
        if ($page > 1) {
            $url = $paginationUrl;
            $url .= http_build_query(['page' => ($page - 1)]);
            $html .= '<a href="' . $url . '">上一页</a>';
        } else {
            $html .= '<span>上一页</span>';
        }
        $html .= '</li>';

        $from = null;
        $to = null;
        if ($pages < 9) {
            $from = 1;
            $to = $pages;
        } else {
            $from = $page - 4;
            if ($from < 1) {
                $from = 1;
            }

            $to = $from + 8;
            if ($to > $pages) {
                $to = $pages;
            }
        }

        if ($from > 1) {
            $html .= '<li><span>...</span></li>';
        }

        for ($i = $from; $i <= $to; $i++) {
            if ($i == $page) {
                $html .= '<li class="active">';
                $html .= '<span>' . $i . '</span>';
                $html .= '</li>';
            } else {
                $url = $paginationUrl;
                $url .= http_build_query(['page' => $i]);
                $html .= '<li>';
                $html .= '<a href="' . $url . '">' . $i . '</a>';
                $html .= '</li>';
            }
        }

        if ($to < $pages) {
            $html .= '<li><span>...</span></li>';
        }

        $html .= '<li>';
        if ($page < $pages) {
            $url = $paginationUrl;
            $url .= http_build_query(['page' => ($page + 1)]);
            $html .= '<a href="' . $url . '">下一页</a>';
        } else {
            $html .= '<span>下一页</span>';
        }
        $html .= '</li>';
        $html .= '</ul>';
        $html .= '</nav>';

        echo $html;
    }
    ?>
</be-page-content>