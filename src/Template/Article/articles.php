<be-page-content>
    <style type="text/css">
        .article-image {width: 100%;}
        @media (min-width: 768px) {
            .article-image {width: 200px;}
        }
    </style>
    <?php
    $isMobile = \Be\Be::getRequest()->isMobile();
    $i = 0;
    foreach ($this->result['rows'] as $article) {
        ?>
        <div class="be-row<?php echo $i === 0 ? '': ' be-mt-300';?>">
            <div class="be-col-24 be-md-col-auto">
                <div class="article-image">
                    <a class="be-d-inline-block" href="<?php echo beUrl('Cms.Article.detail', ['id'=> $article->id]); ?>" title="<?php echo $article->title; ?>"<?php echo $isMobile ? '' : ' target="_blank"';?>>
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

            <div class="be-col-24 be-md-col-auto">
                <div class="be-pl-100 be-pt-100"></div>
            </div>

            <div class="be-col-24 be-md-col">
                <a class="be-fs-150 be-fw-bold be-lh-200" href="<?php echo beUrl('Cms.Article.detail', ['id'=> $article->id]); ?>" title="<?php echo $article->title; ?>"<?php echo $isMobile ? '' : ' target="_blank"';?>>
                    <?php echo $article->title; ?>
                </a>
                <div class="be-mt-100 be-lh-150 be-c-666">
                    <?php echo $article->summary; ?>
                </div>
                <div class="be-mt-100 be-c-999">
                    <span><?php echo date(beLang('App.Cms', 'ARTICLE.PUBLISH_TIME_YYYY_MM_DD'), strtotime($article->publish_time)); ?></span>
                    <?php
                    if ($article->author !== '') {
                        echo '<span class="be-ml-100">' . beLang('App.Cms', 'ARTICLE.AUTHOR') . ': ' .   $article->author . '</span>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php

        $i++;
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
            $html .= '<a href="' . $url . '">' . beLang('App.Cms', 'PAGINATION.PREVIOUS'). '</a>';
        } else {
            $html .= '<span>' . beLang('App.Cms', 'PAGINATION.PREVIOUS'). '</span>';
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
            $html .= '<a href="' . $url . '">' . beLang('App.Cms', 'PAGINATION.NEXT'). '</a>';
        } else {
            $html .= '<span>' . beLang('App.Cms', 'PAGINATION.NEXT'). '</span>';
        }
        $html .= '</li>';
        $html .= '</ul>';
        $html .= '</nav>';

        echo $html;
    }
    ?>
</be-page-content>