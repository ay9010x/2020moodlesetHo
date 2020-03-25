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
 * Strings for component 'lti', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   lti
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accept'] = '接受';
$string['accepted'] = '已接受';
$string['accept_grades'] = '從這工具接受分數';
$string['accept_grades_admin'] = '從這工具接受分數';
$string['accept_grades_admin_help'] = '指定的工具提供者可以新增、更新、讀取、刪除分數。

某些工具提供者可以加入、更新、讀取、刪除、與這個工具類型實體的成績。一些工具提供支援報告等級到Moodle在該工具中所採取的行動基礎上，建立一個更加一體化的體驗。';
$string['accept_grades_help'] = '指定這工具提供者能否添加、更新、讀取和刪除與這外部工具有關的分數。

一些工具提供者能夠把學生在工具上參與活動的成績報告到Moodle上，而建立一個更加一體化的體驗。

注意！這一設定可能會覆寫工具配置。';
$string['action'] = '動作';
$string['activate'] = '啟用';
$string['activatetoadddescription'] = '在你添加一段說明之前，你需要先啟動這一工具';
$string['active'] = '活動';
$string['activity'] = '活動';
$string['addnewapp'] = '啟用外部應用程式';
$string['addserver'] = '新增一可信任的伺服器';
$string['addtype'] = '新增事先配置的工具';
$string['allow'] = '允許';
$string['allowinstructorcustom'] = '允許教師添加自訂參數';
$string['allowsetting'] = '允許工具在Moodle儲存8K的設定值';
$string['always'] = '永遠';
$string['autoaddtype'] = '添加工具';
$string['automatic'] = '自動的，依據啟動網址';
$string['baseurl'] = '基本網址/工具註冊名稱';
$string['basiclti'] = '學習工具互通';
$string['basicltiactivities'] = '學習工具互通性活動';
$string['basiclti_base_string'] = '外部工具互動的官方授權基礎字串';
$string['basiclti_endpoint'] = '學習工具互通性啟動端點';
$string['basicltifieldset'] = '自訂範例欄位集';
$string['basiclti_in_new_window'] = '活懂已經顯示在新的視窗';
$string['basicltiintro'] = '活動說明';
$string['basicltiname'] = '活動名稱';
$string['basiclti_parameters'] = '學習工具互通性啟動參數';
$string['basicltisettings'] = '基本學習工具互通性(LTI)設定';
$string['cancel'] = '取消';
$string['cancelled'] = '已取消';
$string['cannot_delete'] = '你不可以刪除這工具的配置';
$string['cannot_edit'] = '你不可以編輯這工具的配置';
$string['capabilities'] = '能力';
$string['capabilities_help'] = '選擇你希望提供給這工具供應者的一些能力，你可以選擇多種能力。';
$string['capabilitiesrequired'] = '這一工具需要存取下列資料才能啟用：';
$string['click_to_continue'] = '<a href="{$a->link}" target="_top">擊點可繼續</a>';
$string['comment'] = '評論';
$string['configpassword'] = '預設遠端工具密碼';
$string['configpreferheight'] = '預設偏好的高度';
$string['configpreferwidget'] = '設定小工具為預設發動';
$string['configpreferwidth'] = '預設偏好的寬度';
$string['configresourceurl'] = '預設資源網址';
$string['configtoolurl'] = '預設遠端工具網址';
$string['configtypes'] = '啟用學習工具互通性應用';
$string['configured'] = '配置';
$string['confirmtoolactivation'] = '你確定你要啟用這一工具？';
$string['courseactivitiesorresources'] = '課程活動或資源';
$string['courseid'] = '課程編號';
$string['courseinformation'] = '課程資訊';
$string['courselink'] = '跳到這課程';
$string['coursemisconf'] = '課程設定錯誤';
$string['course_tool_types'] = '課程工具類型';
$string['createdon'] = '建立於';
$string['curllibrarymissing'] = '要使用學習工具互通必須安裝PHP Curl函式庫';
$string['custom'] = '自訂參數';
$string['custom_config'] = '使用自訂的工具設定';
$string['custom_help'] = '自訂參數是工具提供者所使用的設定。例如一個自訂參數可被用來顯示來自工具提供者的特定資源。
每一參數應該各自成一行輸入，並使用"name=value"的格式，例如，"chapter=3"。

