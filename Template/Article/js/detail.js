
$(function(){
	$("#form-comment").validate({
		rules: {
			body: {
				required: true,
				minlength: 10,
				maxlength: 1000
			}
		},
		messages: {
			body: {
				required: "请输入评论内容",
				minlength: "最少{0}个字符",
				maxlength: "最多{0}个字符"
			}
		},

		submitHandler: function(form){

			var $submit = $(".btn-submit", $(form));
			var sValue = $submit.val();
			
			$submit.prop("disabled", true).val("正在提交...");

			$.ajax({
				type: "POST",
				url: BONE_URL + "/?app=Cms&controller=Article&action=ajax_comment",
				data: $(form).serialize(),
				dataType: "json",
				success: function(json){
					
					$submit.prop("disabled", false).val(sValue);
					
					if(json.error=="0")
					{
						$("textarea", $(form)).val("");
						window.location.reload();
					}
					else
					{
						alert( json.message );
					}
				}
			});	

		}		

	});
});



function like(iArticleID)
{
	$.ajax({
		url: BONE_URL + "/?app=Cms&controller=Article&action=ajax_like&article_id="+iArticleID,
		dataType: "json",
		success: function(json){

			if(json.error=="0")
			{
				window.location.reload();
			}
			else
			{
				alert( json.message );
			}
		}
	});	
}

function dislike(iArticleID)
{
	$.ajax({
		url: BONE_URL + "/?app=Cms&controller=Article&action=ajax_dislike&article_id="+iArticleID,
		dataType: "json",
		success: function(json){

			if(json.error=="0")
			{
				window.location.reload();
			}
			else
			{
				alert( json.message );
			}
		}
	});	
}

function commentLike(iCommentID)
{
	$.ajax({
		url: BONE_URL + "/?app=Cms&controller=Article&action=ajax_comment_like&comment_id="+iCommentID,
		dataType: "json",
		success: function(json){

			if(json.error=="0")
			{
				window.location.reload();
			}
			else
			{
				alert( json.message );
			}
		}
	});	
}

function commentDislike(iCommentID)
{
	$.ajax({
		url: BONE_URL + "/?app=Cms&controller=Article&action=ajax_comment_dislike&comment_id="+iCommentID,
		dataType: "json",
		success: function(json){

			if(json.error=="0")
			{
				window.location.reload();
			}
			else
			{
				alert( json.message );
			}
		}
	});	
}