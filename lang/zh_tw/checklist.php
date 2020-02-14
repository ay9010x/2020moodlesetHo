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
 * Strings for component 'checklist', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   checklist
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addcomments'] = '新增評論';
$string['additem'] = '新增';
$string['additemalt'] = '新增一項目到檢核表上';
$string['additemhere'] = '在這一個之後插入一新項目';
$string['addownitems'] = '新增您自己的項目';
$string['addownitems-stop'] = '不要再新增您自己的項目';
$string['allowmodulelinks'] = '允許模組連結';
$string['anygrade'] = '任何';
$string['autopopulate'] = '在檢核表中顯示課程模組';
$string['autopopulate_help'] = '這會自動增加現在課程中所有資源和活動的列表至清單上.<br />
每當您前往清單編輯頁面，此清單會自動更新有關課程的更改。<br />
點擊隱藏的選項，可以將選項從清單上隱藏<br />
要移除清單上的自動選項, 選擇 否 之後在編輯頁面中點擊 \'移除課程模組項目\'';
$string['autoupdate'] = '當模組完成後，標示為檢查過';
$string['autoupdate_help'] = '當您完成課程中的相關活動後會自動勾除清單上的項目<br />
每個活動的完成方式都是不一樣 - \'檢視\' 過資源、 \'完成\' 一個測驗或作業、 \'發表\' 一篇文章到討論區或參與聊天等<br />
如果對活動開啟了完成追踪的設定，會用記號勾出清單上的項目。<br />
如果想了解標記成 \'完成\' 的原因, 請詢問您的網站管理員查看檔案 \'mod/checklist/autoupdate.php\'<br />
注意：更新學生清單上的活動需要一段時間（當使用完成追踪）';
$string['autoupdatenote'] = '如果是“學生”標記會自動更新，如果是“只限老師”的檢核表就顯示不更新';
$string['autoupdatewarning_both'] = '此檢核表中有些項目在學生完成相關活動後即自動更新。但是，由於這是一個「學生與教師」的檢核表，進度桿只會在教師同意標記後更新。';
$string['autoupdatewarning_student'] = '此檢核表中有些項目在學生完成相關活動後即自動更新。';
$string['autoupdatewarning_teacher'] = '此檢核表的自動更新已設為開啟，但是這些記號不會顯示出來，只有"教師"的記號會顯示出來。';
$string['calendardescription'] = '此事件是件查表{$a}所新增';
$string['canceledititem'] = '取消';
$string['changetextcolour'] = '下個文字顏色';
$string['checkeditemsdeleted'] = '打勾之項目已被刪除';
$string['checklist'] = '檢核表';
$string['checklist:addinstance'] = '新增一檢核表';
$string['checklistautoupdate'] = '允許檢核表自動更新';
$string['checklist:edit'] = '新增並編輯檢核表';
$string['checklist:emailoncomplete'] = '接受完成之電郵';
$string['checklistfor'] = '檢核表：';
$string['checklistintro'] = '介紹';
$string['checklist:preview'] = '預覽檢核表';
$string['checklistsettings'] = '設定';
$string['checklist:updatelocked'] = '更新被鎖定之檢核表的標記';
$string['checklist:updateother'] = '更新學生的檢核表的標記';
$string['checklist:updateown'] = '更新你的檢核表的標記';
$string['checklist:viewmenteereports'] = '只查看受指導者的進度';
$string['checklist:viewreports'] = '查看學生的進度';
$string['checks'] = '檢查標記';
$string['comments'] = '評語';
$string['completionpercent'] = '應被檢查過的項目之百分比';
$string['completionpercentgroup'] = '需要檢查過';
$string['configchecklistautoupdate'] = '在允許使用這一功能之前，你必須對於Moodle的核心程式碼做一些更改，請參見 mod/checklist/README.txt 以了解詳情';
$string['configshowcompletemymoodle'] = '若這被取消勾選，那麼已完成的檢核表將不會出現在\'儀表板\'頁面上';
$string['configshowmymoodle'] = '若這被取消勾選，那麼檢核表活動(含進度列)將不再出現在\'儀表板\'頁面上';
$string['configshowupdateablemymoodle'] = '若勾選，那麼只有可更新的檢核表會被顯示在\'儀表板\'頁面上';
$string['confirmdeleteitem'] = '您確定要永遠刪除這一檢核表的項目嗎?';
$string['deleteitem'] = '刪除此項目';
$string['duedatesoncalendar'] = '添加截止日期到行事曆上';
$string['edit'] = '編輯檢核表';
$string['editchecks'] = '編輯檢查';
$string['editdatesstart'] = '編輯日期';
$string['editdatesstop'] = '停止編輯日期';
$string['edititem'] = '編輯這一項目';
$string['emailoncomplete'] = '當檢核表完成時電子郵件通知';
$string['emailoncompletebody'] = '用戶 {$a->user}已經完成了在 \'{$a->coursename}\'課程裡的檢核表\'{$a->checklist}\' 。
在此查看這檢核表：';
$string['emailoncompletebodyown'] = '您已經完成了在 \'{$a->coursename}\'課程裡的檢核表\'{$a->checklist}\' 。
在此查看這檢核表：';
$string['emailoncomplete_help'] = '當檢核表已經完成，系統會以電子郵件通知：(1)這位已完成的學生，(2)這一課程上的所有教師，(3)或兩者都通知。

