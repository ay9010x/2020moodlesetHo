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
 * Strings for component 'report_security', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   report_security
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['check_configrw_details'] = '建議您在安裝之後，將檔案config.php的讀寫權限加以變更，這樣檔案才不會被網頁伺服器所更改。

請注意，這一措施並不能顯著提高伺服器的安全性，雖然它可能會減慢或限制一般性攻擊。';
$string['check_configrw_name'] = '可寫入的config.php';
$string['check_configrw_ok'] = 'config.php不能被PHP修改';
$string['check_configrw_warning'] = 'PHP腳本可以修改config.php';
$string['check_cookiesecure_details'] = '若你啟用 https 溝通，建議你也起用安全cookies。你也應該加上持續性的重新導向，從 http 到 https。';
$string['check_cookiesecure_error'] = '請啟用安全 cookies';
$string['check_cookiesecure_name'] = '安全 cookies';
$string['check_cookiesecure_ok'] = '安全 cookies已經啟用';
$string['check_defaultuserrole_details'] = '<p>所有已登入的用戶都會擁有預設角色的權限。請確認此角色沒有危險的權限。</p>
<p>預設用戶唯一能繼承的舊角色只能是<em>已認證的用戶</em>。查看課程的權限一定不能啟用。</p>';
$string['check_defaultuserrole_error'] = '預設用戶角色"{$a}"定義不正確';
$string['check_defaultuserrole_name'] = '所有用戶的預設角色';
$string['check_defaultuserrole_notset'] = '預設角色沒有設定';
$string['check_defaultuserrole_ok'] = '所有用戶的預設角色的定義OK';
$string['check_displayerrors_details'] = '在正式運行的網站上，不建議你啟用PHP的設定<code>display_errors</code>，因為這些錯誤訊息會洩漏你的伺服器的敏感訊息。';
$string['check_displayerrors_error'] = 'PHP的設定是要在網頁上顯示執行時的錯誤。我們建議除非是測試用的網站，否則應關閉它，以免洩漏網站弱點。';
$string['check_displayerrors_name'] = '顯示PHP錯誤';
$string['check_displayerrors_ok'] = '顯示PHP錯誤的功能已經關閉';
$string['check_emailchangeconfirmation_details'] = '當用戶在他們的個人資料表上更改他們的email地址時，建議要有一email確認步驟。如果關閉，亂發廣告信的人可能利用這伺服器來發送垃圾廣告。
Email欄位也可能被認證外掛所封鎖，這一可能性在此暫不考慮。';
$string['check_emailchangeconfirmation_error'] = '用戶可以輸入任何email地址';
$string['check_emailchangeconfirmation_info'] = '用戶只可以輸入下列網域的email地址';
$string['check_emailchangeconfirmation_name'] = 'Email更改的確認';
$string['check_emailchangeconfirmation_ok'] = '確認用戶在個人資料表email地址的變更';
$string['check_embed_details'] = '無限制的物件嵌入是非常危險的---任何註冊的用戶可以對其他伺服器用戶發動一次XSS攻擊。正式運作的網站應該將這設定關閉。';
$string['check_embed_error'] = '已經啟用無限制的物件鑲入---這對於大多數的伺服器都是非常危險的';
$string['check_embed_name'] = '允許EMBED 和 OBJECT';
$string['check_embed_ok'] = '不允許無限制的物件鑲入';
$string['check_frontpagerole_details'] = '<p>預設的首頁角色，是給予所有註冊用戶在參與首頁活動時所使用的角色。請確保該角色未被賦予高風險的權限。</p>
<p>我們建議您為此目的建立一個特殊角色，且不要給它賦予任何繼承類型的角色。</p>';
$string['check_frontpagerole_error'] = '錯誤！偵測到首頁角色定義錯誤';
$string['check_frontpagerole_name'] = '首頁角色';
$string['check_frontpagerole_notset'] = '首頁角色沒有設定';
$string['check_frontpagerole_ok'] = '首頁角色定義OK';
$string['check_google_details'] = '這"對Google開放"設定可以讓搜尋引擎以訪客身分進入課程。若已經不允許訪客登入，那開啟這設定是無效的。';
$string['check_google_error'] = '錯誤！你允許搜尋引擎存取，但關閉訪客存取已經關閉';
$string['check_google_info'] = '搜尋引擎可以用訪客身分進入';
$string['check_google_name'] = '對Google開放';
$string['check_google_ok'] = '不允許搜尋引擎進入存取';
$string['check_guestrole_details'] = '這訪客角色是指派給訪客、沒登入的用戶、和暫時的訪客課程存取。
請確定沒有高風險的權限被指派給這一角色。
訪客角色唯一能夠繼承的類型是<em>訪客</em>';
$string['check_guestrole_error'] = '錯誤！訪客角色"{$a}"沒有被正確定義！';
$string['check_guestrole_name'] = '訪客角色';
$string['check_guestrole_notset'] = '沒有設定訪客角色';
$string['check_guestrole_ok'] = '訪客角色定義OK';
$string['check_mediafilterswf_details'] = '自動的swf是非常危險的---任何註冊的用戶可以對其他伺服器用戶發動一次XSS攻擊。正式運作的網站應該將這設定關閉。';
$string['check_mediafilterswf_error'] = 'Flash媒體過濾器已經被啟用---這對大多數的伺服器來說是非常危險的。';
$string['check_mediafilterswf_name'] = '已經啟用 .swf 媒體過濾器';
$string['check_mediafilterswf_ok'] = '沒有啟用 Flash 媒體過濾器';
$string['check_noauth_details'] = '這<em>免認證</em>外掛套件不要用於正式網站的。請關閉它，除非這是一個測試用的網站。';
$string['check_noauth_error'] = '免認證外掛不可以用在正式網站。';
$string['check_noauth_name'] = '免認證';
$string['check_noauth_ok'] = '免認證外掛已被關閉';
$string['check_openprofiles_details'] = '開放個人資料表可能會被亂發廣告者濫用。建議您將<em>用戶登入後才可看到個人資料表</em>或<em>強制所有用戶登入</em>兩者之一開啟。';
$string['check_openprofiles_error'] = '任何人不需要登入，即可檢視用戶個人資料表';
$string['check_openprofiles_name'] = '開放用戶個人資料表';
$string['check_openprofiles_ok'] = '在檢視用戶個人資料表之前，要求先登入';
$string['check_passwordpolicy_details'] = '<p>建議您設定一個密碼管理規則，因為猜測密碼是最常見的非法入侵方法。同時您也不要把密碼管理規則設定的太苛刻，這會導致用戶無法記住他們的密碼，以至於若不是忘記密碼或就是得把密碼寫下來。</p>';
$string['check_passwordpolicy_error'] = '沒有設定密碼管理規則';
$string['check_passwordpolicy_name'] = '密碼管理規則';
$string['check_passwordpolicy_ok'] = '密碼管理規則已經啟用';
$string['check_riskadmin_detailsok'] = '<p>請驗證下列系統管理員的名單：</p>{$a}';
$string['check_riskadmin_detailswarning'] = '<p>請驗證下列系統管理員的名單：</p>{$a->admin}
<p>建議你只在系統脈絡中指派管理員角色。下列用戶在其他脈絡有(未支援的)管理員的角色指派：</p>
{$a->unsuported}';
$string['check_riskadmin_name'] = '管理員';
$string['check_riskadmin_ok'] = '找到{$a}位伺服器管理員';
$string['check_riskadmin_unassign'] = '<a href="{$a->url}">{$a->fullname} ({$a->email}) 審查角色指派</a>';
$string['check_riskadmin_warning'] = '找到{$a->admincount}位伺服器管理員和 {$a->unsupcount} 位沒支援管理員角色指派';
$string['check_riskbackup_detailsok'] = '沒有角色明顯允許備份用戶資料。但是有"做任何事"權限的管理員，仍然可以這樣做。';
$string['check_riskbackup_details_overriddenroles'] = '<p>這些主動覆蓋給予用戶有能力去包含用戶資料在備份檔中。請確定這一權限是必要的</p>{$a}';
$string['check_riskbackup_details_systemroles'] = '<p>下列的系統角色，現在允許用戶將用戶資料包含在備份中。請確定這一權限是必要的。<p>
{$a}';
$string['check_riskbackup_details_users'] = '<p>因為上述的角色或本地的覆蓋，下列用戶帳號現在有權限使備份檔包含所有選課用戶的私人資料。請確定他們是(a)可信任的(b)有很強的密碼保護著：</p>
{$a}';
$string['check_riskbackup_editoverride'] = '<a href="{$a->url}">{$a->name}在 {$a->contextname}</a>';
$string['check_riskbackup_editrole'] = '<a href="{$a->url}">{$a->name}</a>';
$string['check_riskbackup_name'] = '用戶資料的備份';
$string['check_riskbackup_ok'] = '沒有角色明確地允許備份用戶資料';
$string['check_riskbackup_unassign'] = '<a href="{$a->url}">{$a->fullname} ({$a->email})在{$a->contextname}</a>';
$string['check_riskbackup_warning'] = '找到{$a->rolecount}個角色，{$a->overridecount} 個覆蓋和 {$a->usercount} 位用戶有能力去備份用戶資料。';
$string['check_riskxss_details'] = '<p>RISK_XSS 展示所有的危險權限，他們只有可信任的用戶可以使用。</p>
<p>請查驗下列的用戶清單，並確定你可以在這伺服器上完全信任他們：</p><p>{$a}</p>';
$string['check_riskxss_name'] = 'XSS可信任的用戶';
$string['check_riskxss_warning'] = 'RISK_XSS --找到{$a}位可以被信任的用戶';
$string['check_unsecuredataroot_details'] = '資料根目錄必須不能讓他人透過網路存取。要讓它不會被他人存取，最好的方式是在公開網頁目錄(www)之外，另外建立目錄。

