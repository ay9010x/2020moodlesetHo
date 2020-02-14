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
 * Strings for component 'tool_uploadcourse', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_uploadcourse
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowdeletes'] = '允許刪除';
$string['allowdeletes_help'] = '是否接受刪除欄位';
$string['allowrenames'] = '允許重新命名';
$string['allowrenames_help'] = '是否接受重新命名欄位';
$string['allowresets'] = '允許重設';
$string['allowresets_help'] = '是否接受重設欄位';
$string['cachedef_helper'] = '輔助說明快取';
$string['cannotdeletecoursenotexist'] = '不能刪除一個不存在的課程';
$string['cannotgenerateshortnameupdatemode'] = '當允許更新時，不能產生簡稱';
$string['cannotreadbackupfile'] = '無法讀取備份檔';
$string['cannotrenamecoursenotexist'] = '無法重新命名一個不存在的課程';
$string['cannotrenameidnumberconflict'] = '無法重新命名這課程，這編號與現有課程相衝突';
$string['cannotrenameshortnamealreadyinuse'] = '無法重新命名這課程，這簡稱已經被使用';
$string['cannotupdatefrontpage'] = '禁止修改首頁';
$string['canonlyrenameinupdatemode'] = '只有允許更新時才能重新命名一課程';
$string['canonlyresetcourseinupdatemode'] = '只有在更新模式時才能重設一課程';
$string['couldnotresolvecatgorybyid'] = '不能以ID來決定類別';
$string['couldnotresolvecatgorybyidnumber'] = '不能以ID編號來決定類別';
$string['couldnotresolvecatgorybypath'] = '不能以路徑來決定類別';
$string['coursecreated'] = '課程已經建立';
$string['coursedeleted'] = '課程已經刪除';
$string['coursedeletionnotallowed'] = '不允許刪除課程';
$string['coursedoesnotexistandcreatenotallowed'] = '這課程不存在，且不允許建立課程';
$string['courseexistsanduploadnotallowed'] = '這課程存在，且不允許更新';
$string['coursefile'] = '檔案';
$string['coursefile_help'] = '這檔案必須是一個CSV檔';
$string['courseidnumberincremented'] = '課程編號遞增{$a->from} -> {$a->to}';
$string['courseprocess'] = '課程處理';
$string['courserenamed'] = '課程已重新命名';
$string['courserenamingnotallowed'] = '不允許課程重新命名';
$string['coursereset'] = '課程重設';
$string['courseresetnotallowed'] = '現在允許課程重設';
$string['courserestored'] = '課程已經還原';
$string['coursescreated'] = '課程已建立：{$a}';
$string['coursesdeleted'] = '課程已刪除：{$a}';
$string['courseserrors'] = '課程錯誤：{$a}';
$string['courseshortnamegenerated'] = '課程簡稱已產生：{$a}';
$string['courseshortnameincremented'] = '課程簡稱遞增 {$a->from} -> {$a->to}';
$string['coursestotal'] = '課程總數：{$a}';
$string['coursesupdated'] = '課程已更新：{$a}';
$string['coursetemplatename'] = '在上傳之後，從這一課程還原';
$string['coursetemplatename_help'] = '輸入一個現有的課程簡稱來當作樣板使用';
$string['coursetorestorefromdoesnotexist'] = '要還原的課程的來源不存在';
$string['courseupdated'] = '課程已更新';
$string['createall'] = '建立全部，若需要的話，增加簡稱';
$string['createnew'] = '建立新課程或更新現有的';
$string['createorupdate'] = '建立新課程或更新現有的';
$string['csvdelimiter'] = 'CSV分隔符號';
$string['csvdelimiter_help'] = 'CSV檔案的CSV分隔符號';
$string['csvfileerror'] = '這CSV檔的格式有些問題。請檢查標題的數目和欄位是否符合，且分隔符號和檔案編碼是否正確：{$a}';
$string['csvline'] = '行';
$string['defaultvalues'] = '預設的課程值';
$string['encoding'] = '編碼';
$string['encoding_help'] = '這CSV檔案的編碼';
$string['errorwhiledeletingcourse'] = '在刪除這課程時發生錯誤';
$string['errorwhilerestoringcourse'] = '在還原這課程時發生錯誤';
$string['generatedshortnamealreadyinuse'] = '這產生的簡稱已經被使用';
$string['generatedshortnameinvalid'] = '這產生的簡稱是無效的';
$string['id'] = '編號';
$string['idnumberalreadyinuse'] = '編號已經被其他課程所使用';
$string['importoptions'] = '匯入的選項';
$string['invalidbackupfile'] = '無效的備份檔';
$string['invalidcourseformat'] = '無效的課程格式';
$string['invalidcsvfile'] = '無效的輸入CSV檔案';
$string['invalidencoding'] = '無效的編碼';
$string['invalideupdatemode'] = '選出的更新模式無效';
$string['invalidmode'] = '選出的模式無效';
$string['invalidroles'] = '無效的角色名稱：{$a}';
$string['invalidshortname'] = '無效的簡稱';
$string['missingmandatoryfields'] = '必填欄位{$a}的遺漏值';
$string['missingshortnamenotemplate'] = '缺少簡稱，而且沒有設定簡稱樣版';
$string['mode'] = '上傳模式';
$string['mode_help'] = '這讓您指定課程逢否被建立和/或被更新';
$string['nochanges'] = '沒有更改';
$string['pluginname'] = '課程上傳';
$string['preview'] = '預覽';
$string['reset'] = '上傳之後重設課程';
$string['reset_help'] = '在建立/更新之後，是否重設課程';
$string['restoreafterimport'] = '匯入後還原';
$string['result'] = '結果';
$string['rowpreviewnum'] = '預覽幾行';
$string['rowpreviewnum_help'] = '在預覽下一頁時，可以看到這CSV檔的幾行。這選項是用來限制下一頁的大小。';
$string['shortnametemplate'] = '用來產生一個簡稱的樣版';
$string['shortnametemplate_help'] = '在預覽下一頁時，可以看到這CSV檔的幾行。這選項是用來限制下一頁的大小。';
$string['templatefile'] = '在上傳之後，從這一檔案還原';
$string['templatefile_help'] = '選擇一個檔案來當作樣版，用來建立所有的課程';
$string['unknownimportmode'] = '不知的匯入模式';
$string['updatemissing'] = '用CSV資料和預設來填滿少掉的項目';
$string['updatemode'] = '更新模式';
$string['updatemodedoessettonothing'] = '更新模式不允許任何東西被更新';
$string['updatemode_help'] = '若您允許課程被更新，您也需要告訴工具要以什麼來更新。';
$string['updateonly'] = '只更新現有的課程';
$string['updatewithdataonly'] = '只以CSV資料來更新';
$string['updatewithdataordefaults'] = '以CSV資料和預設來更新';
$string['uploadcourses'] = '批次建立課程';
$string['uploadcourses_help'] = '課程可以經由文字檔上傳。檔案格式應該像下面：

* 檔案中每一行包含一筆紀錄。

* 每一筆紀錄是一系列資料，以逗點(或其他分隔符號)相互隔開。

* 第一筆紀錄包含欄位名稱的列表，也界定了這檔案其他部分的格式。

* 一定要的欄位名稱是:課程簡稱、課程全名、和類別。';
$string['uploadcoursespreview'] = '批次建課程的預覽';
$string['uploadcoursesresult'] = '批次建課程的結果';
