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
 * Strings for component 'enrol_imsenterprise', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_imsenterprise
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aftersaving...'] = '當您儲存了設定，您也許想要';
$string['allowunenrol'] = '允許 IMS 資料來<strong>退選</strong>學生/教師';
$string['allowunenrol_desc'] = '如果啟用此項，企業資料裡特別指定的選課會被刪除。';
$string['basicsettings'] = '基本設定';
$string['coursesettings'] = '課程資料設定';
$string['createnewcategories'] = '如果在Moodle中沒有找到，就建立一個新的（隱藏的）課程種類';
$string['createnewcategories_desc'] = '如果<org><orgunit>出現在課程的匯入資料中，並且課程為新建，那麼它的內容會被替換為指定的分類。此外掛套件不會對現有的課程重新分類。
如果沒有所要的名稱的類別存在，那就會建立一個隱藏的類別。';
$string['createnewcourses'] = '如果在Moodle中沒有找到，就建立一個新的（隱藏的）課程';
$string['createnewcourses_desc'] = '如果允許此項，IMS企業級外掛能建立在IMS資料庫中而不在Moodle資料庫中的所有課程。所有新建的課程初始都是隱藏的。';
$string['createnewusers'] = '如果找不到就建立新的使用者';
$string['createnewusers_desc'] = 'IMS企業選課資料典型地描述一組用戶。如果啟用，會為任何在Moodle資料庫中找不到的用戶建立帳號。
首先通過"學號"尋找用戶，然後再用"用戶名稱"。密碼不是通過IMS企業外掛套件匯入的。建議使用身份認證外掛驗證用戶。';
$string['cronfrequency'] = '處理頻率';
$string['deleteusers'] = 'IMS 資料指定時刪除使用者帳號';
$string['deleteusers_desc'] = '如果啟用，IMS企業選課資料可以刪除使用者帳號（當" recstatus" 的值設為3時，表示刪除這一帳號）。其實在Moodle中，使用者記錄並沒有真的從 Moodle資料庫刪除，而是用一個標記來表示已刪除。';
$string['doitnow'] = '馬上進行一次IMS企業導入';
$string['emptyattribute'] = '此處須予以空白';
$string['filelockedmail'] = '您在IMS檔案基礎上用的文字檔案登入({$a}) 無法被排程刪除。這通常意味著它的權限發生錯誤了。請確定權限使Moodle可以刪除文件，否則排程可能會重複';
$string['filelockedmailsubject'] = '重大錯誤：選課檔案';
$string['fixcasepersonalnames'] = '變更個人姓名字首大寫';
$string['fixcaseusernames'] = '變更個人姓名為小寫';
$string['ignore'] = '略過';
$string['importimsfile'] = '匯入IMS企業檔案';
$string['imsenterprise:config'] = '配置IMS企業選課實例';
$string['imsenterprisecrontask'] = '選課檔案處理';
$string['imsrolesdescription'] = 'IMS計劃規範包括8個截然不同的角色類型。請您選擇在Moodle中如何分配這些角色，包括應該忽略那些角色。';
$string['location'] = '檔案位置';
$string['logtolocation'] = '日誌檔案輸出到本地（空白表示沒有日誌檔案）';
$string['mailadmins'] = '電子郵件通知管理員';
$string['mailusers'] = '電子郵件通知用戶';
$string['messageprovider:imsenterprise_enrolment'] = 'IMS 企業選課消息';
$string['miscsettings'] = '雜項';
$string['pluginname'] = 'IMS企業檔案';
$string['pluginname_desc'] = '此方法將會重複檢查並且處理您指定路徑的特殊格是的純文字檔案。該檔案必須符合IMS企業規範，包含個人、群組、成員等XML元素。';
$string['processphoto'] = '加入用戶照片資料到個人檔案';
$string['processphotowarning'] = '警告：影像處理程序如同對伺服器增加一個大量的工作。如果有大量的學生等待被處理推薦您不要啟用這個選項。';
$string['restricttarget'] = '只有處理資料，假如有指定目標。';
$string['restricttarget_desc'] = 'IMS企業資料檔案可以供多個目標使用，例如不同的LMSes，不同的校務系統。通過<target> 裡的<properties>標籤對目標系統命名，可以在企業管理系統文件中指定資料，這些資料提供一個或者更多的已命名的目標系統使用。

在很多情況下，您不需要擔心這些。您可以不更改預設設定，不論這個目標是否已經指定，MOODLE總是會處理文件，否則，您就在<target>中輸入正確的名字。';
$string['roles'] = '角色';
$string['settingfullname'] = '供課程全名使用的IMS描述標籤';
$string['settingfullnamedescription'] = '這完整名稱是一個必要的課程欄位，因此你需要在你的IMS企業檔中界定選出的描述標籤';
$string['settingshortname'] = '供課程簡稱使用的IMS描述標籤';
$string['settingshortnamedescription'] = '簡稱為一個必填之課程欄位，因此您必須在您的IMS企業檔案中描述你選出之標籤，以便給它定義';
$string['settingsummary'] = '供課程摘要使用的IMS描述標籤';
$string['settingsummarydescription'] = '此欄位為選擇性。如果你不願述明某課程摘要，就選Leave it empty(予以空白)';
$string['sourcedidfallback'] = '如果"userid"沒找到就使用 "sourcedid" 為個人userid';
$string['sourcedidfallback_desc'] = '在IMS資料中，<sourcedid>欄位代表了來源系統中用戶的永久ID碼。而<userid>欄位是一個單獨的欄位，它包含使用者登入時使用的ID碼。雖然在許多情況下這兩個碼可能是相同的，但是它們並不是一直相同。

有時，一些學生資訊系統不能輸出<userid>欄位。在這種情況下，您應啟用這一設定來允許將<sourcedid>用作Moodle使用者ID。而如果可以輸出<userid>欄位，則停用該設定。';
$string['truncatecoursecodes'] = '截去課程代碼到這個長度';
$string['truncatecoursecodes_desc'] = '在一些情境中，您可能想在處理程序前把課程代碼截斷到指定的長度，那麼請在這個對話框中輸入需要截斷的字元數目，如果不想截斷，就不需要在對話框中輸入數字。';
$string['usecapitafix'] = '如果用"Capita"選擇這個框框（他們的XML格式有錯誤）';
$string['usecapitafix_desc'] = '我們發現在Capita編輯的學生資浪系統中，XML檔案輸出有一些小問題。如果您使用此項功能，請啟用這個選項；否則，不選擇。';
$string['usersettings'] = '用戶的資料選項';
$string['zeroisnotruncation'] = '0表示沒有截斷';