不要更改這一欄位會比較安全，除非這工具提供者有指示。';
$string['custominstr'] = '自訂參數';
$string['debuglaunch'] = '除錯選項';
$string['debuglaunchoff'] = '正常啟動';
$string['debuglaunchon'] = '除錯啟動';
$string['default'] = '預設';
$string['default_launch_container'] = '預設啟動容器';
$string['default_launch_container_help'] = '當從課程中啟動外部學習工具時，啟動容器會影響這工具的顯示方式。有些啟動容器提供更多的螢幕空間給這工具，而有些則提供更強調與Moodle 環境整合的感覺。

* **預設**--使用工具配置所指定的啟動容器。
* **鑲入**--外部工具會顯示在現有的Moodle視窗，看起來就像是其他的活動類型。
* **鑲入，沒有區塊**--外部工具會顯示在現有的Moodle視窗，但只伴隨在這一頁頂端的導覽控制。
* **新視窗**--外部工具會開啟新視窗，且占據所有可用空間。它會開啟一新分頁或是一彈出式視窗，要看瀏覽器而定，也有可能瀏覽器會阻止新視窗的開啟。';
$string['delegate'] = '委派給教師';
$string['delete'] = '刪除';
$string['delete_confirmation'] = '您確定要刪除這個外部工具的設定嗎？';
$string['deletetype'] = '刪除外部工具設定';
$string['display_description'] = '啟動後顯示活動敘述';
$string['display_description_help'] = '如果啟用，上面設定的活動描述會顯示在工具提供者的內容之上。

此描述可以用來提供工具未提供的各種說明，不過這不是必須的。

如果工具是在新視窗中啟動，則永遠不會顯示描述。';
$string['display_name'] = '啟動後顯示活動名稱';
$string['display_name_help'] = '如果啟用，上面設定的活動名稱會顯示在工具提供者的內容之上。

因為工具提供者也有可能會顯示活動名稱。此選項可以避免活動名稱被顯示兩次。

如果工具是在新視窗中啟動，則永遠不會顯示描述。';
$string['domain_mismatch'] = '啟動網址的域名不符合工具的設定';
$string['donot'] = '不要送出';
$string['donotaccept'] = '不要接受';
$string['donotallow'] = '不要允許';
$string['duplicateregurl'] = '這一註冊的網址已經被使用';
$string['editdescription'] = '點選這裡給這一工具加上描述';
$string['edittype'] = '編輯外部工具的配置';
$string['embed'] = '嵌入';
$string['embed_no_blocks'] = '不使用區塊嵌入';
$string['enableemailnotification'] = '發送郵件通知';
$string['enableemailnotification_help'] = '若啟用，當學生的提交打完分數後，他們會收到郵件通知。';
$string['enterkeyandsecret'] = '輸入你的客戶密鑰和共用密碼';
$string['errorbadurl'] = '網址不是一個有效的工具網址或載具';
$string['errormisconfig'] = '配置錯誤的工具。請要求你的Moodle管理員修護這工具的設定。';
$string['existing_window'] = '現存的視窗';
$string['extensions'] = '學習工具互通性擴展服務';
$string['external_tool_type'] = '外部工具類型';
$string['external_tool_type_help'] = '工具配置的主要目的是為 Moodle 和工具提供商之間提供一個安全的溝通管道。
它也提供了由此工具提供的預設配置以及設定這工具提供的附加服務。

* **自動的，基於 Launch URL** - 該項設置幾乎應該被應用在所有情況下。 Moodle 會基於Launch URL 選擇最合適的工具配置。由管理員或者課程內配置的工具將會被用到。
       當指定了 Launch URL， 無論是否識別該 URL ，  Moodle都會提供一個回饋。如果 Moodle 未能識別 Launch URL，您可能需要手動進入工具配置細節。

* **一種特殊的工具類型** - 通過選擇一種特殊的工具類型，在與外部工具提供者進行交流的時候，您可以強制 Moodle 使用該工具配置。如果該 Launch URL 並不屬於該工具提供者，一個警告將會出現。在某些情況下，沒有必要在提供一種特殊的工具類型時進入一個 Launch URL （如果對於工具提供者沒有連接到特定的資源）。

