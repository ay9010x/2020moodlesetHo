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
 * Strings for component 'badges', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   badges
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = '動作';
$string['activate'] = '啟用存取';
$string['activatesuccess'] = '存取獎章已經順利啟用';
$string['addbadgecriteria'] = '添加獎章判斷標準';
$string['addcourse'] = '添加課程';
$string['addcourse_help'] = '選出這一獎章所要求的所有課程。按下CTRL鍵可選擇多個項目。';
$string['addcriteria'] = '添加判斷規準';
$string['addcriteriatext'] = '要開始添加判斷規準，請從下拉選單中選擇一個選項。';
$string['addtobackpack'] = '放入收藏夾';
$string['adminonly'] = '這一頁面只能給網站管理員使用';
$string['after'] = '在頒授的日期之後';
$string['aggregationmethod'] = '整合的方法';
$string['all'] = '全部';
$string['allmethod'] = '符合全部選出的條件';
$string['allmethodactivity'] = '完成全部選出的活動';
$string['allmethodcourseset'] = '完成全部選出的課程';
$string['allmethodmanual'] = '授與獎章給所有選出的角色';
$string['allmethodprofile'] = '所有選出的個人資料表欄位都有填寫';
$string['allowcoursebadges'] = '啟用課程獎章';
$string['allowcoursebadges_desc'] = '允許在課程處境中，建立並頒授獎章';
$string['allowexternalbackpack'] = '啟用到外部獎章收藏的連結';
$string['allowexternalbackpack_desc'] = '允許用戶設定連接並顯示來自外部獎章收藏服務提供者的獎章。

注意：如果這網站無法連上網際網路(比如因為防火牆)，建議讓這選項維持關閉狀態。';
$string['any'] = '任何';
$string['anymethod'] = '符合任合選出的條件';
$string['anymethodactivity'] = '完成任何選出的活動';
$string['anymethodcourseset'] = '完成任何選出的課程';
$string['anymethodmanual'] = '對選出的角色授予獎章';
$string['anymethodprofile'] = '完成任何選出的個人資料表欄位';
$string['archivebadge'] = '你想要刪除獎章\'{$a}\'，但保持已核發出去的獎章？';
$string['archiveconfirm'] = '刪除並保持已核發出去的獎章';
$string['archivehelp'] = '<p>這一選項表示這獎章將會被標示為"撤回"，且將不會出現在獎章的列表中。用戶將不再獲得這一，但是現有的獎章受獎人仍然能夠在他們的個人資料頁上顯示這一獎章，和將它放到他們的外部獎章收藏夾。</p>
<p>若您希望您的用戶仍然能存取他贏得的獎章，您應該選擇這一選項，而不是完全刪除獎章</p>';
$string['attachment'] = '在訊息上附加上獎章';
$string['attachment_help'] = '若勾選，一個頒發的獎章將會附加在收件者的email上以便下載。(你必須透過網站管理 >外掛 > 訊息輸出 > 電子郵件，來啟用附件)';
$string['award'] = '授與獎章';
$string['awardedtoyou'] = '頒發給我';
$string['awardoncron'] = '這一獎章的存取功能已經成功啟用。由於有太多用戶可以立即贏得這一獎章，為確保網站的效能，這一頒發動作將花一些時間來處理。';
$string['awards'] = '收件者';
$string['backpackavailability'] = '外部獎章驗證';
$string['backpackavailability_help'] = '為了讓獎章收件者能證明他們從您那邊贏得他們的獎章，一個外部獎章收藏服務應該能夠存取您的網站並驗證從它頒授的獎章。 您的網站目前似乎無法存取，這表示說您已經頒發的獎章和未來將頒發的獎章都無法驗證。

##為何我會看到此一訊息?

它可能是您的防火牆阻止您的網路之外的用戶存取，或您的網站有密碼保護，或您把網站架在無法連上網際網路的電腦上(比如區域網路)

##這是個麻煩問題嗎?

在您計畫要頒授獎章的網站上，你應該修復這一問題。否則收件者將無法證明他們是從您那邊贏得他們的獎章。若您的網站還沒正式啟用，那您可以建立並頒授測試用獎章來檢視網站的可取用性。

