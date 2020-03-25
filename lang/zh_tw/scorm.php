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
 * Strings for component 'scorm', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   scorm
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activation'] = '啟動';
$string['activityloading'] = '你將會被自動重新導向到這活動，在';
$string['activityoverview'] = '您有需要注意的SCORM教材包';
$string['activitypleasewait'] = '活動載入中，請等待.....';
$string['adminsettings'] = '管理員設定';
$string['advanced'] = '進階';
$string['aicchacpkeepsessiondata'] = 'AICC HACP對話連線資料';
$string['aicchacpkeepsessiondata_desc'] = '要保存AICC HACP對話資料的天數(設定較高天數將使資料表塞滿舊資料，但對除錯很有幫助)';
$string['aicchacptimeout'] = 'AICC HACP的時間限制';
$string['aicchacptimeout_desc'] = '外部AICC HACP對話連線可以保持開啟的時間有幾分鐘';
$string['aiccuserid'] = '傳送數字的用戶ID編號給AICC';
$string['aiccuserid_desc'] = 'AICC對於用戶姓名的要求比起Moodle來說是非常嚴格的。它只允許字母數字字元、短線符號( - )、底線符號( _ )。而不允許使用句點、空白和@符號。若啟用，用戶的ID編號將會代替用戶姓名傳送給AICC包裹。';
$string['aliasonly'] = '當您從一個倉儲選擇一個 imsmanifest.xml 檔案時，你必須使用這一檔案的別名/簡稱。';
$string['allowapidebug'] = '啟用API除錯和追蹤(設定抓取遮版要伴隨API除錯遮版)';
$string['allowtypeaicchacp'] = '開啟外部AICC HACP';
$string['allowtypeaicchacp_desc'] = '若啟用，將會允許AICC HACP外部溝通，而不需要用戶登入之後向外部的AICC包裹提出請求。';
$string['allowtypeexternal'] = '啟用外部包裹類型';
$string['allowtypeexternalaicc'] = '啟用直接AICC網址';
$string['allowtypeexternalaicc_desc'] = '若啟用，將允許使用一直接網址到一簡單AICC課程包';
$string['allowtypelocalsync'] = '啟用可以下載的課程包裹類型';
$string['apidebugmask'] = 'API除錯抓取遮罩 --使用簡單正規表達式
<username>:<activityname>
例如 admin:.* 將只用於管理員的除錯';
$string['areacontent'] = '內容檔案';
$string['areapackage'] = '課程包檔案';
$string['asset'] = '資源';
$string['assetlaunched'] = '已閱讀的資源';
$string['attempt'] = '作答次';
$string['attempt1'] = '1次作答';
$string['attempts'] = '作答次';
$string['attemptsmanagement'] = '作答次管理';
$string['attemptstatusall'] = '儀表板和條目頁面';
$string['attemptstatusentry'] = '只有條目頁面';
$string['attemptstatusmy'] = '只有儀表板';
$string['attemptsx'] = '{$a}次作答';
$string['attr_error'] = '在{$a->tag}標籤中，({$a->attr})的屬性值錯誤';
$string['autocommit'] = '自動提交';
$string['autocommitdesc'] = '若這SCORM包裹沒有儲存，它會自動儲存SCORM資料';
$string['autocommit_help'] = '若啟用，SCORM資料會自動儲存到資料庫。這對於沒有定期儲存他們的資料的SCORM物件非常有用。';
$string['autocontinue'] = '自動繼續';
$string['autocontinuedesc'] = '設定在預設情況下活動是否會自動繼續';
$string['autocontinue_help'] = '若啟用，後續的學習物件會自動地推出，否則用戶就需要使用"繼續"按鈕才能繼續下去。';
$string['averageattempt'] = '平均使用次數';
$string['badarchive'] = '你必須提供一有效的zip壓縮檔案';
$string['badimsmanifestlocation'] = '有發現一個imsmanifest.xml 檔案，但是它不在你的zip壓縮檔的根目錄，請再重新包裹你的SCORM。';
$string['badmanifest'] = '某些裝載清單錯誤:請看錯誤記錄';
$string['browse'] = '預覽';
$string['browsed'] = '已瀏覽';
$string['browsemode'] = '預覽模式';
$string['browserepository'] = '瀏覽倉儲';
$string['calculatedweight'] = '計算的權重';
$string['cannotaccess'] = '你不能以這種方式呼叫這一腳本';
$string['cannotfindsco'] = '無法找到SCO';
$string['chooseapacket'] = '選取或更新scorm包裏';
$string['closebeforeopen'] = '你所指定的關閉日期是在開啟日期之前';
$string['collapsetocwinsize'] = '當視窗大小低於多少時，折疊內容目錄(TOC)。';
$string['collapsetocwinsizedesc'] = '指定當視窗長寬於多少以下時，內容目錄應該自動摺疊起來';
$string['compatibilitysettings'] = '兼容性設定';
$string['completed'] = '已完成';
$string['completionscorerequired'] = '需要最低分數';
$string['completionscorerequired_help'] = '啟用這一設定，將會要求用戶至少有一最低分數，才可在這一SCORM活動中標示為完成，如同任何其他活動的完成要求。';
$string['completionstatus_completed'] = '已完成';
$string['completionstatus_passed'] = '已及格';
$string['completionstatusrequired'] = '要求狀態';
$string['completionstatusrequired_help'] = '若你勾選一或多個狀態，將會要求用戶至少完成一個被勾選的狀態，才能被標示為完成這一SCORM活動，就如同其他活動完成的要求。';
$string['confirmloosetracks'] = '警告：此課程包可能發生了改變或者被修改過了。如果改變了課程包的結構，那麼在更新過程中一些使用追蹤紀錄可能會遺失。';
$string['contents'] = '目錄';
$string['coursepacket'] = '課程包裹';
$string['coursestruct'] = '課程結構';
$string['currentwindow'] = '當前視窗';
$string['datadir'] = '檔案系統錯誤：無法建立課程資料目錄';
$string['defaultdisplaysettings'] = '預設的顯示設定';
$string['defaultgradesettings'] = '預設的計分設定';
$string['defaultothersettings'] = '其他的預設設定';
$string['deleteallattempts'] = '刪除所有SCORM使用紀錄';
$string['deleteattemptcheck'] = '您確定您要完全刪除這些作答紀錄嗎？';
$string['deleteselected'] = '刪除選取的作答紀錄';
$string['deleteuserattemptcheck'] = '您確定您要完全刪除您的作答紀錄嗎？';
$string['details'] = 'SCO 追蹤細節';
$string['directories'] = '顯示目錄連結';
$string['disabled'] = '關閉';
$string['display'] = '顯示';
$string['displayactivityname'] = '顯示活動名稱';
$string['displayactivityname_help'] = '是否要在SCORM撥放器上顯示活動名稱。';
$string['displayattemptstatus'] = '顯示作答狀態';
$string['displayattemptstatusdesc'] = '預設是否要將用戶作答次的摘要顯示在儀表板和/或SCORM條目頁面的課程綜覽區塊上';
$string['displayattemptstatus_help'] = '這一偏好允許你將用戶作答次的摘要顯示在儀表板和/或SCORM條目頁面的課程綜覽區塊。';
$string['displaycoursestructure'] = '在條目頁顯示課程結構';
$string['displaycoursestructuredesc'] = '預設是否要在條目頁面顯示課程結構';
$string['displaycoursestructure_help'] = '若啟用，內容目錄會顯示在SCORM綱要頁面';
$string['displaydesc'] = '預設是否要為一活動顯示課程包裹';
$string['displaysettings'] = '顯示設定';
$string['dnduploadscorm'] = '添加一個SCORM課程包';
$string['domxml'] = 'DOMXML外部程式庫';
$string['duedate'] = '到期日';
$string['element'] = '元素';
$string['enter'] = '進入';
$string['entercourse'] = '進入SCORM課程';
$string['errorlogs'] = '錯誤記錄';
$string['eventattemptdeleted'] = '已刪除作答次';
$string['eventinteractionsviewed'] = '已檢視互動';
$string['eventreportviewed'] = '已檢視報告';
$string['eventscolaunched'] = '已執行SCORM';
$string['eventscorerawsubmitted'] = '已提交SCORM原始分數';
$string['eventstatussubmitted'] = '已提交SCORM狀態';
$string['eventtracksviewed'] = '已檢視追蹤';
$string['eventuserreportviewed'] = '已檢視用戶報告';
$string['everyday'] = '每一天';
$string['everytime'] = '每次使用時';
$string['exceededmaxattempts'] = '你已經達到最大的作答次數';
$string['exit'] = '離開課程';
$string['exitactivity'] = '離開活動';
$string['expired'] = '抱歉，這活動已經在{$a}關閉，已經無法使用';
$string['external'] = '更新外來課程包的時間';
$string['failed'] = '失敗';
$string['finishscorm'] = '如果你已經看完這一資源，{$a}';
$string['finishscormlinkname'] = '點選這裡回到課程頁面';
$string['firstaccess'] = '第一次存取';
$string['firstattempt'] = '第一次使用';
$string['floating'] = '浮動的';
$string['forcecompleted'] = '強迫完成';
$string['forcecompleteddesc'] = '預設強制完成的設定';
$string['forcecompleted_help'] = '若啟用，當前訪問的狀態會變為“強制完成”。該項設定僅適用於SCORM1.2 課程包。如果SCORM課程包在複習或瀏覽模式中，不能正確地處理再次訪問一作答次，或者不能正確處理完成狀態時，這功能非常有用。';
$string['forcejavascript'] = '強迫用戶去啟動JavaScript';
$string['forcejavascript_desc'] = '若啟用(推薦)，在用戶瀏覽器不支援或者未啟用 JavaScript 時，禁止存取SCORM物件。如果設定為關閉，則用戶仍可以查看SCORM的內容，但是API的通訊會失敗，且沒有成績資訊被保存到系統中。';
$string['forcejavascriptmessage'] = '要看到這一物件，需要有JavaScript。請在您的瀏覽器上啟用JavaScrip，並再試一次。';
$string['forcenewattempt'] = '強迫新的作答';
$string['forcenewattemptdesc'] = '若啟用，每一次SCORM被存取時，將被視為一次新作答。啟用後會隱藏"開始新的作答次"並禁止複習模式。

