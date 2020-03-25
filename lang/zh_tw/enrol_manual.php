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
 * Strings for component 'enrol_manual', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_manual
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alterstatus'] = '改變狀態';
$string['altertimeend'] = '改變結束時間';
$string['altertimestart'] = '改變開始時間';
$string['assignrole'] = '分配角色';
$string['browsecohorts'] = '瀏覽同期生';
$string['browseusers'] = '瀏覽用戶';
$string['confirmbulkdeleteenrolment'] = '您確定要刪除這些用戶的選課嗎？';
$string['defaultperiod'] = '預設選課期間';
$string['defaultperiod_desc'] = '預設時間長度，此為有效時間長度。如設為 0，註冊時間在預設值將為無限制。';
$string['defaultperiod_help'] = '用戶選課的預設有效時間長度，從用戶加入課程算起。若關閉，意味著預設選課期間無限制。';
$string['defaultstart'] = '預設選課開始';
$string['deleteselectedusers'] = '刪除選出用戶的選課';
$string['editselectedusers'] = '編輯被選出的用戶的選課';
$string['enrolledincourserole'] = '在 "{$a->course}" 課程註冊為 "{$a->role}" 角色';
$string['enrolusers'] = '加入用戶到此課程';
$string['expiredaction'] = '註冊截止指令';
$string['expiredaction_help'] = '選擇當用戶選課過其之後要執行的動作。注意：當課程結束之後，某些用戶資料和設定會被清除掉。';
$string['expirymessageenrolledbody'] = '親愛的{$a->user}
於此通知您於{$a->course}課程之註冊即將於{$a->timeend}到期
如您需要協助，請連絡{$a->enroller}';
$string['expirymessageenrolledsubject'] = '註冊截止通知';
$string['expirymessageenrollerbody'] = '對以下使用者{$a->users}註冊{$a->course}將於下一個{$a->threshold}截止。
如欲展期，請至{$a->extendurl}';
$string['expirymessageenrollersubject'] = '註冊截止通知。';
$string['manual:config'] = '設定手動選課實體';
$string['manual:enrol'] = '以手動方式將其他用戶加入一課程';
$string['manual:manage'] = '管理已經選課的用戶';
$string['manualpluginnotinstalled'] = '這"手動"外掛還沒被安裝';
$string['manual:unenrol'] = '把用戶從這課程退選';
$string['manual:unenrolself'] = '把自己從這課程退選';
$string['messageprovider:expiry_notification'] = '手動註冊截止通知。';
$string['now'] = '現在';
$string['pluginname'] = '手動選課';
$string['pluginname_desc'] = '透過手動選課外掛，有權限的用戶（例如老師）可以在課程管理設定中的一個連結中手動為其他用戶選課。此外掛套件通常是啟用的，因為有其他外掛（例如自行選課）需要用到它。';
$string['status'] = '啟用手動選課';
$string['status_desc'] = '允許內部選課的用戶存取課程。大部分情況都應該啟用這個選項。';
$string['statusdisabled'] = '停用';
$string['statusenabled'] = '啟用';
$string['status_help'] = '此設定決定用戶是否可以被用戶(如教師)經由課程管理設定業的鏈結，以手動選課。';
$string['unenrol'] = '退選';
$string['unenrolselectedusers'] = '將選出的用戶退選';
$string['unenrolselfconfirm'] = '您確定要退選自己對“{$a}”課程的選課嗎？';
$string['unenroluser'] = '您確定要從“{$a->course}”課程將用戶“{$a->user}”退選嗎？';
$string['unenrolusers'] = '退選用戶';
$string['wscannotenrol'] = '外掛套件無法以手動把一個用戶加入id = {$a->courseid}}的課程';
$string['wsnoinstance'] = '對於課程(id = {$a->courseid})，手動選課外掛套件不存在或者被停用';
$string['wsusercannotassign'] = '您沒有權限在這一課程({$a->courseid})中指派這一個角色({$a->roleid})給這一用戶({$a->userid})。';