##若我無法讓我的網站公開存取，我該怎麼辦？

為了驗證，唯一需要的網址是 [your-site-url]/badges/assertion.php ] ，因此若您能修改您的防火牆，已允許外部存取這個檔案，那獎章的驗證仍然可以進行。';
$string['backpackbadges'] = '您有來自{$a->totalcollections} 的 {$a->totalbadges} 個獎章被顯示出來。<a href="mybackpack.php">更改獎章收藏設定</a>。';
$string['backpackconnection'] = '連接獎章收藏服務';
$string['backpackconnection_help'] = '這一頁允許您設定連接到一個外部獎章收藏提供者。連接到一個獎章收藏服務讓你可以在這網站上顯示外部的獎章，必把在此贏得的獎章放到你的背包。

目前只有支援<a href="http://backpack.openbadges.org">Mozilla 開放獎章背包</a> 。你需要先註冊獎章收藏服務，才可以在這一頁設定背包連結。';
$string['backpackdetails'] = '獎章收藏夾設定';
$string['backpackemail'] = 'email 地址';
$string['backpackemail_help'] = '這一email地址關連到你的獎章收藏服務。當你被連接時，任何在這網站贏得的獎章，將會與這email地址相關聯。';
$string['backpackimport'] = '獎章匯入設定';
$string['backpackimport_help'] = '在成功建立獎章收藏服務連結之後，來自您的獎章收藏夾的獎章可以顯示在您的"我的獎章"頁面和您的個人資料頁面。

在這一區域，你可以從獎章收藏服務選擇獎章的蒐藏庫，以顯示在您的個人資料頁。';
$string['badgedetails'] = '獎章細節';
$string['badgeimage'] = '獎章圖樣';
$string['badgeimage_help'] = '這是代表這一獎章的圖像。

要添加一個新圖像，瀏覽並選出一個圖像(JPG 或PNG格式)，然後點選 "儲存更改"。這圖像將會被裁剪成正方形並縮放以符合獎章圖像的要求。';
$string['badgeprivacysetting'] = '獎章隱私設定';
$string['badgeprivacysetting_help'] = '您贏得的獎章可以顯示在您的個人資料頁上。這一設定讓您自動設定新贏得獎章的可見度。

您仍然可以在您的"我的獎章"頁面中控制個別獎章的隱私設定。';
$string['badgeprivacysetting_str'] = '自動在我的個人資料頁上顯示我獲得的獎章';
$string['badges'] = '獎章';
$string['badgesalt'] = '要掩蓋時添加在收件人地址中的隨機碼';
$string['badgesalt_desc'] = '使用添加密碼，可以讓獎章收藏服務去確認獎章得獎人，而不需要暴露他們的email地址。這一設定只允許使用數字和英文字母。