SCORM會允許學生在任何點上回到同一個作答次，而且除非前一個作答次被設定為"已完成"，"通過"或"失敗"，否則他無法開始新的作答。如果SCORM包裹沒有這樣做，學生永遠會回到同一個作答次。';
$string['forcenewattempt_help'] = '若啟用，每一次SCORM被存取時，將被視為一次新作答。啟用後會隱藏"開始新的作答次"並禁止複習模式。

SCORM會允許學生在任何點上回到同一個作答次，而且除非前一個作答次被設定為"已完成"，"通過"或"失敗"，否則他無法開始新的作答。如果SCORM包裹沒有這樣做，學生永遠會回到同一個作答次。';
$string['found'] = '找到裝載清單檔';
$string['frameheight'] = '這階段架框或視窗的高度';
$string['framewidth'] = '這階段架框或視窗的寬度';
$string['fromleft'] = '從左邊';
$string['fromtop'] = '從上面';
$string['fullscreen'] = '填滿整個畫面';
$string['general'] = '一般資料';
$string['gradeaverage'] = '平均成績';
$string['gradeforattempt'] = '作答次的分數';
$string['gradehighest'] = '最高成績';
$string['grademethod'] = '評分方式';
$string['grademethoddesc'] = '預設的活動的計分方式';
$string['grademethod_help'] = '計分方式是定義這一活動的作答要如何計分。

