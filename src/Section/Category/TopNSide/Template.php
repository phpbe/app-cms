<?php

namespace Be\App\Cms\Section\Category\TopNSide;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['west', 'east'];


    private function css()
    {
        echo '<style type="text/css">';
        echo $this->getCssBackgroundColor('app-cms-category-top-n-side');
        echo $this->getCssPadding('app-cms-category-top-n-side');
        echo $this->getCssMargin('app-cms-category-top-n-side');

        echo '#' . $this->id . ' .app-cms-category-top-n-side ul {';
        echo 'margin: 0;';
        echo 'padding: 0;';
        echo '}';

        echo '#' . $this->id . ' .app-cms-category-top-n-side li {';
        echo 'list-style: none;';
        echo '}';

        echo '#' . $this->id . ' .app-cms-category-top-n-side a {';
        echo 'display: block;';
        echo 'padding: 1rem 0;';
        echo '}';

        echo '</style>';
    }


    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $categories = Be::getService('App.Cms.Category')->getCategories($this->config->quantity);
        if (count($categories) === 0) {
            return;
        }

        $this->css();

        echo '<div class="app-cms-category-top-n-side">';

        if (isset($this->config->title) && $this->config->title !== '') {
            echo $this->page->tag0('be-section-title');
            echo $this->config->title;
            echo $this->page->tag1('be-section-title');
        }

        echo $this->page->tag0('be-section-content');
        echo '<ul>';
        foreach ($categories as $category) {
            echo '<li>';
            echo '<a href="'. beUrl('Cms.Category.articles', ['id' => $category->id]) .'">' . $category->name . '</a>';
            echo '</li>';
        }
        echo '</ul>';
        echo $this->page->tag1('be-section-content');

        echo '</div>';
    }
}
