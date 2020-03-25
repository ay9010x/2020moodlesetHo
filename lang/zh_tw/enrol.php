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
 * Strings for component 'enrol', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actenrolshhdr'] = '可用的選課外掛套件';
$string['addinstance'] = '新增方式';
$string['addinstanceanother'] = '新增方法和建立另一個';
$string['ajaxnext25'] = '下25筆...';
$string['ajaxoneuserfound'] = '找到１個用戶';
$string['ajaxxusersfound'] = '找到{$a}個用戶';
$string['assignnotpermitted'] = '您在這一課程中，沒有權限或無法指派角色';
$string['bulkuseroperation'] = '批次用戶操作';
$string['configenrolplugins'] = '請選擇所有需要的外掛套件，然後作適當的順序排列。';
$string['custominstancename'] = '自訂實例名稱';
$string['defaultenrol'] = '添加實例到新的課程';
$string['defaultenrol_desc'] = '加入這個外掛預設會加入到所有的新的課程';
$string['deleteinstanceconfirm'] = '您即將要刪除選課方法"{$a->name}"。所有的{$a->users}位使用這一方式選課的用戶將會被退選，而任何與課程相關的資料，比如用戶的分數，群組身分或討論區的訂閱都會被刪除。

你確定你要繼續進行刪除？';
$string['deleteinstanceconfirmself'] = '您確定你想刪除例証{$a->名稱}，它本可允許您進入課程。可能接下來您不能再進入此課程。';
$string['deleteinstancenousersconfirm'] = '您確定要刪除選課方法"{$a->name}" ，你確定你要繼續進行刪除？';
$string['disableinstanceconfirmself'] = '您確定你想讓例証{$a->名稱}無效，它本可允許您進入課程。可能接下來您不能再進入此課程。';
$string['durationdays'] = '{$a}天';
$string['editenrolment'] = '編輯選課';
$string['enrol'] = '選修';
$string['enrolcandidates'] = '沒有選課的用戶';
$string['enrolcandidatesmatching'] = '對沒有選課的用戶進行配對';
$string['enrolcohort'] = '為校定班級群組進行選課';
$string['enrolcohortusers'] = '為用戶進行選課';
$string['enrollednewusers'] = '成功地為{$a}位新用戶加選';
$string['enrolledusers'] = '已經選課的用戶';
$string['enrolledusersmatching'] = '對已經選課的用戶進行配對';
$string['enrolme'] = '為我加選這一課程';
$string['enrolmentinstances'] = '選課方式';
$string['enrolmentnew'] = '在{$a}中新選課的';
$string['enrolmentnewuser'] = '{$a->user}已經選修"{$a->course}"這個課程';
$string['enrolmentoptions'] = '選課的選項';
$string['enrolments'] = '選課';
$string['enrolnotpermitted'] = '您沒有權限，或不被允許將某人加入這一課程中';
$string['enrolperiod'] = '選課期限';
$string['enroltimecreated'] = '選課已經建立';
$string['enroltimeend'] = '選課結束';
$string['enroltimestart'] = '選課開始';
$string['enrolusage'] = '實例/選課';
$string['enrolusers'] = '將用戶加入課程';
$string['enrolxusers'] = '註冊{$a}個用戶';
$string['errajaxfailedenrol'] = '無法將用戶加入課程';
$string['errajaxsearch'] = '當搜尋用戶的時候，發生錯誤';
$string['erroreditenrolment'] = '當嘗試編輯一用戶的選課時，發生錯誤';
$string['errorenrolcohort'] = '在此課程中建立校定班級群組同步選課實例時，發生錯誤';
$string['errorenrolcohortusers'] = '在這一課程中為校定班級群組成員進行選修時，發生錯誤';
$string['errorthresholdlow'] = '通知的門檻必須至少一天';
$string['errorwithbulkoperation'] = '在處理您的大批次選課時發生錯誤';
$string['eventenrolinstancecreated'] = '選課實例已建立';
$string['eventenrolinstancedeleted'] = '選課實例已刪除';
$string['eventenrolinstanceupdated'] = '選課實例已更新';
$string['eventuserenrolmentcreated'] = '已選修課程的用戶';
$string['eventuserenrolmentdeleted'] = '從課程中退選的用戶';
$string['eventuserenrolmentupdated'] = '已更新用戶退選';
$string['expirynotify'] = '在選課過期之前發出通知';
$string['expirynotifyall'] = '負責選課者和已選課的用戶';
$string['expirynotifyenroller'] = '負責選課者';
$string['expirynotify_help'] = '設定是否要發出選課過期的通知簡訊';
$string['expirynotifyhour'] = '在幾小時前，發出選課過期通知';
$string['expirythreshold'] = '發出通知的門檻';
$string['expirythreshold_help'] = '在選課過期之前多久，要通知用戶？';
$string['extremovedaction'] = '外部的退選動作';
$string['extremovedaction_help'] = '當用戶的選課從外部選課資源中消失時，
請選擇要執行的動作。

