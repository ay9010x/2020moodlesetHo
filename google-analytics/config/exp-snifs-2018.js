/**
 * 適用網頁：http://vinson.rd.ssic.nccu.edu.tw/
 * 事件查詢表：https://docs.google.com/spreadsheets/d/1MtMtw9lKLDTUzfBd6Ld0fAe_FGe5u-Mlkh5WfZiH5qM/edit
 * @author Pudding 20170203
 */

GA_TRACE_CODE = "UA-118020470-1";

var _local_debug = false;


    CSS_URL = "/google-analytics/config/exp-snifs-2018.css";
    LIB_URL = "/google-analytics/ga_inject_lib.js";


var exec = function () {

    $.get("/username.php", function (username) {
        set_user_id(username);

        //按鈕
        ga_mouse_click_event(".gsc-search-button","external_search", function () {
            return $("#gsc-i-id1").val().trim();
        });
        $("#gsc-i-id1").keydown(function (_event) {
            //console.log(_event.keyCode);
            if (_event.keyCode === 13) {
                //console.log("enter");
                var _event_key = 'mouse_click';
                ga_mouse_click_event_trigger($(".gsc-search-button"), ".gsc-search-button", $("#gsc-i-id1").val().trim(), "external_search", _event_key);
            }
        });
        ga_mouse_click_event(".gsc-search-button","external_search", function () {
            return $("#gsc-i-id1").val().trim();
        });

        //ga_mouse_click_event(".gsc-search-button","external_search", function () {
        //    return $(".gsc-input").val().trim();
        //});

        //切換個人snifs圖
        ga_mouse_click_event("#btn_snifs_switch_person", "snifs_switch_person");
        //切換小組snifs圖
        ga_mouse_click_event("#btn_snifs_switch_group", "snifs_switch_group");
        //開啟閱讀教材
        ga_mouse_click_event("#btnArticles", "snifs_switch_articles");
        //關閉討論區教材
        ga_mouse_click_event("#btnClose", "snifs_switch_close_articles");
        //回討論區
        ga_mouse_click_event("#btnDiscuss", "snifs_switch_discuss");
        //回到首頁
        ga_mouse_click_event("#btnTop", "snifs_switch_backtop");
        
    });


};


// --------------------------------------

$(function () {
    $.getScript(LIB_URL, function () {
        ga_setup(function () {
            exec();
        });
    });
});

function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
}
