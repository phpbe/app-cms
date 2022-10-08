<?php

namespace Be\App\Cms;


class Property extends \Be\App\Property
{

    protected string $label = 'CMS';
    protected string $icon = 'el-icon-document-copy';
    protected string $description = '内容管理系统';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
