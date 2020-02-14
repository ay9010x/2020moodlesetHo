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
 * Strings for component 'plagiarism_turnitin', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   plagiarism_turnitin
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allownonor'] = '允許提交任何檔案類型？';
$string['allownonor_help'] = '此設定將會允許提交任何檔案類型。當設定意選項為"是"，遞交在可能的情況下將會檢查其原創性、遞交在可能的情況下將可以下載及在可能的情況下可以使用線上評分（GradeMark）回饋工具。';
$string['anonblindmarkingnote'] = '注意： 已移除分隔Turnitin匿名評分設定。Turnitin將會使用Moodle的不記名評分設定來決定匿名評分設定';
$string['attachrubric'] = '在此作業附上紅字';
$string['attachrubricnote'] = '注意：學生將可以查看附上的紅字及提交前的內容。';
$string['because'] = '這是因為管理員已從等候隊列中刪除待定作業及中止提交到Turnitin。<br /><strong>檔案仍在Moodle中，請聯絡您的教師。</strong><br />請查看以下的如有的錯誤編碼：';
$string['changerubricwarning'] = '變更或分離紅字將會從作業的文稿中移除所有現有的紅字評分，包括已評核的評分卡。將會保留已評核作業的整體分數。';
$string['checkagainstnote'] = '注意： 如果您在以下"對證..."的選項中至少點選一個"是"，將不會生成任何原創性報告。';
$string['closebutton'] = '關閉';
$string['code'] = '編碼';
$string['compareinstitution'] = '將已提交檔案中的文稿對比在此機構中已提交的作業';
$string['config'] = '配置';
$string['configupdated'] = '已更新配置';
$string['connecttesterror'] = '連接Turnitin時發生錯誤，錯誤訊息為：<br />';
$string['course'] = '課程';
$string['cronsubmittedsuccessfully'] = '遞交：課程{$a->coursename}的作業 {$a->assignmentname} ，{$a->title} (TII ID: {$a->submissionid})已成功提交到Turnitin。';
$string['defaults'] = '默認設定';
$string['defaultsdesc'] = '以下設定是默認設定當在活動模組中開啟了Turnitin';
$string['defaultupdated'] = '已更新Turnitin默認設定';
$string['deleteconfirm'] = '您確定要刪除此提交？此動作將不能撤消。';
$string['deleted'] = '已刪除';
$string['deletesubmission'] = '刪除提交';
$string['digitalreceipt'] = '電子回條';
$string['digital_receipt_subject'] = '這是您的Turnitin電子回條';
$string['draftsubmit'] = '何時提交您的檔案至Turnitin？';
$string['erater'] = '啟用e-rater語法檢查';
$string['erater_categories'] = 'e-rater類別';
$string['erater_dictionary'] = 'e-rater字典';
$string['erater_dictionary_en'] = '包括美國及英國的英語字典';
$string['erater_dictionary_engb'] = '英國英語字典';
$string['erater_dictionary_enus'] = '美國的英語字典';
$string['erater_grammar'] = '語法';
$string['erater_handbook'] = 'ETS&複製; 手冊';
$string['erater_handbook_advanced'] = '進階';
$string['erater_handbook_elementary'] = '小學';
$string['erater_handbook_highschool'] = '中學';
$string['erater_handbook_learners'] = '英語學習者';
$string['erater_handbook_middleschool'] = '中學';
$string['erater_mechanics'] = '構成';
$string['erater_spelling'] = '拼寫';
$string['erater_style'] = '風格';
$string['erater_usage'] = '用法';
$string['errorcode0'] = '此檔案尚未提交到Turnitin，請詢問您的系統管理員';
$string['errorcode1'] = '此檔案尚未提交到Turnitin，因為沒有足夠內容生成原創性報告。';
$string['errorcode2'] = '此檔案尚未提交到Turnitin，因為超過了允許容量的上限{$a}';
$string['errorcode3'] = '此檔案尚未提交到Turnitin，因為用戶尚未同意Turnitin終端用戶的
許可協議。';
$string['errorcode4'] = '您必須上載可支援的檔案格式的作業。可接受的檔案格式包括：.doc, .docx, .ppt, .pptx, .pps, .ppsx, .pdf, .txt, .htm, .html, .hwp, .odt, .wpd, .ps and .rtf';
$string['errorcode5'] = '此檔案尚未提交到Turnitin，因為在生成Turnitin模組時出現了問題阻止了遞交作業，請詢問您的API日誌以取得更多資料。';
$string['errorcode6'] = '此檔案尚未提交到Turnitin，因為在編輯Turnitin模組時出現了問題阻止了遞交作業，請詢問您的API日誌以取得更多資料。';
$string['errorcode7'] = '此檔案尚未提交到Turnitin，因為在生成Turnitin用戶時出現了問題阻止了遞交作業，請詢問您的API日誌以取得更多資料。';
$string['errorcode8'] = '此檔案尚未提交到Turnitin，因為在生成Turnitin範本檔案時出現了問題。最大可能的原因是無效的檔案名稱，請重新命名您的檔案並使用編輯提交重新上載。';
$string['errorcode9'] = '不能提交此檔案，因為在文件庫中沒有可訪問內容用作提交';
$string['errors'] = '錯誤';
$string['excludebiblio'] = '排除參考書目';
$string['excludepercent'] = '百分率';
$string['excludequoted'] = '排除已引用材料';
$string['excludevalue'] = '排除較小的符合處';
$string['excludewords'] = '詞語';
$string['faultcode'] = '故障代碼';
$string['filedoesnotexist'] = '已刪除檔案';
$string['genduedate'] = '在截止日期生成報告（在截止日期前允許重新提交）';
$string['genimmediately1'] = '即時生成報告（不允許重新提交）';
$string['genimmediately2'] = '即時生成報告（在截止日期前允許重新提交）';
$string['genspeednote'] = '注意：生成重新提交的原創性報告會受到24小時延遲。';
$string['grademark'] = '線上評分（GradeMark）';
$string['id'] = 'ID';
$string['institutionalrepository'] = '機構庫（如適用）';
$string['internetcheck'] = '與互聯網對證';
$string['journalcheck'] = '與期刊、<br /> 刊物及成果發表對證';
$string['launchpeermarkmanager'] = '啟用同儕評鑑（Peermark）管理員';
$string['launchpeermarkreviews'] = '啟用同儕評鑑（Peermark）反饋';
$string['launchquickmarkmanager'] = '啟用快速評分（Quickmark）管理員';
$string['launchrubricmanager'] = '啟用紅字管理員';
$string['launchrubricview'] = '查看用作評分的紅字';
$string['line'] = '線';
$string['loadingdv'] = '載入Turnitin文件檢視器...';
$string['locked_message'] = '鎖定訊息';
$string['locked_message_default'] = '此設定已在網站層面上鎖定';
$string['locked_message_help'] = '如果任何設定已被鎖定，此訊息會顯示作說明。';
$string['message'] = '訊息';
$string['messageprovider:submission'] = 'Turnitin剽竊外掛程式電子回條的提示';
$string['module'] = '模組';
$string['norepository'] = '沒有資料庫';
$string['norubric'] = '沒有紅字';
$string['noscriptula'] = '（因為您沒有啟用javascript，您在同意Turnitin用戶協議後，遞交前需要手動重新載入此頁面）';
$string['notavailableyet'] = '不可用';
$string['notorcapable'] = '不可能為此檔案生成原創性報告。';
$string['otherrubric'] = '使用屬於其他教師的紅字';
$string['pending'] = '待定';
$string['pluginname'] = 'Turnitin剽竊外掛程式';
$string['pp_configuredesc'] = '您必須在Turnitintooltwo模組中設置此模組。請點擊<a href={$a}/admin/settings.php?section=modsettingturnitintooltwo>這裡</a>以設置此外掛程式。';
$string['ppcronsubmissionlimitreached'] = '沒有更多透過此cron執行的提交會在傳送至Turnitin，因為只有 {$a}會在每輪中處理。';
$string['pp_digital_receipt_message'] = '致{$a->firstname} {$a->lastname}，您在<strong>{$a->submission_date}</strong>已成功提交<strong>{$a->course_fullname}</strong>中的<strong>{$a->assignment_name}{$a->assignment_part}</strong>的<strong>{$a->submission_title}</strong> 的檔案。您的提交編號是<strong>{$a->submission_id}</strong>。您的完整電子回條可以在檔案檢視器中的列印／下載的按鈕中檢視和下載。<br /><br />感謝使用Turnitin，<br /><br /> Turnitin團隊';
$string['pperrorsdesc'] = '在嘗試上載以下檔案到Turnitin時出現了問題。要重新提交，選擇您想要重新提交的檔案並點擊重新提交的按鈕。這會在cron在下一輪執行時處理。';
$string['pperrorsfail'] = '您選擇的檔案有一些出現了問題，新的cron事件不能被創建。';
$string['pperrorssuccess'] = '您選擇的檔案已經重新提交並將會由cron處理。';
$string['ppeventsfailedconnection'] = '由於未能建立與Turnitin的連接，Turnitin剽竊外掛程式中使用的cron執行不會處理任何事件。';
$string['ppqueuesize'] = '在剽竊外掛程式中事件隊列中的事件數目';
$string['pp_submission_error'] = 'Turnitin就你的提交送回了錯誤訊息：';
$string['ppsubmissionerrorseelogs'] = '此檔案尚未提交到Turnitin，請詢問您的系統管理員。';
$string['ppsubmissionerrorstudent'] = '此檔案尚未提交至Turnitin，請詢問您的導師以取得更多資料。';
$string['reportgenspeed'] = '報告生成的速度';
$string['resubmitselected'] = '重新提交已選擇的檔案';
$string['resubmitting'] = '正在重新提交';
$string['resubmittoturnitin'] = '重新提交到Turnitin';
$string['saveusage'] = '儲存數據轉儲';
$string['semptytable'] = '沒有找到的結果。';
$string['sharedrubric'] = '共享紅字';
$string['showusage'] = '顯示數據轉儲';
$string['similarity'] = '相似度';
$string['spapercheck'] = '與已存學生文稿對比';
$string['standardrepository'] = '標準庫';
$string['student'] = '學生';
$string['student_notread'] = '學生尚未查看此文稿';
$string['student_read'] = '學生已檢視此文稿在：';
$string['studentreports'] = '給學生顯示原創性報告';
$string['studentreports_help'] = '允許您給學生用戶顯示原創性報告。如果您設定為「是」，學生將可以查看Turnitin生成的原創性報告。';
$string['submitondraft'] = '在第一次上載時提交檔案';
$string['submitonfinal'] = '在學生傳送評分時提交檔案';
$string['submitpapersto'] = '儲存學生文稿';
$string['submitpapersto_help'] = '此設定為教師提供選擇文稿是否儲存在Turnitin學生文稿庫的權限。提交文稿至學生文稿庫的好處是學生提交的作業可以與現時及過往班別中的學生文稿作對比。如果您選擇"沒有資料庫"，您的學生文稿將不會儲存在Turnitin學生文稿庫中。';
$string['tiiexplain'] = 'Turnitin是商業產品，您必須有付費訂閱才可使用此服務，更多資訊請到<a href=http://docs.moodle.org/en/Turnitin_administration>http://docs.moodle.org/en/Turnitin_administration</a>';
$string['tii_submission_failure'] = '請詢問您的導師或系統管理員以取得更多資料。';
$string['tiisubmissionsgeterror'] = '當嘗試從Turnitin中取得此作業的提交時出現了問題。';
$string['transmatch'] = '翻譯匹配';
$string['turnitin'] = 'Turnitin';
$string['turnitinconfig'] = 'Turnitin剽竊外掛配置';
$string['turnitindefaults'] = 'Turnitin剽竊外掛默認設定';
$string['turnitindeletionerror'] = '刪子Turnitin提交失敗。本地Moodle副本已被移除，但不能刪除在Turnitin的提交。';
$string['turnitin:enable'] = '啟用Turnitin';
$string['turnitinid'] = 'Turnitin ID';
$string['turnitinpluginsettings'] = 'Turnitin剽竊外掛設定';
$string['turnitinppulapost'] = '您的檔案尚未提交至Turnitin。請點擊此同意我們的最終用戶許可協議。';
$string['turnitinppulapre'] = '要提交檔案至Turnitin，您必須先同意我們的最終用戶許可協議。選擇不同意我們的最終用戶許可協議會只提交您的檔案到Moodle。點擊這裡以同意協議。';
$string['turnitinrefreshingsubmissions'] = '正重新載入提交';
$string['turnitinrefreshsubmissions'] = '重新載入提交';
$string['turnitinstatus'] = 'Turnitin狀態';
$string['turnitintooltwo'] = 'Turnitin工具';
$string['turnitin:viewfullreport'] = '查看原創性報告';
$string['useturnitin'] = '啟用Turnitin';
$string['useturnitin_mod'] = '啟用{$a}的Turnitin';
