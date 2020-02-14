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
 * Strings for component 'apply', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   apply
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accept_entry'] = '接受。';
$string['acked_accept'] = '接受。';
$string['acked_notyet'] = '尚未。';
$string['acked_reject'] = '駁回。';
$string['add_pagebreak'] = '新增分頁符號';
$string['adjustment'] = '調整。';
$string['apply:addinstance'] = '新增申請';
$string['apply:applies'] = '提出申請。';
$string['apply:createprivatetemplate'] = '建立私有範本。';
$string['apply:createpublictemplate'] = '建立公開範本。';
$string['apply:deletesubmissions'] = '提交刪除。';
$string['apply:deletetemplate'] = '刪除範本。';
$string['apply:edititems'] = '編輯項目。';
$string['apply:edittemplates'] = '編輯範本。';
$string['apply_is_already_submitted'] = '已提出申請';
$string['apply_is_closed'] = '申請時間已過。';
$string['apply_is_disable'] = '你不能使用此"套用"。';
$string['apply_is_not_open'] = '尚未開放申請';
$string['apply_is_not_ready'] = '"套用"尚未可用。請先編輯各項目。';
$string['apply_options'] = '套用選項。';
$string['apply:preview'] = '預視。';
$string['apply:receivemail'] = '接受email通知。';
$string['apply:submit'] = '';
$string['apply:view'] = '檢視套用。';
$string['apply:viewanalysepage'] = '提交後檢視分析頁面。';
$string['apply:viewentries'] = '條目表。';
$string['apply:viewreports'] = '檢視報告。';
$string['average'] = '平均。';
$string['back_button'] = '回上一步。';
$string['before_apply'] = '提交前。';
$string['cancel_entry'] = '取消。';
$string['cancel_entry_button'] = '取消';
$string['cancel_moving'] = '取消移動。';
$string['cannot_save_templ'] = '不允許儲存範本';
$string['captcha'] = '驗證碼';
$string['check'] = '勾選方塊。';
$string['checkbox'] = '勾選空格';
$string['class_cancel'] = '取消';
$string['class_draft'] = '草稿。';
$string['class_newpost'] = '新貼文';
$string['class_update'] = '更新。';
$string['confirm_cancel_entry'] = '您確定要取消此條目?';
$string['confirm_delete_entry'] = '您確定要撤回此條目?';
$string['confirm_delete_item'] = '您確定要刪除此元素?';
$string['confirm_delete_submit'] = '您確定要刪除此套用?';
$string['confirm_delete_template'] = '您確定要刪除此範本?';
$string['confirm_rollback_entry'] = '你確定要撤回此條目?';
$string['confirm_use_template'] = '您確定要使用此範本?';
$string['count_of_nums'] = '數目。';
$string['creating_templates'] = '儲存這些問題做為新範本。';
$string['delete_entry'] = '撤回。';
$string['delete_entry_button'] = '撤銷';
$string['delete_item'] = '刪除題目';
$string['delete_submit'] = '刪除應用';
$string['delete_template'] = '刪除樣版';
$string['delete_templates'] = '刪除範本';
$string['depending_help'] = '我們可以用另一個項目的值來表示某一特定項目。<br />
<strong>舉例如下</strong><br />
<ul>
<li>首先先設定某個項目，而另一個項目將依之變動。</li>
<li>接著，加個分頁符號。</li>
<li>接著，增加某些項目，這些項目將依先前項目設定值變動。下一步，由"Dependence item"(依變項"列表中選出某項目並於正文標示"Dependence value"(依變值)處寫下必要之數值。</li>
</ul>
<strong>此項目之結構應該像這樣。</strong>
<ol>
<li>項目Q: Do you have a car? A: yes/no</li>
<li>分頁符號</li>
<li>項目Q: What colour is your car?<br />
(這個項目依照項目1的數值 yes而定)</li>
<li>項目Q: Why don\'t you have a car?<br />
(這個項目依照項目1的數值no而定)</li>
<li> ... 其它項目</li>
</ol>';
$string['dependitem'] = '依變項';
$string['dependvalue'] = '依變值';
$string['description'] = '說明';
$string['display_button'] = '顯示';
$string['do_not_analyse_empty_submits'] = '不要分析空白的提交';
$string['dropdown'] = '下拉選單';
$string['edit_entry'] = '編輯';
$string['edit_entry_button'] = '編輯';
$string['edit_item'] = '編輯問題';
$string['edit_items'] = '編輯項目';
$string['email_entry'] = '寄出Email給申請者';
$string['email_notification'] = '寄送email通知';
$string['email_notification_help'] = '如經啟用，館理員會收到email通知有人提出申請。';
$string['email_notification_user'] = '寄出Email通知給申請者';
$string['email_notification_user_help'] = '若啟用，管理員可以寄出申請處理進度的email給申請者';
$string['email_teacher'] = '{$a->username}已經提交申請表格';
$string['email_user_accept'] = '你的申請已被接受';
$string['email_user_done'] = '你的申請的處理已經完成';
$string['email_user_other'] = '管理員已經處理你的申請';
$string['email_user_reject'] = '你的申請被拒絕了';
$string['enable_deletemode'] = '刪除模式';
$string['enable_deletemode_help'] = '如啟動此選項，教師可刪除所有套用。<br />為了安全起見，我們通常將之設成No。';
$string['entries_list_title'] = '項目選單';
$string['entry_saved'] = '您的申請已經儲存，謝謝您';
$string['entry_saved_draft'] = '您的申請已經被存為<strong>草稿</strong>。';
$string['entry_saved_operation'] = '已受理您的要求。';
$string['execd_done'] = '已完成';
$string['execd_entry'] = '已完成';
$string['execd_notyet'] = '未完成';
$string['exist'] = '存在';
$string['export_templates'] = '匯出範本';
$string['hide_no_select_option'] = '隱藏未被選出之選項';
$string['horizontal'] = '水平';
$string['import_templates'] = '匯入範本';
$string['info'] = '資訊';
$string['infotype'] = '資訊形式';
$string['item_label'] = '標示';
$string['item_name'] = '問題';
$string['items_are_required'] = '您須回答加星號之項目。';
$string['label'] = '標籤';
$string['maximal'] = '最大';
$string['messageprovider:message'] = '訊息';
$string['modulename'] = '申請表單';
$string['modulename_help'] = '您可製作簡易的申請表格，讓使用者可以提交。';
$string['modulenameplural'] = '申請表';
$string['movedown_item'] = '把問題向下移動';
$string['move_here'] = '移至此處';
$string['move_item'] = '移動此問題';
$string['moveup_item'] = '把問題往上移動';
$string['multichoice'] = '複選';
$string['multichoicerated'] = '多重選擇(已分級)';
$string['multichoicetype'] = '複選型式';
$string['multichoice_values'] = '複選數值';
$string['multiple_submit'] = '重複申請';
$string['multiple_submit_help'] = '如您啟用匿名審視，使用者可以無限次申請。';
$string['name'] = '姓名';
$string['name_required'] = '姓名必填';
$string['next_page_button'] = '下一頁';
$string['no_itemlabel'] = '無標籤';
$string['no_itemname'] = '無項目名';
$string['no_items_available_yet'] = '尚未設定問題';
$string['no_settings_captcha'] = 'CAPTCHA設定尚未編輯';
$string['no_submit_data'] = '指定條目資料不存在';
$string['no_templates_available_yet'] = '尚無範本';
$string['not_exist'] = '不存在';
$string['no_title'] = '無標題';
$string['not_selected'] = '未選擇';
$string['numeric'] = '多個答案';
$string['numeric_range_from'] = '範圍自';
$string['numeric_range_to'] = '範圍至';
$string['only_one_captcha_allowed'] = '每個申請中只能有一個captcha';
$string['operate_is_disable'] = '你不能使用此操作';
$string['operate_submit'] = '操作';
$string['operate_submit_button'] = '過程';
$string['operation_error_execd'] = '如果您不接受條目(entry)，你不能勾選"已完成"';
$string['overview'] = '檢視並提';
$string['pagebreak'] = '分頁';
$string['pluginadministration'] = '申請管理';
$string['pluginname'] = '申請表單';
$string['position'] = '位置';
$string['preview'] = '預覽';
$string['preview_help'] = '在此預覽中您可以變更問題之順序。';
$string['previous_apply'] = '先前提交者。';
$string['previous_page_button'] = '前一頁。';
$string['public'] = '公開。';
$string['radio'] = '收音機按鈕。';
$string['radiobutton'] = '單選按鈕';
$string['radiobutton_rated'] = '';
$string['radiorated'] = '圓形按鈕';
$string['reject_entry'] = '駁回';
$string['related_items_deleted'] = '您們使用者針對此問題之回應將被全數刪除。';
$string['required'] = '必要鄉';
$string['resetting_data'] = '重設套用回應。';
$string['responsetime'] = '回應時間。';
$string['returnto_course'] = '返回';
$string['rollback_entry'] = '收回。';
$string['rollback_entry_button'] = '撤回';
$string['save_as_new_item'] = '以新問題儲存。';
$string['save_as_new_template'] = '以新範本儲存。';
$string['save_draft_button'] = '以草稿儲存。';
$string['save_entry_button'] = '提交此項目。';
$string['save_item'] = '儲存此項目。';
$string['saving_failed'] = '儲存失敗。';
$string['saving_failed_because_missing_or_false_values'] = '儲存失敗因為少了一個數值或數值為偽。';
$string['separator_decimal'] = '.';
$string['separator_thousand'] = ',';
$string['show_all'] = '出示所有 {$a}。';
$string['show_perpage'] = '每頁表示{$a}';
$string['start'] = '開始。';
$string['started'] = '已開始。';
$string['stop'] = '結束。';
$string['subject'] = '主題。';
$string['submit_form_button'] = '';
$string['submit_new_apply'] = '';
$string['submit_num'] = '提交數目。';
$string['submitted'] = '已提交。';
$string['switch_item_to_not_required'] = '轉換至：不需回答。';
$string['switch_item_to_required'] = '轉換至：需回答。';
$string['templates'] = '範本';
$string['template_saved'] = '範本已存';
$string['textarea'] = '文字答案更長';
$string['textarea_height'] = '行數';
$string['textarea_width'] = '寛度';
$string['textfield'] = '文字答案更短';
$string['textfield_maxlength'] = '可接受文字最大值';
$string['textfield_size'] = '文字範圍寛度';
$string['time_close'] = '結束時間';
$string['time_close_help'] = '您可指定時間讓申請可以有人回應。如此空格未勾選，則無時間限制。';
$string['time_open'] = '開放時間';
$string['time_open_help'] = '您可指定申請時間，在此時間內有人可回應申請事宜。如未勾選，則無時間限制。';
$string['title_ack'] = '接受';
$string['title_before'] = '提交前';
$string['title_check'] = '打勾';
$string['title_class'] = '狀態';
$string['title_draft'] = '草案';
$string['title_exec'] = '執行';
$string['title_title'] = '標題';
$string['title_version'] = '版本';
$string['update_entry'] = '更新';
$string['update_entry_button'] = '更新';
$string['update_item'] = '儲存問題變更';
$string['use_calendar'] = '使用日曆';
$string['use_calendar_help'] = '提出申請期間會註記在日曆上。';
$string['use_item'] = '使用{$a}';
$string['use_one_line_for_each_value'] = '<br />每個答案用一行';
$string['username_manage'] = '帳號管理';
$string['user_pic'] = '照片';
$string['use_this_template'] = '使用此範本';
$string['using_templates'] = '使用一個範本';
$string['vertical'] = '垂直';
$string['yes_button'] = '是';
