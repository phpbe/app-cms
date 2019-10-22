$(function(){
	checkThumbnailPickUp();
	checkThunbmail();		   
});



function getSummary(e)
{
	var $submit = $(e);
	var sValue = $submit.val();
	
	$submit.prop("disabled", true).val("正在提取，请稍候...");

	$.ajax({
		type: "POST",
		url: "./?app=Cms&controller=Article&action=ajax_get_summary",
		data: $("#admin_ui_editor_form").serialize(),
		dataType: "json",
		success: function(json){
			$submit.prop("disabled", false).val(sValue);
			if(json.error=="0") $("#summary").val(json.summary);
		}
	});	
}


function getMetaKeywords(e)
{
	var $submit = $(e);
	var sValue = $submit.val();
	
	$submit.prop("disabled", true).val("正在提取，请稍候...");

	$.ajax({
		type: "POST",
		url: "./?app=Cms&controller=Article&action=ajax_get_meta_keywords",
		data: $("#admin_ui_editor_form").serialize(),
		dataType: "json",
		success: function(json){
			$submit.prop("disabled", false).val(sValue);
			if(json.error=="0") $("#meta_keywords").val(json.meta_keywords);
		}
	});	
}


function getMetaDescription(e)
{
	var $submit = $(e);
	var sValue = $submit.val();
	
	$submit.prop("disabled", true).val("正在提取，请稍候...");

	$.ajax({
		type: "POST",
		url: "./?app=Cms&controller=Article&action=ajax_get_meta_description",
		data: $("#admin_ui_editor_form").serialize(),
		dataType: "json",
		success: function(json){
			$submit.prop("disabled", false).val(sValue);
			if(json.error=="0") $("#meta_description").val(json.meta_description);
		}
	});	
}


function selectImage( src_id )
{
	tinymce.activeEditor.windowManager.open({
		title: "上传的文件",
		file: './?controller=system_filemanager&action=browser&filter_image=1&src_id='+src_id,
		width: 880,
		height: 600
	});
}

function checkThumbnailPickUp()
{
	var $e = $("#thumbnail_pick_up");
	var $prev = $e.parent().parent().parent().prev();
	if($e.prop("checked"))
	{
		$prev.slideUp();
		$(":text", $prev).val("");
		$("input, button", $prev).prop("disabled", true);
	}
	else
	{
		$prev.slideDown();	
		$("input, button", $prev).prop("disabled", false);
		checkThunbmail();
	}
}

function checkThunbmail()
{
	var $e1 = $("#thumbnail_source_upload");
	var $e2 = $("#thumbnail_source_url");
	
	if($e1.prop("checked"))
	{
		$e1.parent().next().prop("disabled", false);
		$("input, button", $e2.parent().next()).prop("disabled", true);
	}
	else if($e2.prop("checked"))
	{
		$e1.parent().next().val("").prop("disabled", true);
		$("input, button", $e2.parent().next()).prop("disabled", false);
	}
}