有四種計分方式：

* 學習目標 -- 已經完成/及格的學習目標
* 最高分數 -- 在所有及格的學習目標中獲得的最高分數
* 平均分數 -- 所有分數的平均數
* 累加分數 -- 所有分數的累加和';
$string['gradereported'] = '成績報告';
$string['gradescoes'] = '學習目標';
$string['gradesettings'] = '評分設定';
$string['gradesum'] = '加總';
$string['height'] = '高度';
$string['hidden'] = '隱藏';
$string['hidebrowse'] = '停用預覽模式';
$string['hidebrowsedesc'] = '預設是否啟用或關閉預覽模式';
$string['hidebrowse_help'] = '預覽模式允許學生在正式使用活動之前瀏覽它們。若預覽模式被關閉，預覽按鈕會被隱藏。';
$string['hideexit'] = '隱藏退出的超連結';
$string['hidereview'] = '隱藏回顧的按鈕';
$string['hidetoc'] = '在播放器中顯示課程結構';
$string['hidetocdesc'] = '預設是否在SCORM播放器中顯示或隱藏課程結構(TOC)';
$string['hidetoc_help'] = '此設定指明內容目錄如何顯示在SCORM播放器上';
$string['highestattempt'] = '最高使用次數';
$string['identifier'] = '題目識別碼';
$string['incomplete'] = '不完整';
$string['info'] = '訊息';
$string['interactions'] = '互動';
$string['invalidactivity'] = 'SCORM活動不正確';
$string['invalidhacpsession'] = '無效的HACP對話';
$string['invalidmanifestname'] = '只可以選出imsmanifest.xml 或 .zip 檔案';
$string['invalidmanifestresource'] = '警告：在您的裝載清單檔案上有參照到下列資源，但是找不到它們：';
$string['invalidurl'] = '無效的網址';
$string['invalidurlhttpcheck'] = '指定的網址無效。除錯訊息<pre>{$a->cmsg}</pre>';
$string['last'] = '最後瀏覽於';
$string['lastaccess'] = '最後瀏覽';
$string['lastattempt'] = '最後完成的作答次';
$string['lastattemptlock'] = '最後一次作答之後加以鎖定';
$string['lastattemptlockdesc'] = '預設是否在最後一次作答之後封鎖播放器';
$string['lastattemptlock_help'] = '若啟用，學生在用完他所分配的作答次術後，將無法發動SCORM撥放器。';
$string['location'] = '顯示位址列';
$string['masteryoverride'] = '精熟分數覆寫狀態';
$string['masteryoverridedesc'] = '這會作為精熟分數覆寫設定的預設值';
$string['masteryoverride_help'] = '若啟用並提供一精熟分數，當 LMSFinish 被呼叫且有一原始分數被設定，狀態將會使用原始分數和精熟分數重新計算，且任何SCORM提供的狀態(包括未完成)將會被記錄。';
$string['max'] = '最高分';
$string['maximumattempts'] = '使用次數';
$string['maximumattemptsdesc'] = '設定一個活動預設的最高做答次數';
$string['maximumattempts_help'] = '設設定啟用了作答次數的限制。他只限用於SCORM1.2和AICC課程包';
$string['maximumgradedesc'] = '設定一個活動的預設最高分數(配分)';
$string['menubar'] = '顯示選單列';
$string['min'] = '最低分';
$string['missing_attribute'] = '{$a->tag}標籤遺漏{$a->attr}屬性';
$string['missingparam'] = '必要的參數遺漏了或發生錯誤';
$string['missing_tag'] = '遺漏{$a->tag}標籤';
$string['mode'] = '模式';
$string['modulename'] = 'SCORM課程包';
$string['modulename_help'] = '一個SCORM課程包是將一堆檔案依照學習物件的共同訂定的標準加以打包。這一SCORM模組讓SCORM或AICC包裹以壓縮檔形式上傳，並加入到一課程中。

