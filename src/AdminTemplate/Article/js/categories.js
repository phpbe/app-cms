/**
 * @author: liu12 ( http://www.liu12.com )
 */


// 为新添加的节点分配临时ID
var g_iNewCategoryID = 0;

$(function(){	
	updateOrderIcon();	
});

// 添加分类
function addSubCategory(iCategoryID)
{
	if(g_iNewCategoryID==0)
	{
		for(var x in g_oCategoryChain)
		{
			x = Number(x);
			if(g_iNewCategoryID<x) g_iNewCategoryID = x+1;
		}
	}
	
	g_iNewCategoryID++;
	
	if(iCategoryID==0)	// iCategoryID为0 时, 没有指定父节点， 因些直接添加顶级分类， 并在将该节点添加到链表的尾部
	{
		var str = '<tr id="row-'+g_iNewCategoryID+'" onMouseOver="this.className=\'hover\'" onMouseOut="this.className=\'\'">'
		str += '<td></td>'
		str += '<td></td>'
		str += '<td>';
		str += '<input type="hidden" name="category_id[]" id="id-'+g_iNewCategoryID+'" value="0" />';
		str += '<input type="hidden" name="parent_id[]" id="parent_id-'+g_iNewCategoryID+'" value="0" />';
		str += '<input type="text" name="name[]" id="name-'+g_iNewCategoryID+'"  value="" size="50" maxlength="120" />';
		str += '</td>';
		str += '<td align="center">';
		str += '<a href="javascript:;" onclick="javascript:orderUp('+g_iNewCategoryID+')" class="order-up-on" id="order-up-'+g_iNewCategoryID+'"></a>';
		str += '</td>';
		str += '<td align="center">';
		str += '<a href="javascript:;" onclick="javascript:orderDown('+g_iNewCategoryID+')" class="order-down-on" id="order-down-'+g_iNewCategoryID+'"></a>';
		str += '</td>';		
		str += '<td align="center"><a href="javascript:;" onclick="javascript:deleteCategory('+g_iNewCategoryID+')" class="delete"></a></td>';
		str += '</tr>';
		
		if(g_iCategoryChainHead)
		{
			// 如果已有表头存在，则从表头开始遍历. 直到找到链表的结尾
			var oCategory = g_oCategoryChain[g_iCategoryChainHead];
			var oThisCategory = oCategory;
			while(oThisCategory.next_id!=0)
			{
				oThisCategory =  g_oCategoryChain[oThisCategory.next_id];	// 链表后移
			}
			
			
			oThisCategory.next_id = g_iNewCategoryID;
			g_oCategoryChain[g_iNewCategoryID] = {"pre_id":oThisCategory.id,"next_id":0,"id":g_iNewCategoryID,"name":"","parent_id":0,"level":0};
			$("#row-"+oThisCategory.id).after(str);
		}
		else
		{
			g_iCategoryChainHead = g_iNewCategoryID;
			g_oCategoryChain[g_iNewCategoryID] = {"pre_id":0,"next_id":0,"id":g_iNewCategoryID,"name":"","parent_id":0,"level":0};
			$("#row-list").append(str);
		}
		
		updateOrderIcon();
		return;
	}

	// 用户指定了在 iCategoryID 添加子节点， 遍历链表中iCategoryID之后的元素， 直到找出不是iCategoryID节点子孙的节点。 在该节点之前添加即可
	var oCategory = g_oCategoryChain[iCategoryID];
	var oThisCategory = oCategory;
	
	if(oThisCategory.next_id>0)
	{
		while(oThisCategory.next_id!=0)
		{
			oThisCategory =  g_oCategoryChain[oThisCategory.next_id];	// 链表后移
			
			if(oThisCategory.level<=oCategory.level)
			{
				oThisCategory = g_oCategoryChain[oThisCategory.pre_id];	// 找到不是 iCategoryID 子孙的节点， 因为该节点的 level 等于或小于 iCategoryID 对应的节点
				break;
			}
		}
	}

	if(oThisCategory.next_id==0)
	{
		// 如果找到的节点是链表尾， 则新添加的节点为新的表尾
		oThisCategory.next_id = g_iNewCategoryID;
		g_oCategoryChain[g_iNewCategoryID] = {"pre_id":oThisCategory.id,"next_id":0,"id":g_iNewCategoryID,"name":"","parent_id":iCategoryID,"level":oCategory.level+1};
	}
	else
	{
		//在链表中插入新节点，
		var oNextCategory =  g_oCategoryChain[oThisCategory.next_id];
		oThisCategory.next_id = g_iNewCategoryID;
		oNextCategory.pre_id = g_iNewCategoryID;
		g_oCategoryChain[g_iNewCategoryID] = {"pre_id":oThisCategory.id,"next_id":oNextCategory.id,"id":g_iNewCategoryID,"name":"","parent_id":iCategoryID,"level":oCategory.level+1};
	}

	
	// 输出显示
	var str = '<tr id="row-'+g_iNewCategoryID+'" onMouseOver="this.className=\'hover\'" onMouseOut="this.className=\'\'">'
	str += '<td></td>'
	str += '<td></td>'
	str += '<td>';
	for(var i=0; i<=oCategory.level; i++)
	{
		str += '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ';
	}
	str += '|_ ';
	str += '<input type="hidden" name="category_id[]" id="id-'+g_iNewCategoryID+'" value="0" />';
	str += '<input type="hidden" name="parent_id[]" id="parent_id-'+g_iNewCategoryID+'" value="'+iCategoryID+'" />';
	str += '<input type="text" name="name[]" id="name-'+g_iNewCategoryID+'"  value="" size="50" maxlength="120" />';
	str += '</td>';
	str += '<td align="center">';
	str += '<a href="javascript:;" onclick="javascript:orderUp('+g_iNewCategoryID+')" class="order-up-on" id="order-up-'+g_iNewCategoryID+'"></a>';
	str += '</td>';
	str += '<td align="center">';
	str += '<a href="javascript:;" onclick="javascript:orderDown('+g_iNewCategoryID+')" class="order-down-on" id="order-down-'+g_iNewCategoryID+'"></a>';
	str += '</td>';
	str += '<td align="center"><a href="javascript:;" onclick="javascript:deleteCategory('+g_iNewCategoryID+')" class="delete"></a></td>';
	str += '</tr>';

	$("#row-"+oThisCategory.id).after(str);
	

	var $e = $("#toggle-"+iCategoryID);
	if(oCategory.children)
	{
		if($e.attr("class") == "toggle-off")	// 添加新节点时， 如果父节点是合上的， 则自动打开
			togleSubCategory(iCategoryID)
	}
	else
	{
		$e.fadeIn();
	}
	
	// 更新孩子节点数
	oCategory.children++;
	
	updateOrderIcon();
}