* **自定義配置** - 為了在這個實例中開始自定義工具配置，顯示高級選項並進入使用者密匙和共用密匙。如果您沒有用戶密匙和共用密匙，您可以從工具提供者獲得他們。
       並不是所有的工具都需要一個用戶密匙和共用密匙，在這種情況下相應的填寫密匙處可以留空不填。

### 工具類型編輯

在外部工具類型的下拉選單中，有三個圖示可供使用：

* **添加** - 建立一個課程層級的工具配置。在該課程下的所有外部工具實例都可以使用該工具配置。
* **編輯** - 從該下拉選單中選擇課程層級工具類型，然後點擊該圖示。工具配置的細節可以被編輯。
* **刪除** - 刪除選出的課程層級的工具類型。';
$string['external_tool_types'] = '外部工具類型';
$string['failedtoconnect'] = 'Moodle無法與"{$a}"系統通訊';
$string['failedtocreatetooltype'] = '建立新工具失敗，請檢察網址並再試一次';
$string['failedtodeletetoolproxy'] = '無法刪除工具註冊。你可能需要去到"管理外部工具註冊"然後以手動方式刪除它。';
$string['filter_basiclti_configlink'] = '設定您首選的網站及它們的密碼';
$string['filter_basiclti_password'] = '必須輸入密碼';
$string['filterconfig'] = '學習工具互通管理';
$string['filtername'] = '學習工具互通';
$string['fixexistingconf'] = '對錯誤的設定使用一個已經存在的實體';
$string['fixnew'] = '新的配置';
$string['fixnewconf'] = '對錯誤配置的實例重新使用一個新的配置';
$string['fixold'] = '使用現有設定';
$string['forced_help'] = '這一設定已經強制用於一個課程或網站層級工具配置中。您無法從這一介面去改變它。';
$string['force_ssl'] = '強制使用SSL';
$string['force_ssl_help'] = '勾選這個選項目，將強制這工具提供者使用SSL。

此外，來自這工具提供者提出的所有網頁服務請求都將使用SSL。

若使用此選項，請確認這一Moodle網站和這工具提供者都支援SSL。';
$string['generaltool'] = '一般工具';
$string['global_tool_types'] = '全局工具類型';
$string['grading'] = '分數路由';
$string['icon_url'] = '圖示的網址';
$string['icon_url_help'] = '這個圖示網址可讓顯示在課程列表的圖示所代表的活動能被修改。
除了使用預設的學習工具互動圖示之外，一個圖示可以傳達特定的活動類型。';
$string['id'] = 'id';
$string['invalidid'] = '學習工具互通的編號不正確';
$string['launch_in_moodle'] = '在moodle中啟動工具';
$string['launchinpopup'] = '啟動容器';
$string['launch_in_popup'] = '啟動工具在彈出視窗';
$string['launchinpopup_help'] = '當從課程中啟動外部學習工具時，啟動容器會影響這工具的顯示方式。有些啟動容器提供更多的螢幕空間給這工具，而有些則提供更強調與Moodle 環境整合的感覺。

* **預設**--使用工具配置所指定的啟動容器。
* **鑲入**--外部工具會顯示在現有的Moodle視窗，看起來就像是其他的活動類型。
* **鑲入，沒有區塊**--外部工具會顯示在現有的Moodle視窗，但只伴隨在這一頁頂端的導覽控制。
* **新視窗**--外部工具會開啟新視窗，且占據所有可用空間。它會開啟一新分頁或是一彈出式視窗，要看瀏覽器而定，也有可能瀏覽器會阻止新視窗的開啟。';
$string['launchoptions'] = '啟動的選項';
$string['launch_url'] = '發動用網址';
$string['launch_url_help'] = '啟動網址是指一外部工具的網際網路位址，且可能包含額外的資訊，比如要顯示的資源。

若你不確定要在啟動網址輸入什麼，請聯絡工具提供者已娶得更多資訊。

