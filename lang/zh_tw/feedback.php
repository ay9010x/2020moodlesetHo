<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'feedback', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   feedback
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['add_item'] = '新增問題到回饋單中';
$string['add_pagebreak'] = '加分頁符號';
$string['adjustment'] = '調整';
$string['after_submit'] = '填答提交之後';
$string['allowfullanonymous'] = '允許完全匿名';
$string['analysis'] = '分析';
$string['anonymous'] = '匿名';
$string['anonymous_edit'] = '記錄用戶名稱';
$string['anonymous_entries'] = '匿名輸入({$a})';
$string['anonymous_user'] = '匿名使用者';
$string['append_new_items'] = '附加新的項目';
$string['autonumbering'] = '自動將問題編號';
$string['autonumbering_help'] = '啟用或關閉每一問題的自動編號';
$string['average'] = '平均數';
$string['bold'] = '粗體';
$string['calendarend'] = '回饋 {$a}關閉';
$string['calendarstart'] = '回饋 {$a}開啟';
$string['cancel_moving'] = '取消搬移';
$string['cannotaccess'] = '你可以從一課程只存取這一回饋';
$string['cannotmapfeedback'] = '資料庫問題，無法把回饋單對應到課程';
$string['cannotsavetempl'] = '不允許儲存範本';
$string['cannotunmap'] = '資料庫問題，無法取消對應';
$string['captcha'] = '圖形驗證碼';
$string['captchanotset'] = '圖形驗證碼沒有設定。';
$string['check'] = '選擇題 - 複選';
$string['checkbox'] = '選擇題 - 複選 (核取方塊)';
$string['check_values'] = '可能的回應';
$string['choosefile'] = '第一步:選擇檔案';
$string['chosen_feedback_response'] = '選擇回饋回應';
$string['closebeforeopen'] = '你指定的結束日期早於開始日期';
$string['completed'] = '完成';
$string['completed_feedbacks'] = '已回答';
$string['complete_the_form'] = '填寫回饋單...';
$string['completionsubmit'] = '若回饋已經提交，視為已經完成';
$string['configallowfullanonymous'] = '若設定為"是"，那麼用戶不需要登入就可以在首頁參與回饋活動。';
$string['confirmdeleteentry'] = '您確定要刪除使用者的回應嗎？';
$string['confirmdeleteitem'] = '您確定要刪除這個項目嗎？';
$string['confirmdeletetemplate'] = '您確定要刪除這個範本嗎？';
$string['confirmusetemplate'] = '您確定要使用這個範本嗎？';
$string['continue_the_form'] = '繼續填表';
$string['count_of_nums'] = '計數';
$string['courseid'] = '課程識別編號';
$string['creating_templates'] = '將這些問題儲存為新範本';
$string['delete_entry'] = '刪除';
$string['delete_item'] = '刪除問題';
$string['delete_old_items'] = '刪除舊有項目';
$string['delete_pagebreak'] = '刪除分頁符號';
$string['delete_template'] = '刪除範本';
$string['delete_templates'] = '刪除範本...';
$string['depending'] = '依賴性';
$string['depending_help'] = '依賴性使問題的顯示取決於對其他問題的回答。<br />
<strong>這裏有一個使用例子：</strong> <br /> <ul>
<li>首先創建一條其他問題要依賴的題。</li> <li>然後添加一個分頁符。</li>
<li>接著添加一條依賴於上面那道題的題。<br /> 在創建問題表單中的“依賴於問題”列表中選擇那道題，並將需要的值填入“依賴值”文本框中。</li> </ul>
<strong>試題結構應該像下面這樣：</strong>
<ol>
<li>問題：您有汽車嗎？回答：有/沒有</li>
<li>分頁符</li>
<li>問題：您的車是什麼顏色的？<br /> （此題當問題1選擇“有”時才顯示）</li>
<li>問題：您為什麼沒有車？<br /> （此題當問題1選擇“沒有”時才顯示）</li>
<li> ……其他問題</li>
</ol>';
$string['dependitem'] = '相依題標籤';
$string['dependvalue'] = '相依題值';
$string['description'] = '描述';
$string['do_not_analyse_empty_submits'] = '不分析空的填答';
$string['downloadresponseas'] = '下載所有回應如：';
$string['dropdown'] = '選擇題 - 單選 (下拉選單)';
$string['dropdownlist'] = '選擇題 - 單選 (下拉)';
$string['dropdownrated'] = '下拉選單 (評分)';
$string['dropdown_values'] = '回答';
$string['drop_feedback'] = '移除這個課程的表單';
$string['edit_item'] = '編輯問題';
$string['edit_items'] = '編輯問題';
$string['email_notification'] = '啟用填答通知';
$string['email_notification_help'] = '若啟用，有回饋單提交時，教師將會收到email通知';
$string['emailteachermail'] = '{$a->username} 已完成回饋活動 : \'{$a->feedback} \'