// 打开/合上子节点
function togleSubCategory(iCategoryID)
{
	var $e = $("#toggle-"+iCategoryID);
	if($e.attr("class") == "toggle-on")
		$e.attr("class","toggle-off");
	else
		$e.attr("class","toggle-on");

	var oCategory = g_oCategoryChain[iCategoryID];
	var oThisCategory = oCategory;
	while(oThisCategory.next_id>0)
	{
		oThisCategory =  g_oCategoryChain[oThisCategory.next_id];	// 链表后移
		if(oThisCategory.level>oCategory.level)
		{
			if($e.attr("class") == "toggle-on")
			{
				$("#row-"+oThisCategory.id).show();
				$("#toggle-"+oThisCategory.id).attr("class","toggle-on");
			}
			else
			{
				$("#row-"+oThisCategory.id).hide();
				$("#toggle-"+oThisCategory.id).attr("class","toggle-off");
			}
		}
		else
			break;	
	}
}



function updateOrderIcon()
{
	if(g_iCategoryChainHead==0) return;

	var oCategory = g_oCategoryChain[g_iCategoryChainHead];
	var oThisCategory = oCategory;
	while(oThisCategory.next_id!=0)
	{
		if(oThisCategory.pre_id!=0)
		{
			if( g_oCategoryChain[oThisCategory.pre_id].level<oThisCategory.level)
				$("#order-up-"+oThisCategory.id).attr("class", "order-up-off");
			else
				$("#order-up-"+oThisCategory.id).attr("class", "order-up-on");
		}
		else
			$("#order-up-"+oThisCategory.id).attr("class", "order-up-off");

		if( g_oCategoryChain[oThisCategory.next_id].level<oThisCategory.level)
			$("#order-down-"+oThisCategory.id).attr("class", "order-down-off");
		else
			$("#order-down-"+oThisCategory.id).attr("class", "order-down-on");

		if(oThisCategory.children!=0)
		{
			var oTmp = g_oCategoryChain[oThisCategory.next_id];
			while(oTmp.next_id!=0 && oTmp.level>oThisCategory.level) oTmp = g_oCategoryChain[oTmp.next_id];
			
			if(oTmp.level!=oThisCategory.level)
			{
				$("#order-down-"+oThisCategory.id).attr("class", "order-down-off");
			}
		}

		oThisCategory = g_oCategoryChain[oThisCategory.next_id];
	}
	
	if(oThisCategory.pre_id==0)
		$("#order-up-"+oThisCategory.id).attr("class", "order-up-off");
	else
	{
		if( g_oCategoryChain[oThisCategory.pre_id].level==oThisCategory.level)
			$("#order-up-"+oThisCategory.id).attr("class", "order-up-on");
		else
			$("#order-up-"+oThisCategory.id).attr("class", "order-up-off");		
	}
	
	$("#order-down-"+oThisCategory.id).attr("class", "order-down-off");
	
	if(oThisCategory.level !=0)
	{
		while(oThisCategory.level !=0)
		{
			oThisCategory = g_oCategoryChain[oThisCategory.pre_id];
		}
		$("#order-down-"+oThisCategory.id).attr("class", "order-down-off");
	}
}