若你已經選擇一特定的工具類型，你可能不需要輸入啟動網址。如果這工具連結只是用來啟動這工具提供者的系統，而不是去到特定的資源，就可以這樣做。';
$string['leaveblank'] = '如果你不需要它們，請留空白';
$string['lti'] = '學習工具互通';
$string['lti:addcoursetool'] = '添加課程特定的工具配置';
$string['lti:addinstance'] = '添加一新外部工具活動';
$string['lti_administration'] = '學習工具互通性管理';
$string['lti_errormsg'] = '這工具傳回下列錯誤訊息："{$a}"';
$string['lti:grade'] = '檢視由外部工具傳回的分數';
$string['lti_launch_error'] = '當啟動外部工具時發生錯誤：';
$string['lti_launch_error_tool_request'] = '要提交一個要求，請求管理員完成工具配置，請點選 <a href="{$a->admin_request_url}" target="_top">這裡</a>。';
$string['lti_launch_error_unsigned_help'] = '這個錯誤可能是少了來自工具提供者的客戶編號和共用密碼。

如果你有一個客戶編號和共用密碼，你可以在編輯外部工具實例時輸入它(要確定可以看到進階選項)。

或者，你可以<a href="{$a->course_tool_editor}">建立一個課程層次的工具提供者配置</a>。';
$string['lti:manage'] = '當這工具啟動時，成為教師';
$string['lti:requesttooladd'] = '要求一外部工具配置成整個網站都可使用';
$string['ltisettings'] = '學習工具互通設定';
$string['lti_tool_request_added'] = '工具配置的要求已經順利地提交出去。你可能需要聯絡網站管理員來完成工具的配置。';
$string['lti_tool_request_existing'] = '一個為這工具網域而設的工具配置已經提交出去。';
$string['ltiunknownserviceapicall'] = '學習工具互動性未知的服務的API呼叫';
$string['lti:view'] = '啟動外部工具活動';
$string['main_admin'] = '一般幫助';
$string['manage_external_tools'] = '管理工具';
$string['manage_tool_proxies'] = '管理外部工具註冊';
$string['manage_tools'] = '管理預先配置的工具';
$string['manuallyaddtype'] = '另一個方式，你可以 <a href="{$a}">手動配置一個工具</a>。';
$string['miscellaneous'] = '雜項';
$string['misconfiguredtools'] = '檢測到錯誤配置的工具實例';
$string['missingparameterserror'] = '頁面配置錯誤：“{$a}”';
$string['module_class_type'] = 'Moodle模組類型';
$string['modulename'] = '外部工具';
$string['modulename_help'] = '這外部工具活動模組可以讓學生與在其他網站上的學習資源和活動進行互動。

要建立一個外部工具活動，需要有一個支援學習工具互通性(Learning Tools Interoperability, LTI)的工具提供者。教師可以建立一個外部工具活動，或者直接使用網站管理員所配置的工具。

外部工具活動與一般的網路資源有一些差異：

* 外部工具是情境敏感的，比如說，他們會取得啟動這工具的用戶的資訊，例如機構、課程、學生姓名等。
* 外部工具支援閱讀、更新、和刪除與這活動有關的分數。
* 外部工具的配置會在你的網站和工具提供者之間建立一個信任關係，允需他們之間進行安全的溝通。';
$string['modulenameplural'] = '外部工具';
$string['modulenamepluralformatted'] = '學習工具互通實例';
$string['name'] = '名稱';
$string['never'] = '從未';
$string['new_window'] = '新視窗';
$string['noattempts'] = '對該工具實例沒有做任何嘗試';
$string['no_lti_configured'] = '沒有可用的外部工具配置';
$string['no_lti_pending'] = '沒有被懸置的外部工具';
$string['no_lti_rejected'] = '沒有被拒絕的外部工具';
$string['noltis'] = '沒有外部工具的實例';
$string['no_lti_tools'] = '沒有已配置好的外部工具';
$string['noprofileservice'] = '沒有發現個人資料表服務';
$string['noservers'] = '找不到伺服器';
$string['no_tp_accepted'] = '沒有被接受的外部工具註冊';
$string['no_tp_cancelled'] = '沒有已取消的外部工具註冊';
$string['no_tp_configured'] = '沒有未註冊的外部工具註冊配置';
$string['no_tp_pending'] = '沒有被懸置的外部工具註冊';
$string['no_tp_rejected'] = '沒有被拒絕的外部工具註冊';
$string['notypes'] = '目前在 Moodle 中沒有學習工具互通性工具設置。點擊上方的安裝按鈕去添加一些。';
$string['noviewusers'] = '沒有找到擁有使用該工具許可權的用戶';
$string['optionalsettings'] = '選項設定';
$string['organization'] = '組織細節';
$string['organizationdescr'] = '組織說明';
$string['organizationid'] = '組織編號';
$string['organizationid_help'] = '這一Moodle網站的獨一無二的辨識器，通常是用這一組織的DNS名稱。

