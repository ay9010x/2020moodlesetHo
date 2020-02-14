var _exec_jieba = function () {
	$.get("/jieba-snifs/snifs_discusscut.php", function () {
		setTimeout(function () {
			_exec_jieba();
		}, 30 * 1000);
	});
};

$(function () {
	_exec_jieba();
});
