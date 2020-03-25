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
 * Strings for component 'tool_monitor', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_monitor
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addrule'] = '添加一條新規則';
$string['allevents'] = '所有事件';
$string['allmodules'] = '所有實例';
$string['area'] = '區域';
$string['areatomonitor'] = '監看的區域';
$string['cachedef_eventsubscriptions'] = '這是用來儲存個別課程的事件訂閱的清單';
$string['contactadmin'] = '聯絡你的管理員以啟用它';
$string['core'] = '核心';
$string['currentsubscriptions'] = '你目前的訂閱';
$string['defaultmessagetemplate'] = '規則名稱: {rulename}<br />說明: {description}<br />事件名稱: {eventname}';
$string['deleterule'] = '刪除規則';
$string['deletesubscription'] = '刪除訂閱';
$string['description'] = '說明：';
$string['disablefieldswarning'] = '某些欄位無法被編輯，因為這一規則已經有人訂閱';
$string['duplicaterule'] = '重複的規則';
$string['editrule'] = '編輯規則';
$string['enablehelp'] = '啟用/關閉事件監看';
$string['enablehelp_help'] = '必須先啟用事件監看，你才可以建立和訂閱規則。注意，啟用事件監看可能影響你的網站的效能。';
$string['errorincorrectevent'] = '請選擇一個與被選取的外掛相關事件';
$string['event'] = '事件';
$string['eventnotfound'] = '沒發現事件';
$string['eventrulecreated'] = '規則已建立';
$string['eventruledeleted'] = '規則已刪除';
$string['eventruleupdated'] = '規則已更新';
$string['eventsubcreated'] = '訂閱已建立';
$string['eventsubcriteriamet'] = '訂閱規準符合';
$string['eventsubdeleted'] = '訂閱已刪除';
$string['freqdesc'] = '在 {$a->mins} 分鐘內有{$a->freq}次';
$string['frequency'] = '通知';
$string['frequency_help'] = '需要在指定的時間限制內發生多少次事件才送出通知訊息。';
$string['inminutes'] = '分鐘';
$string['invalidmodule'] = '無效的模組';
$string['manage'] = '管理';
$string['managerules'] = '事件監看規則';
$string['manageruleslink'] = '你可以從這{$a}頁面管理規則';
$string['managesubscriptions'] = '事件監看';
$string['managesubscriptionslink'] = '你可以從這{$a}頁面訂閱規則';
$string['messageprovider:notification'] = '規則訂閱的通知';
$string['messagetemplate'] = '通知訊息';
$string['messagetemplate_help'] = '一旦達到發出通知的門檻，將會寄出通知訊息給訂閱者。此訊息可以包含以下變數:
<br /><br />
* 到這事件位置的連結 {link}<br />
* 到這被監看區域的連結 {modulelink}<br />
* 規則名稱 {rulename}<br />
* 說明 {description}<br />
* 事件 {eventname}';
$string['moduleinstance'] = '實例';
$string['monitordisabled'] = '事件監看目前已被關閉';
$string['monitorenabled'] = '事件監看目前已被啟用';
$string['monitor:managerules'] = '管理事件監看規則';
$string['monitor:managetool'] = '啟用/關閉事件監看';
$string['monitor:subscribe'] = '訂閱事件監看規則';
$string['norules'] = '這裡沒有事件監看規則';
$string['pluginname'] = '事件監看器';
$string['processevents'] = '處理事件';
$string['ruleareyousure'] = '你確定要刪除這一規則"{$a}"？';
$string['ruleareyousureextra'] = '這兒有{$a}個對此一規則的訂閱，也將一起被刪除。';
$string['rulecopysuccess'] = '規則已被複製';
$string['ruledeletesuccess'] = '規則已被刪除';
$string['rulehelp'] = '規則細節';
$string['rulehelp_help'] = '當\'{$a->eventcomponent}\'的事件\'{$a->eventname}\'在{$a->minutes} 分鐘內發生{$a->frequency} 次，這規則就會起反應。';
$string['rulename'] = '規則名稱';
$string['rulenopermission'] = '你沒有權限去訂閱任何事件的監看';
$string['rulenopermissions'] = '你沒有權限去"{$a}一規則"';
$string['rulescansubscribe'] = '你可以訂閱的規則';
$string['selectacourse'] = '選擇一課程';
$string['selectcourse'] = '在課程的層次看這一報告以取得可能模組的清單';
$string['subareyousure'] = '你確定要刪除對這規則"{$a}"的訂閱？';
$string['subcreatesuccess'] = '訂閱已被建立';
$string['subdeletesuccess'] = '訂閱已被移除';
$string['subhelp'] = '訂閱的細節';
$string['subhelp_help'] = '當\'{$a->moduleinstance}\' 的事件\'{$a->eventname}\'在{$a->minutes} 分鐘內發生{$a->frequency} 次，這訂閱就會起反應。';
$string['subscribeto'] = '訂閱規則"{$a}"';
$string['taskchecksubscriptions'] = '啟用/關閉無效的規則訂閱';
$string['taskcleanevents'] = '移除任何不必要的事件監看的事件';
$string['unsubscribe'] = '取消訂閱';
