<?php

namespace Be\App\Cms\Section\SideTopTags;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'west', 'center', 'east'];


    private function css()
    {
        echo '<style type="text/css">';
        echo $this->getCssBackgroundColor('app-cms-side-top-tags');
        echo $this->getCssPadding('app-cms-side-top-tags');
        echo $this->getCssMargin('app-cms-side-top-tags');

        echo '#' . $this->id . ' .app-cms-side-top-tags {';
        //echo 'box-shadow: 0 0 10px var(--font-color-9);';
        echo 'box-shadow: 0 0 10px #eaf0f6;';
        echo 'transition: all 0.3s ease;';
        echo 'line-height: 1.75rem;';
        echo '}';

        echo '#' . $this->id . ' .app-cms-side-top-tags:hover {';
        //echo 'box-shadow: 0 0 15px var(--font-color-8);';
        echo 'box-shadow: 0 0 15px #dae0e6;';
        echo '}';


        echo '#' . $this->id . ' .app-cms-side-top-tags .tag {';
        echo 'color: #fff;';
        echo 'background-color: var(--major-color);';
        echo 'padding: .1rem .5rem;';
        echo 'margin-right: .25rem;';
        echo 'border-radius: .3rem;';
        echo '}';

        echo '#' . $this->id . ' .app-cms-side-top-tags .tag:hover {';
        echo 'color: #fff;';
        echo 'background-color: var(--major-color2);';
        echo '}';
        echo '</style>';
    }


    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $topTags = Be::getService('App.Cms.Article')->getTopTags($this->config->quantity);
        if (count($topTags) === 0) {
            return;
        }

        $this->css();

        ?>
        <div class="app-cms-side-top-tags">
            <?php
            if (isset($this->config->title) && $this->config->title !== '') {
                echo $this->page->tag0('be-section-title', true);
                echo $this->config->title;
                echo $this->page->tag1('be-section-title', true);
            }

            echo $this->page->tag0('be-section-content', true);
            ?>

            <?php
            echo '<div class="be-mt-100 be-lh-175">';
            foreach ($topTags as $topTag) {
                echo '<a class="tag" href="'. beUrl('Cms.Article.tag', ['tag' => $topTag]) .'">' . $topTag . '</a>';
            }
            echo '</div>';


            echo $this->page->tag1('be-section-content', true);
            ?>
        </div>
        <?php
    }

}

