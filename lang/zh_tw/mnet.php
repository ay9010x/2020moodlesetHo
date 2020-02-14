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
 * Strings for component 'mnet', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   mnet
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aboutyourhost'] = '關於您的伺服器';
$string['accesslevel'] = '存取層次';
$string['addhost'] = '增加主機';
$string['addnewhost'] = '增加一個新主機';
$string['addtoacl'] = '新增到存取控制清單';
$string['allhosts'] = '全部主機';
$string['allhosts_no_options'] = '察看多台主機時無可用選項';
$string['allow'] = '允許';
$string['applicationtype'] = '應用類型';
$string['authfail_nosessionexists'] = '認證失敗：mnet連線session不存在。';
$string['authfail_sessiontimedout'] = '認證失敗：mnet連線session逾時。';
$string['authfail_usermismatch'] = '認證失敗：使用者不符合。';
$string['authmnetdisabled'] = 'Moodle網路<em>認證外掛</em>已經被<strong>停用</strong>。';
$string['badcert'] = '這不是合法的證書。';
$string['certdetails'] = '認證細節';
$string['configmnet'] = 'MNet支援此伺服器與其他伺服器或服務之間的通訊。';
$string['couldnotgetcert'] = '在<br />{$a} <br />沒有找到證書。主機可能已經當機或是設定錯誤。';
$string['couldnotmatchcert'] = '這和目前web伺服器發佈的證書不符合。';
$string['courses'] = '課程';
$string['courseson'] = '課程在';
$string['currentkey'] = '現在的公鑰';
$string['current_transport'] = '現在的傳輸';
$string['databaseerror'] = '無法將內容寫到這資料庫';
$string['deleteaserver'] = '刪除一個伺服器';
$string['deletedhostinfo'] = '此主機已經刪除。如果您想還原它，就把刪除狀態改為"否"。';
$string['deletedhosts'] = '已經刪除主機：{$a}';
$string['deletehost'] = '刪除主機';
$string['deletekeycheck'] = '您確定要刪除這個密鑰嗎？';
$string['deleteoutoftime'] = '60秒刪除密鑰的視窗已經逾時了，請重新開始。';
$string['deleteuserrecord'] = '單一登入存取控制機制：從\'{$a->host}\'中刪除用戶\'{$a->user}\'的記錄。';
$string['deletewrongkeyvalue'] = '發生了一個錯誤。如果您沒有刪除伺服器上的SSL密鑰，則很有可能您已經成為惡意攻擊的對象。未進行任何操作。';
$string['deny'] = '拒絕';
$string['description'] = '描述';
$string['duplicate_usernames'] = '我們未能創造一個索引，在您的用戶表上“ mnethostid ”和“使用者”的欄位中。您複製使用者的在您的用戶表時，可能發生。您的升級應仍順利完成。按一下上面的鏈接，將顯示在一個新視窗來修正此問題。您可以最後再升級。';
$string['enabled_for_all'] = '(這個服務已經對所有網站啟動)。';
$string['enterausername'] = '請輸入一個使用名稱，或是以逗號分隔的使用者清單。';
$string['error7020'] = '這個錯誤通常發生，如果遠端站台為您創造了一個帶有錯誤wwwroot紀錄，例：http://yoursite.com 而非http://www.yoursite.com，你應該與遠端站的管理員聯繫點與您的wwwroot，讓她為您的主機更新她的紀錄';
$string['error7022'] = '您發送到遠程站點的郵件是加密的，但沒有簽名。這是非常意外，如果發生這種情況，您應該上報程式錯誤(盡可能多給訊息，有問題的程式版本等等)';
$string['error7023'] = '遠程站點試圖使用所有的方法將您的訊息解密。他們都失敗了。您或許能夠解決這個問題，使用手動重新鍵控與遠程站點。這是不可能發生的，除非你已經沒有與遠程站點溝通已經數個月';
$string['error7024'] = '您發送未加密的信息到遠端站台，但遠程站點不接受從您的網站的未加密的溝通，這是非常意外，如果發生這種情況，您應該上報程式錯誤(盡可能多給訊息，有問題的程式版本等等)';
$string['error7026'] = '關鍵是您的留言已簽署了與不同的關鍵遠程主機已對文件進行了您的伺服器。此外，遠程主機的企圖取得您當前的key，並沒有這樣做，請手動再試一次重新核對與遠程主機';
$string['error709'] = '遠端站台未能從你那邊獲取一個SSL的key';
$string['eventaccesscontrolcreated'] = '已建立存取控制';
$string['eventaccesscontrolupdated'] = '已建立存取控制';
$string['expired'] = '密鑰過期於';
$string['expires'] = '有效至';
$string['expireyourkey'] = '刪除這個密鑰';
$string['expireyourkeyexplain'] = 'moodle會自動每隔28天輪換您的密碼（預設)，但你也可以在任何時候選擇<em>手動方式</em>使這個密碼過期。如果您認為此密碼已經外洩，這將會是很有用的。替換的密碼會立即自動生成。