注意：為了收件者驗證的目的，一旦你開始頒發獎章，就不可以更改這一設定。';
$string['badgesdisabled'] = '這一網站尚未啟用獎章功能';
$string['badgesearned'] = '贏得的獎章數：{$a}';
$string['badgesettings'] = '獎章設定';
$string['badgestatus_0'] = '用戶不可以使用';
$string['badgestatus_1'] = '用戶可以使用';
$string['badgestatus_2'] = '用戶不可以使用';
$string['badgestatus_3'] = '用戶可以使用';
$string['badgestatus_4'] = '存檔';
$string['badgestoearn'] = '可用的獎章數量: {$a}';
$string['badgesview'] = '課程獎章';
$string['badgeurl'] = '已頒發獎章的鏈結';
$string['bawards'] = '收件人（{$a}）';
$string['bcriteria'] = '判斷規準';
$string['bdetails'] = '編輯細節';
$string['bmessage'] = '訊息';
$string['boverview'] = '綜覽';
$string['bydate'] = '完成於';
$string['clearsettings'] = '清除設定';
$string['completioninfo'] = '頒發這獎章是因為完成:';
$string['completionnotenabled'] = '這一課程沒有啟用"課程完成"功能，因此它不能作為頒發獎章的判斷規準。你可以在課程設定中啟用"課程完成"功能。';
$string['configenablebadges'] = '若啟用，這一功能能讓你建立獎章並它們用來獎勵網站用戶。';
$string['configuremessage'] = '獎章訊息';
$string['connect'] = '連結';
$string['connected'] = '已經連結';
$string['connecting'] = '連接中...';
$string['contact'] = '聯絡';
$string['contact_help'] = '獎章頒發者的電子郵件地址';
$string['copyof'] = '{$a}複製品';
$string['coursebadges'] = '獎章';
$string['coursebadgesdisabled'] = '在這網站並未啟用課程獎章';
$string['coursecompletion'] = '用戶必須完成這一課程';
$string['create'] = '新獎章';
$string['createbutton'] = '建立獎章';
$string['creatorbody'] = '<p>{$a->user} 已經完成獎章所要求的條件，並已經被頒授獎章。請在 {$a->link}檢視獲得的獎章 。</p>';
$string['creatorsubject'] = '\'{$a}\' 已經被獎勵!';
$string['criteria_0'] = '這一獎章是用來獎勵...';
$string['criteria_1'] = '活動完成';
$string['criteria_1_help'] = '允許頒發獎章給在一課程中完成一組活動的客戶。';
$string['criteria_2'] = '依據角色手動頒發';
$string['criteria_2_help'] = '允許手動頒發獎章給在這網站或課程中有特殊角色的用戶。';
$string['criteria_3'] = '社會參與';
$string['criteria_3_help'] = '社交';
$string['criteria_4'] = '完成課程';
$string['criteria_4_help'] = '允許頒發獎章給完成這課程的用戶。這判斷規準可以加上附帶條件，例如，最低分數或課程完成日期。';
$string['criteria_5'] = '完成一組課程';
$string['criteria_5_help'] = '允許頒發獎章給完成一組課程的用戶。每一課程可以加上附帶條件，例如，最低分數或課程完成日期。';
$string['criteria_6'] = '完成個人資料表';
$string['criteria_6_help'] = '允許頒發獎章給有填寫個人資料表上特定欄位的用戶。你可以從預設和自訂的個人資料表欄位上選擇。';
$string['criteriacreated'] = '已成功建立獎章判斷規準';
$string['criteriadeleted'] = '已成功刪除獎章判斷規準';
$string['criteria_descr'] = '用戶完成以下條件，即可獲頒這一獎章。';
$string['criteria_descr_0'] = '用戶若完成<strong>{$a}</strong>個列表中的條件，即可獲頒這一獎章。';
$string['criteria_descr_1'] = '已經完成<strong>{$a}</strong>個下列活動：';
$string['criteria_descr_2'] = '這獎章已經頒發給有<strong>{$a}</strong> 角色的用戶，
有下列角色：';
$string['criteria_descr_4'] = '用戶必須完成這課程';
$string['criteria_descr_5'] = '必須至少完成<strong>{$a}</strong>個下列課程：';
$string['criteria_descr_6'] = '必須至少完成<strong>{$a}</strong>個下列用戶資料表欄位：';
$string['criteria_descr_bydate'] = '於日期<em>{$a}</em>';
$string['criteria_descr_grade'] = '要達到最低成績 <em>{$a}</em>';
$string['criteria_descr_short0'] = '完成 <strong>{$a}</strong> 個，共有：';
$string['criteria_descr_short1'] = '完成 <strong>{$a}</strong> 個，共有：';
$string['criteria_descr_short2'] = '頒獎者： <strong>{$a}</strong> 於:';
$string['criteria_descr_short4'] = '完成這課程';
$string['criteria_descr_short5'] = '完成<strong>{$a}</strong> 的：';
$string['criteria_descr_short6'] = '完成<strong>{$a}</strong> 的：';
$string['criteria_descr_single_1'] = '必須完成下列活動：';
$string['criteria_descr_single_2'] = '這一獎章必須頒授給有下列角色的用戶：';
$string['criteria_descr_single_4'] = '用戶必須完成這一課程';
$string['criteria_descr_single_5'] = '必須完成下列課程：';
$string['criteria_descr_single_6'] = '必須完成下列用戶資料欄位：';
$string['criteria_descr_single_short1'] = '完成：';
$string['criteria_descr_single_short2'] = '頒獎者：';
$string['criteria_descr_single_short4'] = '完成這課程';
$string['criteria_descr_single_short5'] = '完成：';
$string['criteria_descr_single_short6'] = '完成：';
$string['criteriasummary'] = '判斷規準摘要';
$string['criteriaupdated'] = '已成功更新獎章判斷規準';
$string['criterror'] = '當前附加條件問題';
$string['criterror_help'] = '這一欄位設定顯示所有當初加到獎章要求條件的附加條件，但是現在已經不再可以使用了。

