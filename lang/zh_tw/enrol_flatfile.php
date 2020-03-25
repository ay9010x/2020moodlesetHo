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
 * Strings for component 'enrol_flatfile', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_flatfile
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['encoding'] = '檔案編碼中';
$string['expiredaction'] = '註冊終止';
$string['expiredaction_help'] = '當使用者註冊終止時，選出該執行之';
$string['filelockedmail'] = '您所使用註冊課程的文字檔({$a}) 無法被排程程序刪除,這通常是權限上的錯誤,請修正權限,讓moodle可以刪除這個檔案,否則它會被一直重複處理';
$string['filelockedmailsubject'] = '重大錯誤: 註冊檔';
$string['flatfile:manage'] = '以手動方式管理使用者的註冊';
$string['flatfilesync'] = '以純文字檔同步選課';
$string['flatfile:unenrol'] = '以手動方式將已註冊之使用者移出課程';
$string['location'] = '檔案位置';
$string['location_desc'] = '指定註冊之完整路徑。這個檔案之後會自動被刪除';
$string['mapping'] = '文字檔角色對應';
$string['messageprovider:flatfile_enrolment'] = '一般檔案對應選課資訊';
$string['notifyadmin'] = '指出管理者';
$string['notifyenrolled'] = '指出已註冊之使用者';
$string['notifyenroller'] = '通知註冊之使用者';
$string['pluginname'] = '一般檔案(CSV)';
$string['pluginname_desc'] = '這一方法是讓系統在你所指定的地方重複地檢查並處理一個特定格式的文字檔。
這檔案格式是每一行包含有以逗點隔開的四到六個欄位：

    operation, role, user idnumber, course idnumber [, starttime [, endtime]]

其意義是:

* operation - add | del
* role - student | teacher | teacheredit
* user idnumber - 在用戶資料表的編號， NB 代表無編號
* course idnumber - 在課程資料表的編號， NB 代表無編號
* starttime - 開始時間 (從紀元開始以秒計) - 可有可無
* endtime - 結束時間 (從紀元開始以秒計) - 可有可無l

它看起來像是這樣：
<pre class="informationbox">
   add, student, 5, CF101
   add, teacher, 6, CF101
   add, teacheredit, 7, CF101
   del, student, 8, CF101
   del, student, 17, CF101
   add, student, 21, CF101, 1091115000, 1091215000
</pre>';