刪去這一密碼將會變得無法與其他moodles溝通，要直到您以人工方式聯繫每一個管理員，並提供您的新密碼給他們。';
$string['exportfields'] = '要匯出的欄位';
$string['failedaclwrite'] = '無法將用戶\'{$a}\'寫入Moodle網路的存取控制清單(ACL)';
$string['findlogin'] = '尋找登入';
$string['forbidden-function'] = '對RPC而言該功能尚未啟用';
$string['forbidden-transport'] = '您正在嘗試使用的傳輸方法是不容許';
$string['forcesavechanges'] = '強制儲存變更';
$string['helpnetworksettings'] = '配置MNet的溝通';
$string['hidelocal'] = '隱藏本地端的用戶';
$string['hideremote'] = '隱藏遠端的用戶';
$string['host'] = '主機';
$string['hostcoursenotfound'] = '主機或課程沒有發現';
$string['hostdeleted'] = '主機已被刪除';
$string['hostexists'] = '有一筆紀錄說明有這只機名稱的主機已經存在(它可能已被刪除)， <a href="{$a}">按此繼續編輯</a> 這筆記錄。';
$string['hostlist'] = '網路主機清單';
$string['hostname'] = '主機名稱';
$string['hostnamehelp'] = '完整合格的遠端主機網域名稱，例：www.example.com';
$string['hostnotconfiguredforsso'] = '這一伺服器未配置作為遠端登入之用。';
$string['hostsettings'] = '主機各項設定';
$string['http_self_signed_help'] = '允許使用在遠端主機上的自已簽名的DIY SSL憑證進行連接';
$string['https_self_signed_help'] = '允許使用自我簽名的diy的SSL在PHP上連接，對遠端主機超過HTTP';
$string['https_verified_help'] = '允許使用在遠端主機上的以驗證過的SSL憑證進行連接';
$string['http_verified_help'] = '允許使用遠端主機上的在PHP上經過認證的SSL憑證進行連接，但是是經由HTTP（不是https）。';
$string['id'] = 'ID';
$string['idhelp'] = '這個值是自動分配且不能被改變的';
$string['importfields'] = '要匯入的欄位';
$string['inspect'] = '檢查';
$string['installnosuchfunction'] = '原始碼錯誤！正試圖從檔案（{$a->file}）安装mnet xmlrpc函数（{$a->method}），但是檔案並不存在！';
$string['installnosuchmethod'] = '原始碼錯誤！正試安裝類別（{$a->class}）中的mnet xmlrpc函数（{$a->method}），但是它並不存在！';
$string['installreflectionclasserror'] = '原始碼錯誤！MNet  introspection 呼叫類別“{$a->class}”的“{$a->method}”方法失敗。原始的錯誤訊息是：“{$a->error}”';
$string['installreflectionfunctionerror'] = '原始碼錯誤！MNet  introspection 呼叫檔案“{$a->file}”中的“{$a->method}”函數失敗。原始的錯誤訊息是：“{$a->error}”';
$string['invalidaccessparam'] = '無效的存取參數';
$string['invalidactionparam'] = '無效的動作參數';
$string['invalidhost'] = '您必須提供有效的主機標識';
$string['invalidpubkey'] = '識別碼不是一個有效的SSL識別碼';
$string['invalidurl'] = '無效的URL參數';
$string['ipaddress'] = 'IP 位址';
$string['is_in_range'] = '這IP位址 <code>{$a}</code>  代表了一個有效值得信賴的主機';
$string['ispublished'] = '{$a} 已為您啟用這項服務';
$string['issubscribed'] = '{$a}正在您的主機上訂閱該服務。';
$string['keydeleted'] = '您的密碼已成功刪除且被取代';
$string['keymismatch'] = '你持有的此主機的公鑰，是不同於目前公佈的公鑰。目前公布的公鑰是：';
$string['last_connect_time'] = '最後連線時間';
$string['last_connect_time_help'] = '上次連接到主機的時間';
$string['last_transport_help'] = '上次連結該主機使用的傳輸方式。';
$string['leavedefault'] = '使用預設的參數代替';
$string['listservices'] = '服務列表';
$string['loginlinkmnetuser'] = '<br/>如果您是MNet遠端用戶，可以<a href="{$a}">在這裡確認email地址</a>您可能重新導向到登入頁面。<br />';
$string['logs'] = '日誌';
$string['managemnetpeers'] = '管理對等(peer)';
$string['method'] = '方法';
$string['methodhelp'] = '{$a}的方法幫助';
$string['methodsavailableonhost'] = '{$a}的可用方法';
$string['methodsavailableonhostinservice'] = '{$a->host}上可用在{$a->service}的方法';
$string['methodsignature'] = '{$a}的方法簽證';
$string['mnet'] = 'Moodle 網路';
$string['mnet_concatenate_strings'] = '串連（最多)3個字元串及返回結果';
$string['mnetdisabled'] = 'Moodle 網路<strong>已被關閉</strong>';
$string['mnetidprovider'] = 'MNet ID 提供者';
$string['mnetidproviderdesc'] = '您可以使用這這施來取得一鏈結，讓你來登入。如果您能提供正確的email地址來配對您先前用來登入的用戶名稱。';
$string['mnetidprovidermsg'] = '您應該在{$a}登入。';
$string['mnetidprovidernotfound'] = '很抱歉，沒有找到更多的資訊。';
$string['mnetpeers'] = 'Peers';
$string['mnetservices'] = '服務';
$string['mnet_session_prohibited'] = '來自您的主伺服器中的用戶，目前不允許漫遊到到{$a}。';
$string['mnetsettings'] = 'MNet網路設定';
$string['moodle_home_help'] = '在遠程伺服器中，到MNet應用首頁的路徑，例如：/moodle/.';
$string['name'] = '名稱';
$string['net'] = '網路';
$string['networksettings'] = '網路設定';
$string['never'] = '從不';
$string['noaclentries'] = '在SSO存取控制清單中，沒有任何記錄。';
$string['noaddressforhost'] = '抱歉，此主機名稱無法被解析！';
$string['nocurl'] = '沒有安裝PHP cURL 函數庫';
$string['nolocaluser'] = '沒有對應遠端用戶的本地記錄，並且因為主機不能自動建立用戶，所以不能建立記錄。請聯絡您的管理員！';
$string['nomodifyacl'] = '您無權修改MNet瀏覽控制列表。';
$string['nonmatchingcert'] = '認證主題<br /><em>{$a->subject}</em><br />和主機中的紀錄不匹配：<br /><em>{$a->host}</em>。';
$string['nopubkey'] = '擷取公開金鑰遇到問題。<br />可能是主機不允許MNet瀏覽或者金鑰是無效的。';
$string['nosite'] = '不能找到網站等級的課程';
$string['nosuchfile'] = '檔案/函數{$a}不存在。';
$string['nosuchfunction'] = '無法找到函數，或者函數停用RPC。';
$string['nosuchmodule'] = '函數位址不正確，不能被定位。請使用mod/modulename/lib/函數名稱 格式。';
$string['nosuchpublickey'] = '函數位址不正確，不能被定位。請使用mod/modulename/lib/函數名稱 格式。';
$string['nosuchservice'] = 'RPC在主機上無法運行。';
$string['nosuchtransport'] = '傳送ID不存在';
$string['notBASE64'] = '此字串不是base64編碼，所以他不可能是有效的金鑰。';
$string['notenoughidpinfo'] = '您的身份提供者沒有提供足夠的資訊，不能在本地建立或更新您的帳號。抱歉！';
$string['not_in_range'] = 'IP位址<code>{$a}</code>並非一個有效的可信任主機。';
$string['notinxmlrpcserver'] = '沒有在XMLRPC伺服器運作時試著瀏覽MNet遠端客戶端';
$string['notmoodleapplication'] = '警告：這不是一個Moodle應用，所以一些檢查方法可能不能正常工作。';
$string['notPEM'] = '金鑰不是以PEM格式，所以無法使用。';
$string['notpermittedtojump'] = '您沒有權限從該Moodle伺服器啟動一個遠端session。';
$string['notpermittedtojumpas'] = '當您以其他用戶身分登入時，你不能開始一個遠端session。';
$string['notpermittedtoland'] = '您沒有權限啟動遠端session。';
$string['off'] = '關';
$string['on'] = '開';
$string['options'] = '選項';
$string['peerprofilefielddesc'] = '在這裡，您可以覆蓋建立新用戶時發送和匯入哪些個人資料欄位的全面設定。';
$string['permittedtransports'] = '允許傳送';
$string['phperror'] = '內部PHP錯誤停止了您的要求。';
$string['position'] = '位置';
$string['postrequired'] = '刪除函數需要一個POST請求。';
$string['profileexportfields'] = '傳送的欄位';
$string['profilefielddesc'] = '在這裡，您可以透過設定建立或更新帳號時通過MNet傳送和接收哪些個人資料欄位。您也可以為每個MNet伙伴單獨設定這些設定。注意，會永遠傳送下面的選項：{$a}';
$string['profilefields'] = '個人資料表的欄位';
$string['profileimportfields'] = '要匯入的欄位';
$string['promiscuous'] = '混雜的';
$string['publickey'] = '公鑰';
$string['publickey_help'] = '此公共金鑰是從遠端伺服器自動取得';
$string['publickeyrequired'] = '您必須提供一個公鑰';
$string['publish'] = '公開';
$string['reallydeleteserver'] = '確定刪除伺服器嗎？';
$string['receivedwarnings'] = '接收到下面的警告';
$string['recordnoexists'] = '記錄不存在';
$string['reenableserver'] = '否，選擇該選項重新啟用伺服器';
$string['registerallhosts'] = '註冊所有主機（混亂模式）';
$string['registerallhostsexplain'] = '您可以選擇自動註冊所有嘗試連接您的主機。這意味著您的主機中將會出現一個紀錄錶，其中包含了所有與您互連，並請求您的公鑰的MNet網站。<br/>您可以在下面的選項為"所有主機"設定服務。通過啟用一些服務，可以為任何遠端網站提供任何服務。';
$string['registerhostsoff'] = '現在<b>關閉</b>註冊所有主機';
$string['registerhostson'] = '現在<b>開放</b>註冊所有主機';
$string['remotecourses'] = '遠端的課程';
$string['remotehost'] = '遠端的主機';
$string['remotehosts'] = '遠端的主機';
$string['remoteuser'] = '遠端 {$a->remotetype}用戶';
$string['remoteuserinfo'] = '遠端{$a->remotetype}用戶 - 個人資料取自<a href="{$a->remoteurl}">{$a->remotename}</a>';
$string['requiresopenssl'] = 'Moodle網路需要 OpenSSL 延伸套件';
$string['restore'] = '復原';
$string['returnvalue'] = '返回值';
$string['reviewhostdetails'] = '檢閱主機細節';
$string['reviewhostservices'] = '檢閱主機服務';
$string['RPC_HTTP_PLAINTEXT'] = 'HTTP(未加密)';
$string['RPC_HTTP_SELF_SIGNED'] = 'HTTP(自我簽名)';
$string['RPC_HTTPS_SELF_SIGNED'] = 'HTTPS(自我簽名)';
$string['RPC_HTTPS_VERIFIED'] = 'HTTPS(已簽名)';
$string['RPC_HTTP_VERIFIED'] = 'HTTP(已簽名)';
$string['selectaccesslevel'] = '請從列表中選擇一個存取層次';
$string['selectahost'] = '請選擇一個遠端主機。';
$string['service'] = '服務名稱';
$string['serviceid'] = '服務辨識編號';
$string['servicesavailableonhost'] = '{$a}上的可用服務';
$string['serviceswepublish'] = '服務發佈給{$a}';
$string['serviceswesubscribeto'] = '訂閱{$a}上的服務';
$string['settings'] = '設定';
$string['showlocal'] = '顯示本地的用戶';
$string['showremote'] = '顯示遠端的用戶';
$string['ssl_acl_allow'] = '單一登入存取控制機制：允許用戶\'{$a->user}使用\'{$a->host}\'';
$string['ssl_acl_deny'] = '單一登入存取控制機制：拒絕用戶\'{$a->user}使用\'{$a->host}\'';
$string['sslverification'] = 'SSL驗證';
$string['sslverification_help'] = '這一選項允許你在使用HTTPS連結到一同儕電腦時設定它的的安全層次。

