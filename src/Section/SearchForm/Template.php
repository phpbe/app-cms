<?php

namespace Be\App\Cms\Section\SearchForm;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'west', 'center', 'east'];


    private function css()
    {
        echo '<style type="text/css">';
        echo $this->getCssBackgroundColor('app-cms-search-form');
        echo $this->getCssPadding('app-cms-search-form');
        echo $this->getCssMargin('app-cms-search-form');

        echo '#' . $this->id . ' .app-cms-search-form {';
        //echo 'box-shadow: 0 0 10px var(--font-color-9);';
        echo 'box-shadow: 0 0 10px #eaf0f6;';
        echo 'transition: all 0.3s ease;';
        echo '}';

        echo '#' . $this->id . ' .app-cms-search-form:hover {';
        //echo 'box-shadow: 0 0 15px var(--font-color-8);';
        echo 'box-shadow: 0 0 15px #dae0e6;';
        echo '}';

        echo '</style>';
    }


    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $this->css();
        ?>
        <div class="app-cms-search-form">
            <form action="<?php echo beUrl('Cms.Article.search'); ?>" method="get">
                <div class="be-row">
                    <div class="be-col"><input type="text" name="keyword" class="be-input" placeholder="<?php echo beLang('App.Cms', 'ARTICLE.ENTRY_SEARCH_KEYWORDS'); ?>"></div>
                    <div class="be-col-auto"><input type="submit" class="be-btn be-btn-major be-lh-175" value="<?php echo beLang('App.Cms', 'ARTICLE.SEARCH'); ?>"></div>
                </div>
            </form>

            <?php
            if ($this->config->keywords > 0) {
                $topKeywords = Be::getService('App.Cms.Article')->getTopSearchKeywords($this->config->keywords);
                if (count($topKeywords) > 0) {
                    echo '<div class="be-mt-100 be-lh-175">' . beLang('App.Cms', 'ARTICLE.TOP_SEARCH') . ': ';
                    foreach ($topKeywords as $topKeyword) {
                        echo '<a href="'. beUrl('Cms.Article.search', ['keyword' => $topKeyword]) .'">' . $topKeyword . '</a> &nbsp;';
                    }
                    echo '</div>';
                }
            }
            ?>
        </div>
        <?php
    }

}