請注意，在退選的過程中，一些用戶資料和設定會被清除掉。';
$string['extremovedkeep'] = '保留用戶的選修';
$string['extremovedsuspend'] = '關閉課程選修';
$string['extremovedsuspendnoroles'] = '關閉課程選修並移除角色';
$string['extremovedunenrol'] = '將用戶從課程中退選';
$string['finishenrollingusers'] = '完成選課的用戶';
$string['foundxcohorts'] = '找到{$a}同級生';
$string['instanceadded'] = '已添加方法';
$string['instanceeditselfwarning'] = '警告：';
$string['instanceeditselfwarningtext'] = '您已註冊此課程，這個變動可能影響你存取此課程的方式。';
$string['invalidenrolinstance'] = '無效的選課實例';
$string['invalidrole'] = '無效的角色';
$string['manageenrols'] = '管理選課模組';
$string['manageinstance'] = '管理';
$string['migratetomanual'] = '移至手動註冊方式。';
$string['nochange'] = '沒有變更';
$string['noexistingparticipants'] = '不存在任何的參與者';
$string['nogroup'] = '沒有群組';
$string['noguestaccess'] = '訪客無法存取這一課程，請登入。';
$string['none'] = '無';
$string['notenrollable'] = '您不能自行選修這門課';
$string['notenrolledusers'] = '其他用戶';
$string['otheruserdesc'] = '以下的用戶沒有選修這一課程，但是在課程內確實有繼承的或被指派的角色。';
$string['participationactive'] = '活動';
$string['participationstatus'] = '狀態';
$string['participationsuspended'] = '休學(停權)';
$string['periodend'] = '到 {$a}';
$string['periodnone'] = '選課的{$a}';
$string['periodstart'] = '從 {$a}';
$string['periodstartend'] = '從{$a->start} 到{$a->end}';
$string['proceedtocourse'] = '繼續執行課程內容';
$string['recovergrades'] = '若可能的話，還原用戶的舊分數';
$string['rolefromcategory'] = '{$a->role}(從課程類別繼承的)';
$string['rolefrommetacourse'] = '{$a->role}(從父課程繼承的)';
$string['rolefromsystem'] = '{$a->role}(在網站層次被指派的)';
$string['rolefromthiscourse'] = '{$a->role}(在這門課程被指派的)';
$string['startdatetoday'] = '今日';
$string['synced'] = '資料已經同步';
$string['testsettings'] = '測試設定';
$string['testsettingsheading'] = '測試註冊之設定方式 {$a}。';
$string['totalenrolledusers'] = '{$a}位已經選課的用戶';
$string['totalotherusers'] = '{$a}位其它用戶';
$string['unassignnotpermitted'] = '您沒有權限在這一課程中取消角色';
$string['unenrol'] = '退出課程';
$string['unenrolconfirm'] = '您確定要將用戶 "{$a->user}" 從課程 "{$a->course}"中退選?';
$string['unenrolme'] = '將我從 {$a} 課程中退選';
$string['unenrolnotpermitted'] = '您沒有權限或是無法將此用戶從這門課程中退選';
$string['unenrolroleusers'] = '將用戶退選';
$string['uninstallmigrating'] = '遷移"{$a}"選課';
$string['unknowajaxaction'] = '未知的活動要求';
$string['unlimitedduration'] = '沒有限制';
$string['usersearch'] = '搜尋';
$string['withselectedusers'] = '與被選出的用戶';