*無：無安全層次
*只驗證主機：驗證這憑證的域名
*驗證主機和同儕機：(建議的)驗證這憑證的域名和發行者。';
$string['ssoaccesscontrol'] = '單一登入存取控制';
$string['ssoacldescr'] = '使用此頁面允許/拒絕從遠端MNet主機來瀏覽指定的使用者。當您為遠端使用者提供SSO服務時，這個功能非常有用，為了控制您的<em>本地</em>使用者漫遊到其他MNet主機的能力，使用角色系統分配它們<em>mnetlogintoremote</em>權限。';
$string['ssoaclneeds'] = '為了讓這個功能起作用，您必須將網路打開，並啟用MNet認證外掛。';
$string['strict'] = '嚴密的';
$string['subscribe'] = '訂約';
$string['system'] = '系統';
$string['testclient'] = 'MNet測試客戶端';
$string['testtrustedhosts'] = '測試一個位址';
$string['testtrustedhostsexplain'] = '輸入一個IP位址，看是否是一個值得信賴的主機。';
$string['theypublish'] = '他們發佈';
$string['theysubscribe'] = '他們訂閱';
$string['transport_help'] = '這些選項是相互的，因此您只可以強制一個遠端主機使用一簽名的SSL認證，若您的主機也有一簽名的SSL認證。';
$string['trustedhosts'] = 'XML-RPC主機';
$string['trustedhostsexplain'] = '<p>信任主機機制允許指定的主機通過XML-RPC調整任意的Moodle API。這樣就可以使用指令碼控制Moodle的行為，但啟用它也是非常危險的。如果感到迷惑不解，可以將其"關閉"。</p><p><strong>沒有任何標準的MNet特性需要它！</p><p>啟用方法是，在每一行輸入一個IP位址或網路的列表。例如：</p><p>您的本地主機：<br />127.0.0.1<br />您的本地主機(帶掩碼)：<br />127.0.0.1/32<br />IP位址為192.168.0.7的主機：<br />192.168.0.7/32<br />IP在192.168.0.1到192.168.0.255之間的所有主機：<br />192.168.0.0/24<br />任何主機，不管是啥：<br />192.168.0.0/0<br />顯然，最後一個例子<strong>不是</strong>建議配置。';
$string['turnitoff'] = '關閉';
$string['turniton'] = '打開';
$string['type'] = '類型';
$string['unknown'] = '未知';
$string['unknownerror'] = '在通訊交涉中發生不明的錯誤';
$string['usercannotchangepassword'] = '由於您是遠端用戶，所以不能變更密碼。';
$string['userchangepasswordlink'] = '<br />您也許可以在<a href="{$a->wwwroot}/login/change_password.php">{$a->description}</a>提供者那裡修改密碼。';
$string['usernotfullysetup'] = '您的帳號不完整。您需要<a href="{$a}">回到您的資訊提供者</a>並確定您在哪裡的個人資料是完整的。可能需要您重新登入才能生效。';
$string['usersareonline'] = '警告：目前該伺服器有{$a}名用戶登入到您的網站。';
$string['validated_by'] = '通過網路認證： <code>{$a}</code>';
$string['verifyhostandpeer'] = '驗證主機和伙伴點';
$string['verifyhostonly'] = '只驗證主機';
$string['verifysignature-error'] = '簽名驗證失敗。發生錯誤。';
$string['verifysignature-invalid'] = '簽名驗證失敗。';
$string['version'] = '版本';
$string['warning'] = '警告';
$string['wrong-ip'] = '您的IP位址與我們記錄中的位址不相符。';
$string['xmlrpc-missing'] = '您需要在PHP中安裝XML-RPC才能使用這個功能。';
$string['yourhost'] = '您的主機';
$string['yourpeers'] = '您的夥伴點(Peer)';
