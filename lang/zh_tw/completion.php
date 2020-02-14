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
 * Strings for component 'completion', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   completion
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['achievinggrade'] = '完成的成績';
$string['activities'] = '活動';
$string['activitiescompleted'] = '活動完成';
$string['activitiescompletednote'] = '備註:活動必須先設定完成條件，才能出現在以上的列表中。';
$string['activityaggregation'] = '要求的條件';
$string['activityaggregation_all'] = '所有選出要完成的活動';
$string['activityaggregation_any'] = '任何選出要完成的活動';
$string['activitycompletion'] = '活動完成度';
$string['aggregationmethod'] = '彙總方法';
$string['all'] = '全部';
$string['any'] = '任意';
$string['approval'] = '核准';
$string['badautocompletion'] = '當您選擇了自動完成時，您必續啟動至少一個條件(在下面)。';
$string['completed'] = '已完成';
$string['completedunlocked'] = '完成選項已經解鎖';
$string['completedunlockedtext'] = '儲存修改後，所有學生的完成狀態都會被刪除。如果您改變了主意，就不要儲存此表單。';
$string['completedwarning'] = '完成選項已經鎖定';
$string['completedwarningtext'] = '有{$a}位學生已經把這項活動標記為完成。若你改變這完成選項，將會刪除他們的完成狀態，並導致混淆。因此，這些選項已經被鎖定，且除非必要，請不要解除鎖定。';
$string['completion'] = '追蹤完成進度';
$string['completionactivitydefault'] = '使用活動的預設';
$string['completion-alt-auto-enabled'] = '系統根據條件"{$a}"標記此項為完成';
$string['completion-alt-auto-fail'] = '已完成：{$a} (不及格)';
$string['completion-alt-auto-n'] = '尚未完成：{$a}';
$string['completion-alt-auto-pass'] = '已經完成：{$a} (及格)';
$string['completion-alt-auto-y'] = '已經完成：{$a}';
$string['completion-alt-manual-enabled'] = '學生可以自己標記此項目為完成：{$a}';
$string['completion-alt-manual-n'] = '未完成：{$a} 。點選標記為完成。';
$string['completion-alt-manual-y'] = '已完成：{$a} 。點選標記為未完成。';
$string['completion_automatic'] = '當條件都滿足時，將活動標記完成';
$string['completiondefault'] = '預設追蹤完成進度';
$string['completiondisabled'] = '停用，不在活動設定頁面顯示';
$string['completionduration'] = '環境';
$string['completionenabled'] = '啟用，透過進度和活動設定來控制';
$string['completionexpected'] = '預計完成時間';
$string['completionexpected_help'] = '此選項用於設定此活動預期的完成日期。這個日期只顯示在活動進度報告中，不會顯示給學生。';
$string['completion-fail'] = '已完成(不及格)';
$string['completion_help'] = '如果啟用，將基於給定的條件，手動或自動追蹤活動的完成狀態。如果需要，可以設定多個條件，那麼只有所有條件都滿足時活動才被看作已完成。

在課程頁面，當活動已完成時，活動名稱後面會具有一個標記。';
$string['completionicons'] = '完成狀態勾選方格';
$string['completionicons_help'] = '活動名稱後面會有一個打勾，用來表示這活動是否已完成。

如果顯示的是一個虛線的方格，那麼當您依照教師所設的條件完成這一活動時，會自動出現一個打勾。

如果顯示的是一個實線的方格，那麼當您認為您已經完成這活動時，點選它可成打勾狀態(若你改變主意，再次點選可以取消打勾)。

