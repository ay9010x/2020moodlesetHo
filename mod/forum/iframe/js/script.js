
$(function () {

	$("#btnDiscuss").click(function () {
		$("#discuss:first").attr("src", "/mod/hsuforum/view.php?id=564&group=0");
	});

	$("#btnTop").click(function () {
		var _url = $("#discuss:first").attr("src");
		if (_url.indexOf("#") > -1) {
			_url = _url.substr(0, _url.lastIndexOf("#"));
		}
		_url = _url + "#page-mod-hsuforum-view";
		$("#discuss:first").attr("src", _url);
	});

	// ------------------------------
	var _snifs_iframe = $("#specialgraph");
	open_snifs = function (_layout) {
		if (_layout === undefined) {
			_layout = "personal";
		}

		var _src = _snifs_iframe.attr("src");
		var _url = "/snifs-personal-layout/snifs-personal-layout.php?layout=" + _layout;
		if (_src !== _url) {
			_snifs_iframe.attr("src", _url);
			_snifs_scroll_to_center();
		}
	};

	_snifs_scroll_to_center = function () {
		setTimeout(function () {
			// �b�o�̩I�s�L��scroll_to_center
			if (typeof(_snifs_iframe[0].contentWindow.scroll_to_center) === "function") {
				_snifs_iframe[0].contentWindow.scroll_to_center();
			}
			else {
				setTimeout(function () {
					_snifs_scroll_to_center();
				}, 500);
			}

		}, 500);
	};

	_snifs_scroll_to_center();
	_init_submit_report();

}); // $(function () {

var _init_submit_report = function () {

	var _shift_pressed = false;
	show_submit_report = function () {
		if (_shift_pressed === true) {
			$("#submit_report_li").show();
		}
	};

	var _body = $("body");
	_body.keydown(function (_e) {
		if (_e.keyCode === 16) {
			_shift_pressed = true;
			//console.log(true);
		}
		//console.log(_e.keyCode);
	});
	_body.keyup(function (_e) {
		if (_e.keyCode === 16) {
			_shift_pressed = false;
			//console.log(false);
		}
	});

};
