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
 * Strings for component 'publication', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   publication
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['add_uploads'] = '新增檔案';
$string['allfiles'] = '所有檔案';
$string['allowedfiletypes'] = '允許的檔案類型 (,)';
$string['allowedfiletypes_err'] = '檢查輸入！無效的副檔名或分離器。';
$string['allowedfiletypes_help'] = '設定上載作業允許的檔案類型，以逗號分隔(,)，例如：txt, jpg.。如果沒有特定的檔案類型，留空該字段。過濾器不
區分大小寫，所以PDF與PDF相同。';
$string['allowsubmissionsanddescriptionfromdatesummary'] = '作業的詳細資料及遞交表格可在<strong>{$a}</strong>提供。';
$string['allowsubmissionsfromdate'] = '從';
$string['allowsubmissionsfromdateh'] = '用作上載或認可的時段';
$string['allowsubmissionsfromdateh_help'] = '您可以決定學生上載檔案及給予文件出版認可的時段。在這期間，學生可以編輯文件及收回文件出版認可。';
$string['allowsubmissionsfromdate_import'] = '認可由';
$string['allowsubmissionsfromdatesummary'] = '此作業會接受從<strong>{$a}</strong>提交';
$string['allowsubmissionsfromdate_upload'] = '可以從這裡上載';
$string['alwaysshowdescription'] = '總是顯示描述';
$string['alwaysshowdescription_help'] = '如果取消，學生只可在"允許提交日期"時可見上述的作業描述。';
$string['approval_timeover'] = '您可以在變更期間更改您的認可。';
$string['approveusers'] = '全部可見';
$string['assignment'] = '作業';
$string['assignment_help'] = '選擇您要匯入檔案的作業。現時未能支援小組作業因此不能選擇。';
$string['assignment_notfound'] = '未能找到要匯入檔案的作業。';
$string['assignment_notset'] = '未有選擇作業。';
$string['availability'] = '用作上載或認可的時段';
$string['choose'] = '請選擇...';
$string['configmaxbytes'] = '在學生文件夾中的所有檔案的預設最大容量';
$string['configmaxfiles'] = '允許每個用戶的預設附件最大數目。';
$string['configobtainstudentapproval'] = '檔案會在得到學生同意後可見。';
$string['configobtainteacherapproval'] = '默認設定所有人皆可看見學生的檔案。';
$string['configrequiremodintro'] = '如果您不想強制用戶為每個活動輸入
描述，取消此選頂。';
$string['courseuploadlimit'] = '課程上載限制';
$string['cutoffdate'] = '截止日期';
$string['cutoffdatefromdatevalidation'] = '截止日期必須在允許提交日期後。';
$string['cutoffdate_help'] = '如果設定，在沒有延期的情況下，將不會接受在此日期後提文的作業。';
$string['cutoffdate_import'] = '最後批准';
$string['cutoffdate_upload'] = '最後上傳的可能性';
$string['cutoffdatevalidation'] = '截止日期不能早於到期日';
$string['downloadall'] = '以壓縮形式下載所有檔案';
$string['duedate'] = '至';
$string['duedate_help'] = '這是當作業已經到期。亦會接受在此日期後提交的作業，但會標記為遲交。要防止在指定日期後的遞交－ 設定作業截止日期。';
$string['duedate_import'] = '認可';
$string['duedate_upload'] = '上載可能性';
$string['duedatevalidation'] = '到期日必須在允許提交日期後';
$string['edit_timeover'] = '檔案只能在可更改時段時修改';
$string['edit_uploads'] = '編輯／上載檔案';
$string['entiresperpage'] = '每頁顯示的參加者';
$string['extensionduedate'] = '延長到期日';
$string['extensionnotafterduedate'] = '延期日必須在到期日之後';
$string['extensionnotafterfromdate'] = '延期日必須在允許提交日期之後';
$string['extensionto'] = '延至';
$string['go'] = '前往';
$string['grantextension'] = '取得延期';
$string['guideline'] = '所有人可見：';
$string['hidden'] = '隱藏';
$string['hideidnumberfromstudents'] = '隱藏帳號編號';
$string['hideidnumberfromstudents_desc'] = '隱藏學生用的公開檔案表格中的帳號編號';
$string['importfrom_err'] = '您要選擇您想要匯入的作業';
$string['maxbytes'] = '附件容量的上限';
$string['maxfiles'] = '附件數目的上限';
$string['mode'] = '模式';
$string['mode_help'] = '選擇學生是否能夠上載文件夾中的檔案或作業中的檔案作為來源。';
$string['modeimport'] = '從作業中取得檔案';
$string['modeupload'] = '學生可以上載檔案';
$string['modulename'] = '學生的文件夾';
$string['modulename_help'] = '學生的文件夾提供以下特點：

