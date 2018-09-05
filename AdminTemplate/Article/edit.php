<?php
use Phpbe\System\Be;
?>

<!--{head}-->
<?php
$uiEditor = Be::getUi('editor');
$uiEditor->head();
?>
<script type="text/javascript" language="javascript" src="Template/Article/js/edit.js"></script>
<!--{/head}-->

<!--{center}-->
<?php

$article = $this->get('article');
$categories = $this->get('categories');

$configArticle = Be::getConfig('Cms', 'Article');
$configWatermark = Be::getConfig('System', 'Watermark');

$categoryHtml = '<select name="categoryId">';
$categoryHtml .= '<option value="">不属于任何分类</option>';
foreach ($categories as $category) {
    $categoryHtml .= '<option value="' . $category->id . '"';
    if ($category->children > 0) $categoryHtml .= ' disabled="disabled"';
    if ($category->id == $article->categoryId)  $categoryHtml .= ' selected="selected"';
    $categoryHtml .= '>';
    if ($category->level) $categoryHtml .= str_repeat('&nbsp; ', $category->level);
    $categoryHtml .= $category->name . '</option>';
}
$categoryHtml .= '</select>';

$uiEditor = Be::getUi('editor');

$uiEditor->setAction('save', './?app=Cms&controller=Article&action=editSave');	// 显示提交按钮
$uiEditor->setAction('reset');	// 显示重设按钮
$uiEditor->setAction('back');	// 显示返回按钮

$uiEditor->setFields(
    array(
        'type'=>'text',
        'name'=>'title',
        'label'=>'标题',
        'value'=>$article->title,
        'width'=>'500px',
        'validate'=>array(
            'required'=>true
       )
   ),
    array(
        'type'=>'select',
        'label'=>'所属分类',
        'html'=>$categoryHtml
   ),
    array(
        'type'=>'file',
        'label'=>'缩略图',
        'html'=>'<img src="../'.DATA.'/Article/Thumbnail/'.($article->thumbnailS == ''?('default/'.$configArticle->defaultThumbnailS):$article->thumbnailS).'" /> <label class="radio inline"><input type="radio" name="thumbnailSource" id="thumbnailSourceUpload" value="upload" checked="checked" onchange="javascript:checkThunbmail();" />上传：</label><input type="file" name="thumbnailUpload" /><label class="radio inline" style="margin-left:20px;"><input type="radio" name="thumbnailSource" id="thumbnailSourceUrl" value="url" onchange="javascript:checkThunbmail();" />指定网址：</label><div class="input-append"><input type="text" id="srcThunbmail" name="thumbnailUrl" /><button class="btn btn-success" type="button" onclick="javascript:selectImage(\'srcThunbmail\');"><i class="icon-share icon-white"></i></button></div>'
   ),
    array(
        'label'=>'附加选项',
        'html'=>'<label class="checkbox inline"><input type="checkbox" name="thumbnailPickUp" id="thumbnailPickUp" value="1" onchange="javascript:checkThumbnailPickUp();" />提取第一个图片为缩略图</label><label class="checkbox inline" style="margin-left:20px;"><input type="checkbox" name="downloadRemoteImage" value="1"'.($configArticle->downloadRemoteImage == '1'?' checked="checked"':'').' />下载远程图片</label><label class="checkbox inline" style="margin-left:20px;"><input type="checkbox" name="downloadRemoteImageWatermark" value="1"'.($configWatermark->watermark == '0'?'':' checked="checked"').' />下截远程图片添加水印</label>'
   ),
    array(
        'type'=>'richtext',
        'name'=>'body',
        'label'=>'内容',
        'value'=>$article->body,
        'width'=>'600px',
        'height'=>'360px'
   )
);

$uiEditor->addFields(
    array(
        'label'=>'从内容中提取',
        'html'=>'<input type="button" value="摘要" class="btn btn-success" onclick="javascript:getSummary(this);" /> <input type="button" value="Meta 关键字" class="btn btn-warning" onclick="javascript:getMetaKeywords(this);" /> <input type="button" value="Meta 描述" class="btn btn-info" onclick="javascript:getMetaDescription(this);" />'
    )
);


$uiEditor->addFields(
    array(
        'type'=>'textarea',
        'name'=>'summary',
        'label'=>'摘要',
        'value'=>$article->summary,
        'width'=>'95%',
        'height'=>'60px'
   ),
    array(
        'type'=>'text',
        'name'=>'metaKeywords',
        'label'=>'<small>Meta 关键字</small>',
        'value'=>$article->metaKeywords,
        'width'=>'95%'
   ),
    array(
        'type'=>'textarea',
        'name'=>'metaDescription',
        'label'=>'<small>Meta 描述<</small>',
        'value'=>$article->metaDescription,
        'width'=>'95%',
        'height'=>'60px'
   ),
    array(
        'type'=>'text',
        'name'=>'hits',
        'label'=>'点击量',
        'value'=>$article->hits,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'createTime',
        'label'=>'发布时间',
        'value'=>$article->id == 0?date('Y-m-d H:i:s'):date('Y-m-d H:i:s', $article->createTime)
   ),
    array(
        'type'=>'text',
        'name'=>'ordering',
        'label'=>'排序',
        'value'=>$article->ordering,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'text',
        'name'=>'top',
        'label'=>'推荐',
        'value'=>$article->top,
        'width'=>'60px',
        'validate'=>array(
            'digits'=>true
       )
   ),
    array(
        'type'=>'radio',
        'name'=>'block',
        'label'=>'状态',
        'value'=>$article->block,
        'options'=>array('0'=>'公开','1'=>'屏蔽')
    )
);
$uiEditor->addHidden('id', $article->id);
$uiEditor->display();
?>
<!--{/center}-->