內容通常已超過好幾頁方式顯示，並再頁面之間有導覽的鏈結。在彈出式視窗中有好幾種不同的方式來顯示內容，例如，以目錄，以導覽按鈕等等。SCORM活動通常包含試題，其分數會被記錄到成績簿上。


SCORM活動可以用來

* 用來多媒體內容和動畫
* 作為評量工具';
$string['modulenameplural'] = 'SCORM課程包';
$string['nav'] = '顯示導覽按鈕';
$string['navdesc'] = '這設定決定是否要顯示/隱藏導覽按鈕和它們的位置。';
$string['nav_help'] = '這一設定可決定是否要顯示或隱藏導覽按鈕和它們的位置。

它有三種選擇：

* 無 - 不要顯示導覽按鈕。
* 在內容之下 - 在 SCORM 包裹內容之下顯示導覽按鈕。
* 浮動的 - 導覽按鈕的位置是浮動的，由SCORM包裹指定在左邊或在上面。';
$string['navigation'] = '導覽';
$string['navpositionleft'] = '從左邊算起，導覽按鈕的位置(以像素為單位)';
$string['navpositiontop'] = '從頂端算起，導覽按鈕的位置(以像素為單位)';
$string['networkdropped'] = '這SCORM撥放器認為你的網際網路連結是不穩定或被干擾。若你繼續這SCOM活動，你的進度可能無法儲存。<br />
你應該現在退出這活動，等你有可靠的網際網路連線時再回來。';
$string['newattempt'] = '開始新的使用';
$string['next'] = '繼續';
$string['noactivity'] = '沒有報告';
$string['noattemptsallowed'] = '允許作答的次數';
$string['noattemptsmade'] = '你已經作答的次數';
$string['no_attributes'] = '標籤{$a->tag} 必須有屬性';
$string['no_children'] = '標籤{$a->tag} 必須有子類';
$string['nolimit'] = '無限制的使用';
$string['nomanifest'] = '不正確的檔案包裹--無法找到imsmanifest.xml或AICC結構。';
$string['noprerequisites'] = '抱歉，您還沒有達到存取這個活動的條件';
$string['noreports'] = '沒有報告';
$string['normal'] = '一般';
$string['noscriptnoscorm'] = '您的瀏覽器不支援 javascript 或是被停用。這一SCORM包裹可能無法正確地播放或儲存資料。';
$string['notattempted'] = '未嘗試';
$string['not_corr_type'] = '標籤{$a->tag} 型態不正確';
$string['notopenyet'] = '抱歉，這一活動要到{$a}才可以使用';
$string['objectives'] = '目標';
$string['optallstudents'] = '全部用戶';
$string['optattemptsonly'] = '只限有作答的用戶';
$string['options'] = '選項(某些瀏覽器可能無法使用)';
$string['optionsadv'] = '選項(進階)';
$string['optionsadv_desc'] = '若勾選，在這表單中，視窗的選項將會變為進階選項';
$string['optnoattemptsonly'] = '只限於沒有作答過的用戶';
$string['organization'] = '組織';
$string['organizations'] = '組織';
$string['othersettings'] = '額外的設定';
$string['package'] = '包裹的檔案';
$string['packagedir'] = '檔案系統錯誤：無法建立課程包裹目錄';
$string['packagefile'] = '沒有指定包裹檔案';
$string['packagehdr'] = '包裹';
$string['package_help'] = '<p><b>Packages file</b></p>