建議您取消這些附加條件，以確保用戶未來可以贏得這一獎章。';
$string['currentimage'] = '當前圖像';
$string['currentstatus'] = '當前狀態：';
$string['dateawarded'] = '頒發的日期';
$string['dateearned'] = '日期：{$a}';
$string['day'] = '日';
$string['deactivate'] = '關閉存取';
$string['deactivatesuccess'] = '存取獎章的功能已經順利關閉';
$string['defaultissuercontact'] = '預設的獎章頒發單位聯絡細節';
$string['defaultissuercontact_desc'] = '獎章頒發單位的email地址';
$string['defaultissuername'] = '預設的獎章頒發單位名稱';
$string['defaultissuername_desc'] = '頒發獎章的單位或機構名稱';
$string['delbadge'] = '您想要刪除獎章\'{$a}\' ，並移除所有已核發出去的獎章嗎？';
$string['delconfirm'] = '刪除並移除已經核發出去的獎章';
$string['delcritconfirm'] = '你確定你要刪除這一判斷規準嗎?';
$string['deletehelp'] = '<p>完全刪除一獎章表示它的所有資訊和判斷規準將被完全移除。贏得這一獎章的用戶將不再能夠存取它和在他們的個人資料頁上顯示它。 </p>
<p>注意：贏得這一獎章且把它放到他的獎章收藏服務的用戶，仍可保留這一獎章在他的外在獎章收藏夾中。但是他們將無法存取連結回到這一網站的判斷規準和證明頁面。</p>';
$string['delparamconfirm'] = '你確定你要刪除這一附加條件？';
$string['description'] = '說明';
$string['disconnect'] = '中斷連結';
$string['donotaward'] = '目前這一獎章還未被啟用，因此無法頒發給用戶，若你想要頒發這獎章，起把它的狀態設定為啟用。';
$string['editsettings'] = '編輯設定';
$string['enablebadges'] = '啟用獎章';
$string['error:backpackdatainvalid'] = '從獎章收藏服務回傳的資料是無效的';
$string['error:backpackemailnotfound'] = '這一 email \'{$a}\' 與背包無關聯。你需要位這一帳號  <a href="http://backpack.openbadges.org">建立一獎章收藏服務</a> 或用另一個 email 位址登入。';
$string['error:backpackloginfailed'] = '您不能連接到一外部獎章收藏服務，因為下列理由：{$a}';
$string['error:backpacknotavailable'] = '你的網站無法從網際網路存取，因此從這網站頒發的任何獎章都無法由外部獎章收藏服務驗證。';
$string['error:backpackproblem'] = '現在無法連接上你的獎章收藏服務提供網站。請稍後再試。';
$string['error:badjson'] = '這連接企圖回傳無效的資料';
$string['error:cannotact'] = '無法啟用這個獎章';
$string['error:cannotawardbadge'] = '無法授予獎章給用戶';
$string['error:cannotdeletecriterion'] = '這一規準不可以被刪除';
$string['error:clone'] = '無法複製這獎章';
$string['error:connectionunknownreason'] = '這連接未成功，但沒有說明原因';
$string['error:duplicatename'] = '在這系統中已經存有這一名稱的獎章';
$string['error:externalbadgedoesntexist'] = '沒發現獎章';
$string['error:guestuseraccess'] = '你現在是以訪客身分來使用穩站，要看到獎章，你需要使用你的帳號登入';
$string['error:invalidbadgeurl'] = '獎章頒授者的網址格式無效';
$string['error:invalidcriteriatype'] = '無效的判斷規準類型';
$string['error:invalidexpiredate'] = '失效日期必須設定在未來';
$string['error:invalidexpireperiod'] = '失效期限不可以設定為負數或0';
$string['error:noactivities'] = '在這一課程中沒有啟用活動的完成判斷規準';
$string['error:noassertion'] = '沒有獲得肯定的回覆。你可能在完成登入過程之前關閉了對話。';
$string['error:nocourses'] = '在這網站上沒有任何課程啟用課程完成記錄功能，所以無法顯示。你可以在課程設定中啟用課程完成紀錄功能。';
$string['error:nogroups'] = '<p>在您的背包裡，沒有公開的獎章蒐藏庫。</p>