// 上移
function orderUp(iCategoryID)
{
	if( $("#order-up-"+iCategoryID).attr("class") == "order-up-off" ) return;
	
	var oCategory = g_oCategoryChain[iCategoryID];
	
	var oPreCategoryHead = g_oCategoryChain[oCategory.pre_id];
	var oPreCategoryTail = g_oCategoryChain[oCategory.pre_id];
	
	while(oPreCategoryHead.pre_id!=0 && oPreCategoryHead.level>oCategory.level) oPreCategoryHead = g_oCategoryChain[oPreCategoryHead.pre_id];

	if(oPreCategoryHead.pre_id==0)
		g_iCategoryChainHead = oCategory.id;
	else
		g_oCategoryChain[oPreCategoryHead.pre_id].next_id = oCategory.id;

	oCategory.pre_id = oPreCategoryHead.pre_id;

	$("#row-"+oPreCategoryHead.id).before($("#row-"+oCategory.id));
	$("#row-"+oCategory.id).mouseout();
	
	
	var oLastMovedCategory;
	if(oCategory.next_id==0)
	{
		oLastMovedCategory = oCategory;
	}
	else
	{
		oLastMovedCategory = g_oCategoryChain[oCategory.next_id];
		while(oLastMovedCategory.next_id!=0 && oLastMovedCategory.level>oCategory.level)
		{
			$("#row-"+oPreCategoryHead.id).before($("#row-"+oLastMovedCategory.id));
			oLastMovedCategory =  g_oCategoryChain[oLastMovedCategory.next_id];
		}
		
		if(oLastMovedCategory.next_id==0)
		{
			if(oLastMovedCategory.level>oCategory.level)
				$("#row-"+oPreCategoryHead.id).before($("#row-"+oLastMovedCategory.id));
			else
				oLastMovedCategory = g_oCategoryChain[oLastMovedCategory.pre_id];
		}
		else
			oLastMovedCategory = g_oCategoryChain[oLastMovedCategory.pre_id];
	}
	
	if(oLastMovedCategory.next_id!=0)
		g_oCategoryChain[oLastMovedCategory.next_id].pre_id = oPreCategoryTail.id;
		
	oPreCategoryHead.pre_id = oLastMovedCategory.id;
	oPreCategoryTail.next_id = oLastMovedCategory.next_id;
	oLastMovedCategory.next_id = oPreCategoryHead.id;

	updateOrderIcon();
}


