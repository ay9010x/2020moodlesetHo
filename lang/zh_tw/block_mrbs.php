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
 * Strings for component 'block_mrbs', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   block_mrbs
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['about_mrbs'] = '關於MRBS';
$string['accessdenied'] = '限制存取';
$string['accessmrbs'] = 'Schedule a Resource';
$string['addarea'] = '新增種類';
$string['addentry'] = '新增';
$string['addroom'] = '新增會議室/設備';
$string['advanced_search'] = '進階搜尋';
$string['all_day'] = '整天';
$string['area_admin_email'] = '區域管理者email';
$string['areas'] = '區域';
$string['backadmin'] = '回到管理首頁';
$string['bgcolor'] = '背景顏色';
$string['blockname'] = 'Booking system';
$string['bookingmoved'] = '您某一個預約已被移除';
$string['bookingmovedshort'] = '{$a->name} 已移至 {$a->newroom}';
$string['bookingmoveerrormessage'] = '{$a->name} (id: {$a->id})的移動有誤';
$string['bookingsfor'] = '預約';
$string['bookingsforpost'] = '未使用字串';
$string['booking_users'] = '可以進行預約的用戶';
$string['brief_description'] = '簡要說明.';
$string['browserlang'] = '你的瀏覽器設為';
$string['capacity'] = '容量';
$string['charset'] = 'utf-8';
$string['clashemailbody'] = '根據最新匯入的時間表，你的預約有個衝突：在 {$a->time}時, {$a->oldbooking} 及 {$a->newbooking}都訂了{$a->room}室。請自己解決此項問題，預先避開不必要的困擾。我們只提醒這個衝突一次。如您忽略此電郵，將為您產生問題。此訊息由預訂系統自動產生，如您認為您因錯誤而收到此電郵，請聯絡{$a->admin}';
$string['clashemailnotsent'] = '無法寄送email至老師';
$string['clashemailsent'] = 'email寄至';
$string['click_to_reserve'] = '點選格子進行預約登記';
$string['config_area_list_format2'] = '此區應以清單顯示或以下拉選單顯示?';
$string['config_dateformat'] = '日期格式';
$string['config_dateformat2'] = 'MRBS使用的日期格式';
$string['config_default_report_days'] = '報告期間(日)';
$string['config_default_report_days2'] = '預設報告期間(日)';
$string['config_default_view'] = '預設檢視';
$string['config_enable_periods'] = '使用週期';
$string['config_eveningends2'] = '一天結束的時間(以時計)。您必須取消週期方能使用此選項。';
$string['config_eveningends_min'] = '結束分鐘';
$string['config_eveningends_min2'] = '一天結束的時間(以分計)。您必須取消週期方能使用此選項。';
$string['config_highlight_method'] = '突顯方法';
$string['config_highlight_method2'] = '選擇某種突顯的方法：背景顏色，班級，或混合亦可。';
$string['config_mail_admin_on_bookings2'] = '以電郵通知管理者有一個新預約。';
$string['config_mail_admin_on_delete2'] = '以電郵通知管理者有刪除事宜。';
$string['config_mail_area_admin_on_bookings2'] = '以電郵通知區管理者有一個新預約。';
$string['config_mail_booker2'] = '以電郵通知規劃人有一個新預約。';
$string['config_mail_cc'] = '電郵副本給';
$string['config_mail_details'] = '郵件明細看';
$string['config_mail_from'] = '郵件來自於';
$string['config_refresh_rate'] = '頁面刷新時間';
$string['config_resolution'] = '時間區塊';
$string['config_resolution2'] = '時間區塊需加以規劃。週期必須取消方能使用此選項。';
$string['config_search_count'] = '每頁搜尋結果';
$string['config_timeformat'] = '時間格式';
$string['config_timeformat2'] = 'MRBS 使用之時間格式。';
$string['config_times_right_side2'] = '想將時間設定在右邊，以日及週的方式檢視，請設成Yes';
$string['config_view_week_number'] = '檢視第幾週';
$string['config_weeklength'] = '一週長度';
$string['config_weekstarts'] = '一週開始';
$string['config_weekstarts2'] = '選擇一週開始日期';
$string['confirmdel'] = '你確定要刪除此記錄??';
$string['conflict'] = '此時段已被預約';
$string['createdby'] = '預約人';
$string['ctrl_click'] = '用Control+滑鼠右鍵可以重覆選擇';
$string['ctrl_click_type'] = '使用Control-Click選取一個以上的類型';
$string['database'] = '資料庫';
$string['dayafter'] = '查看後一天';
$string['daybefore'] = '查看前一天';
$string['days'] = '天';
$string['delarea'] = '你必須先刪除所屬的會議室<p>';
$string['deleteentry'] = '刪除';
$string['deletefollowing'] = '這個動作會刪除相關的預約紀錄';
$string['deleteseries'] = '整批刪除';
$string['delete_user'] = '刪除使用者';
$string['dontshowoccupied'] = '勿出現刻正使用的房間';
$string['doublebookefailbody'] = '下列訊息未能寄給{$a}';
$string['doublebookesubject'] = '重覆預約通知';
$string['duration'] = '持續時間';
$string['editarea'] = '新增種類';
$string['editentry'] = '修改';
$string['editroom'] = '修改會議室/設備';
$string['editroomarea'] = '修改描述';
$string['editseries'] = '整批修改';
$string['edit_user'] = '編輯使用者';
$string['email_failed'] = '未能送出電郵';
$string['end_date'] = '結束時間';
$string['entries_found'] = '找到預約';
$string['entry'] = '登錄';
$string['entry_found'] = '找到預約';
$string['entryid'] = '登記序號';
$string['error_area'] = '錯誤: 區域';
$string['error_room'] = '錯誤: 會議室/設備';
$string['error_send_email'] = '錯誤: 送信給{$a}出了問題';
$string['eventbookingcreated'] = '已預約';
$string['eventbookingupdated'] = '預約已更新';
$string['external'] = '外部使用';
$string['failed_connect_db'] = '嚴重錯誤: 無法連上資料庫';
$string['failed_to_acquire'] = '無法存取資料庫';
$string['finishedimport'] = '處理完畢，共用時間: {$a}秒';
$string['for_any_questions'] = ',關於任何在這裡找不到答案的問題.';
$string['forciblybook'] = '強制預訂一間會議室';
$string['forciblybook2'] = '強制預約(自動移除其它預約)';
$string['fulldescription'] = '說明:<br>&nbsp;&nbsp;(聯約電話,部門,,<br>&nbsp;&nbsp;會議主題 等)';
$string['goto'] = '至';
$string['gotoroom'] = '至';
$string['gotothismonth'] = '這個月';
$string['gotothisweek'] = '至此週';
$string['gototoday'] = '查看今天';
$string['help_wildcard'] = '注意：使用%符號於所有文字方格中當萬用字元';
$string['highlight_line'] = '加強顯示這行';
$string['hours'] = '小時';
$string['hybrid'] = '混合';
$string['idontcare'] = '不管，重覆預約此會議室';
$string['importedbooking'] = '匯入預約';
$string['importedbookingmoved'] = '匯入預約(已編輯)';
$string['importlog'] = 'MRBS匯入記錄';
$string['in'] = '在';
$string['include'] = '包含';
$string['internal'] = '內部使用';
$string['invalid_booking'] = '預約無效';
$string['invalid_entry_id'] = '身份無效';
$string['invalid_search'] = '空的或不合法的搜尋字串.';
$string['invalid_series_id'] = '序列號錯誤.';
$string['mail_body_changed_entry'] = '一項目已被修正，明細於此。';
$string['mail_body_del_entry'] = '一項目已被刪除，明細於此。';
$string['mail_body_new_entry'] = '已預約一新項目，明細於此。';
$string['mail_changed_entry'] = '一項目已被修正，明細於此。';
$string['mail_deleted_entry'] = '一項目已被刪除，明細於此。';
$string['mail_subject'] = '主題';
$string['mail_subject_delete'] = '以下項目刪除{$a->date}, {$a->room} (booked by {$a->user})';
$string['mail_subject_entry'] = '以下項目變更{$a->date}, {$a->room} (booked by {$a->user})';
$string['mail_subject_newentry'] = '以下項目新增{$a->date}, {$a->room} (booked by {$a->user})';
$string['match_area'] = '符合的種類';
$string['match_descr'] = '符合全部簡述';
$string['match_entry'] = '符合部份的簡述';
$string['match_room'] = '符合部份的簡述';
$string['match_type'] = '符合的類型';
$string['mincapacity'] = '最小的容量';
$string['minutes'] = '分';
$string['month'] = '月';
$string['monthafter'] = '下一月';
$string['monthbefore'] = '上一月';
$string['movedto'] = '移至';
$string['mrbs'] = '會議室預約系統';
$string['mrbsadmin'] = 'MRBS管理者';
$string['mrbs:administermrbs'] = '修改MRBS會議室及設定';
$string['mrbs:doublebook'] = '重覆預約會議室';
$string['mrbs:editmrbs'] = '編輯MRBS預約';
$string['mrbseditor'] = 'MRBS 編輯器';
$string['mrbs:forcebook'] = '強制預約會議室(自動移除先前預約)';
$string['mrbs:myaddinstance'] = '新增MRBS區塊';
$string['mrbs:viewalltt'] = '檢視所有用戶時間表';
$string['mrbs:viewmrbs'] = '檢視MRBS預約';
$string['mustlogin'] = '要存取MRBS日曆區塊您須先登入MOODLE';
$string['namebooker'] = '預約人姓名';
$string['noarea'] = '還沒選擇種類';
$string['noareas'] = '沒有種類';
$string['norights'] = '您無權利修改此筆記錄!!';
$string['norooms'] = '沒有會議室/設備.';
$string['no_rooms_for_area'] = '這個種類沒有定義會議室/設備';
$string['not_found'] = '找不到';
$string['not_php3'] = 'WARNING: This probably doesn\'t work with PHP3';
$string['of'] = 'of';
$string['password_twice'] = 'If you wish to change the password, please type the new password twice';
$string['period'] = 'Period';
$string['periods'] = 'periods';
$string['please_contact'] = '請聯絡';
$string['ppreview'] = '預覽列印';
$string['records'] = '紀錄';
$string['rep_dsp'] = '顯示在報表';
$string['rep_dsp_dur'] = '持續時間';
$string['rep_dsp_end'] = '結束時間';
$string['repeat_id'] = '重覆序號';
$string['rep_end_date'] = '結束重覆的日期';
$string['rep_for_nweekly'] = '(每週)';
$string['rep_for_weekly'] = '(每週)';
$string['rep_freq'] = '頻率';
$string['rep_num_weeks'] = '重覆幾週';
$string['report_and_summary'] = '明細和加總';
$string['report_end'] = '報表結束日';
$string['report_on'] = '會議室報表';
$string['report_only'] = '只要明細';
$string['report_start'] = '報表起始日';
$string['rep_rep_day'] = '重覆的星期';
$string['rep_type'] = '重覆預約';
$string['rep_type_0'] = '不重覆';
$string['rep_type_1'] = '每天';
$string['rep_type_2'] = '每週';
$string['rep_type_3'] = '每月';
$string['rep_type_4'] = '每年';
$string['rep_type_5'] = '每月對應的日期';
$string['rep_type_6'] = '(每週)';
$string['returncal'] = '查看日程表';
$string['returnprev'] = '回前一頁';
$string['rights'] = '權限';
$string['room'] = '會議室/設備';
$string['room_admin_email'] = '會議室管理者email';
$string['rooms'] = '會議室/設備';
$string['sched_conflict'] = '時段衝突';
$string['search_for'] = '搜尋';
$string['search_results'] = '搜尋結果';
$string['seconds'] = '秒';
$string['show_my_entries'] = '顯示全部我的預約';
$string['sort_rep'] = '排序';
$string['sort_rep_time'] = '起始日/時';
$string['start_date'] = '起始時間';
$string['submitquery'] = '產生報表';
$string['sum_by_creator'] = '預約人';
$string['sum_by_descrip'] = '簡述';
$string['summarize_by'] = '加總項目';
$string['summary_header'] = '總共預約(小時)';
$string['summary_header_per'] = '總共預約(次)';
$string['summary_only'] = '只要加總';
$string['sure'] = '確定嗎?';
$string['system'] = '系統';
$string['through'] = '經由';
$string['too_may_entrys'] = '這個選擇造成太多輸入.<br>請重新選擇!';
$string['type'] = '種類';
$string['unknown'] = '未知的';
$string['update_area_failed'] = '更新區域失敗:';
$string['update_room_failed'] = '更新失敗:';
$string['useful_n-weekly_value'] = '可以提供預約的星期.';
$string['valid_room'] = '會議室/設備.';
$string['valid_time_of_day'] = '可以預約的時間.';
$string['viewday'] = '查看日期';
$string['viewmonth'] = '月顯示';
$string['viewweek'] = '週顯示';
$string['weekafter'] = '後一週';
$string['weekbefore'] = '前一週';
$string['weeks'] = '星期';
$string['you_are'] = '您是';
$string['you_have_not_entered'] = '你沒有輸入';
$string['you_have_not_selected'] = '你沒有選';
