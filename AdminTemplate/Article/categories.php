<?php
use Be\System\Be;
?>
<!--{center}-->
<?php
$categories = $this->get('categories');

$uiCategoryTree = Be::getUi('categoryTree');
$uiCategoryTree->setData($categories);
$uiCategoryTree->setAction('save', './?app=Cms&controller=Article&action=saveCategories');
$uiCategoryTree->setAction('delete', './?app=Cms&controller=Article&action=ajaxDeleteCategory');
$uiCategoryTree->setFields(
    array(
        'name'=>'id',
        'label'=>'ID',
        'align'=>'center',
        'width'=>'60',
        'template'=>'<span style="color:#999">{id}</span>'
    )
);
$uiCategoryTree->display();
?>
<!--{/center}-->