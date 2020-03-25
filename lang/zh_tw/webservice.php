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
 * Strings for component 'webservice', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   webservice
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accessexception'] = '存取控制例外狀況';
$string['actwebserviceshhdr'] = '啟動網上服務傳輸協定';
$string['addaservice'] = '新增服務';
$string['addcapabilitytousers'] = '檢查用戶的權限';
$string['addcapabilitytousersdescription'] = '用戶需要有兩種權限----
*webservice:createtoken (建立通行憑證)
*配合傳輸協定的權限，例如
webservice/rest:use, webservice/soap:use 。
授予權限的方法是，由管理員建立一網上服務角色，包含以上兩種權限，並像系統角色一樣，把它指派給網上服務的用戶。';
$string['addfunction'] = '新增功能';
$string['addfunctionhelp'] = '選擇一功能來新增到這一服務。';
$string['addfunctions'] = '新增功能';
$string['addfunctionsdescription'] = '為這新建立的服務選擇必要的功能。';
$string['addrequiredcapability'] = '指派/取消指派 必要的權限';
$string['addservice'] = '增加一新服務：{$a->name} (id: {$a->id})';
$string['addservicefunction'] = '新增功能到這服務"{$a}"';
$string['allusers'] = '所有用戶';
$string['apiexplorer'] = 'API探索器';
$string['apiexplorernotavalaible'] = 'API探索器還無法使用';
$string['arguments'] = '參數';
$string['authmethod'] = '認證方法';
$string['callablefromajax'] = '可從 AJAX 呼叫';
$string['cannotcreatetoken'] = '沒有為服務{$a}建立網路服務令牌的權限。';
$string['cannotgetcoursecontents'] = '無法取的課程內容';
$string['checkusercapability'] = '檢查用戶權限';
$string['checkusercapabilitydescription'] = '用戶應該依照使用的傳輸協定有適當的權限，例如 webservice/rest:use、webservice/soap:use。要指派這些權限，可以建立一個擁有這些權限的網路服務角色，並將它作為系統角色，指派給網路服務用戶。';
$string['configwebserviceplugins'] = '為保障安全，應該只啟用使用中的協定。';
$string['context'] = '情境';
$string['createservicedescription'] = '一個服務是網路服務功能的集合。您將允許此用戶訪問一個新的服務。在<strong>加入新服務</strong>頁面勾選"啟用"只"服務授權用戶"選項。選擇"沒有需要的權限"。';
$string['createserviceforusersdescription'] = '服務是網路服務函式的集合。您將允許用戶瀏覽一個新的服務。在<strong>加入服務</strong>頁面勾選"啟用"，不勾選"只服務授權用戶"選項。選擇"沒有需要的權限"。';
$string['createtoken'] = '建立通行憑證';
$string['createtokenforuser'] = '為一用戶建立通行憑證';
$string['createtokenforuserdescription'] = '為這網頁服務用戶建立通行憑證';
$string['createuser'] = '建立一特定的用戶';
$string['createuserdescription'] = '需要一個網路服務用戶來代表系統控制Moodle。';
$string['criteriaerror'] = '缺少權限來搜尋一個準則。';
$string['default'] = '預設為"{$a}"';
$string['deleteaservice'] = '刪除服務';
$string['deleteservice'] = '刪除這服務： {$a->name} (id: {$a->id})';
$string['deleteserviceconfirm'] = '刪除一服務也將刪除與這服務相關的通行憑證。你真的要刪除外部服務{$a}？';
$string['deletetokenconfirm'] = '你真的要刪除<strong>{$a->user}</strong> 在服務 <strong>{$a->service}</strong>上的網上服務通行憑證？';
$string['disabledwarning'] = '所有網路服務協定都被停用。在進階功能中有"啟用網路服務"選項。';
$string['doc'] = '文件';
$string['docaccessrefused'] = '您未被允許察看此通行憑證的文件';
$string['documentation'] = '網上服務文件';
$string['downloadfiles'] = '可以下載檔案';
$string['downloadfiles_help'] = '如果啟用，任何用戶都可以使用他們的
安全密鑰下載文件。當然，他們只能從該網站下載允許他們下載的文件。';
$string['editaservice'] = '編輯服務';
$string['editservice'] = '編輯這服務 {$a->name} (id: {$a->id})';
$string['enabled'] = '已經啟用';
$string['enabledocumentation'] = '啟用開發者文件';
$string['enabledocumentationdescription'] = '啟用傳輸協定後，可以使用詳細的網上服務文件。';
$string['enablemobilewsoverview'] = '瀏覽{$a->manageservicelink}管理頁面，勾選“{$a->enablemobileservice}”設定並儲存。一切都會為您準備好，所有網站用戶都將可以使用官方Moodle app。目前的狀態：{$a->wsmobilestatus}';
$string['enableprotocols'] = '啟用傳輸協定';
$string['enableprotocolsdescription'] = '至少要啟動一種傳輸協定。為了安全理由，只有實際被使用的傳輸協定才啟用';
$string['enablews'] = '啟用 Web服務';
$string['enablewsdescription'] = '網上服務必須在進階功能中啟用。';
$string['entertoken'] = '輸入一安全密鑰';
$string['error'] = '錯誤：{$a}';
$string['errorcatcontextnotvalid'] = '您不能在類別情境(類別id：{$a->catid}）中執行函數。情境的錯誤訊息是：{$a->message}';
$string['errorcodes'] = '錯誤訊息';
$string['errorcoursecontextnotvalid'] = '您不能在課程情境(課程id：{$a->courseid})中執行函數。情境錯誤資訊是：{$a->message}';
$string['errorinvalidparam'] = '這參數"{$a}"是無效的';
$string['errornotemptydefaultparamarray'] = '名為“{$a}”的網路服務描述參數是一個或多個結構。預設值只能是空陣列。請檢查網路服務敘述。';
$string['erroroptionalparamarray'] = '名為“{$a}”的網路服務描述參數是一個或多個結構。它不能被設定為VALUE_OPTIONAL。請檢查網路服務描述。';
$string['eventwebservicefunctioncalled'] = '網頁服務的功能已呼叫';
$string['eventwebserviceloginfailed'] = '網頁服務登入失敗';
$string['eventwebserviceservicecreated'] = '網頁服務的服務項目已經建立';
$string['eventwebserviceservicedeleted'] = '網頁服務的服務已被刪除';
$string['eventwebserviceserviceupdated'] = '網頁服務的服務項目已經更新';
$string['eventwebserviceserviceuseradded'] = '網頁服務所服務的用戶已經添加';
$string['eventwebserviceserviceuserremoved'] = '網頁服務所服務的用戶已經移除';
$string['eventwebservicetokencreated'] = '網頁服務憑證已經建立';
$string['eventwebservicetokensent'] = '網頁服務憑證已經送出';
$string['execute'] = '執行';
$string['executewarnign'] = '警告：如果您點了執行按鈕，您的資料庫會被修改，並且不能自動恢復這些變更！';
$string['externalservice'] = '外部服務';
$string['externalservicefunctions'] = '外部服務功能';
$string['externalservices'] = '外部服務';
$string['externalserviceusers'] = '外部服務用戶';
$string['failedtolog'] = '無法建立日誌';
$string['filenameexist'] = '檔案名稱已經存在：{$a}';
$string['forbiddenwsuser'] = '無法為一個沒被認證的、被刪除的、被停權的、或訪客用戶建立通行憑證。';
$string['function'] = '功能';
$string['functions'] = '功能';
$string['generalstructure'] = '通用架構';
$string['information'] = '資訊';
$string['installexistingserviceshortnameerror'] = '一個簡稱為"{$a}"的網上服務已經存在。無法安裝/更新另一種有相同簡稱的網上服務。';
$string['installserviceshortnameerror'] = '來源程式碼錯誤：服務的簡稱"{$a}"應該只包含數字、字母和_=..。';
$string['invalidextparam'] = '無效的外部API參數：{$a}';
$string['invalidextresponse'] = '無效的外部API反應：{$a}';
$string['invalidiptoken'] = '無效的通行憑證 -- 你的IP不被支援';
$string['invalidtimedtoken'] = '無效的通行憑證 -- 通行憑證過期';
$string['invalidtoken'] = '無效的通行憑證 -- 找不到通行憑證';
$string['iprestriction'] = 'IP位址限制';
$string['iprestriction_help'] = '用戶將需要從這列表中的IP位址呼叫網頁服務(以逗點隔開)';
$string['key'] = '密鑰';
$string['keyshelp'] = '這密鑰是用來從外部應用程式存取你的Moodle。';
$string['loginrequired'] = '只限用於已經登入的用戶';
$string['manageprotocols'] = '管理傳輸協定';
$string['managetokens'] = '管理通行憑證';
$string['missingcaps'] = '少了權限';
$string['missingcaps_help'] = '以下列表是被選出用戶要使用這服務時，需要有而卻沒有的權限。缺少的權限必須加到這用戶的角色，才能使用這服務。';
$string['missingpassword'] = '少了密碼';
$string['missingrequiredcapability'] = '必需要有{$a}權限';
$string['missingusername'] = '少了用戶名稱';
$string['missingversionfile'] = '程式碼錯誤：元件{$a}找不到version.php檔案';
$string['mobilewsdisabled'] = '停用';
$string['mobilewsenabled'] = '啟用';
$string['nameexists'] = '這一名稱已經被其他服務所使用';
$string['nocapabilitytouseparameter'] = '這用戶沒有必需的權限來使用這參數{$a}';
$string['nofunctions'] = '這一服務沒有功能。';
$string['norequiredcapability'] = '沒有需要的權限';
$string['notoken'] = '這通行憑證清單是空白的';
$string['onesystemcontrolling'] = '允許一外部系統來控制Moodle';
$string['onesystemcontrollingdescription'] = '下面步驟可以協助你設定 Moodle 網路服務，來讓外部系統可以與 Moodle 互動。這包含設定通行憑證(安全密鑰)的認證方法。';
$string['operation'] = '操作';
$string['optional'] = '可選用的';
$string['passwordisexpired'] = '密碼已經過期';
$string['phpparam'] = 'XML-RPC (PHP 結構)';
$string['phpresponse'] = 'XML-RPC (PHP 結構)';
$string['postrestparam'] = 'REST(POST request)的PHP程式碼';
$string['potusers'] = '沒有已經認證的用戶';
$string['potusersmatching'] = '沒有已經認證的用戶符合';
$string['print'] = '印出全部';
$string['protocol'] = '傳輸協定';
$string['removefunction'] = '移除';
$string['removefunctionconfirm'] = '您確定要從服務“{$a->service}”刪除功能“{$a->function}”嗎？';
$string['requireauthentication'] = '此方法需要使用xxx權限認證。';
$string['required'] = '必要的';
$string['requiredcapability'] = '必要的權限';
$string['requiredcapability_help'] = '如果設定，只有對應權限的用戶才能存取此服務。';
$string['requiredcaps'] = '必要的權限';
$string['resettokenconfirm'] = '您確定要重置<strong>{$a->user}</strong>的<strong>{$a->service}</strong>服務網路服務密鑰嗎？';
$string['resettokenconfirmsimple'] = '您想重置這個密鑰嗎？所有使用舊密鑰的連結都將失效。';
$string['response'] = '反應';
$string['restcode'] = 'REST';
$string['restexception'] = 'REST';
$string['restoredaccountresetpassword'] = '還原的帳號在獲得密鑰前需要重置密碼。';
$string['restparam'] = 'REST( POST參數)';
$string['restrictedusers'] = '只有被授權的用戶';
$string['restrictedusers_help'] = '此設定是決定所有的用戶都可以在他們的安全密鑰頁面為此服務建立通行憑證，還是只有被授權的用戶可以。';
$string['securitykey'] = '安全密鑰(通行憑證)';
$string['securitykeys'] = '安全密鑰';
$string['selectauthorisedusers'] = '選擇已被授權的用戶';
$string['selectedcapability'] = '選出的';
$string['selectedcapabilitydoesntexit'] = '目前設定為必要的權限({$a})已經不再存在。請修改它並且儲存更改。';
$string['selectservice'] = '選擇一種服務';
$string['selectspecificuser'] = '選擇一特定的用戶';
$string['selectspecificuserdescription'] = '新增這網上服務的用戶，如同已被認證的用戶';
$string['service'] = '服務';
$string['servicehelpexplanation'] = '服務是一個多種功能的集合。可以讓所有用戶或只給指定的用戶存取。';
$string['servicename'] = '服務名稱';
$string['servicenotavailable'] = '網路服務無法使用(它不存在或者已經停用)';
$string['servicesbuiltin'] = '內建的服務';
$string['servicescustom'] = '自訂的服務';
$string['serviceusers'] = '已授權的用戶';
$string['serviceusersettings'] = '用戶設定';
$string['serviceusersmatching'] = '已授權用戶配對';
$string['serviceuserssettings'] = '為已經被授權的用戶更改設定';
$string['shortnametaken'] = '簡稱已經被其它服務 ({$a})使用了。';
$string['simpleauthlog'] = '簡單認證登入';
$string['step'] = '步驟';
$string['supplyinfo'] = '更多細節';
$string['testauserwithtestclientdescription'] = '用網路服務測試客戶端模擬外部存取這服務。
在開始之前，先要用有moodle/webservice:createtoken權限的用戶並透過我的個人資料設定獲得密鑰(憑證)。您將在測試客戶端使用此憑證。在測試客戶端中，用憑證認證選擇一個已經啟用的傳輸協定。<strong>警告：您測試的功能會被真的執行，所以小心選擇測試什麼！</strong>';
$string['testclient'] = '網上服務測試客戶';
$string['testclientdescription'] = '*此網路服務測試客戶端會<strong>真的執行</strong>被測試函數。請不要測試您不了解的函數。<br />
*此測試客戶端並沒有實現所有的網路服務<br />
*您可以透過測試一些停用的函數，來檢查用戶能否訪問它們。<br />
*要看到更清楚的錯誤訊息，請到"“{$a->atag}”頁面把除錯設定由<strong>{$a->mode}</strong> 改為 {$a->atag}<br/>
*瀏覽 {$a->amfatag}。';
$string['testwithtestclient'] = '測試這一服務';
$string['testwithtestclientdescription'] = '用網路服務測試客戶模擬外部存取。使用一個已經啟用的傳輸協定及以認證過的通行憑證(token)。<strong>警告：您測試的函數會被真的執行，所以小心你選擇測試什麼！</strong>';
$string['token'] = '通行憑證';
$string['tokenauthlog'] = '通行憑證的認證';
$string['tokencreatedbyadmin'] = '只能由管理員重置 (*)';
$string['tokencreator'] = '建立者';
$string['unknownoptionkey'] = '未知的可選用密鑰({$a})';
$string['unnamedstringparam'] = '一個字串參數沒被命名';
$string['updateusersettings'] = '更新用戶設定';
$string['uploadfiles'] = '可以上傳檔案';
$string['uploadfiles_help'] = '若啟用，任何用戶都可以用他們的安全密鑰上傳檔案到他們自己的私人檔案區或草稿檔案區。這會使用到用戶檔案配額。';
$string['userasclients'] = '所有用戶視為有通行憑證的客戶';
$string['userasclientsdescription'] = '下面步驟幫助您為用戶設定Moodle的網路服務。這些步驟也幫助您設定建議採用的通行憑證(安全密鑰)認證方法。在這種情況下，用戶可以在我的個人資料設定中的安全密鑰頁面產生他的通行憑證。';
$string['usermissingcaps'] = '少了權限：{$a}';
$string['usernameorid'] = '用戶名稱 / 用戶編號';
$string['usernameorid_help'] = '輸入一個用戶名稱 / 用戶編號';
$string['usernameoridnousererror'] = '未能找到這一用戶名稱/用戶編號的用戶。';
$string['usernameoridoccurenceerror'] = '發現有多位用戶使用這一用戶名稱，請輸入用戶編號。';
$string['usernotallowed'] = '此用戶無法使用此一服務。首先，您需要在{$a}的許可用戶管理頁面上，允此用戶使用。';
$string['usersettingssaved'] = '用戶設定已經儲存';
$string['validuntil'] = '有效日期到';
$string['validuntil_help'] = '若設定，這服務在這一日期之後，會對此用戶關閉。';
$string['webservice'] = '網路服務';
$string['webservices'] = '網路服務';
$string['webservicesoverview'] = '綜覽';
$string['webservicetokens'] = '網路服務通行憑證';
$string['wrongusernamepassword'] = '錯誤的用戶名稱或密碼';
$string['wsaccessuserdeleted'] = '拒絕網路服務的存取，因為使用已被刪除的用戶名稱：{$a}';
$string['wsaccessuserexpired'] = '拒絕網路服務的存取，因為密碼已經過期，用戶名稱：{$a}';
$string['wsaccessusernologin'] = '拒絕網路服務的存取，因為沒有登入認證，用戶名稱：{$a}';
$string['wsaccessusersuspended'] = '拒絕網路服務的存取，因為被停權，用戶名稱：{$a}';
$string['wsaccessuserunconfirmed'] = '拒絕網路服務的存取，因為沒被確認，用戶名稱：{$a}';
$string['wsclientdoc'] = 'Moodle 網路服務客戶文件';
$string['wsdocapi'] = 'API文件';
$string['wsdocumentation'] = '網路服務文檔';
$string['wsdocumentationdisable'] = '網路服務文檔被關閉';
$string['wsdocumentationintro'] = '要建立客戶，我們建議你先讀這文件{$a->doclink}';
$string['wsdocumentationlogin'] = '或輸入你的網路際服務用戶名稱和密碼：';
$string['wspassword'] = '網路服務密碼';
$string['wsusername'] = '網路服務用戶名稱';