管理員可以用權限 \'mod:checklist/emailoncomplete\' 來控制誰會收到通知，預設上，所有教師和非編輯教師有這一權限。';
$string['emailoncompletesubject'] = '用戶 {$a->user}已經完成了檢核表 \'{$a->checklist}\' 。';
$string['emailoncompletesubjectown'] = '您已經完成了檢核表 \'{$a->checklist}\'';
$string['eventchecklistcomplete'] = '檢核表已完成';
$string['eventeditpageviewed'] = '編輯頁面已檢視';
$string['eventreportviewed'] = '報告已檢視';
$string['eventstudentchecksupdated'] = '學生檢查已更新';
$string['eventteacherchecksupdated'] = '教師檢查已更新';
$string['export'] = '匯出項目';
$string['forceupdate'] = '更新所有會自動檢查的項目';
$string['gradetocomplete'] = '完成的分數：';
$string['guestsno'] = '您沒有權限去檢視這一檢核表';
$string['headingitem'] = '這一項目是標題---它旁邊不會有勾選方格';
$string['import'] = '匯入項目';
$string['importfile'] = '選擇檔案來匯入';
$string['importfromcourse'] = '整個課程';
$string['importfromsection'] = '當前單元';
$string['indentitem'] = '縮進項目';
$string['itemcomplete'] = '已完成';
$string['items'] = '檢核表項目';
$string['linktomodule'] = '連結到這一模組';
$string['lockteachermarks'] = '鎖住教師的標記';
$string['lockteachermarks_help'] = '若啟用，一旦教師儲存一個"是"的檢查結果，他們將無法更改它。只有具有\'mod/checklist:updatelocked\' 權限的用戶可以更改這檢查結果。';
$string['lockteachermarkswarning'] = '注意：一旦您已經儲存這些檢查結果，你將無法更改任何"是"的檢查結果。';
$string['modulename'] = '檢核表';
$string['modulename_help'] = '這檢核表模組可讓教師建立一個檢核清單/待完成事項/任務清單，提供他們的學生按部就班來執行。';
$string['modulenameplural'] = '檢核表';
$string['moveitemdown'] = '項目往下移';
$string['moveitemup'] = '項目往上移';
$string['noitems'] = '檢核表中沒有項目';
$string['optionalhide'] = '隱藏可任選的項目';
$string['optionalitem'] = '這一項目是可以任意選擇的';
$string['optionalshow'] = '顯示可任選的項目';
$string['percentcomplete'] = '必要的項目';
$string['percentcompleteall'] = '所有項目';
$string['pluginadministration'] = '檢核表管理';
$string['pluginname'] = '檢核表';
$string['preview'] = '預覽';
$string['progress'] = '進度';
$string['removeauto'] = '移除課程模組項目';
$string['report'] = '檢視進度';
$string['reporttablesummary'] = '表格會顯示每一位學生在檢核表上已經完成的項目';
$string['requireditem'] = '這一項目是必要的--它必須被完成';
$string['resetchecklistprogress'] = '重設檢核表進度和用戶項目';
$string['savechecks'] = '儲存';
$string['showcompletemymoodle'] = '在儀表板頁面上顯示已完成的檢核表';
$string['showfulldetails'] = '顯示全部細節';
$string['showhidechecked'] = '顯示/隱藏選擇的項目';
$string['showmymoodle'] = '在儀表板頁面顯示檢核表';
$string['showprogressbars'] = '顯示進度列';
$string['showupdateablemymoodle'] = '在儀表板頁面只顯示更新的檢核表';
$string['teacheralongsidecheck'] = '學生和老師';
$string['teachercomments'] = '老師可以加評論';
$string['teacherdate'] = '標記教師更新此項目的日期';
$string['teacheredit'] = '誰可以更改';
$string['teacherid'] = '最後更新此標記的教師';
$string['teachermarkno'] = '教師表示您尚未完成';
$string['teachermarkundecided'] = '老師尚未標記這個';
$string['teachermarkyes'] = '教師表示您已經完成';
$string['teachernoteditcheck'] = '限學生';
$string['teacheroverwritecheck'] = '限老師';
$string['theme'] = '檢核表顯示的布景';
$string['togglecolumn'] = '切換欄位';
$string['toggledates'] = '切換名稱和日期';
$string['togglerow'] = '切換行列';
$string['unindentitem'] = '縮進項目';
$string['updatecompletescore'] = '儲存完成的成績';
$string['updateitem'] = '更新';
$string['userdate'] = '標記用戶更新此項目的日期';
$string['useritemsallowed'] = '可以增加自己的項目';
$string['useritemsdeleted'] = '已刪除用戶的項目';
$string['view'] = '檢視檢核表';
$string['viewall'] = '檢視所有學生';
$string['viewallcancel'] = '取消';
$string['viewallsave'] = '儲存';
$string['viewsinglereport'] = '檢視這一用戶的進度';
$string['viewsingleupdate'] = '為這用戶更新進度';
$string['yesnooverride'] = '是，不能取代';
$string['yesoverride'] = '是，可以取代';
