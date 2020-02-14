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
 * Strings for component 'auth_ldap', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   auth_ldap
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['auth_ldap_ad_create_req'] = '無法在Active Directory建立新的帳號。請確認您符合此動作的所有要求（LDAPS連結，綁定用戶等等）';
$string['auth_ldap_attrcreators'] = '群組或情境的列表，其成員可以建立屬性。可利用 ";" 分隔多個群組， 通常會寫得像 \'cn=teachers,ou=staff,o=myorg\'。';
$string['auth_ldap_attrcreators_key'] = '屬性的建立者';
$string['auth_ldap_auth_user_create_key'] = '透過外部建立用戶';
$string['auth_ldap_bind_dn'] = '如果您想用綁定使用者來搜索使用者，在此指定。就像：‘cn=ldapuser,ou=public,o=org’';
$string['auth_ldap_bind_dn_key'] = '可區辨的名稱';
$string['auth_ldap_bind_pw'] = '綁定用戶的密碼';
$string['auth_ldap_bind_pw_key'] = '密碼';
$string['auth_ldap_bind_settings'] = '繫結的設定';
$string['auth_ldap_changepasswordurl_key'] = '變更密碼的網址';
$string['auth_ldap_contexts'] = '用戶所在情境的列表。以‘;’區隔不同情境。例如：‘ou=users,o=org; ou=others,o=org’';
$string['auth_ldap_contexts_key'] = '情境';
$string['auth_ldap_create_context'] = '如果您允許根據email資訊創建用戶，請指定創建用戶的條件。該值基於安全問題應該有別於別的用戶者。您不需將此條件加到LDAP條件變數，Moodle以此條件搜尋用戶。<br /><b>注意!</b> 您必須變更auth/ldap/auth.php檔案中的user_create()方法以使建立用戶的工作能運作。';
$string['auth_ldap_create_context_key'] = '給新用戶的情境';
$string['auth_ldap_create_error'] = '在LDAP中建立用戶時發生錯誤';
$string['auth_ldap_creators'] = '列出其成員可以建立新課程的群組或情境。可用 \';\' 來分割多個群組。 通常會像 \'cn=teachers,ou=staff,o=myorg\'';
$string['auth_ldap_creators_key'] = '建立者';
$string['auth_ldapdescription'] = '此方法針對一個外部的LDAP伺服器提供身份驗證。

如果所給的用戶名稱和密碼是有效的，那麼Moodle會在資料庫中創建一個新用戶記錄。

