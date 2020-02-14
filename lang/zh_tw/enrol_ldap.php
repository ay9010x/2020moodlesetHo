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
 * Strings for component 'enrol_ldap', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_ldap
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['assignrole'] = '使用者「{$a->user_username}」在課程「{$a->course_shortname}」 (id {$a->course_id})中被分配角色「{$a->role_shortname}」。';
$string['assignrolefailed'] = '無法在課程 \'{$a->course_shortname}\' (id {$a->course_id})中對使用者 \'{$a->user_username}\' 分配\'{$a->role_shortname}\' 角色';
$string['autocreate'] = '假如有註冊資料到一個在Moodle中尚未存在的課程中時，課程可以自動建立。';
$string['autocreate_key'] = '自動建立';
$string['autocreation_settings'] = '自動建立課程機制的設定';
$string['autoupdate_settings'] = '自動課程更新設定';
$string['autoupdate_settings_desc'] = '<p>當同步文字稿時，選出表格來更新(enrol/ldap/cli/sync.php).</p><p>至少需選定一個表格來更新</p>';
$string['bind_dn'] = '如果您想要以綁定使用者來搜尋使用者，請在這兒指定。例如\'cn=ldapuser,ou=public,o=org\'';
$string['bind_dn_key'] = '綁定使用者名稱';
$string['bind_pw'] = '綁定使用者的密碼';
$string['bind_pw_key'] = '密碼';
$string['bind_settings'] = '綁定設定';
$string['cannotcreatecourse'] = '無法建立課程：LDAP記錄中缺少必須要的資料！';
$string['cannotupdatecourse'] = '無法更新課程：LDAP記錄裡沒有必要之數據!課程id數目:{$a->idnumber}';
$string['cannotupdatecourse_duplicateshortname'] = '無法更新課程：簡稱已重複。跳過id號碼為 “{$a->idnumber}”之課程。';
$string['category'] = '自動建立之課程的目錄';
$string['category_key'] = '類別';
$string['contexts'] = 'LDAP 後台';
$string['couldnotfinduser'] = '找不到用戶"{$a}"，略過';
$string['course_fullname'] = '選項：從LDAP欄位取得全名';
$string['course_fullname_key'] = '全名';
$string['course_fullname_updateonsync'] = '使用同步化腳本更新課程完整名稱';
$string['course_fullname_updateonsync_key'] = '更新全名';
$string['course_idnumber'] = '對應到LDAP中唯一的識別，通常是<em>cn</em>或<em>uid</em>。如果您正使用自動課程建立機制，建議您鎖定這個數值。';
$string['course_idnumber_key'] = 'ID號碼';
$string['coursenotexistskip'] = '課程\'{$a}\'不存在並且自動建立被停用，正在跳過中';
$string['course_search_sub'] = '在子場景中搜尋群組成員';
$string['course_search_sub_key'] = '搜尋子場景';
$string['course_settings'] = '課程註冊設定';
$string['course_shortname'] = '選項：從LDAP欄位取得簡稱';
$string['course_shortname_key'] = '簡稱';
$string['course_shortname_updateonsync'] = '在同步腳本時更新簡稱';
$string['course_shortname_updateonsync_key'] = '更新簡稱';
$string['course_summary'] = '選項：從LDAP欄位取得摘要';
$string['course_summary_key'] = '總結';
$string['course_summary_updateonsync'] = '在同步腳本時更新總結';
$string['course_summary_updateonsync_key'] = '更新總結';
$string['courseupdated'] = '那些有id數字的課程都已更新成功';
$string['courseupdateskipped'] = '那些有id數字的課程並不需要更新。跳過';
$string['createcourseextid'] = '建立用戶選的一個不存在的課程“{$a->courseextid}”';
$string['createnotcourseextid'] = '用戶選了一個不存在的課程“{$a->courseextid}”';
$string['creatingcourse'] = '正在建立課程\'{$a}\'...';
$string['duplicateshortname'] = '無法建立課程。簡稱重複了。跳過這些有id數目的課程';
$string['editlock'] = '鎖定數值';
$string['emptyenrolment'] = '課程「{$a->course_shortname}」中沒有與角色「{$a->role_shortname}」有關的選課';
$string['enrolname'] = 'LDAP';
$string['enroluser'] = '將用戶"{$a->user_username}"加入課程"{$a->course_shortname}"（id {$a->course_id}）';
$string['enroluserenable'] = '已啟用用戶"{$a->user_username}"在課程"{$a->course_shortname}"（id {$a->course_id}）中的選課';
$string['explodegroupusertypenotsupported'] = 'ldap_explode_group()不支援選擇的用戶類型：{$a}';
$string['extcourseidinvalid'] = '課程的外部id無效！';
$string['extremovedsuspend'] = '已關閉用戶"{$a->user_username}"在課程"{$a->course_shortname}"（id {$a->course_id}）中的選課';
$string['extremovedsuspendnoroles'] = '已停用用戶"{$a->user_username}"在課程"{$a->course_shortname}"（id {$a->course_id}）中的選課，並取消其角色';
$string['extremovedunenrol'] = '將用戶"{$a->user_username}"從課程"{$a->course_shortname}"（id {$a->course_id}）刪除';
$string['failed'] = '失敗！';
$string['general_options'] = '一般選項';
$string['group_memberofattribute'] = '屬性的名稱。指組成群體的屬性或成員共有的屬性。(例如：memberOf、groupMembership等）';
$string['group_memberofattribute_key'] = '「成員」屬性';
$string['host_url'] = '使用URL格式指定LDAP主機，如\'ldap://ldap.myorg.com/\'　或\'ldaps://ldap.myorg.com/\'';
$string['host_url_key'] = '主機URL';
$string['idnumber_attribute'] = '如果群組成員包含識別名稱，請使用您在LDAP認證設定中對應用戶"學號"的屬性';
$string['idnumber_attribute_key'] = 'id號碼屬性';
$string['ldap_encoding'] = '指定LDAP服務器使用的編碼。大多都是utf-8，但MS AD v2使用平台預設編碼，如cp1252、p1250等。';
$string['ldap_encoding_key'] = 'LDAP編碼';
$string['ldap:manage'] = '管理LDAP選課實體';
$string['memberattribute'] = 'LDAP 成員屬性';
$string['memberattribute_isdn'] = '如果群組成員包含識別名稱，您需要在此指定。如果包含，您還需要設定本單元中的其他設定';
$string['memberattribute_isdn_key'] = '成員屬性使用dn';
$string['nested_groups'] = '您要使用巢狀群組（群組中的群組）選課嗎？';
$string['nested_groups_key'] = '巢狀群組';
$string['nested_groups_settings'] = '巢狀群組設定';
$string['nosuchrole'] = '沒有這個角色：\'{$a}\'';
$string['objectclass'] = 'objectClass　用來搜尋課程。通常設為\'posixGroup\'。';
$string['objectclass_key'] = '物件類別';
$string['ok'] = '好！';
$string['opt_deref'] = '如果組成員包含識別名，指定在搜尋時如何處理別名。選擇這些值中的一個：「否」（LDAP_DEREF_NEVER）或「是」（LDAP_DEREF_ALWAYS）';
$string['opt_deref_key'] = '解析別名';
$string['phpldap_noextension'] = '<em>PHP LDAP模組似乎不存在。如果您要使用此選課外掛套件，請確認它已經安裝且被啟用。</em>';
$string['pluginname'] = 'LDAP選課';
$string['pluginname_desc'] = '<p>您可以使用LDAP伺服器來控制註冊。假設您的LDAP樹已包含可以對應課程的群組，同時每個群組/課程將有成員資料來對應到學生。</p>
<p>假設課程已在LDAP中以群組定義，而每個群組包含多個成員欄位(<em>成員</em>或<em>成員識別號</em>)，這欄位包含一個唯一的用戶識別。</p>
<p>使用LDAP註冊，您的用戶<strong>必須</strong>有一個有效的身份證號欄位。LDAP群組必須有這個身份證號存在成員的欄位中，讓用戶可以註冊課程。如果您已經使用LDAP認證，這通常能運作正常。</p>
<p>當用戶登入時，註冊資料就會更新。您也可以執行腳本來保持註冊資料同步。請看<em>enrol/ldap/enrol_ldap_sync.php</em>。</p>
<p>這個外掛套件可以設定為，當新的群組出現在LDAP時，自動建立新的課程。</p>';
$string['pluginnotenabled'] = '外掛套件尚未啟用！';
$string['role_mapping'] = '<p>對每一個您想從LDAP分配的角色，您需要指定這些角色的課程群組位於哪些場景。用「;」分隔不同的場景。</p><p>您還需要指定您的LDAP伺服器使用哪個屬性保存組成員。通常是「member」或「memberUid」</p>';
$string['role_mapping_attribute'] = 'LDAP成員屬性{$a}';
$string['role_mapping_context'] = 'LDAP情境{$a}';
$string['role_mapping_key'] = '從LDAP對應角色';
$string['roles'] = '角色對應';
$string['server_settings'] = 'LDAP伺服器設定';
$string['synccourserole'] = '== 正在同步課程「{$a->idnumber}」中的「{$a->role_shortname}」角色';
$string['template'] = '選項：自動建立的課程可以從一個範例課程複製其設定';
$string['template_key'] = '模版';
$string['unassignrole'] = '從課程"{$a->course_shortname}"（id {$a->course_id}）取消用戶"{$a->user_username}" 的"{$a->role_shortname}" 角色';
$string['unassignrolefailed'] = '無法從課程"{$a->course_shortname}"（id {$a->course_id}）取消用戶"{$a->user_username}"的"{$a->role_shortname}"角色';
$string['unassignroleid'] = '取消用戶"{$a->user_id}"的角色"{$a->role_id}"';
$string['updatelocal'] = '更新本地資料';
$string['user_attribute'] = '如果群組成員包含識別名稱，指定用來命名/搜尋用戶的屬性。如果您同時使用LDAP認證，此值應該就是在LDAP認證外掛中與用戶「ID號碼」對應的屬性';
$string['user_attribute_key'] = 'ID編號屬性';
$string['user_contexts'] = '如果群組成員包含識別名稱，指定用戶都位於哪些場景。用「;」分隔不同場景。例如：「ou=users,o=org; ou=others,o=org」';
$string['user_contexts_key'] = '場景';
$string['user_search_sub'] = '如果群組成員包含識別名稱，指定是否還在子場景中搜尋用戶';
$string['user_search_sub_key'] = '搜尋子場景';
$string['user_settings'] = '用戶尋找設定';
$string['user_type'] = '如果組成員包含識別名稱，指定用戶在LDAP中是如何被儲存的';
$string['user_type_key'] = '用戶類型';
$string['version'] = '您的伺服器所使用的LDAP協定版本';
$string['version_key'] = '版本';
