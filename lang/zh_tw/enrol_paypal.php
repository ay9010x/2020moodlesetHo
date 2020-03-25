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
 * Strings for component 'enrol_paypal', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_paypal
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['assignrole'] = '指派角色';
$string['businessemail'] = 'PayPal的商業email';
$string['businessemail_desc'] = '您的商業PayPal帳號的email地址';
$string['cost'] = '選課費用';
$string['costerror'] = '選課費用不是數字';
$string['costorkey'] = '請在下列註冊方法中選擇一種';
$string['currency'] = '貨幣';
$string['defaultrole'] = '預設的角色指派';
$string['defaultrole_desc'] = '選擇一角色，它將指派給透過Paypal選課的用戶';
$string['enrolenddate'] = '截止日期';
$string['enrolenddate_help'] = '若啟用，用戶只有在此日期之後才能選修課程。';
$string['enrolenddaterror'] = '選課結束日期不能在開始日期之前';
$string['enrolperiod'] = '選課期間';
$string['enrolperiod_desc'] = '預設這一選課的有效時限。若設定為0，這選課時效將會是無限制。';
$string['enrolperiod_help'] = '用戶身份有效時間長度，從用戶自行加入課程之日算起。停用此項目表示用戶身份永遠有效。';
$string['enrolstartdate'] = '開始時間';
$string['enrolstartdate_help'] = '如果啟用，用戶只能在此日期之後取消選課。';
$string['expiredaction'] = '註冊截止行為。';
$string['expiredaction_help'] = '選擇當用戶選課期間已經結束之後要執行的動作。注意，當課程結束之後，某些用戶資料和設定會被清除掉。';
$string['mailadmins'] = '通知管理員';
$string['mailstudents'] = '通知學生';
$string['mailteachers'] = '通知老師';
$string['messageprovider:paypal_enrolment'] = 'Paypal選課訊息';
$string['nocost'] = '這一課程完全免費！';
$string['paypalaccepted'] = 'PayPal 付款成功';
$string['paypal:config'] = '設定PayPal選課實體';
$string['paypal:manage'] = '管理已選課的用戶';
$string['paypal:unenrol'] = '把用戶從課程中退選';
$string['paypal:unenrolself'] = '把自己從課程中退選';
$string['pluginname'] = 'PayPal付費選課';
$string['pluginname_desc'] = 'PayPal模組提供付費課程支援。如果你將課程的學費設置為零，學生將免費選擇該課程。你也可以設定全站的學費標準的預設值，也可以對每個課程單獨進行設定。如果對課程單獨設定了收費標準，則課程將依此標準收費。';
$string['sendpaymentbutton'] = '使用Paypal送出款項';
$string['status'] = '允許PayPal選課';
$string['status_desc'] = '預設允許用戶使用PayPal選課。';
$string['unenrolselfconfirm'] = '您確定要取消您自己對"{$a}"課程的選課嗎？';
