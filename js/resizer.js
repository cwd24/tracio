$(document).ready(function() {
	
	var originalFontSize = $('#main_content').css('font-size');
	$(".resetFont").click(function() {
		$('body').css('font-size', originalFontSize);
	});
	function resizeText(multi) {
		multi = Number (multi);
		var currentFontSize = $('#main_content').css('font-size');
		var currentFontSizeNum = parseFloat(originalFontSize, 10);
		if (multi == 0) {
			var newFontSize = originalFontSize;
		} else {
			var newFontSize = currentFontSizeNum * multi;
		}
		$('#main_content').css('font-size', newFontSize);
		$('input').css('font-size', newFontSize);
		$('select').css('font-size', newFontSize);
		var options = { path : '/', expires : 10};
		

	}
	$("#txt_norm").click(function(e) {
		resizeText(0);
		e.preventDefault();
	});
	$("#txt_small").click(function(e) {
		resizeText(0.8);
		e.preventDefault();
	});
	$("#txt_large").click(function(e) {
		resizeText(1.5);
		e.preventDefault();
	});
	$("#txt_xlarge").click(function(e) {
		resizeText(2);
		e.preventDefault();
	
	});
});