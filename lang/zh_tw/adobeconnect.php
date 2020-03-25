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
 * Strings for component 'adobeconnect', language 'zh_tw', branch 'MOODLE_30_STABLE'
 *
 * @package   adobeconnect
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addparticipant'] = '添加';
$string['addpresenter'] = '添加';
$string['adminemptyxml'] = '目前無法連結到Adobe Connect Pro伺服器。請點選"繼續"來處理這活動的設計頁，並測試這一連結。';
$string['admin_httpauth'] = 'HTTP認證標題';
$string['admin_httpauth_desc'] = '在你的custom.ini中，HTTP_AUTH_HEADER 所用的值。';
$string['admin_login'] = '管理員登入';
$string['admin_login_desc'] = '以主要管理員帳號登入';
$string['adminnotsetupproperty'] = '這一活動模組尚未正確設定。請點選"繼續"來處理這活動設計的頁面，並測試這一連結。';
$string['admin_password'] = '管理員密碼';
$string['admin_password_desc'] = '主要管理員的密碼';
$string['adobeconnect'] = 'Adobe連結';
$string['adobeconnectfieldset'] = 'Adobe連結的設定';
$string['adobeconnecthost'] = 'Adobe連結的主持人';
$string['adobeconnecthostdescription'] = '主持人可以給予其他用戶特權，如一個報告者可做的之外，可以開始和停止一個會議。';
$string['adobeconnectintro'] = '說明';
$string['adobeconnect:meetinghost'] = '會議主機';
$string['adobeconnect:meetingparticipant'] = '會議參與者';
$string['adobeconnect:meetingpresenter'] = '會議報告者';
$string['adobeconnectname'] = '會議名稱';
$string['adobeconnectparticipant'] = 'Adobe連結參與者';
$string['adobeconnectparticipantdescription'] = '可以檢視，但無法修改這會議的任何設定';
$string['adobeconnectpresenter'] = 'Adobe連結報告者';
$string['allusers'] = '所有用戶';
$string['assignadoberole'] = '指派Adobe的角色';
$string['assignadoberoles'] = '為 {$a->meetname} ({$a->groupname})指派 {$a->role}角色';
$string['assignroles'] = '指派角色';
$string['availablelist'] = '可用的';
$string['backtomeeting'] = '回到{$a}會議';
$string['cancelchanges'] = '取消';
$string['connectiontesttitle'] = '連結測試視窗';
$string['duplicatemeetingname'] = '在這伺服器上發現重複的會議名稱';
$string['duplicateurl'] = '在這伺服器上發現重複的會議網址';
$string['email_login'] = '用Email位址登入';
$string['emptyxml'] = '目前無法連結到 Adobe Connect Pro 伺服器。請告知您的Moodle網站管理員。';
$string['endtime'] = '結束時間';
$string['error1'] = '你必須是網站管理員才可以存取這一頁面';
$string['error2'] = '這個\'{$a}\'是空白的，請輸入一個值，並儲存這一設定。';
$string['errormeeting'] = '擷取記錄時發生錯誤';
$string['errorrecording'] = '無法找到紀錄段落';
$string['existingusers'] = '{$a}現有用戶';
$string['greaterstarttime'] = '開始時間不能比結束時間晚';
$string['groupswitch'] = '以群組過濾';
$string['host'] = '主機';
$string['host_desc'] = 'REST呼叫要送去的地方';
$string['https'] = 'HTTPS連結';
$string['https_desc'] = '經由HTTPS連結到連結伺服器';
$string['invalidadobemeeturl'] = '輸入的資料對這一欄位無效。請點選協助小圓圈以了解有效的輸入資料。';
$string['invalidurl'] = '這網址需要以英文字母(a-z)開頭';
$string['joinmeeting'] = '加入會議';
$string['longurl'] = '這個會議的網址太長。試著縮短它。';
$string['meethost_desc'] = '域名是指伺服器安裝所在';
$string['meetinfo'] = '更多會議的細節';
$string['meetinfotxt'] = '參見伺服器上會議細節';
$string['meetingend'] = '會議結束時間';
$string['meetinggroup'] = '會議群組';
$string['meetinghost'] = '會議域名';
$string['meetinginfo'] = '會議說明';
$string['meetingintro'] = '會議摘要';
$string['meetingname'] = '會議名稱';
$string['meetingstart'] = '會議開始時間';
$string['meetingtype'] = '會議類型';
$string['meettemplates'] = '會議的樣版';
$string['meettemplates_help'] = '會議室樣版可以用來以自訂的版面格式來建立會議';
$string['meeturl'] = '會議的網址';
$string['missingexpectedgroups'] = '沒有可用的群組';
$string['modulename'] = 'Adobe Connect';
$string['modulenameplural'] = 'Adobe Connect';
$string['nomeeting'] = '這伺服器上沒有任何會議';
$string['notparticipant'] = '您不是這一會議的參與者';
$string['notsetupproperty'] = '這一活動模組沒有適當的設定，請通知你的Moodle管理員';
$string['participantbtngrp'] = '參與者動作';
$string['participantsgrp'] = '會議的用戶';
$string['particpantslabel'] = '參與者';
$string['pluginadministration'] = 'Adobe Connect 管理';
$string['pluginname'] = 'Adobe Connect';
$string['port'] = '連接埠';
$string['port_desc'] = '用來連接到Adobe Connect的連接埠';
$string['potentialusers'] = '{$a}個潛在用戶';
$string['presenterbtngrp'] = '報告者動作';
$string['presenterlabel'] = '報告者';
$string['private'] = '私人';
$string['public'] = '公開';
$string['record_force'] = '強制進行會議紀錄';
$string['record_force_desc'] = '強制將所有的 Adobe Connect 會議都記錄下來。這會影響整個網站，且 Adobe Connect 伺服器必須重新啟動。';
$string['recordinghdr'] = '紀錄';
$string['removeparticipant'] = '移除';
$string['removepresenter'] = '移除';
$string['roletoassign'] = '要指派的角色';
$string['samemeettime'] = '無效的會議時間';
$string['savechanges'] = '儲存';
$string['selectparticipants'] = '指派角色';
$string['starttime'] = '開始時間';
$string['testconnection'] = '測試連線';
$string['unableretrdetails'] = '無法擷取會議的細節';
$string['usergrouprequired'] = '這一會議需要用戶是在一群組織內才可以加入';
$string['usernotenrolled'] = '只有已經選課且在此課程中有一角色的用戶才可以加入這一會議';