<p>The package is a particular file with <b>zip</b> (or pif) extension that contains
   valid AICC or SCORM course definition files.</p>

<p>A <b>SCORM</b> package must contains in the root of zip a file named <b>imsmanifest.xml</b>
   that define SCORM course structure, resource location and many other things.</p>

<p>An <b>AICC</b> package is defined by several files (from 4 to 7) with defined extensions.
   Here you find the extensions meaning:</p>
   <ul>
	<li>CRS - Course Description file (mandatory)</li>
	<li>AU  - Assignable Unit file (mandatory)</li>
	<li>DES - Descriptor file (mandatory)</li>
	<li>CST - Course Structure file (mandatory)</li>
	<li>ORE - Objective Relationship file (optional)</li>
	<li>PRE - Prerequisites file (optional)</li>
	<li>CMP - Completition Requirements file (optional)</li>
   </ul>';
$string['packageurl'] = '網址';
$string['packageurl_help'] = '這一設定可以指定SCORM課程包的網址，
而不是經由檔案挑選器選擇一個檔案。';
$string['page-mod-scorm-x'] = '任何SCORM模組頁面';
$string['pagesize'] = '頁面大小';
$string['passed'] = '通過';
$string['php5'] = 'PHP 5 (DOMXML原始函式庫)';
$string['pluginadministration'] = 'SCORM課程包管理';
$string['pluginname'] = 'SCORM包裹';
$string['popup'] = '在新視窗中開啟學習物件檔';
$string['popuplaunched'] = '這一SCORM包裹已經展現在一彈出式視窗中，若你已經看完這一資源，擊點這裡可回到課程頁面。';
$string['popupmenu'] = '在下拉式選單中';
$string['popupopen'] = '在新視窗開啟包裝';
$string['popupsblocked'] = '似乎是彈出式視窗被封鎖了，停止了SCORM模組的播放。在重新開始之前，請檢查你的瀏覽器。';
$string['position_error'] = '標籤{$a->tag} 不是{$a->parent}標籤的子標籤';
$string['preferencespage'] = '只適用於這一頁面的偏好';
$string['preferencesuser'] = '適用於這一報告的偏好';
$string['prev'] = '前一頁';
$string['protectpackagedownloads'] = '保護包裹下載';
$string['protectpackagedownloads_desc'] = '若啟用，SCORM包裹只有在用戶具有 course:manageactivities權限時，才可以下載。若關閉，SCORM包裹永遠可以被下載(由行動裝置或其他工具)。';
$string['raw'] = '原始分數';
$string['regular'] = '常規的裝載清單';
$string['report'] = '結果';
$string['reportcountallattempts'] = '{$a->nbusers} 位用户的 {$a->nbattempts} 次作答，共 {$a->nbresults} 個結果';
$string['reportcountattempts'] = '{$a->nbresults} 個結果（{$a->nbusers} 名用户）';
$string['reports'] = '報告';
$string['repositorynotsupported'] = '這一倉儲並不支援鏈結到一個imsmanifest.xml 檔案';
$string['response'] = '回答';
$string['result'] = '結果';
$string['results'] = '結果';
$string['review'] = '複習';
$string['reviewmode'] = '複習模式';
$string['rightanswer'] = '正確答案';
$string['scoes'] = '學習目標';
$string['score'] = '分數';
$string['scorm12standard'] = '啟用 SCOM1.2 標準模式';
$string['scorm12standarddesc'] = '關閉這一設定將允許Moodle儲存比SCOM1.2規格明細表所允許的還更多的資料。
若你的SCORM包裹允許用戶輸入大量的文字，或若你的包裹試著儲存大量資料在suspend_data欄位，就關閉這個設定。';
$string['scorm:addinstance'] = '添加一SCORM包裹';
$string['scormclose'] = '可用於';
$string['scormcourse'] = '學習課程';
$string['scorm:deleteownresponses'] = '刪除自己的作答';
$string['scorm:deleteresponses'] = '刪除SCORM的作答';
$string['scormloggingoff'] = 'API日誌紀錄是關閉的';
$string['scormloggingon'] = 'API日誌紀錄是開啟的';
$string['scormopen'] = '開放使用時間';
$string['scormresponsedeleted'] = '刪除用戶作答';
$string['scorm:savetrack'] = '保存路徑';
$string['scorm:skipview'] = '跳過';
$string['scormtype'] = '類型';
$string['scormtype_help'] = '此設定決定課程包裹如何包含在課程內。這兒有四種選擇：

