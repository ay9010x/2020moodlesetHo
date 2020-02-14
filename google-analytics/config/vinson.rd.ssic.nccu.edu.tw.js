/**
 * 適用網頁：http://vinson.rd.ssic.nccu.edu.tw/
 * 事件查詢表：https://docs.google.com/spreadsheets/d/1MtMtw9lKLDTUzfBd6Ld0fAe_FGe5u-Mlkh5WfZiH5qM/edit
 * @author Pudding 20170203
 */

GA_TRACE_CODE = "UA-89833109-4";

var _local_debug = false;

if (_local_debug === true) {
    CSS_URL = "https://localhost/GA-project/config/vinson.rd.ssic.nccu.edu.tw.css";
    LIB_URL = "https://localhost/GA-project/ga_inject_lib.js";
    console.log("[LOCAL TEST MODE]");
}
else {
    CSS_URL = "https://billxu0521.github.io/GA-project/config/vinson.rd.ssic.nccu.edu.tw.css";
    LIB_URL = "https://billxu0521.github.io/GA-project/ga_inject_lib.js";
}


var exec = function () {
    auto_set_user_id();   

    //搜尋按鈕
    ga_mouse_click_event(".glyphicon glyphicon-search","Click");
    ga_mouse_click_event('a[title="文集瀏覽"]', "Click", function (_ele) {
        return _ele.text();});
    ga_mouse_click_event(".evenRowEvenCol", "Click");
    ga_mouse_click_event(".oddRowEvenCol", "Click");
    ga_mouse_click_event("[href]", "Click", function (_ele) {
        return _ele.text();});
    ga_mouse_click_event("[name='submit']", "Click", function (_ele) {
        return _ele.text();});
   

    //偵測搜尋表單
    /*
    ga_submit_event('#glyphicon glyphicon-search > form',"Form", 
        function(form){
            console.log("submit act"+form);
            return "start-year=" + form.find('select[name="start-year"]').val();
        });
    */
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