<p>只有公開的蒐藏庫會被顯示，所以<a href="http://backpack.openbadges.org">請檢視您的獎章收藏夾</a>來建立一些公開的獎章蒐。</p>';
$string['error:nopermissiontoview'] = '你沒被授權去看獎章收件者';
$string['error:nosuchbadge'] = '編號{$a}的獎章並不存在';
$string['error:nosuchcourse'] = '警告：這一課程已經無法使用';
$string['error:nosuchfield'] = '警告：這一用戶資料表欄位已經無法使用';
$string['error:nosuchmod'] = '警告：這一活動已經無法使用';
$string['error:nosuchrole'] = '警告：這一角色已經無法使用';
$string['error:nosuchuser'] = '這email地址的用戶在目前獎章收藏服務提供網站中沒有帳號。';
$string['error:notifycoursedate'] = '警告：需要完成課程和活動才頒授的獎章，在課程開始日期之前無法頒發。';
$string['error:parameter'] = '警告：至少要選出一個附加條件以確保正確的獎章頒授流程。';
$string['error:personaneedsjs'] = '目前需要Javascript 來連結到您的被包。若可以，請請啟動Javascript 並重新裝載此頁。';
$string['error:requesterror'] = '這連接的請求失敗(錯誤代碼{$a})';
$string['error:requesttimeout'] = '這連接的請求，在他完成之前已超過時間';
$string['error:save'] = '無法儲存這獎章';
$string['error:userdeleted'] = '{$a->user} (這一用戶已經不存在 {$a->site})';
$string['eventbadgeawarded'] = '已頒授獎章';
$string['evidence'] = '證據';
$string['existingrecipients'] = '現有獎章的收件人';
$string['expired'] = '已經失效';
$string['expiredate'] = '這獎章在{$a}失效';
$string['expireddate'] = '這獎章已經在{$a}過期失效';
$string['expireperiod'] = '這一獎章在頒授後{$a}天失效。';
$string['expireperiodh'] = '這一獎章在頒授後{$a}小時失效。';
$string['expireperiodm'] = '這一獎章在頒授後{$a}分鐘失效。';
$string['expireperiods'] = '這一獎章在頒授後{$a}秒失效。';
$string['expirydate'] = '失效日期';
$string['expirydate_help'] = '你可自行決定，讓獎章到一指定日期即失效，或是獎章頒發給用戶多久之後即失效。';
$string['externalbadges'] = '我的來自其他網站的獎章';
$string['externalbadges_help'] = '這塊用來展示來您外面的獎章收藏';
$string['externalbadgesp'] = '來自其他網站的獎章:';
$string['externalconnectto'] = '若要顯示外面的獎章，您需要連接到<a href="{$a}">一個獎章存放網站</a>。';
$string['fixed'] = '固定的日期';
$string['hiddenbadge'] = '很抱歉，這獎章的擁有者沒有提供這一資訊';
$string['issuancedetails'] = '獎章到期';
$string['issuedbadge'] = '頒授獎章的訊息';
$string['issuerdetails'] = '頒授者細節';
$string['issuername'] = '頒授者的姓名';
$string['issuername_help'] = '頒授機關或主管的名稱';
$string['issuerurl'] = '頒授者的網址';
$string['localbadges'] = '我的來自{$a}網站的獎章';
$string['localbadgesh'] = '我的來自這一網站的獎章';
$string['localbadgesh_help'] = '你可以透過完成課程、完成課程活動、或完成其他要求，而在這網站上獲得獎章。