這一打勾方式是可自行選用的，也是追蹤你在這課程的學習進度的簡單方法。';
$string['completion_manual'] = '學生可以手動標記此活動為完成';
$string['completionmenuitem'] = '完成';
$string['completion-n'] = '未完成';
$string['completion_none'] = '不標示活動完成狀態';
$string['completionnotenabled'] = '尚未啟用進度追蹤功能';
$string['completionnotenabledforcourse'] = '本課程尚未開啟進度追蹤功能';
$string['completionnotenabledforsite'] = '本站尚未開啟進度追蹤功能';
$string['completionondate'] = '日期';
$string['completionondatevalue'] = '用戶必須維持選課直到';
$string['completion-pass'] = '已經完成(及格)';
$string['completionsettingslocked'] = '完成設定已經鎖定';
$string['completion-title-manual-n'] = '標記為完成：{$a}';
$string['completion-title-manual-y'] = '標記為未完成：{$a}';
$string['completionusegrade'] = '必須要有成績';
$string['completionusegrade_desc'] = '學生必須取得成績才能完成此活動';
$string['completionusegrade_help'] = '如果啟動，此活動在學生取得成績時被標記為完成。如果活動設定了及格線，那麼會顯示及格或是不及格的圖示。';
$string['completionview'] = '必須瀏覽';
$string['completionview_desc'] = '學生必須瀏覽此活動，才能完成它';
$string['completion-y'] = '已經完成';
$string['configcompletiondefault'] = '當建立新活動時，完成進度追蹤的預設設定。';
$string['configenablecompletion'] = '當啟用後，您就可以在課程層級中開啟完成進度追蹤功能。';
$string['confirmselfcompletion'] = '確認後設為完成';
$string['courseaggregation'] = '需要的條件';
$string['courseaggregation_all'] = '要完成所有選出的課程';
$string['courseaggregation_any'] = '要完成任何選出的課程';
$string['coursealreadycompleted'] = '您已經完成了這門課程';
$string['coursecomplete'] = '課程進度';
$string['coursecompleted'] = '課程已經完成';
$string['coursecompletion'] = '課程完成進度';
$string['coursecompletioncondition'] = '條件:{$a}';
$string['coursegrade'] = '課程成績';
$string['coursesavailable'] = '可選的課程';
$string['coursesavailableexplaination'] = '備註：必須先設定課程完成條件，此課程才能出現在以上的列表中。';
$string['criteria'] = '規準';
$string['criteriagroup'] = '各種判斷條件';
$string['criteriarequiredall'] = '必須滿足以下所有條件';
$string['criteriarequiredany'] = '必須滿足以下任一條件';
$string['csvdownload'] = '以試算表格式(UTF-8.csv)下載';
$string['datepassed'] = '通過日期';
$string['days'] = '天數';
$string['daysoftotal'] = '{$a->days}天，總共有 {$a->total}天';
$string['deletecompletiondata'] = '刪除課程完成進度資料';
$string['dependencies'] = '依賴條件';
$string['dependenciescompleted'] = '完成其他課程';
$string['editcoursecompletionsettings'] = '編輯課程進度追蹤設定';
$string['enablecompletion'] = '啟用進度追蹤';
$string['enablecompletion_help'] = '若啟用，可以在活動設定上設定活動完成條件，且/或設定課程完成條件。';
$string['enrolmentduration'] = '選課期限';
$string['enrolmentdurationlength'] = '用戶必須維持選課';
$string['err_noactivities'] = '沒有任何活動啟用了完成訊息，所以什麼都不能顯示。您可以透過修改活動設定來啟用完成訊息。';
$string['err_nocourses'] = '沒有其他課程啟用課程進度追蹤功能，所以沒有可顯示的。您可以在課程設定中啟用課程進度追蹤功能。';
$string['err_nograde'] = '此課程還尚未設定及格分數線。要想起用這種策略，您必須先為此課程建立及格數線。';
$string['err_noroles'] = '課程中沒有任何角色
有“moodle/course:markcomplete”權限。';
$string['err_nousers'] = '課程或小組中沒有顯示完成訊息的學生。（預設狀況下，只顯示學生的完成訊息，所以沒有學生的話，您就會看到這個錯誤。管理員可以修改此選項。）';
$string['err_settingslocked'] = '因為至少有一位學生已經完成了一個規準，所以這些設定已經被鎖住。若你解開完成規準的設定，將會刪除所有現存的用戶資料，並造成困惑。';
$string['err_system'] = '進度追蹤系統內部發生錯誤。(系統管理員可以啟動除錯訊息來瀏覽更多細節。)';
$string['eventcoursecompleted'] = '課程完成追蹤';
$string['eventcoursecompletionupdated'] = '課程完成追蹤已更新';
$string['eventcoursemodulecompletionupdated'] = '課程模組完成已更新';
$string['excelcsvdownload'] = '用Excel相容模式(.csv)下載';
$string['fraction'] = '分數';
$string['graderequired'] = '要求的課程分數';
$string['gradexrequired'] = '至少得{$a}';
$string['inprogress'] = '處理中';
$string['manualcompletionby'] = '由他人手動標記完成';
$string['manualcompletionbynote'] = '注意：在課程中標記完成的權限，必須指派給這清單中的一個角色。';
$string['manualselfcompletion'] = '手動自我完成';
$string['manualselfcompletionnote'] = '注意：如果啟用"手動自我完成"，應該把"自我完成"區塊添加到這課程。';
$string['markcomplete'] = '標為完成';
$string['markedcompleteby'] = '由{$a}標記為完成';
$string['markingyourselfcomplete'] = '標記您自己為完成';
$string['moredetails'] = '更多細節';
$string['nocriteriaset'] = '本課程尚未設定完成條件';
$string['notcompleted'] = '未完成';
$string['notenroled'] = '您沒有加入此課程';
$string['nottracked'] = '你在這一課程上，現在沒被追蹤完成進度';
$string['notyetstarted'] = '尚未開始';
$string['overallaggregation'] = '完成課程所需要的條件';
$string['overallaggregation_all'] = '當所有條件都符合時，課程即完成';
$string['overallaggregation_any'] = '當任一條件符合時，課程即完成';
$string['pending'] = '等待中';
$string['periodpostenrolment'] = '選課後多久';
$string['progress'] = '學生進度';
$string['progress-title'] = '{$a->user}, {$a->activity}: {$a->state} {$a->date}';
$string['progresstotal'] = '進度：{$a->complete} / {$a->total}';
$string['recognitionofpriorlearning'] = '確認先修課程';
$string['remainingenroledfortime'] = '保持選課達到規定時間長度';
$string['remainingenroleduntildate'] = '在指定日期之前維持選課';
$string['reportpage'] = '顯示從{$a->from}到{$a->to}的用戶，共{$a->total}人';
$string['requiredcriteria'] = '必要條件';
$string['restoringcompletiondata'] = '寫入進度資料';
$string['roleaggregation'] = '需要的條件';
$string['roleaggregation_all'] = '當滿足這條件時，對所有選出的角色做標記。';
$string['roleaggregation_any'] = '當滿足這條件時，對任何選出的角色做標記。';
$string['roleidnotfound'] = '沒有找到角色編號{$a}';
$string['saved'] = '儲存';
$string['seedetails'] = '瀏覽細節';
$string['self'] = '自己';
$string['selfcompletion'] = '自我完成';
$string['showinguser'] = '顯示用戶';
$string['unenrolingfromcourse'] = '從課程中退選';
$string['unenrolment'] = '取消選課';
$string['unit'] = '單元';
$string['unlockcompletion'] = '解開追蹤完成進度的選項';
$string['unlockcompletiondelete'] = '解開追蹤完成進度的選項，並刪除用戶進度資料';
$string['usealternateselector'] = '使用替代的課程選擇器';
$string['usernotenroled'] = '用戶沒有選修此一課程';
$string['viewcoursereport'] = '查看課程報告';
$string['viewingactivity'] = '查看{$a}';
$string['writingcompletiondata'] = '寫入進度完成資料';
$string['xdays'] = '{$a}天';
$string['yourprogress'] = '您的進度';