您可以在這裡查看它:

 {$a->url}';
$string['emailteachermailhtml'] = '<p>{$a->username} 已完成回饋活動 :
<i>\'{$a}-feedback\'</i>。</p>
<p>您可以在<a href="{$a->url}">這裡</a> 查看它。</p>';
$string['entries_saved'] = '您的回答已經儲存， 感謝您。';
$string['eventresponsedeleted'] = '回應已刪除';
$string['eventresponsesubmitted'] = '回應已提交';
$string['export_questions'] = '匯出回饋單';
$string['export_to_excel'] = '匯出到Excel';
$string['feedback:addinstance'] = '添加一新回饋';
$string['feedbackclose'] = '結束填答時間';
$string['feedback:complete'] = '完成回饋單';
$string['feedbackcompleted'] = '{$a->username} 已經完成 {$a->feedbackname}';
$string['feedback:createprivatetemplate'] = '建立私人用的範本';
$string['feedback:createpublictemplate'] = '建立公用的範本';
$string['feedback:deletesubmissions'] = '刪除完成的提交';
$string['feedback:deletetemplate'] = '刪除範本';
$string['feedback:edititems'] = '編輯項目';
$string['feedback_is_not_for_anonymous'] = '回饋單不給匿名者使用';
$string['feedback_is_not_open'] = '這個回饋單還沒有開放';
$string['feedback:mapcourse'] = '向全球的回饋繪製課程地圖';
$string['feedbackopen'] = '開始填答時間';
$string['feedback:receivemail'] = '收到電子郵件通知';
$string['feedback:view'] = '檢視一個回饋';
$string['feedback:viewanalysepage'] = '提交後可檢視分析頁面';
$string['feedback:viewreports'] = '檢視報告';
$string['file'] = '檔案';
$string['filter_by_course'] = '課程過濾器';
$string['handling_error'] = '回饋模組處理時發生錯誤';
$string['hide_no_select_option'] = '隱藏 "未選" 項目';
$string['horizontal'] = '水平';
$string['importfromthisfile'] = '第二步:確定';
$string['import_questions'] = '匯入回饋單';
$string['import_successfully'] = '匯入成功';
$string['info'] = '資訊';
$string['infotype'] = '資訊的類型';
$string['insufficient_responses'] = '回應不足夠';
$string['insufficient_responses_for_this_group'] = '不充足的回應';
$string['insufficient_responses_help'] = '這一群組沒有足夠的回應。