* 上傳的課程包裹 - 通過檔案選取器來選擇 SCORM 課程包。
* 外部的 SCORM 載貨清單 - 可以指定一個 imsmanifest.xml 網址。注意：如果此網址與您的網站的域名不同，那麼最好用“下載的課程包裹”，否則不能保存成績。
* 下載的課程包裹 - 可以指定一個課程包裹的網址。課程包會被解壓縮且保存在本地端電腦上，並隨著外部 SCORM 課程包裹的更新而更新。
* 外部 AICC 網址 - 這網址是單一 AICC 活動的啟動網址。會根據它建立一個虛擬的課程包裹。';
$string['scorm:viewreport'] = '檢視報告';
$string['scorm:viewscores'] = '檢視分數';
$string['scrollbars'] = '允許捲動視窗';
$string['search:activity'] = 'SCORM包裹 - 活動訊息';
$string['selectall'] = '選擇全部';
$string['selectnone'] = '刪除全部';
$string['show'] = '顯示';
$string['sided'] = '在左邊';
$string['skipview'] = '學生跳過內容結構頁面';
$string['skipviewdesc'] = '預設何時要跳過內容結構頁面';
$string['skipview_help'] = '此設定決定是否內容結構(目錄)頁面應該被跳過（不顯示）。如果課程包裹只包含一個學習物件，內容結構頁面就總是被跳過。';
$string['slashargs'] = '警告：這一網站已經關閉了斜線參數物件可能無法如預期的運作。';
$string['stagesize'] = '頁框/視窗大小';
$string['stagesize_help'] = '這兩個設定用來指定這學習物件的框架/視窗的寬度或高度。';
$string['started'] = '開始';
$string['status'] = '狀態';
$string['statusbar'] = '顯示狀態列';
$string['student_response'] = '回答';
$string['subplugintype_scormreport'] = '報告';
$string['subplugintype_scormreport_plural'] = '報告';
$string['suspended'] = '中止';
$string['syntax'] = '語法錯誤';
$string['tag_error'] = '內容為{$a->value}的({$a->tag})是不知名的標籤';
$string['time'] = '時間';
$string['title'] = '標題';
$string['toc'] = '目錄';
$string['toolbar'] = '顯示工具列';
$string['too_many_attributes'] = '標籤{$a->tag} 太多屬性了';
$string['too_many_children'] = '標籤{$a->tag} 太多子標籤';
$string['totaltime'] = '時間';
$string['trackcorrectcount'] = '答對數';
$string['trackcorrectcount_help'] = '這一試題的答對次數';
$string['trackid'] = '編號';
$string['trackid_help'] = '這是你的SCORM包裹為這試題所設的編號，這SCORM的規格明細表不允許提供完整的試題文字。';
$string['trackingloose'] = '注意：這個課程包的學習追蹤資料將會遺失。';
$string['tracklatency'] = '所用時間';
$string['tracklatency_help'] = '允許多次作答時，第一次作答和第二次作答的間隔時間';
$string['trackpattern'] = '標準答案';
$string['trackpattern_help'] = '這是這一問題應該有的正確答案，他沒有顯示學習者的答案';
$string['trackresponse'] = '回答';
$string['trackresponse_help'] = '這是這一學習者對這一問題所做的回答';
$string['trackresult'] = '結果';
$string['trackresult_help'] = '基於學生反應和<br />正確結果';
$string['trackscoremax'] = '最高分';
$string['trackscoremax_help'] = '原始分數的全距中的最大值';
$string['trackscoremin'] = '最低分';
$string['trackscoremin_help'] = '原始分數的全距中的最小值';
$string['trackscoreraw'] = '原始分數';
$string['trackscoreraw_help'] = '百分位置分數=((學生得分-min)/(max-min))*100%';
$string['tracksuspenddata'] = '暫停的數據';
$string['tracksuspenddata_help'] = '在學生工作階段之間提供空間來儲存和擷取資料';
$string['tracktime'] = '時間';
$string['tracktime_help'] = '作答次開始的時間';
$string['tracktype'] = '類型';
$string['tracktype_help'] = '試題的類型，比如"選擇題"或"簡答題"';
$string['trackweight'] = '權重';
$string['trackweight_help'] = '指派到這元件的權重';
$string['type'] = '類型';
$string['typeaiccurl'] = '外部AICC網址';
$string['typeexternal'] = '外部SCORM裝載清單';
$string['typelocal'] = '上傳的課程包裹';
$string['typelocalsync'] = '下載的課程包裹';
$string['undercontent'] = '在內容之下';
$string['unziperror'] = '課程包解壓縮時發現一個錯誤';
$string['updatefreq'] = '自動更新頻率';
$string['updatefreqdesc'] = '預設一個活動的自動更新的頻率';
$string['updatefreq_error'] = '只有當包裹檔案是寄存在外部伺服器時，才可以設定自動更新的頻率。';
$string['updatefreq_help'] = '這設定允許外部課程包可以自動下載並更新';
$string['validateascorm'] = '合法的SCORM包裹';
$string['validation'] = '檢驗結果';
$string['validationtype'] = '此偏好在設定正式版的SCORM所使用的DOMXML函式庫。如果您不清楚，就保留原來的選項。';
$string['value'] = '值';
$string['versionwarning'] = '{$a->tag}標籤出現警告，現存版本比1.3還舊';
$string['viewallreports'] = '檢視{$a}次的使用報告';
$string['viewalluserreports'] = '檢視{$a}位用戶的報告';
$string['whatgrade'] = '使用評分';
$string['whatgradedesc'] = '預設的作答計分方式';
$string['whatgrade_help'] = '如果允許多次答題，那麼此設定決定是要將最高分、平均分、第一次得分、還是最後一次的得分，記入成績簿中。

對多次答題的處理

* 開始新做答的選項是一個核取方塊，它在內容結構(目錄)頁面的進入按鈕上面。因此，如果您想允許多次答題的話，一定要允許用戶訪問此頁。

* 有些SCORM課程包能智慧處理新的作答，有些則不能。這意味著如果學習者重新進入一個已有的做答，當該SCORM沒有避免舊做答被覆蓋的邏輯時，它們就可能被覆蓋，哪怕這些做答是已經“完成”或者“通過”。

* “強制完成”、“強制新的作答”和“最後做答後鎖定”三個設定，也提供了多次做答的進一步管理功能。';
$string['width'] = '寬度';
$string['window'] = '視窗';
