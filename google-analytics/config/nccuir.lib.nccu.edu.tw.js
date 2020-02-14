/**
 * 適用網頁：http://vinson.rd.ssic.nccu.edu.tw/
 * 事件查詢表：https://docs.google.com/spreadsheets/d/1MtMtw9lKLDTUzfBd6Ld0fAe_FGe5u-Mlkh5WfZiH5qM/edit
 * @author Pudding 20170203
 */

GA_TRACE_CODE = "UA-107585606-1";

var _local_debug = false;

if (_local_debug === true) {
    CSS_URL = "https://localhost/GA-project/config/nccuir.lib.nccu.edu.tw.css";
    LIB_URL = "https://localhost/GA-project/ga_inject_lib.js";
    console.log("[LOCAL TEST MODE]");
}
else {
    CSS_URL = "https://billxu0521.github.io/GA-project/config/nccuir.lib.nccu.edu.tw.css";
    LIB_URL = "https://billxu0521.github.io/GA-project/ga_inject_lib.js";
}


var exec = function () {
    auto_set_user_id();   

    //偵測語法
    //ga_mouse_click_event("選擇要素","準則");
    ga_mouse_click_event(".btn","Click");
    ga_mouse_click_event(".btn scholar_href","Click");
    ga_mouse_click_event(".btn btn-info btn-lg mouse_clickClick","Click");
    ga_mouse_click_event("[href]", "Click", function (_ele) {
        return _ele.text();});
    ga_submit_event("form", "Form", function (_ele) {
        return _ele.text();});
};

// --------------------------------------

$(function () {
    $.getScript(LIB_URL, function () {
        ga_setup(function () {
            exec();
        });
    });
});
