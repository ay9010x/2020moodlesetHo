/**
 * 適用網頁：http://vinson.rd.ssic.nccu.edu.tw/
 * 事件查詢表：https://docs.google.com/spreadsheets/d/1MtMtw9lKLDTUzfBd6Ld0fAe_FGe5u-Mlkh5WfZiH5qM/edit
 * @author Pudding 20170203
 */

GA_TRACE_CODE = "UA-89833109-5";
        
var _local_debug = false;

if (_local_debug === true) {
    CSS_URL = "https://localhost/GA-project/config/billxu_tool.GA_test.css";
    LIB_URL = "https://localhost/GA-project/ga_inject_lib.js";
    console.log("[LOCAL TEST MODE]");
}
else {
    CSS_URL = "https://billxu0521.github.io/GA-project/config/billxu_tool.GA_test.css";
    LIB_URL = "https://billxu0521.github.io/GA-project/ga_inject_lib.js";
}


var exec = function () {
    auto_set_user_id();   

    //按鈕
    ga_mouse_click_event("#btn_1","Click Btn_1");
    ga_mouse_click_event(".btn","Click Btn");

    //滑鼠滑入滑出
    ga_mouse_over_event(".img","Hover img");

    //表單送出
    ga_submit_event("form", "submit", function (_ele) {
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