＊參加者可以上載其他參加者即時可見的檔案或當您勾選了檔案並給予同意。
＊作業可以選作為學生文件夾的基本。教師可以決定作業中那一個檔案為所有參加者可視。教師亦可以讓學生決定那一個檔案為所有參加者可視。';
$string['modulenameplural'] = '學生文件夾';
$string['myfiles'] = '自己的檔案';
$string['name'] = '學生文件夾名稱';
$string['nofiles'] = '沒有可用檔案';
$string['nofilestozip'] = '沒有可壓縮的檔案';
$string['nopublicationsincourse'] = '此課程中沒有出版範本';
$string['nothingtodisplay'] = '沒有可顯示的條目';
$string['notice'] = '注意：';
$string['notice_importnoapproval'] = '以下的檔案為所有人可見';
$string['notice_importrequireapproval'] = '決定那一個檔案為所有人可見';
$string['notice_uploadnoapproval'] = '所有檔案會在上載後可見。教師保留隱藏已出版文件的權利。';
$string['notice_uploadrequireapproval'] = '所有檔案只會在教師查閱後可見';
$string['obtainstudentapproval'] = '取得認可';
$string['obtainstudentapproval_help'] = '決定會否取得學生同意：
<br><ul><li> 是 - 檔案會在得到學生同意後可見。教師可以選擇個人學生／文件以取得同意.</li><li>否 -不會透過Moodle取得學生認可。只有教師決定檔案是否可見</li></ul>';
$string['obtainteacherapproval'] = '從默認取得同意';
$string['obtainteacherapproval_help'] = '決定檔案會否在上載後馬上可見：<br><ul><li>檔案會否在上載後馬上可見</li><li> 否 - 檔案只會在得到教師同意後出版</li></ul>';
$string['optionalsettings'] = '選項';
$string['pluginadministration'] = '學生文件夾管理';
$string['pluginname'] = '學生文件夾';
$string['publication:addinstance'] = '新增學生文件夾';
$string['publication:approve'] = '決定檔未是否為所有學生可見';
$string['publication:grantextension'] = '取得延期';
$string['publication:upload'] = '上載文件至學生文件夾中';
$string['publication:view'] = '查看學生文件夾';
$string['publicfiles'] = '公開檔案';
$string['published_aftercheck'] = '否，只能在教師同意之後';
$string['published_immediately'] = '是，馬上，不用教師的同意';
$string['rejectusers'] = '所有人不可見';
$string['requiremodintro'] = '需要活動的描述';
$string['reset'] = '還原';
$string['resetstudentapproval'] = '重設狀態';
$string['reset_userdata'] = '所有資料';
$string['saveapproval'] = '儲存同意';
$string['save_changes'] = '儲存變更';
$string['savestudentapprovalwarning'] = '您確定要儲存這些更改？一旦已設定狀態，您將不可以改變';
$string['saveteacherapproval'] = '儲存同意';
$string['status'] = '狀態';
$string['studentapproval'] = '狀態';
$string['studentapproval_help'] = '這些狀態代表學生的同意回覆：

* ? - 未決定同意
* ✓ - 己同意
* ✖ - 不同意';
$string['student_approve'] = '同意';
$string['student_approved'] = '已同意';
$string['student_pending'] = '不可見（不同意）';
$string['student_reject'] = '拒絕';
$string['student_rejected'] = '已拒絕';
$string['teacherapproval'] = '同意';
$string['teacher_approved'] = '可見（已同意）';
$string['teacher_pending'] = '未決定確認';
$string['teacher_rejected'] = '已拒絕';
$string['updatefiles'] = '更新檔案';
$string['updatefileswarning'] = '當學生提交其作業時，在學生文件夾中的檔案將會更新。當刪除或重新載入檔案時，已設定為可見的學生檔案亦會被取代。
學生在可見性的設定將不會改變。';
$string['visibility'] = '所有人可見';
$string['visible'] = '可見';
$string['visibleforstudents'] = '所有可見';
$string['visibleforstudents_no'] = '此檔案為學生不可見';
$string['visibleforstudents_yes'] = '學生可見此檔案';
$string['warning_changefromobtainstudentapproval'] = '如果您進行此變更，只有您可以決定學生可以看見那一個檔案。將不會詢問學生的同意。所有標記成同意的檔案將不論學生的決定，一律變更為可見的。';
$string['warning_changefromobtainteacherapproval'] = '當啟動此設定，所有上載檔案將會變為參加者可見。所有上載會變為可見的。您可以手動設置檔案為部份學生不可見。';
$string['warning_changetoobtainstudentapproval'] = '當您進行此變更，所有標記為可見的檔案，將會詢問學生的同意。檔案只會在得到學生同意後設定為可見。';
$string['warning_changetoobtainteacherapproval'] = '當關閉設定，已上載的檔案將不會自動設置成其他參加者可見。您需要決定那一個檔案為可見。已設置為可見的檔案將會變為不可見。';
$string['withselected'] = '包括所選的...';
$string['zipusers'] = '以壓縮方式下載';