若這欄位留空白，這一Moodle網站的主機名稱將會作為預設值。';
$string['organizationurl'] = '組織網址';
$string['organizationurl_help'] = 'Moodle 實例的基本 URL 。

若這一欄位被留空，那麼根據網站的配置，一個預設值將會被使用。';
$string['pagesize'] = '每一頁顯示的作品數';
$string['parameter'] = '工具的參數';
$string['parameter_help'] = '工具參數是指要通過工具提供者的工具代理伺服器時，所必要的設定。';
$string['password'] = '共用密碼';
$string['password_admin'] = '共用密碼';
$string['password_admin_help'] = '共用密鑰可以被看作是用來授權存取工具的一個密碼。它應該同用戶密鑰一同由工具供應商提供。

那些不需要使用 Moodle 的保密通信，以及不提供額外服務（例如成績報告）的工具，可能不需要一個共用密鑰。';
$string['password_help'] = '對於預先配置的工具，此處沒有必要輸入共用密鑰，因為該共用密鑰在配置過程中將會被提供。

如果向工具建立的連接沒有配置，該區域應該填寫。

如果在此課程中工具將會被多次使用，添加一個課程工具配置會是一個好主意。

共用密鑰可以被看作是用來授權訪問工具的一個密鑰。它應該同用戶密鑰一同由工具供應商提供。

那些不需要使用 Moodle 的保密通信，以及不提供額外服務（例如成績報告）的工具，可能不需要一個共用密鑰。';
$string['pending'] = '待決';
$string['pluginadministration'] = '學習工具互通管理';
$string['pluginname'] = '學習工具互通';
$string['preferheight'] = '偏好的高度';
$string['preferwidget'] = '偏好小工具';
$string['preferwidth'] = '偏好的寬度';
$string['press_to_submit'] = '按下來發動這一活動';
$string['privacy'] = '隱私權';
$string['quickgrade'] = '允許快速計分';
$string['quickgrade_help'] = '若啟用，多種工具可以在一個頁面上被評分。加上分數及評語，然後點選"儲存我所有的回饋"按鈕以儲存在此頁面上的更改。';
$string['redirect'] = '你將在幾秒內被重新導向，若沒有，請按下這按鈕。';
$string['register'] = '註冊者';
$string['registertype'] = '配置一個新外部工具註冊';
$string['register_warning'] = '這註冊頁面似乎需要花點時間才能開啟。若它沒有出現，請檢查你在配置設定上所輸入的網址是否正確。如果 Moodle 是使用 https，要確定你配置的工具有支援 https，且你在網址上有用 https。';
$string['registrationname'] = '工具提供者名稱';
$string['registrationname_help'] = '輸入要註冊的工具提供者名稱';
$string['registration_options'] = '註冊選項';
$string['registrationurl'] = '註冊網址';
$string['registrationurl_help'] = '註冊網址是由工具提供者所提供，它是註冊申請要送去的地方。';
$string['reject'] = '拒絕';
$string['rejected'] = '已被拒絕';
$string['resource'] = '資源';
$string['resourcekey'] = '資源密鑰';
$string['resourcekey_admin'] = '客戶密鑰';
$string['resourcekey_admin_help'] = '用戶密鑰可以被看作是用來做認證工具存取權限的一個用戶名稱。

它可以由工具用來唯一識別 Moodle網站，通過 Moodle網站，用戶可以使用這種工具

用戶密鑰必須由工具提供者提供。獲得用戶密鑰的方法會隨著工具提供者的不同而有差異。它可以是一個自動的過程，或者它可能需要和工具提供者進行對話。

那些不需要使用 Moodle 的保密通信，以及不提供額外服務（例如成績報告）的工具，可能不需要一個用戶密鑰。';
$string['resourcekey_help'] = '就預先配置的工具而言，它不需要在此輸入資源密鑰，因為在配置的過程就會提供客戶密鑰。

