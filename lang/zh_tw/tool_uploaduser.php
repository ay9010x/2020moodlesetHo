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
 * Strings for component 'tool_uploaduser', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_uploaduser
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowdeletes'] = '允許刪除';
$string['allowrenames'] = '允許更名';
$string['allowsuspends'] = '允許帳號的休學和復學';
$string['assignedsysrole'] = '指派的系統角色{$a}';
$string['csvdelimiter'] = 'CSV 分隔符號';
$string['defaultvalues'] = '預設值';
$string['deleteerrors'] = '刪除錯誤';
$string['encoding'] = '編碼';
$string['errormnetadd'] = '無法添加遠端用戶';
$string['errors'] = '錯誤';
$string['nochanges'] = '不變更';
$string['pluginname'] = '用戶上傳';
$string['renameerrors'] = '修改名稱時發生錯誤';
$string['requiredtemplate'] = '必需的，您可使用此範列語法( (%l = 姓, %f = 名, %u = 使用者名稱)。細節及實例，請參見說明。';
$string['rowpreviewnum'] = '預覽資料筆數';
$string['unassignedsysrole'] = '指派的系統角色{$a}';
$string['uploadpicture_baduserfield'] = '指定的使用者屬性無效，請重試。';
$string['uploadpicture_cannotmovezip'] = '無法搬移zip檔案到暫存目錄。';
$string['uploadpicture_cannotprocessdir'] = '無法處理解壓縮的檔案。';
$string['uploadpicture_cannotsave'] = '無法儲存用戶{$a}的圖片，請檢查原來的圖片檔案。';
$string['uploadpicture_cannotunzip'] = '無法解壓縮圖片檔';
$string['uploadpicture_invalidfilename'] = '圖片檔{$a}的檔名中有無效的字元，略過。';
$string['uploadpicture_overwrite'] = '覆寫現有的用戶相片';
$string['uploadpictures'] = '上傳用戶相片';
$string['uploadpictures_help'] = '用戶照片可以將所有圖檔壓縮成一個zip檔後上傳。

"圖檔名稱"要與"用戶名稱"相一致，例如，若有一位學生申請帳號時，以Paul_Ou為用戶名稱 ，那它的照片的圖檔要取名為Paul_Ou.jpg。';
$string['uploadpicture_userfield'] = '配合圖片使用的用戶屬性：';
$string['uploadpicture_usernotfound'] = '具有\'{$a->uservalue}\' 的 \'{$a->userfield}\' 不存在，略過。';
$string['uploadpicture_userskipped'] = '略過用戶{$a} (已經有圖片了)';
$string['uploadpicture_userupdated'] = '用戶{$a}圖片已更新';
$string['uploadusers'] = '批次建立用戶';
$string['uploadusers_help'] = '<p align="center"><b>上傳用戶</b></p>
<p>首先，要注意，<strong>通常是沒有必要大量上傳用戶的</strong> - 要讓您自己的維護工作比較輕鬆，首先應當看一下是否有合適的無需手工維護的認證模式，
如鏈結到已有的外部資料庫或讓用戶自己創建他們的帳號。請看一下管理功能表中的認證部分。</p>
<p>如果您確定要從文字檔案中匯入眾多用戶帳號時，您應當將該檔以下述格式儲存：</p>
<ul>
  <li>檔案中的每一行只能包含一筆記錄</li>
  <li>每筆記錄都是一系列以逗號分隔的資料</li>
  <li>檔案的第一條記錄很特殊，它包含著欄位名稱的列表。這定義了檔案中剩下部分的格式。
    <blockquote>
    <p><strong>必需的欄位名：</strong>這些欄位名必須包含在第一條記錄中，並且每個用戶都需要有。</p>
      <p></p>
      <font color="#990000" face="Courier New, Courier, mono">username, password, firstname, lastname, email</font></p>
