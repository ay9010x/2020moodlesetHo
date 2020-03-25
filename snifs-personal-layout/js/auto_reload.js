/**
 * Auto reload pageX
 * @author Pulipuli Chen 20180508
 */
$(function () {
	var _reload_counter = null;
	var _reload_counter_start = function () {
		_reload_counter = setTimeout(function () {
			/*
			if ($(".fullscreen-mask:visible").length === 0) {
				_draw_go_graph();
				//console.log("reloaded");
			}
			*/
			$(".loading").show();
			_draw_go_graph();
			$(".loading").hide();
		}, 6 * 1000);
	};

	var _reload_counter_stop = function () {
		if (_reload_counter === null) {
			return;
		}

		clearTimeout(_reload_counter);
		_reload_counter = null;
	};

	_reload_counter_start();
	$(document).mouseover(function () {
		_reload_counter_stop();
	});
	$(document).mouseout(function () {
		_reload_counter_start();
	});
});
	

/**
 * @departed 不使用了
 * 重整網頁
 */
function funReload() {
	location.reload();
}