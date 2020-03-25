var _exec_jieba = function () {
	dic = "/jieba-snifs/snifs_discusscut.php?course_id="+course_id+'&forum_id='+forum_id;
	$.get(dic, function (result) {
		//測試用console log
		var time = new Date().getTime() / 1000;
		console.log('course id課程 ='+course_id+'forum id討論版'+courseb+' 135456 '+forum_id+' 時間:'+time);
		console.log(result);
		setTimeout(function () {
			_exec_jieba();
		}, 30 * 1000);
	});
};

$(function () {
	_exec_jieba();
});