如果你建立一個連結到一工具提供者，而它還沒有被配置，這一欄位就需要輸入資料。如果一個工具提供者在此課程中被使用多次，添加一個課程工具配置是比較好的主意。

客戶密鑰可以被想成是一個用戶名稱用來被認證以便存取這工具。它也可以被工具提供者用來辨識啟動這工具的用戶是來自哪一個 Moodle 網站。

這客戶密鑰必須是由工具提供者提供。而獲得密鑰的方法各不相同，它可能是個自動的過程，也可能需要與工具提供者對話。

那些不需要與Moodle做安全溝通，和不提供額外服務(如成績報告)的工具，可能不需要一個資源密鑰。';
$string['resourceurl'] = '資源網址';
$string['return_to_course'] = '點擊<a href="{$a->link}" target="_top">這裏</a>返回課程。';
$string['saveallfeedback'] = '儲存所有我的回饋';
$string['search:activity'] = '學習工具互動活動';
$string['secure_icon_url'] = '安全圖示的網址';
$string['secure_icon_url_help'] = '類似一般圖示網址，但只有在用戶為了安全透過 SSL 存取 Moodle 時使用它。這一欄位的主要目的是讓瀏覽器警告用戶，如果基礎頁面是透過SSL存取，但是卻要求顯示一個不安全的圖像。';
$string['secure_launch_url'] = '安全起動的網址';
$string['secure_launch_url_help'] = '類似啟動網址，但若需要高度安全性時，可用來代替啟動網址。
如果Moodle網站是透過SSL來存取，或這工具配置是設定永遠透過SSL啟動，那安全啟動網址將會取代啟動網址。

這啟動網址也可以設定為以 https 開頭的位址，以強迫從SSL啟動，而這一欄位就可以留空白。';
$string['send'] = '傳送';
$string['services'] = '服務';
$string['services_help'] = '選出你希望提供給工具提供者的那些服務。可以選擇一個以上的服務。';
$string['setupoptions'] = '設定選項';
$string['share_email'] = '與工具共用啟動者的email';
$string['share_email_admin'] = '與工具共用啟動者的email';
$string['share_email_admin_help'] = '請指明是否要將啟動這一工具的用戶的email位址，提供給這一工具提供者。

工具提供者需要啟動者的email位址來區別有相同姓名的學生，或者送email給學生。';
$string['share_email_help'] = '請指明是否要將啟動這一工具的用戶的email位址，提供給這一工具提供者。

工具提供者需要啟動者的email位址來區別有相同姓名的學生，或者送email給學生。

注意，這一設定可能在工具配置中被覆寫過去。';
$string['share_name'] = '與這工具共用啟動者的名稱';
$string['share_name_admin'] = '與工具共用啟動者的名稱';
$string['share_name_admin_help'] = '決定這一啟動工具者的完整姓名是否要告知工具提供者。
工具提供者可能需要啟動者的名字，以便在工具中顯示有意義的訊息。';
$string['share_name_help'] = '決定這一啟動工具者的完整姓名是否要告知工具提供者。

工具提供者可能需要啟動者的名字，以便在工具中顯示有意義的訊息。

注意，這一設定可以在工具配置中被覆寫過去。';
$string['share_roster'] = '允許這工具存取這一課程名冊';
$string['share_roster_admin'] = '太多存取課程名冊';
$string['share_roster_admin_help'] = '若一個課程有啟用這一類型工具，請指明這一工具是否可以存取有選修這一課程的用戶的清單。';
$string['share_roster_help'] = '指明這一工具是否可以存取有選修這一課程的用戶的清單。

注意，這一設定可以被在工具配置中加以覆蓋。';
$string['size'] = '大小參數';
$string['submission'] = '提交作品';
$string['submissions'] = '提交';
$string['submissionsfor'] = '{$a}的提交';
$string['subplugintype_ltiresource'] = '學習工具互通服務資源';
$string['subplugintype_ltiresource_plural'] = '學習工具互通服務資源';
$string['subplugintype_ltiservice'] = '學習工具互通服務';
$string['subplugintype_ltiservice_plural'] = '學習工具互通服務';
$string['subplugintype_ltisource'] = '學習工具互通資源';
$string['subplugintype_ltisource_plural'] = '學習工具互通資源';
$string['successfullycreatedtooltype'] = '已經成功建立新工具!';
$string['toggle_debug_data'] = '切換除錯資料';
$string['tool_config_not_found'] = '沒有找到供這網址使用的工具配置。';
$string['tooldescription'] = '工具描述';
$string['tooldescription_help'] = '這一工具的描述將會在活動清單上顯示給教師看。