要保持回饋的匿名性，至少要有2個回應。';
$string['item_label'] = '標籤';
$string['item_name'] = '問題';
$string['label'] = '標籤';
$string['labelcontents'] = '內容';
$string['line_values'] = '評分';
$string['mapcourse'] = '限制回饋只用於特定課程';
$string['mapcourse_help'] = '預設狀況下，您在主要頁面建立的回應表單全站都可使用，並且會在所有用了回應區塊的課程中出現。您可以把回應表單設定成黏貼區塊來強制它出現，或者透過將它對應到指定的課程來限制此回應表單出現的課程。';
$string['mapcourseinfo'] = '這是個整個網站通用的回饋，藉由使用回饋區塊，它可以用於所有的課程。然而，你能透過配對限制它只出現在某些課程。搜尋這課程並且把它配對到這回饋。';
$string['mapcoursenone'] = '沒有課程對應。所有課程都可以使用回饋單';
$string['mapcourses'] = '對應回饋單到課程';
$string['mapcourses_help'] = '如果您在搜尋中選擇的相關的課程，您可以使用對應課程將它們與本回應關連。按住Apple或ctrl按鈕點選課程名稱，可以選擇多個課程。任何時候都可以取消課程回應的關連。';
$string['mappedcourses'] = '對應的課程';
$string['mappingchanged'] = '課程對應已經被更改';
$string['max_args_exceeded'] = 'Max 6個爭論能被處理，贊成的太多的意見';
$string['maximal'] = '最高限制';
$string['messageprovider:message'] = '回饋提醒者';
$string['messageprovider:submission'] = '回饋單通知';
$string['minimal'] = '最小限制';
$string['mode'] = '模式';
$string['modulename'] = '回饋單';
$string['modulename_help'] = '回饋單模組可以讓老師建立自訂問卷，用各種題型(包含選擇、是非或輸入文字)對學生蒐集回饋資料。

回饋單的回應可以設定是否匿名，回饋結果可以顯示給全部師生看，或是設定只有老師可以檢視。在網站首頁的所有回饋單活動，也可以給未登入的訪客填寫。

回饋單活動可以用在