function orderDown(iCategoryID)
{
	if( $("#order-down-"+iCategoryID).attr("class") == "order-down-off" )return;
	
	var oCategory = g_oCategoryChain[iCategoryID];

	var oNextCategoryHead = g_oCategoryChain[oCategory.next_id];
	while( oNextCategoryHead.level>oCategory.level ) oNextCategoryHead = g_oCategoryChain[oNextCategoryHead.next_id];

	var oNextCategoryTail = oNextCategoryHead;
	while(oNextCategoryTail.next_id!=0 && g_oCategoryChain[oNextCategoryTail.next_id].level>oNextCategoryHead.level) oNextCategoryTail = g_oCategoryChain[oNextCategoryTail.next_id];

	if(oCategory.pre_id==0)
		g_iCategoryChainHead = oNextCategoryHead.id;
	else
		g_oCategoryChain[oCategory.pre_id].next_id = oNextCategoryHead.id;

	oNextCategoryHead.pre_id = oCategory.pre_id;
	oCategory.pre_id = oNextCategoryTail.id;
	
	oLastMovedCategory = oCategory;
	while(oLastMovedCategory.next_id != oNextCategoryHead.id)
	{
		$("#row-"+oLastMovedCategory.pre_id).after($("#row-"+oLastMovedCategory.id));
		oLastMovedCategory = g_oCategoryChain[oLastMovedCategory.next_id];
	}
	$("#row-"+oLastMovedCategory.pre_id).after($("#row-"+oLastMovedCategory.id));
	$("#row-"+oCategory.id).mouseout();
	
	if(oNextCategoryTail.next_id!=0)
		g_oCategoryChain[oNextCategoryTail.next_id].pre_id = oLastMovedCategory.id;

	oLastMovedCategory.next_id = oNextCategoryTail.next_id;
	oNextCategoryTail.next_id = oCategory.id;

	updateOrderIcon();
}


function confirmDeleteCategory(iCategoryID)
{
	// 如果是新添加的节点， 直接删除
	if($("#id-"+iCategoryID).val() == 0)
	{
		deleteSingleCategory(iCategoryID);
		return;
	}
	
	var oCategory = g_oCategoryChain[iCategoryID];	// 获取当前操作的节点
	var msg = '';
	if(oCategory.children)
		msg = '本操作将同时删除该分类, 子分类, 以及相关分类下的所有文章, 确认要删除吗?';
	else
		msg = '本操作将同时删除该分类下的所有文章, 确认要删除吗?';


	if(confirm(msg)) deleteCategory(iCategoryID)
}

// 删除分类
function deleteCategory(iCategoryID)
{
	var oCategory = g_oCategoryChain[iCategoryID];	// 获取当操作的节点
	var oThisCategory = oCategory;
	while(oThisCategory.next_id>0)
	{
		oThisCategory =  g_oCategoryChain[oThisCategory.next_id];	// 链表后移
		
		if(oThisCategory.level>oCategory.level)
			deleteSingleCategory(oThisCategory.id);
		else
			break;	
	}
	deleteSingleCategory(iCategoryID);
	
	updateOrderIcon();
}

function deleteSingleCategory(iCategoryID)
{
	var oCategory = g_oCategoryChain[iCategoryID];	// 获取当前操作的节点

	// 如果有直接前趋, 则把当前节点的直接后继赋值给上一个节点的直接后继
	if(oCategory.pre_id)
		g_oCategoryChain[oCategory.pre_id].next_id = oCategory.next_id;
	else
		g_iCategoryChainHead = oCategory.next_id;
	
	// 如果有直接后继, 则把当前节点的直接前趋赋值给下一个节点的直接前趋
	if(oCategory.next_id)
		g_oCategoryChain[oCategory.next_id].pre_id = oCategory.pre_id;

	
	if(oCategory.parent_id)
	{
		// 判数父节点是否已清空， 如果已为空， 则隐藏打开/缩起图标
		var oParentCategory = g_oCategoryChain[oCategory.parent_id];
		if(oParentCategory)
		{
			if(oParentCategory.next_id)
			{
				if( g_oCategoryChain[oParentCategory.next_id].level<= oParentCategory.level )
				{
					oParentCategory.children = 0;	//下一个节点比当前节点的级别低， 证明父节点已没有孩子节点, 设置父节点的孩子数为0
					$("#toggle-"+oParentCategory.id).fadeOut();	// 隐藏缩放图标
				}
			}
			else
			{
				oParentCategory.children = 0;	//父节点的直接后继为空， 为链表的结尾， 因些设置父节点的孩子数为0
				$("#toggle-"+oParentCategory.id).fadeOut();	// 隐藏缩放图标
			}
		}
	}
	
	g_oCategoryChain[iCategoryID] = null;
	
	
	if($("#id-"+iCategoryID).val() == 0)
	{
		// 如果是新添加的分类， 直接删队， 无须提交服务器端
		$("#row-"+iCategoryID).remove();
		return;
	}
	
	$("#name-"+iCategoryID).after(" &nbsp; "+g_sLoadingImage+"删除中...");
	// 提交服务器删除操作
	$.ajax({
		url: "./?app=Cms&controller=Article&action=ajax_delete_category&category_id="+iCategoryID,
		success: function(msg){
			$("#row-"+iCategoryID).remove();
		}
	});	
}