<?php
use Be\System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->setLeftWidth(280);
$uiEditor->head();
?>
<!--{/head}-->

<!--{center}-->
<?php
$configArticle = $this->get('configArticle');

$uiEditor = Be::getUi('editor');
$uiEditor->setAction('save', './?app=Cms&controller=Article&action=settingSave');

$htmlDefaultThumbnailL = '<img src="../'.DATA.'/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailL.'" />';
$htmlDefaultThumbnailL .= '<br /><input type="file" name="defaultThumbnailL" />';

$htmlDefaultThumbnailM = '<img src="../'.DATA.'/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailM.'" />';
$htmlDefaultThumbnailM .= '<br /><input type="file" name="defaultThumbnailM" />';

$htmlDefaultThumbnailS = '<img src="../'.DATA.'/Article/Thumbnail/Default/'.$configArticle->defaultThumbnailS.'" />';
$htmlDefaultThumbnailS .= '<br /><input type="file" name="defaultThumbnailS" />';

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'getSummary',
        'label'=>'提取摘要字数',
        'value'=>$configArticle->getSummary,
        'width'=>'120px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'getMetaKeywords',
        'label'=>'提取META关键词个数',
        'value'=>$configArticle->getMetaKeywords,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'getMetaDescription',
        'label'=>'提取 META 描述字数',
        'value'=>$configArticle->getMetaDescription,
        'width'=>'120px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'comment',
        'label'=>'允许评论',
        'value'=>$configArticle->comment,
        'options'=>array('1'=>'允许', '0'=>'禁止')
   ),
    array(
        'type'=>'radio',
        'name'=>'commentPublic',
        'label'=>'评论默认公开',
        'value'=>$configArticle->commentPublic,
        'options'=>array('1'=>'公开', '0'=>'不公开，需要审核')
   ),
    array(
        'type'=>'radio',
        'name'=>'downloadRemoteImage',
        'label'=>'下载远程图片',
        'value'=>$configArticle->downloadRemoteImage,
        'options'=>array('1'=>'默认选中', '0'=>'默认不选中')
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnailLW',
        'label'=>'缩图图大图宽度'.' <small>(px)</small>',
        'value'=>$configArticle->thumbnailLW,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnailLH',
        'label'=>'缩图图大图高度'.' <small>(px)</small>',
        'value'=>$configArticle->thumbnailLH,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnailMW',
        'label'=>'缩图图中图宽度'.' <small>(px)</small>',
        'value'=>$configArticle->thumbnailMW,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnailMH',
        'label'=>'缩图图中图高度'.' <small>(px)</small>',
        'value'=>$configArticle->thumbnailMH,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnailSW',
        'label'=>'缩图图小图宽度'.' <small>(px)</small>',
        'value'=>$configArticle->thumbnailSW,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'thumbnailSH',
        'label'=>'缩图图小图高度'.' <small>(px)</small>',
        'value'=>$configArticle->thumbnailSH,
        'width'=>'80px',
        'validate'=>array(
            'required'=>true,
            'digits'=>true
       )
   ),
    array(
        'type'=>'file',
        'label'=>'默认缩略图大图',
        'html'=>$htmlDefaultThumbnailL
   ),
    array(
        'type'=>'file',
        'label'=>'默认缩略图中图',
        'html'=>$htmlDefaultThumbnailM
   ),
    array(
        'type'=>'file',
        'label'=>'默认缩略图小图',
        'html'=>$htmlDefaultThumbnailS
   )
);
$uiEditor->display();
?>
<!--{/center}-->