* 為了課程評鑑，幫助改善未來的課程內容。
* 讓學生們能夠登入課程模組或事件等等。
* 調查訪客選課意願、學校政策意見等等。
* 反霸凌調查，學生能匿名回應意外事件。';
$string['modulenameplural'] = '回饋單';
$string['movedown_item'] = '向下移動這個問題';
$string['move_here'] = '移到這裡';
$string['move_item'] = '移動這個問題';
$string['moveup_item'] = '向上移動這個問題';
$string['multichoice'] = '選擇題';
$string['multichoicerated'] = '選擇題(評值)';
$string['multichoicetype'] = '選擇題類型';
$string['multichoice_values'] = '選擇題的選項';
$string['multiplesubmit'] = '可以多次填答';
$string['multiplesubmit_help'] = '若啟用，在匿名調查時，用戶可以不限次數提交回饋單。';
$string['name'] = '名稱';
$string['name_required'] = '要求輸入名稱';
$string['next_page'] = '下一頁';
$string['no_handler'] = '沒有行動管理者存在為了';
$string['no_itemlabel'] = '沒有標籤';
$string['no_itemname'] = '沒有項目名稱';
$string['no_items_available_yet'] = '沒有問題目前已被建立';
$string['non_anonymous'] = '用戶名稱和回應將被記錄';
$string['non_anonymous_entries'] = '具名輸入({$a})';
$string['non_respondents_students'] = '沒有回應的學生({$a})';
$string['notavailable'] = '這一回饋無法使用';
$string['not_completed_yet'] = '目前未完成';
$string['no_templates_available_yet'] = '沒有可用的範本';
$string['not_selected'] = '未選';
$string['not_started'] = '沒有開始';
$string['numberoutofrange'] = '超過範圍的人數';
$string['numeric'] = '數字答案';
$string['numeric_range_from'] = '範圍從';
$string['numeric_range_to'] = '範圍到';
$string['of'] = '屬於';
$string['oldvaluespreserved'] = '儲存所有舊的問題和設定值';
$string['oldvalueswillbedeleted'] = '刪除目前的問題和所有使用者的回應';
$string['only_one_captcha_allowed'] = '在一個回饋中，只允許有一個captcha';
$string['overview'] = '概要';
$string['page'] = '頁';
$string['page_after_submit'] = '完成後的訊息';
$string['pagebreak'] = '分頁';
$string['page-mod-feedback-x'] = '任何回饋模組頁面';
$string['parameters_missing'] = '從遺失的參數';
$string['picture'] = '圖片';
$string['picture_file_list'] = '圖片清單';
$string['picture_values'] = '選擇一個或更多<br />來自清單的圖片檔案:';
$string['pluginadministration'] = '回饋單管理';
$string['pluginname'] = '回饋單';
$string['position'] = '位置';
$string['preview'] = '預覽';
$string['preview_help'] = '在預覽時，你可以改變這些問題的順序';
$string['previous_page'] = '上一頁';
$string['public'] = '共用';
$string['question'] = '問題';
$string['questionandsubmission'] = '問題與提交設定';
$string['questions'] = '問題';
$string['questionslimited'] = '只顯示前{$a}個試題，要檢視個別答案或下載表格資料才可以看到全部。';
$string['radio'] = '選擇題 - 單選';
$string['radiobutton'] = '選擇題 - 單選 (選項按鈕)';
$string['radiobutton_rated'] = '選項按鈕 (評分)';
$string['radiorated'] = '選項按鈕 (評分)';
$string['radio_values'] = '回應';
$string['ready_feedbacks'] = '準備好回饋';
$string['relateditemsdeleted'] = '刪除所有這個問題的所有使用者的回應';
$string['required'] = '必答';
$string['resetting_data'] = '重設回饋回應';
$string['resetting_feedbacks'] = '重新設定回饋';
$string['response_nr'] = '回應編號';
$string['responses'] = '回應';
$string['responsetime'] = '回應的時間';
$string['save_as_new_item'] = '另存為新問題';
$string['save_as_new_template'] = '儲存';
$string['save_entries'] = '送出並結束';
$string['save_item'] = '儲存';
$string['saving_failed'] = '儲存失敗';
$string['saving_failed_because_missing_or_false_values'] = '儲存失敗因為遺失或者錯誤值';
$string['search:activity'] = '回饋活動';
$string['search_course'] = '搜尋課程';
$string['searchcourses'] = '搜尋課程';
$string['searchcourses_help'] = '以代碼或課程名稱來搜尋你要加上這一回饋活動的課程';
$string['selected_dump'] = '選擇索引的 $SESSION 變數顯示下方:';
$string['send'] = '送出';
$string['send_message'] = '送出訊息';
$string['separator_decimal'] = '.';
$string['separator_thousand'] = ',';
$string['show_all'] = '全部顯示';
$string['show_analysepage_after_submit'] = '顯示分析頁面';
$string['show_entries'] = '顯示回應';
$string['show_entry'] = '顯示回應';
$string['show_nonrespondents'] = '顯示沒有回應的學生';
$string['site_after_submit'] = '完成後的網站';
$string['sort_by_course'] = '課程分類';
$string['start'] = '開始';
$string['started'] = '已經開始';
$string['stop'] = '結束';
$string['subject'] = '主旨';
$string['switch_group'] = '交換群組';
$string['switch_item_to_not_required'] = '設定為非必要的';
$string['switch_item_to_required'] = '設定為必要的';
$string['template'] = '範本';
$string['template_deleted'] = '樣板已經刪除';
$string['templates'] = '範本';
$string['template_saved'] = '範本已經儲存';
$string['textarea'] = '問答題';
$string['textarea_height'] = '行數';
$string['textarea_width'] = '寬度';
$string['textfield'] = '簡答題';
$string['textfield_maxlength'] = '最多輸入多少字元';
$string['textfield_size'] = '文字框的寬度';
$string['there_are_no_settings_for_recaptcha'] = '這兒沒有針對captcha的設定';
$string['this_feedback_is_already_submitted'] = '您\'已經完成這種活動。';
$string['typemissing'] = '少了數值"類型"';
$string['update_item'] = '儲存';
$string['url_for_continue'] = '鏈結到下一活動';
$string['url_for_continue_help'] = '在提交回饋單之後，會顯現一個繼續按鈕，它會鏈結的一個課程頁面。但是它也可以是鏈結到下一個活動，若你把這活動的網址輸入這裡。';
$string['use_one_line_for_each_value'] = '每一個答案一行!';
$string['use_this_template'] = '確定';
$string['using_templates'] = '使用範本';
$string['vertical'] = '垂直';
$string['viewcompleted'] = '已經完成的回饋';
$string['viewcompleted_help'] = '你可以查看已經完成的回饋表單，它可以由課程和/或問題來搜尋。
回饋反應可以匯出成Excel檔。';