你可以藉由讓它們在你的個人資料頁上公開或隱藏，來管理你的獎章。

你可以下載全部獎章或逐一下載，並儲存在你自己的電腦上。下載的獎章可以被添加到你外面的獎章收藏服務。';
$string['localbadgesp'] = '獎章來自{$a}：';
$string['localconnectto'] = '若要分享網站外的獎章，您需要連接到<a href="{$a}">一個獎章存放網站</a>。';
$string['makeprivate'] = '對外隱藏';
$string['makepublic'] = '對外公開';
$string['managebadges'] = '管理獎章';
$string['message'] = '訊息主文';
$string['messagebody'] = '<p>你已經被頒授一個獎章 "%badgename%"!</p>
<p>有關這獎章的詳細訊息，請看 %badgelink%獎章訊息頁。</p>
<p>你可以從你的{$a}頁面管理和下載這獎章。</p>';
$string['messagesubject'] = '恭喜! 您剛贏得一個獎章!';
$string['method'] = '這一判斷規準已完成，當...';
$string['mingrade'] = '需要的最低成績';
$string['month'] = '月';
$string['mybackpack'] = '我的獎章收藏設定';
$string['mybadges'] = '我的獎章';
$string['never'] = '從不';
$string['newbadge'] = '添加一新獎章';
$string['newimage'] = '新圖像';
$string['noawards'] = '還沒有人贏得這一獎章';
$string['nobackpack'] = '沒有獎章收藏服務連接到這一帳號<br/>';
$string['nobackpackbadges'] = '在你選出的蒐藏中沒有獎章。<a href="mybackpack.php">添加更多蒐藏</a>。';
$string['nobackpackcollections'] = '沒有選出獎章蒐藏。<a href="mybackpack.php">添加蒐藏</a>。';
$string['nobadges'] = '這裡沒有可用的獎章';
$string['nocriteria'] = '這一獎章的判斷規準還沒有設定';
$string['noexpiry'] = '這一獎章沒有定失效日期';
$string['noparamstoadd'] = '這裡沒有附加條件可以加到這一獎章的要求條件中。';
$string['notacceptedrole'] = '您目前的角色不在可以手動頒發這獎章的角色中。<br/>
若您要看有誰已經贏得這一獎章您可以到{$a}頁面。';
$string['notconnected'] = '沒有連結上';
$string['nothingtoadd'] = '這兒沒有可用的判斷規準可以添加';
$string['notification'] = '通知獎章的創造者';
$string['notification_help'] = '這一設定用來控制要如何通知獎章創造者，讓他們知道有多少獎章頒授出去。

有五個選項可以使用:

* **從不**
* **每一次**
* **每天**
* **每週**
* **每月**';
$string['notifydaily'] = '每日';
$string['notifyevery'] = '每次';
$string['notifymonthly'] = '每月';
$string['notifyweekly'] = '每週';
$string['numawards'] = '這一獎章一經頒發給 <a href="{$a->link}">{$a->count}</a> 位用戶。';
$string['numawardstat'] = '這一獎章已經頒授給{$a}位用戶。';
$string['overallcrit'] = '選出的判斷規準已完成。';
$string['personaconnection'] = '用你的email登入';
$string['personaconnection_help'] = 'Persona 是一個使用您擁有的Email地址來在不同網站中證明您自己的系統。這 Open Badges backpack使用 Persona 為登入系統，因此為了要連接到獎章存放服務，您需要一個 Persona 帳號。

