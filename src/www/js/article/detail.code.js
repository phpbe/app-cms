hljs.highlightAll();
hljs.initLineNumbersOnLoad();

$(function () {
	let counter = 0;
	$("pre").each(function () {
		if ($(this).has("code")) {
			$(this).addClass("copy-code");
			$(this).prepend('<button class="be-btn be-btn-sm btn-copy-code">复制</button>');
			counter++;
		}
	});

	if (counter > 0) {
		let buttons = new ClipboardJS('.btn-copy-code', {
			target:function(trigger){
				return trigger.nextElementSibling;
			}
		});

		buttons.on('success',function(e) {
			//e.clearSelection();
			let $trigger = $(e.trigger);
			$trigger.addClass("be-btn-gray").html("代码已复制");
			setTimeout(function () {
				$trigger.removeClass("be-btn-gray").html("复制");
			}, 3000);
		});

		buttons.on('error',function(e) {});
	}
})