它應該描述這工具的用途、功能，以及教師需要知道的任何額外訊息。';
$string['toolisbeingused'] = '這一工具已經被使用過{$a}次';
$string['toolisnotbeingused'] = '這一工具還沒被使用過';
$string['toolproxy'] = '外部工具設定';
$string['toolproxy_help'] = '外部工具註冊允許Moodle網站管理員從一工具代理伺服器去配置外部工具，若它是來自支援學習工具互動性(LTI)2.0的工具提供者。
管理員必須經由工具提供者所提供的註冊網址是來開始這一註冊程序。在配置一個新註冊時，管理員需要選擇要提供給工具提供者的權限和服務。

列在本頁上的各種工具註冊狀態可分成四類：

* **已被配置**--這些工具註冊已經被設定好，但是註冊程序還沒有開始。
* **擱置中**--這些工具註冊的註冊程序已經開始，但不知為何卻沒有完成，開啟後修改它並儲存這些設定，就可以把它移回到"已被配置"這一類，這樣註冊程序就可以重新開始。
* **已被接受**--工具註冊已經被核准，在這工具代理伺服器的指定資源將會出現在外部工具類型頁面。
* **已被拒絕**--這些是在註冊過程中被拒絕的，開啟後修改它並儲存這些設定，就可以把它移回到"已被配置"這一類，這樣註冊程序就可以重新開始。';
$string['toolproxyregistration'] = '外部工具設定';
$string['toolregistration'] = '外部工具設定';
$string['tool_settings'] = '工具各項設定';
$string['toolsetup'] = '外部工具配置';
$string['tooltypeadded'] = '預先配置的工具已添加';
$string['tooltypedeleted'] = '預先配置的工具已刪除';
$string['tooltypenotdeleted'] = '無法刪除預先配置的工具';
$string['tooltypes'] = '工具';
$string['tooltypeupdated'] = '預先配置的工具已更新';
$string['toolurl'] = '工具基本網址/承載器網址';
$string['toolurl_help'] = '工具的基本網址用是來和工具啟動網址相配對，以確定使用正確的工具配置。網址前端的http(s) 是可有可無。

除此之外，若再外部工具實例中未指定啟動 URL，則會使用此基本網址作為啟動網址。

舉例來說，*tool.com*的基本網址將會配對成：

* tool.com
* tool.com/quizzes
* tool.com/quizzes/quiz.php?id=10
* www.tool.com/quizzes

*www.tool.com/quizzes* 的基本往知將會配對成:

* www.tool.com/quizzes
* tool.com/quizzes
* tool.com/quizzes/take.php?id=10

*quiz.tool.com*的基本網址將會配對成：

* quiz.tool.com
* quiz.tool.com/take.php?id=10


如果有兩個不同的工具配置是使用同一個網域，那麼會使用符合程度最高的那個。';
$string['toolurlplaceholder'] = '工具網址...';
$string['typename'] = '工具名稱';
$string['typename_help'] = '工具名稱是用來辨識在Moodle內的工具提供者。

教師在課程內添加外部工具時，就可以看到這個輸入的名稱。';
$string['types'] = '類型';
$string['unabletocreatetooltype'] = '無法建立工具';
$string['unabletofindtooltype'] = '無法為 {$a->id}找到工具';
$string['unknownstate'] = '未知的狀態';
$string['update'] = '更新';
$string['useraccountinformation'] = '用戶帳戶訊息';
$string['userpersonalinformation'] = '用戶個人資訊';
$string['using_tool_cartridge'] = '使用工具盒';
$string['using_tool_configuration'] = '使用工具配置';
$string['validurl'] = '有效的網址必須以http(s)://
作為開頭';
$string['viewsubmissions'] = '檢視提交作品和評分螢幕';