要知道更多Persona，請訪問 <a href="https://login.persona.org/about">https://login.persona.org/about</a>。';
$string['potentialrecipients'] = '可能的獎章收件者';
$string['preferences'] = '獎章偏好';
$string['recipientdetails'] = '收件者細節';
$string['recipientidentificationproblem'] = '在現有的用戶中，無法找到這一獎章的收件人。';
$string['recipients'] = '獎章收件者';
$string['recipientvalidationproblem'] = '當前用戶無法驗證是否為這一獎章的收件人';
$string['relative'] = '相對日期';
$string['requiredcourse'] = '至少要添加一個課程到課程組合的判斷規準。';
$string['reviewbadge'] = '變更獎章的存取狀態';
$string['reviewconfirm'] = '<p>這將讓用戶看到你的獎章，並讓他們開始贏得它 </p>

<p>可能有些用戶已經符合這獎章的判斷規準，並在您啟用它之後立即獲頒獎章</p>

<p>一旦有獎章被般發出去，它將會被<strong> 鎖定</strong> ---某些設定，包括判斷規準和失效日期都無法再更改。
</p>

<p>你確定你要啟用這獎章"{$a}"的存取嗎？</p>';
$string['save'] = '儲存';
$string['searchname'] = '用名稱搜尋';
$string['selectaward'] = '請選擇你要使用來頒授這獎章的角色';
$string['selectgroup_end'] = '只有公開的獎章收藏夾會被顯示，<a href="http://backpack.openbadges.org">檢視您的獎章收藏夾</a>來建立更多公開的收藏。';
$string['selectgroup_start'] = '從你的獎章收藏夾選出來顯示在這網站';
$string['selecting'] = '和選出的獎章...';
$string['setup'] = '設定連接';
$string['signinwithyouremail'] = '以您的email註冊';
$string['sitebadges'] = '網站獎章';
$string['sitebadges_help'] = '網站獎章只可以因網站有關的活動而頒授給用戶。這包括完成一組課程或部分的個人資料表。網站獎章也可以用手動方式由一個用戶到另一個用戶。

課程相關活動的獎章必須在課程層次建立，課程獎章可以在"課程管理 > 獎章"之下找到。';
$string['status'] = '獎章狀態';
$string['status_help'] = '獎章的狀態會決定它在這系統裡的動作：

* **可用的** -- 表示用戶可以贏得這一獎章，一旦獎章可以頒授給用戶，它的判斷規準就不能被修改了。

* **無法使用** -- 表示這獎章還無法被贏得或人工的頒授。如果這一獎章以前從未頒授過，那它的判斷規準還可以被更改。

一個獎章一旦被頒授過，它會自動被**鎖住**，被鎖住的獎章還可以繼續頒發，但是它的判斷規準就不再可以更改。如果你需要修改一個被鎖住的獎章的細節或判斷規準，你可以複製這一獎章，然後作所有需要的修改。';
$string['statusmessage_0'] = '這一獎章目前用戶無法取用。若您要讓用戶可以贏得這一獎章，請啟動存取功能。';
$string['statusmessage_1'] = '這一獎章目前用戶可以取用。請關閉存取功能來做更改。';
$string['statusmessage_2'] = '這一獎章目前用戶無法取用，且它的判斷規準已被鎖定。若您要讓用戶可以贏得這一獎章，請啟動存取功能。';
$string['statusmessage_3'] = '這一獎章現在用戶已經可以使用，且它的判斷規準已經鎖定。';
$string['statusmessage_4'] = '這一獎章目前已被存檔';
$string['subject'] = '訊息主旨';
$string['variablesubstitution'] = '訊息中可替換的變項';
$string['variablesubstitution_help'] = '在獎章的訊息中，你可以在訊息的主旨或內文中插入某些特定的變項，這樣在訊息送出時會將變項替換成真實的資料。這些變項要一字不變地插在文字中，它包括：

%badgename%
:   這變項將替換成獎章的完整名稱。

%username%
:   這變項將替換成收件者的全名。

%badgelink%
:  這變項將替換成公開的網址，裡面有這頒授獎章的訊息。';
$string['viewbadge'] = '檢視頒授的獎章';
$string['visible'] = '可見的';
$string['warnexpired'] = '(這一獎章已經過期失效！)';
$string['year'] = '年';
