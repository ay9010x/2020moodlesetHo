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
 * Strings for component 'webexactivity', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   webexactivity
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allchat'] = '參與者可互相交談';
$string['apipassword'] = 'WebEx 管理者密碼';
$string['apipassword_help'] = '您位置之管理帳號密碼';
$string['apisettings'] = 'API 設定';
$string['apiusername'] = 'WebEx管理者用戶名';
$string['availabilityendtime'] = '延長進入WebEx結束時間';
$string['badpassword'] = '您WebEx用戶名與密碼不相符。';
$string['badpasswordexception'] = '您WebEx密碼不正確且無法更新。';
$string['confirmrecordingdelete'] = '你確定要刪除長度{$a->time}的錄音檔<b>{$a->name}</b>?刪除將無法復原。';
$string['connectionexception'] = '連結至{$a->error}時有錯誤。';
$string['defaultmeetingtype'] = '預設會議型式';
$string['defaultmeetingtype_help'] = '新建會議時會先預選此會議型式。';
$string['deletetime'] = '刪除時間';
$string['deletionsoon'] = '<div>即將刪除。</div>';
$string['description'] = '描述';
$string['duration'] = '預期時程';
$string['duration_help'] = '預期會議持續時間。此項時間謹作資料用途，並不影響會議要進行多久。';
$string['errordeletingrecording'] = '刪除錄音時有錯誤';
$string['error_HM_AccessDenied'] = '您無法主持會議';
$string['error_JM_InvalidMeetingKey'] = 'WebEx會議有一項嚴重錯誤，致使您不能參加此會議。';
$string['error_JM_MeetingLocked'] = '會議已鎖住，您不能參加此會議。';
$string['error_JM_MeetingNotInProgress'] = '目前並未進行會議，可能尚未開始，亦或早已結束。';
$string['error_LI_AccessDenied'] = '使用者不能登入WebEx。';
$string['error_LI_AccountLocked'] = 'WebEx使用者帳戶已鎖住。';
$string['error_LI_AutoLoginDisabled'] = '此使用者不能自動登入。';
$string['error_LI_InvalidSessionTicket'] = '此會期票券無效。請再試一次。';
$string['error_LI_InvalidTicket'] = '此登入票券無效。請再試一次。';
$string['error_unknown'] = '產生未知錯誤。';
$string['event_meeting_ended'] = '會議結束';
$string['event_meeting_hosted'] = '主持的會議';
$string['event_meeting_joined'] = '參加的會議';
$string['event_meeting_started'] = '會議開始';
$string['event_recording_created'] = '新增錄音';
$string['event_recording_deleted'] = '錄音刪除';
$string['event_recording_downloaded'] = '錄音檔下載';
$string['event_recording_viewed'] = '檢視錄音';
$string['externalpassword'] = '參與者亦需知道會議密碼：<b>{$a}</b>';
$string['getexternallink'] = '<a href="{$a->url}">取得外界參與者之連結</a>';
$string['host'] = '主持';
$string['hostschedulingexception'] = '使用者不能替主持人設計會議。';
$string['inprogress'] = '進行中';
$string['invalidtype'] = '無效型式';
$string['joinmeetinglink'] = '<a href="{$a->url}">參加會議</a>';
$string['longavailability'] = '延長參與(會議)';
$string['manageallrecordings'] = '管理全部WebEx錄音檔';
$string['manageallrecordings_help'] = '從WebEx伺服器管理全部錄音檔，而非以單一Moodle活動來管理數個錄音。';
$string['meetingpast'] = '會議已經過了。';
$string['meetingsettings'] = '會議設定';
$string['recordingname'] = '錄音名';
$string['recordingstreamurl'] = '播放';
$string['recordingtrashtime_help'] = '永遠刪除錄音前之保存時數。';
$string['requiremeetingpassword'] = '須有會議密碼';
$string['startssoon'] = '即將開始';
$string['starttime'] = '開始時間';
$string['studentdownload'] = '允許學生下載錄音';
$string['studentdownload_help'] = '允許學生連結錄音檔';
