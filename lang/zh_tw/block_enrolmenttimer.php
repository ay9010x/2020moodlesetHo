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
 * Strings for component 'block_enrolmenttimer', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   block_enrolmenttimer
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activecountdown'] = '主動倒數';
$string['activecountdown_help'] = '主動倒數學生剩下的時間，並用Java描述語言存取課程。';
$string['completionpercentage'] = '完成多少百分比才發通知';
$string['completionpercentage_help'] = '規定學生必須完成課程活動之多少百分比，才能收到一封"完成"的Email。';
$string['completionsmessage'] = '課程完成的Email';
$string['completionsmessagechk'] = '啟動 課程完成的Email';
$string['completionsmessagechk_help'] = '啟動/關閉  完成電郵';
$string['completionsmessage_help'] = '將會送出 Email 祝賀學生完成這一課程。在Email的內容中你可以使用[[user_name]] [[course_name]]作為自訂變數';
$string['daystoalertenrolmentend'] = '結束前幾天送出警告信';
$string['daystoalertenrolmentend_help'] = '在選課結束的幾天之前，要送出警告通知的Email';
$string['displayNothingNoDateSet'] = '隱藏區塊(不設定結束日期)';
$string['displayNothingNoDateSet_help'] = '為不設定結束日期的用戶隱藏區塊，若關閉它，會有訊息通知這些學生';
$string['displayTextCounter'] = '顯示文字計數器';
$string['displayTextCounter_help'] = '在主要計數器之下，顯示文字計數器';
$string['displayUnitLabels'] = '顯示單位標籤';
$string['displayUnitLabels_help'] = '在主要計數器之下顯示每一個單位';
$string['emailsubject'] = 'Email主旨';
$string['emailsubject_completion_default'] = '已完成的課程';
$string['emailsubject_expiring_default'] = '選課即將到期';
$string['emailsubject_help'] = '將會送給用戶的 Email 的主旨';
$string['enrolmenttimer'] = '選課計時器';
$string['enrolmenttimer:addinstance'] = '新增一個選課計時器區塊';
$string['expirytext'] = '直到您的選課到期';
$string['forceDefaults'] = '強制使用預設值';
$string['forceDefaults_help'] = '關閉教師更改每一個區塊實例的設定的權限';
$string['forceTwoDigits'] = '強制使用二位數';
$string['forceTwoDigits_help'] = '強制倒數計時器使用兩位數來顯示剩餘時間(例如，還剩下01小時)';
$string['instance_title'] = '設定這區塊實例的標題';
$string['key_days'] = '日';
$string['key_hours'] = '時';
$string['key_minutes'] = '分';
$string['key_months'] = '月';
$string['key_seconds'] = '秒';
$string['key_weeks'] = '週';
$string['key_years'] = '年';
$string['noDateSet'] = '您的選課尚未到期';
$string['pluginname'] = '選課計時器';
$string['settings_general'] = '一般設定';
$string['settings_notifications_alert'] = '警告通知的電子郵件設定';
$string['settings_notifications_completion'] = '完成通知的電子郵件設定';
$string['settings_notifications_defaults'] = '設定在實例設定中所要用的預設值';
$string['timeleftmessage'] = '發出警示訊息前還剩下的時間';
$string['timeleftmessagechk'] = '啟用剩餘時間警告通知電子郵件';
$string['timeleftmessagechk_help'] = '啟用/關閉 警告通知的電子郵件';
$string['timeleftmessage_help'] = '此電郵將發送給學生，告知他們課程還剩下多少日子，例如說十天。您可以自訂如下[[user_name]] [[course_name]] [[days_to_alert]]';
$string['viewoptions'] = '顯示的增加量';
$string['viewoptions_desc'] = '選擇要顯示在區塊中的增加量';