若你移動目錄，你要記得更新在<code>config.php</code> 裡的<code>$CFG->dataroot</code>的設定，改成新目錄的位置。';
$string['check_unsecuredataroot_error'] = '你的資料根目錄<ode>{$a}</code>放錯位置，且已經暴露在網路中。';
$string['check_unsecuredataroot_name'] = '不安全的dataroot目錄';
$string['check_unsecuredataroot_ok'] = '資料根目錄必須不能經由網路直接存取';
$string['check_unsecuredataroot_warning'] = '你的資料根目錄<ode>{$a}</code>放錯位置，且可能暴露在網路中。';
$string['check_webcron_details'] = '從網頁瀏覽器執行此網站的cron腳本可能會洩漏重要訊息給不知名的用戶。建議你從伺服器的指令行來執行cron腳本，或是設定要從遠端執行cron腳本所需要的密碼。';
$string['check_webcron_name'] = '網站cron(週期性執行指令)';
$string['check_webcron_ok'] = '匿名用戶不能執行cron(週期性執行指令)';
$string['check_webcron_warning'] = '匿名用戶不能執行cron(週期性執行指令)';
$string['configuration'] = '配置';
$string['description'] = '描述';
$string['details'] = '細節';
$string['issue'] = '問題';
$string['pluginname'] = '資安概況';
$string['security:view'] = '檢視資安報告';
$string['status'] = '狀態';
$string['statuscritical'] = '危急';
$string['statusinfo'] = '訊息';
$string['statusok'] = 'OK';
$string['statusserious'] = '嚴重';
$string['statuswarning'] = '警告';
$string['timewarning'] = '資料處理可能很花時間，請耐心等待';
