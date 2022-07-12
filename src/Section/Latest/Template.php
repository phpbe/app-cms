<?php

namespace Be\App\Cms\Section\Latest;

use Be\Theme\Section;

class Template extends Section
{
    public array $positions = ['middle', 'center'];

    public function display()
    {
        if ($this->config->enable) {

            echo '<style type="text/css">';

            echo $this->getCssBackgroundColor('banner');
            echo $this->getCssPadding('banner');

            // 手机版，电脑版上传不同的图片
            echo '@media (max-width: 768px) {';
            echo '#' . $this->id . ' .banner-image {';
            echo 'display:none;';
            echo '}';
            echo '#' . $this->id . ' .banner-mobile-image {';
            echo 'display:block;';
            echo '}';
            echo '}';
            // 手机版，电脑版上传不同的图片
            echo '@media (min-width: 768px) {';
            echo '#' . $this->id . ' .banner-image {';
            echo 'display:block;';
            echo '}';
            echo '#' . $this->id . ' .banner-mobile-image {';
            echo 'display:none;';
            echo '}';
            echo '}';

            echo '#' . $this->id . ' .banner-image img,';
            echo '#' . $this->id . ' .banner-mobile-image img {';
            echo 'width: 100%;';
            echo '}';

            echo '#' . $this->id . ' .banner-image .banner-no-image,';
            echo '#' . $this->id . ' .banner-mobile-image .banner-no-image {';
            echo 'width: 100%;';
            echo 'height: 400px;';
            echo 'line-height: 400px;';
            echo 'color: #fff;';
            echo 'font-size: 24px;';
            echo 'text-align: center;';
            echo 'text-shadow:  5px 5px 5px #999;';
            echo 'background-color: #f5f5f5;';
            echo '}';

            echo '</style>';

            echo '<div class="banner">';
            if ($this->position === 'middle' && $this->config->width === 'default') {
                echo '<div class="be-container">';
            }

            echo '<div class="banner-image">';
            if (!$this->config->image) {
                echo '<div class="banner-no-image">1200X400px+</div>';
            } else {
                if ($this->config->link) {
                    echo '<a href="' . $this->config->link . '">';
                }
                echo '<img src="' . $this->config->image . '">';
                if ($this->config->link) {
                    echo '</a>';
                }
            }
            echo '</div>';
            echo '<div class="banner-mobile-image">';
            if (!$this->config->imageMobile) {
                echo '<div class="banner-no-image">720X400px+</div>';
            } else {
                if ($this->config->link) {
                    echo '<a href="' . $this->config->link . '">';
                }
                echo '<img src="' . $this->config->imageMobile . '">';
                if ($this->config->link) {
                    echo '</a>';
                }
            }
            echo '</div>';

            if ($this->position === 'middle' && $this->config->width === 'default') {
                echo '</div>';
            }
            echo '</div>';
        }
    }
}