</p>
      <p><strong>預設的欄位名：</strong>這些是可選的——如果並未包含它們，則會使用主管理員的值作為預設值。</p>
      <p><font color="#990000" face="Courier New, Courier, mono">institution, department, city, country, lang, auth, timezone</font> </p>
      <p><strong>可選的欄位名：</strong>所有這些都是可選的。課程名稱是課程的“簡稱”——如果存在，則用戶會自動選該課程。小組名稱是和課程對應的，例如course1中的group1。</p>
      <p> <font color="#990000" face="Courier New, Courier, mono">idnumber, icq, phone1, phone2, address, url, description, mailformat, maildisplay, htmleditor, autosubscribe, course1, course2, course3, course4, course5, group1, group2, group3, group4, group5</font></p>
    </blockquote>
    </li>
  <li>資料中的逗號應當重新編碼為&amp;#44——系統會自動將其解碼。</li>
  <li>對於布林欄位，是用0表示假、1表示真。</li>
  <li>注意：如果用戶已經在用戶資料庫中註冊，系統會返回用戶已有的 userid 號(資料庫中的索引)，以及將他加入到相應的課程中，但不會更新其他的資料。</li>
</ul>


<p>以下是一個合法的匯入檔：</p>
<p><font size="-1" face="Courier New, Courier, mono">username, password, firstname, lastname, email, lang, idnumber, maildisplay, course1, group1<br />
jonest, verysecret, Tom, Jones, jonest@someplace.edu, en, 3663737, 1, Intro101, Section 1<br />
reznort, somesecret, Trent, Reznor, reznort@someplace.edu, en_us, 6736733, 0, Advanced202, Section 3</font></p>
　　';
$string['uploaduserspreview'] = '預覽上傳的使用者帳號';
$string['uploadusersresult'] = '批次建立用戶的結果';
$string['uploaduser:uploaduserpictures'] = '上傳用戶照片';
$string['useraccountupdated'] = '已更新的用戶';
$string['useraccountuptodate'] = '用戶帳號已是最新的';
$string['userdeleted'] = '已刪除的用戶';
$string['userrenamed'] = '已改名的用戶';
$string['userscreated'] = '已建立的用戶';
$string['usersdeleted'] = '已刪除的用戶';
$string['usersrenamed'] = '已修改名稱的用戶';
$string['usersskipped'] = '忽略過的用戶';
$string['usersupdated'] = '已更新的用戶';
$string['usersweakpassword'] = '用戶使用不夠安全的密碼';
$string['uubulk'] = '選擇已進行大批用戶的處理';
$string['uubulkall'] = '所有用戶';
$string['uubulknew'] = '新用戶';
$string['uubulkupdated'] = '已更新的用戶';
$string['uucsvline'] = 'CSV列';
$string['uulegacy1role'] = '(原學生) typeN=1';
$string['uulegacy2role'] = '(原教師) typeN=2';
$string['uulegacy3role'] = '(原助理教師) typeN=3';
$string['uunoemailduplicates'] = '防止電子郵件信箱重覆';
$string['uuoptype'] = '上傳類型';
$string['uuoptype_addinc'] = '全部加入，如果需要，在使用者名稱後加入計數';
$string['uuoptype_addnew'] = '只加入新的，略過已存在的使用者';
$string['uuoptype_addupdate'] = '加入新的並且更新已存在的使用者';
$string['uuoptype_update'] = '只更新已存在的使用者';
$string['uupasswordcron'] = 'Cron所用的密碼';
$string['uupasswordnew'] = '新的用戶密碼';
$string['uupasswordold'] = '現在的用戶密碼';
$string['uustandardusernames'] = '標準用戶名稱';
$string['uuupdateall'] = '以檔案及預設值覆寫';
$string['uuupdatefromfile'] = '以檔案覆寫';
$string['uuupdatemissing'] = '填寫缺少的檔案及預設值';
$string['uuupdatetype'] = '現有的用戶詳細資料';
$string['uuusernametemplate'] = '用戶名稱樣板';