此模組可以從LDAP中讀取用戶的屬性，並把需要的欄位預先填入Moodle資料庫。 此後的登入只需檢驗用戶名稱和密碼。';
$string['auth_ldap_expiration_desc'] = '選擇"否"來關閉密碼過期與否的檢查，或者選"LDAP"來直接從LDAP讀取密碼過期時間。';
$string['auth_ldap_expiration_key'] = '有效期限';
$string['auth_ldap_expiration_warning_desc'] = '要在幾天之前，發出密碼過期的警告';
$string['auth_ldap_expiration_warning_key'] = '過期警告';
$string['auth_ldap_expireattr_desc'] = '選項：覆寫LDAP中有關儲存密碼過期時間的屬性';
$string['auth_ldap_expireattr_key'] = '過期屬性';
$string['auth_ldapextrafields'] = '這些欄位是可選的。您可以在此指定一些<B>LDAP欄位</B>複製到Mmoodle的資料庫的用戶欄位中。
<P>如果您讓這些欄位空白，LDAP不會轉移任何東西到Moodle，Moodle將使用原來的預設值。</p>
<P>無論是上述任一情況，用戶在登入之後都可以編輯所有這些欄位。</p>';
$string['auth_ldap_graceattr_desc'] = '選項:覆寫寬容登入屬性';
$string['auth_ldap_gracelogin_key'] = '寬容登入屬性';
$string['auth_ldap_gracelogins_desc'] = '啟用LDAP gracelogin 支援';
$string['auth_ldap_gracelogins_key'] = '寬容登入';
$string['auth_ldap_groupecreators'] = '群組或內容的列表，其成員可以建立屬性。利用";"區分多個群組， 通常會寫得像\'cn=teachers,ou=staff,o=myorg\'。';
$string['auth_ldap_groupecreators_key'] = '群組建立者';
$string['auth_ldap_host_url'] = '以網址形式指定LDAP主機，類似於：‘ldap://ldap.myorg.com/’或‘ldaps://ldap.myorg.com/’ 。可用 \';\' 來分隔多個伺服器，以便在故障時得到轉移主機的支援。';
$string['auth_ldap_host_url_key'] = '主機網址';
$string['auth_ldap_ldap_encoding'] = '指定 LDAP 伺服器使用的編碼，通常是 utf-8，MS AD v2 使用預設平台編碼，像是 cp1252、cp1250 等。';
$string['auth_ldap_ldap_encoding_key'] = 'LDAP編碼';
$string['auth_ldap_login_settings'] = '登入設定';
$string['auth_ldap_memberattribute'] = '指定從屬於某個組的使用者屬性,一般是"會員"';
$string['auth_ldap_memberattribute_isdn'] = '選填：覆蓋會員屬性值處理， 0 或 1';
$string['auth_ldap_memberattribute_isdn_key'] = '會員屬性使用 dn';
$string['auth_ldap_memberattribute_key'] = '會員屬性';
$string['auth_ldap_noconnect'] = 'LDAP 模組無法連線伺服器： {$a}';
$string['auth_ldap_noconnect_all'] = 'LDAP 模組無法連線到任何伺服器： {$a}';
$string['auth_ldap_noextension'] = '<em> PHP 的 LDAP 模組似乎不存在，若你要使用這一外掛套件，請確認是否已經安裝及並啟用。</em>';
$string['auth_ldap_no_mbstring'] = '您需要mbstring延伸集才能在Active Directory建立使用者。';
$string['auth_ldapnotinstalled'] = '無法使用 LDAP 認證，PHP 的 LDAP 模組未安裝。';
$string['auth_ldap_objectclass'] = '選項：覆寫用來在 ldap_user_type 上命名/搜尋用戶的 objectClass，通常你不需要去更改它。';
$string['auth_ldap_objectclass_key'] = '物件類別';
$string['auth_ldap_opt_deref'] = '決定ldap在搜尋時的別名:選擇:"否" (LDAP_DEREF_NEVER) 或 "是" (LDAP_DEREF_ALWAYS)';
$string['auth_ldap_opt_deref_key'] = '不同的代稱';
$string['auth_ldap_passtype'] = '指定在 LDAP 伺服器新增或修改密碼的格式';
$string['auth_ldap_passtype_key'] = '密碼格式';
$string['auth_ldap_passwdexpire_settings'] = 'LDAP 密碼過期設定';
$string['auth_ldap_preventpassindb'] = '選擇"是" 可以防止密碼被儲存到Moodle的資料庫中';
$string['auth_ldap_preventpassindb_key'] = '不要快取密碼';
$string['auth_ldap_search_sub'] = '從次級上下文中搜索用戶';
$string['auth_ldap_search_sub_key'] = '搜尋子條件';
$string['auth_ldap_server_settings'] = 'LDAP 伺服器設定';
$string['auth_ldap_suspended_attribute'] = '任選的：若有提供，這一屬性將被用來啟用/休學那地方建立的用戶帳號。';
$string['auth_ldap_suspended_attribute_key'] = '休學屬性';
$string['auth_ldap_unsupportedusertype'] = '認證： ldap user_create() 不支援選出的用戶類型：{$a}';
$string['auth_ldap_update_userinfo'] = '從LDAP向本系統更新用戶資訊（姓名、位址……）要查看映射資訊，請看/auth/ldap/attr_mappings.php';
$string['auth_ldap_user_attribute'] = '用於命名/搜索使用者的屬性。通常為‘cn’。';
$string['auth_ldap_user_attribute_key'] = '用戶屬性';
$string['auth_ldap_user_exists'] = 'LDAP使用者名稱已經存在。';
$string['auth_ldap_user_settings'] = '用戶搜尋設定';
$string['auth_ldap_user_type'] = '選擇使用者儲存在LDAP的方法,此設定同時也指定可登入時間,grace 登入和用者建立等功能';
$string['auth_ldap_user_type_key'] = '用戶類型';
$string['auth_ldap_usertypeundefined'] = 'config.user_type 未定義或是函式 ldap_expirationtime2unix 不支援選擇的類型！';
$string['auth_ldap_usertypeundefined2'] = 'config.user_type 未定義或是函式 ldap_unixi2expirationtime 不支援選擇的類型！';
$string['auth_ldap_version'] = '您目前LDAP 伺服器的使用版本';
$string['auth_ldap_version_key'] = '版本';
$string['auth_ntlmsso'] = 'NTLM SSO';
$string['auth_ntlmsso_enabled'] = '設定為\'是\'，以使用NTLM領域來Single Sign On。<strong>注意:</strong>此動作需在網站伺服器做額外安裝，請參見<a href="http://docs.moodle.org/en/NTLM_authentication">http://docs.moodle.org/en/NTLM_authentication</a>';
$string['auth_ntlmsso_enabled_key'] = '啟用';
$string['auth_ntlmsso_ie_fastpath'] = '設定為來啟用NTLM單點登入快速路徑（將跳過某些步驟，但只在客戶端瀏覽器為微軟Internet Explorer時生效)。';
$string['auth_ntlmsso_ie_fastpath_attempt'] = '所有瀏覽器都使用 NTLM';
$string['auth_ntlmsso_ie_fastpath_key'] = '微軟IE捷徑？';
$string['auth_ntlmsso_ie_fastpath_yesattempt'] = '是的，其他瀏覽器也使用NTLM';
$string['auth_ntlmsso_ie_fastpath_yesform'] = '是的，所有其他瀏覽器都使用標準登入表單';
$string['auth_ntlmsso_maybeinvalidformat'] = '無法從 REMOTE_USER header抽出用戶名稱，你設定的格式是否正確?';
$string['auth_ntlmsso_missing_username'] = '你需要在遠端用戶名稱格式中至少指定 %username%';
$string['auth_ntlmsso_remoteuserformat'] = '如果你在"身分認證類型"已選擇了"NTLA"，你可以在此指定遠端用戶名稱的格式。如果你保留空白，系統將會使用預設的DOMAINusername格式。你可以使用可選用性的<b>%domain%</b>變項符號來指定這域名要出現在哪裡，並使用強制性的<b>%username%</b>變相符號來指定用戶名稱出現在哪裡。 一些常用的格式是<tt>%domain%%username%</tt> (MS Windows 預設)，<tt>%domain%/%username%</tt>, <tt>%domain%+%username%</tt> 和單單是 <tt>%username%</tt> (若沒有域名的部分)。';
$string['auth_ntlmsso_remoteuserformat_key'] = '遠端用戶名稱格式';
$string['auth_ntlmsso_subnet'] = '如果設定，將只在此子網路中的客戶端使用SSO。格式：xxx.xxx.xxx.xxx/遮罩位元。你可以用英文逗點\',\'來分開多個子網路。';
$string['auth_ntlmsso_subnet_key'] = '子網';
$string['auth_ntlmsso_type'] = 'Web伺服器中設定的刪除認證方法(如果不知道該加入什麼，就選NTLM)';
$string['auth_ntlmsso_type_key'] = '授權類型';
$string['connectingldap'] = '連線到LDAP伺服器';
$string['creatingtemptable'] = '建立臨時表單 {$a}';
$string['didntfindexpiretime'] = 'password_expire（）沒有發現逾期時間。';
$string['didntgetusersfromldap'] = '沒有從LDAP取得任何刪除-錯誤？---退出中';
$string['gotcountrecordsfromldap'] = '取得{$a} 筆資料從LDAP';
$string['morethanoneuser'] = '奇怪！在LDAP中找到一條以上的用戶紀錄。只使用第一條。';
$string['needbcmath'] = '您需要安裝BCMath，擴展才能使用Active Directory的寬限登入';
$string['needmbstring'] = '您需要安裝mbstring擴展才能在 Active Directory中改變密碼';
$string['nodnforusername'] = 'user_update_password()出現錯誤。{$a->username}沒有DN';
$string['noemail'] = '嘗試寄送郵件給您時發生錯誤！';
$string['notcalledfromserver'] = '不應該從web伺服器呼叫！';
$string['noupdatestobedone'] = '沒有更新';
$string['nouserentriestoremove'] = '沒有用戶條目被刪除';
$string['nouserentriestorevive'] = '沒有用戶條目被復原';
$string['nouserstobeadded'] = '沒有新增用戶';
$string['ntlmsso_attempting'] = '透過NTLM進行Single Sign On';
$string['ntlmsso_failed'] = '自動登入失敗，請使用一般登入頁面。';
$string['ntlmsso_isdisabled'] = 'NTLM SSO無效。';
$string['ntlmsso_unknowntype'] = '未知的ntlmsso型態！';
$string['pagedresultsnotsupp'] = '不支援LDAP分頁結果(若非不支援你的PHP版本，就是你將Moodle組態設成使用LDAP協定版本2)';
$string['pagesize'] = '請確定這一頁面大小的值是比你的LDAP伺服器結果大小限制的值還小。(只在單次查詢時可以回傳的最大條目數)';
$string['pagesize_key'] = '頁面大小';
$string['pluginname'] = '使用一個LDAP伺服器';
$string['pluginnotenabled'] = '外掛套件未被啟用！';
$string['renamingnotallowed'] = '不允許用戶在LDAP更名';
$string['rootdseerror'] = '在Active Directory查詢rootDSE出現錯誤';
$string['start_tls'] = '使用一般 LDAP 服務 (port 389) 以及 TLS 加密';
$string['start_tls_key'] = '使用TLS';
$string['synctask'] = 'LADP的用戶同步作業';
$string['updatepasserror'] = 'user_update_password()出錯。錯誤代碼：{$a->errno}；錯誤資訊：{$a->errstring}';
$string['updatepasserrorexpire'] = 'user_update_password()讀取密碼期限時候出現錯誤。錯誤代碼：{$a->errno}；錯誤資訊：{$a->errstring}';
$string['updatepasserrorexpiregrace'] = 'user_update_password()修改密碼期限或寬限登入時出錯。錯誤代碼：{$a->errno}；錯誤資訊：{$a->errstring}';
$string['updateremfail'] = '更新LDAP記錄出錯。錯誤代碼：{$a->errno}；錯誤資訊：{$a->errstring}<br />鍵值（{$a->key}）- 舊的moodle值："{$a->ouvalue}"，新值：“{$a->nuvalue}”';
$string['updateremfailamb'] = '無法使用不明確的欄位{$a->key}更新LDAP-舊moodle值：“{$a->ouvalue}”，新值：“{$a->nuvalue}”';
$string['updateusernotfound'] = '在外部更新時找不到用戶。細節資訊：搜尋基於“{$a->userdn}”；搜尋過濾器：“(objectClass=*)”；搜尋屬性：{$a->attribs}';
$string['useracctctrlerror'] = '取得{$a}的userAccountControl時出錯';
$string['user_activatenotsupportusertype'] = '認證：ldap user_activate()不支援所選的用戶類性：{$a}';
$string['user_disablenotsupportusertype'] = '認證：ldap user_disable()不支援所選的用戶類性：{$a}';
$string['userentriestoadd'] = '要加入的用戶項目：{$a}';
$string['userentriestoremove'] = '要刪除的用戶項目：{$a}';
$string['userentriestorevive'] = '要還原的用戶項目：{$a}';
$string['userentriestoupdate'] = '要更新的用戶項目：{$a}';
$string['usernotfound'] = '在LDAP中找不到用戶';
