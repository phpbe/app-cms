<?php

namespace Be\App\Cms\Section\PageTitle;

use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'center'];


    private function css()
    {
        echo '<style type="text/css">';
        echo $this->getCssBackgroundColor('app-cms-page-title');
        echo $this->getCssPadding('app-cms-page-title');
        echo $this->getCssMargin('app-cms-page-title');

        echo '#' . $this->id . ' .app-cms-page-title {';
        //echo 'box-shadow: 0 0 10px var(--font-color-9);';
        echo 'box-shadow: 0 0 10px #eaf0f6;';
        echo 'transition: all 0.3s ease;';
        echo '}';

        echo '#' . $this->id . ' .app-cms-page-title:hover {';
        //echo 'box-shadow: 0 0 15px var(--font-color-8);';
        echo 'box-shadow: 0 0 15px #dae0e6;';
        echo '}';

        echo '</style>';
    }


    /**
     * 输出内容
     *
     * @return void
     */
    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $this->css();

        $this->before();
        $this->page->pageTitle();
        $this->after();
    }

    public function before()
    {
        if ($this->config->enable) {
            echo '<div class="app-cms-page-title">';

            if ($this->position === 'middle' && $this->config->width === 'default') {
                echo '<div class="be-container">';
            }

            echo '<h1 class="be-' . $this->config->size . ' be-ta-' . $this->config->align . '">';
        }
    }

    public function after()
    {
        if ($this->config->enable) {
            echo '</h1>';

            if ($this->position === 'middle' && $this->config->width === 'default') {
                echo '</div>';
            }

            echo '</div>';
        }
    }

}

