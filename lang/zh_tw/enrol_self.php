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
 * Strings for component 'enrol_self', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_self
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['canntenrol'] = '選課已被關閉或過期';
$string['canntenrolearly'] = '你還不能選課；選課開始於{$a}';
$string['canntenrollate'] = '你已經不能選課，因為選課已結束於{$a}';
$string['cohortnonmemberinfo'] = '只有校定班級群組\'{$a}\'的成員可以自行選課';
$string['cohortonly'] = '只有校定班級群組成員';
$string['cohortonly_help'] = '教師可以限制為只有指定的校定班級群組的成員才自行選課。注意，改變這一設定對於已經選課的用戶沒有影響。';
$string['customwelcomemessage'] = '自訂歡迎訊息';
$string['customwelcomemessage_help'] = '自訂的歡迎訊息可以以純文字加入，或是用Moodle自動格式，包含Html標籤及多語系標籤。

訊息中可以包含以下佔位符號的訊息：

*課程名稱{$a->coursename}
*連接到用戶的個人資料頁面{$a->profileurl}
*用戶的電子郵件 {$a->email}
*用戶的完整姓名{$a->fullname}';
$string['defaultrole'] = '預設的角色指派';
$string['defaultrole_desc'] = '選擇當用戶自行選課時，被指派的角色';
$string['enrolenddate'] = '結束日期';
$string['enrolenddate_help'] = '如果啟用，用戶只能在此日期前自行加入此課程。';
$string['enrolenddaterror'] = '選課的結束日期不可以早於開始日期。';
$string['enrolme'] = '將我加入';
$string['enrolperiod'] = '選課期間';
$string['enrolperiod_desc'] = '預設的選課期間有效的時間長度(以秒為單位）。如果設定為0，就預設不限制報名時間長度。';
$string['enrolperiod_help'] = '選課有效的時間長度，從用戶自行加入課程的那一刻算起。若停用此項，表示選課期間沒有限制。';
$string['enrolstartdate'] = '開始日期';
$string['enrolstartdate_help'] = '若啟用，用戶只能在此日期之後自行加入此課程。';
$string['expiredaction'] = '選課過期的動作';
$string['expiredaction_help'] = '選擇選課過期後所要進行的動作。請注意，在課程退選時，有些用戶資料和設定會被清除。';
$string['expirymessageenrolledbody'] = '親愛的 {$a->user}：

您在 \'{$a->course}\' 課程的選課，即將在{$a->timeend}過期，所以特別通知你。

若您需要幫助，請聯絡 {$a->enroller}。';
$string['expirymessageenrolledsubject'] = '自行選課過期通知';
$string['expirymessageenrollerbody'] = '在\'{$a->course}\' 課程的自行選課將會在下{$a->threshold} 過期，受影響的用戶有：

{$a->users}

要延長他們的選課，請到{$a->extendurl}';
$string['expirymessageenrollersubject'] = '自行選課過期通知';
$string['groupkey'] = '使用群組選課密碼';
$string['groupkey_desc'] = '預設使用群組選課密碼';
$string['groupkey_help'] = '除了只限制知道密碼的用戶瀏覽課程以外，還可以讓用戶在選課時輸入分組密碼，這樣他就能自動加入到小組中。

為了要使用群組選課密碼，必須在群組設定中設定群組選課密碼的同時，也在課程設定中指定一個選課密碼。';
$string['keyholder'] = '你可以從此人取得選課密鑰：';
$string['longtimenosee'] = '超過多久不活動就將他退選';
$string['longtimenosee_help'] = '若用戶有很長的時間沒有存取課程，那麼他們的選課會自動被取消。這一參數用來指定這個時間限制。';
$string['maxenrolled'] = '最大的選課用戶數目';
$string['maxenrolled_help'] = '指定可以自行選課的最大用戶數目。0表示無限制。';
$string['maxenrolledreached'] = '已經達到自行選課用戶數目的上限。';
$string['messageprovider:expiry_notification'] = '自行選課過期通知';
$string['newenrols'] = '允許新的選課';
$string['newenrols_desc'] = '預設為允許用戶自行選修新課程';
$string['newenrols_help'] = '這一設定決定用戶能否選修這一課程';
$string['nopassword'] = '不需要選課密碼';
$string['password'] = '選課密碼';
$string['password_help'] = '只有知道選課密碼的人才能存取課程。

如果此處空白，那麼任何人都可以隨意選課。

如果指定選課密碼後，任何想選課的用戶都必須輸入這個密碼，他們只需要輸入一次就能完成選課。';
$string['passwordinvalid'] = '選課密碼錯誤，請重試';
$string['passwordinvalidhint'] = '所輸入的選課密碼不正確, 請重新輸入<br />(提示 - 以{$a}為開頭)';
$string['pluginname'] = '自行選課';
$string['pluginname_desc'] = '透過自行選課外掛，用戶可以自己選擇想參加的課程。可以透過選課密碼保護課程。選課過程是透過人工選課外掛完成的，所以必須在課程中啟用這個外掛套件。';
$string['requirepassword'] = '必須設定選課密碼';
$string['requirepassword_desc'] = '在新課程必須設定選課密碼，而現有課程不能移除選課密碼。';
$string['role'] = '預設的被分配的角色';
$string['self:config'] = '配置自行選課外掛套件';
$string['self:holdkey'] = '以自行選課密鑰的持有者身分出現';
$string['self:manage'] = '管理已經選課的用戶';
$string['self:unenrol'] = '將用戶從課程中退選';
$string['self:unenrolself'] = '自己從課程中退選';
$string['sendcoursewelcomemessage'] = '傳送課程的歡迎訊息';
$string['sendcoursewelcomemessage_help'] = '若啟用，當用戶自行選課時，會透過email收到一封歡迎的信。';
$string['showhint'] = '顯示提示';
$string['showhint_desc'] = '顯示訪客密碼的第一個字母';
$string['status'] = '允許現有的選課';
$string['status_desc'] = '在新課程啟用自行選課方式';
$string['status_help'] = '若啟用，但是伴隨著關閉"允許新選課"，那就只有先前自行選課的用戶可以存取這課程。
若關閉，這一自行選課的方法就完全失效，因為所有先前自行選課者會被停學，且新用戶無法選課。';
$string['unenrol'] = '將用戶退選';
$string['unenrolselfconfirm'] = '您確定要將自己從"{$a}"課程中退選？';
$string['unenroluser'] = '您確定要從課程“{$a->course}”將用戶“{$a->user}”退選嗎？';
$string['usepasswordpolicy'] = '使用密碼規則';
$string['usepasswordpolicy_desc'] = '對選課密碼使用標準的密碼規則';
$string['welcometocourse'] = '歡迎來到 {$a}';
$string['welcometocoursetext'] = '歡迎來到 {$a->coursename}！

您所需要做的第一件事，就是編輯你的個人資料頁面，以便我們可以多了解您：

  {$a->profileurl}';
