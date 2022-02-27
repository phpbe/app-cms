<?php

namespace Be\App\Cms;


class Property extends \Be\App\Property
{

    protected $label = '内容';
    protected $icon = 'el-icon-document-copy';
    protected $description = '内容管理系统';

    public function __construct() {
        parent::__construct(__FILE__);
    }

}
