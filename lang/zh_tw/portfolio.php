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
 * Strings for component 'portfolio', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   portfolio
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activeexport'] = '持續主動匯出';
$string['activeportfolios'] = '可使用的學習歷程檔案';
$string['addalltoportfolio'] = '全部匯出到學習歷程檔案';
$string['addnewportfolio'] = '增加一個新的學習歷程檔案';
$string['addtoportfolio'] = '匯出到學習歷程檔案';
$string['alreadyalt'] = '已經在匯出-請點選此處解析這次傳送';
$string['alreadyexporting'] = '您這次工作階段已經有一個活動的歷程檔案匯出。在繼續之前，您必須完成這一匯出，或者取消它。您要繼續完成匯出嗎？（"否"會取消匯出）';
$string['availableformats'] = '可用的匯出格式';
$string['callbackclassinvalid'] = '提供的呼叫函數類別無效，或它不是繼承自porfolio_caller';
$string['callercouldnotpackage'] = '無法打包你的資料來匯出：原始錯誤是{$a}';
$string['cannotsetvisible'] = '無法設定為可見的 - 因為設定錯誤，此外掛已被完全停用。';
$string['commonportfoliosettings'] = '共同的學習歷程檔案設定';
$string['commonsettingsdesc'] = '<p>一次傳送要花費的時間被視作"適中"還是"大量"決定了用戶是否可以等待傳送結束。</p><p>小於"適中"門檻的檔案會立刻被傳送，不會詢問用戶。而"中度"和"大量"的傳送會先給用戶選項，並警告他們這可能會耗掉一些時間。</p><p>另外，某些學習歷程檔案外掛會完全忽略此選項，並強制所有轉移必須要排隊。</p>';
$string['configexport'] = '設定匯出的資料';
$string['configplugin'] = '設定學習歷程檔案外掛';
$string['configure'] = '設定';
$string['confirmcancel'] = '您確定要取消本次的匯出？';
$string['confirmexport'] = '請確認本次匯出';
$string['confirmsummary'] = '您的匯出的摘要';
$string['continuetoportfolio'] = '繼續瀏覽您的學習歷程檔案';
$string['deleteportfolio'] = '刪除學習歷程檔案';
$string['destination'] = '目的地';
$string['disabled'] = '抱歉，本站沒有啟用學習歷程檔案匯出功能';
$string['disabledinstance'] = '已經停用';
$string['displayarea'] = '匯出區域';
$string['displayexpiry'] = '傳送到期時間';
$string['displayinfo'] = '匯出資訊';
$string['dontwait'] = '不要等待';
$string['enabled'] = '啟用學習歷程檔案系統';
$string['enableddesc'] = '若啟用，用戶可以匯出自己的內容（例如討論區發表的文章、繳交的作業等）成為外部的學習歷程檔案或HTML頁面。';
$string['err_uniquename'] = '學習歷程檔案的名稱必須是唯一的(每一外掛)。';
$string['exportalreadyfinished'] = '學習歷程檔案匯出完成！';
$string['exportalreadyfinisheddesc'] = '學習歷程檔案匯出完成！';
$string['exportcomplete'] = '學習歷程檔案匯出完成！';
$string['exportedpreviously'] = '先前的匯出';
$string['exportexceptionnoexporter'] = '一個工作階段 portfolio_export_exception 被拋出，也沒有匯出目標。';
$string['exportexpired'] = '學習歷程檔案匯出逾時';
$string['exportexpireddesc'] = '您曾反覆嘗試匯出某些資訊，或開始一個空白的匯出。

正確的操作是，退回到原初的位置，再重新開始。

會發生這種情況，一般是因為在匯出結束之後，您按到了退回按紐，或者是因為使用了一個不正確的網址。';
$string['exporting'] = '匯出到學習歷程檔案';
$string['exportingcontentfrom'] = '正在從{$a}匯出內容';
$string['exportingcontentto'] = '正在匯出內容到{$a}';
$string['exportqueued'] = '歷程檔案匯出已經順利排隊等待轉移';
$string['exportqueuedforced'] = '學習歷程檔案匯出已經順利排隊等待匯出(遠端系統已經執行排隊的轉移)';
$string['failedtopackage'] = '無法找到檔案來打包';
$string['failedtosendpackage'] = '將您的資料發送往您選擇的歷程檔案儲存系統時出錯：原始錯誤訊息是{$a}';
$string['filedenied'] = '無法存取這一檔案';
$string['filenotfound'] = '沒找到檔案';
$string['fileoutputnotsupported'] = '這一格式不支援這一格式的重寫檔案輸出';
$string['format_document'] = '文件';
$string['format_file'] = '檔案';
$string['format_image'] = '圖像';
$string['format_leap2a'] = 'Leap2A 學習歷程檔案格式';
$string['format_mbkp'] = 'Moodle備份格式';
$string['format_pdf'] = 'PDF格式';
$string['format_plainhtml'] = 'HTML格式';
$string['format_presentation'] = '簡報檔格式';
$string['format_richhtml'] = 'HTML及附件';
$string['format_spreadsheet'] = '試算表格式';
$string['format_text'] = '純文字格式';
$string['format_video'] = '影片格式';
$string['highdbsizethreshold'] = '資料庫筆數超過多少算大量轉移？';
$string['highdbsizethresholddesc'] = '資料庫紀錄筆數超過多少，將會被認為需 要花大量時間來轉移。';
$string['highfilesizethreshold'] = '檔案大小超過多少時，算是大量的轉移？';
$string['highfilesizethresholddesc'] = '大小超過此門檻的檔案，將會被認為需要花大量時間來轉移';
$string['insanebody'] = '您好！因為您是{$a->sitename}的管理員，所以您會收到此訊息。

因為設定錯誤，一些歷程檔案外掛模組被自動停用，這表示用戶現在不能使用這些外掛匯出內容到這些歷程檔案。

被停用的歷程檔案外掛模組有：

{$a->textlist}

請瀏覽 {$a->fixurl} ，儘快修正此問題。';
$string['insanebodyhtml'] = '<p>您好！因為您是{$a->sitename}的管理員，所以您會收到此訊息。</p>

<p>因為設定錯誤，一些歷程檔案外掛模組被自動停用，這表示用戶現在不能使用這些外掛匯出內容到這些歷程檔案。</p>

<p>被停用的歷程檔案外掛模組的清單有：</p>

{$a->htmlist}

<p>請瀏覽 <a href="{$a->fixurl}">歷程檔案設定頁面</a>，儘快修正此問題。</p>';
$string['insanebodysmall'] = '您好！因為您是{$a->sitename}的管理員，所以您會收到此訊息。因為設定錯誤，一些學習歷程檔案外掛被自動停用，這表示用戶現在不能使用這些外掛匯出內容到歷程檔案。請瀏覽 {$a->fixurl} ，儘快修正此問題。';
$string['insanesubject'] = '某些歷程檔案服務已經自動被停用';
$string['instancedeleted'] = '已成功刪除學習歷程檔案';
$string['instanceismisconfigured'] = '歷程檔案服務設定不正確，略過。錯誤是：{$a}';
$string['instancenotdelete'] = '無法刪除學習歷程檔案';
$string['instancenotsaved'] = '無法儲存學習歷程檔案';
$string['instancesaved'] = '學習歷程檔案儲存成功';
$string['intro'] = '您建立的內容，比如說提交的作業、討論區的貼文、在部落格上的文章等，都可以匯出到一學習歷程檔案或被下載到您自己的電腦上。
任何您不希望使用到的學習歷程檔案，都可以被隱藏起來，這樣它就不會在列在匯出的內容上。';
$string['invalidaddformat'] = '傳送給portfolio_add_button的附加格式無效。({$a})必須是PORTFOLIO_ADD_XXX 中的一個';
$string['invalidbuttonproperty'] = '找不到portfolio_button 的屬性（{$a}）';
$string['invalidconfigproperty'] = '找不到設定屬性（{$a->class} 的 {$a->property}）';
$string['invalidexportproperty'] = '找不到匯出設定屬性（{$a->class} 的 {$a->property}）';
$string['invalidfileareaargs'] = '傳送給set_file_and_format_data 的檔案區域參數無效-必須包含contextid、component、fileare 和 itemid';
$string['invalidformat'] = '正在匯出無效格式，{$a}';
$string['invalidinstance'] = '找不到這個學習歷程檔案服務';
$string['invalidpreparepackagefile'] = '呼叫prepare_package_file無效-必須設定single 或 multifiles';
$string['invalidproperty'] = '找不屬性（{$a->class} 的 {$a->property}）';
$string['invalidsha1file'] = '呼叫get_sha1_file 無效 — 必須設定 single 或 multifiles';
$string['invalidtempid'] = '無效的匯出id。可能它已經逾期';
$string['invaliduserproperty'] = '找不到用戶設定屬性（{$a->class} 的 {$a->property}）';
$string['leap2a_emptyselection'] = '沒有選出必要的值';
$string['leap2a_entryalreadyexists'] = '您試著加入的id為{$a}的Leap2A項目在feed中已經存在。';
$string['leap2a_feedtitle'] = '為{$a}從Moodle匯出Leap2A';
$string['leap2a_filecontent'] = '試圖設定Leap2A項的內容到檔案，而不是使用檔案子類別。';
$string['leap2a_invalidentryfield'] = '您試圖設定一個不存在的欄位項目（{$a}），或者您不能直接設定';
$string['leap2a_invalidentryid'] = '您試著用一個不存在的id連接一個項目({$a})';
$string['leap2a_missingfield'] = '缺少必要的Leap2A項目欄位{$a}';
$string['leap2a_nonexistantlink'] = '一個Leap2A項目($a->from})試著用rel{$a->rel}連結到一個不存在的項目{$a->to})';
$string['leap2a_overwritingselection'] = '在make_selection中覆蓋項目({$a})的原始類型來選擇類型';
$string['leap2a_selflink'] = '一個Leap2A項($a->id})試圖用rel{$a->rel}連接自己';
$string['logs'] = '轉移日誌';
$string['logsummary'] = '先前成功的轉移';
$string['manageportfolios'] = '管理學習歷程檔案';
$string['manageyourportfolios'] = '管理您的學習歷程檔案';
$string['mimecheckfail'] = '這學習歷程檔案外掛{$a->plugin}不支援 {$a->mimetype}類型';
$string['missingcallbackarg'] = '缺少呼叫類別{$a->class}的所需參數 {$a->arg}';
$string['moderatedbsizethreshold'] = '轉移時適度的資料庫大小';
$string['moderatedbsizethresholddesc'] = '資料庫紀錄的數目，超過這數目將花較多時間來傳送';
$string['moderatefilesizethreshold'] = '檔案大小超過多少時，算是中量的轉移？';
$string['moderatefilesizethresholddesc'] = '檔案大小超過這一界線，將會花適量的時間來轉移';
$string['multipleinstancesdisallowed'] = '試圖建立一個外掛的實例，但它不允許多個實體（{$a}）';
$string['mustsetcallbackoptions'] = '您必須在 portfolio_add_button 的建構子或使用 set_callback_options 的方法設定呼叫選項';
$string['noavailableplugins'] = '抱歉，但是這裡沒有學習歷程檔可讓您匯出';
$string['nocallbackclass'] = '找不到可呼叫的類別（{$a}）';
$string['nocallbackcomponent'] = '找不到指定的原件{$a}';
$string['nocallbackfile'] = '您正試圖從中會出的模組有些問題-找不到要求的檔案({$a})';
$string['noclassbeforeformats'] = '您在設定回應callback方式之前，必須先設定在portfolio_button的calling set_formats';
$string['nocommonformats'] = '在呼叫的位置 {$a->location}(呼叫者支援{$a->formates}） 和可用的歷程檔案外掛之間，沒有共同的格式。';
$string['noinstanceyet'] = '還沒有被選擇';
$string['nologs'] = '沒有日誌檔可以顯示!';
$string['nomultipleexports'] = '抱歉，但學習歷程檔案的目的地({$a->plugin}）不支援同時進行多個匯出。請<a href="{$a->link}">先完成目前的工作</a>再重新嘗試';
$string['nonprimative'] = '傳送到 portfolio_add_button的呼叫參數不是原始值。無法繼續。參數的key是{$a->key}，值是 {$a->value}';
$string['nopermissions'] = '抱歉，但是你沒有權限從這一區域匯出檔案。';
$string['notexportable'] = '抱歉，你試著要匯出的內容的類型，是不可匯出的。';
$string['notimplemented'] = '抱歉，您要匯出的格式還尚未實做（{$a}）';
$string['notyetselected'] = '還沒有被選擇';
$string['notyours'] = '你正試著還原一個學習歷程檔案的匯出檔，但它不是屬於你的。';
$string['nouploaddirectory'] = '無法建立一暫時目錄來打包你的資料';
$string['off'] = '啟用但隱藏';
$string['on'] = '啟用且可看見';
$string['plugin'] = '學習歷程檔案外掛';
$string['plugincouldnotpackage'] = '為匯出而打包您的資料時出錯：原始錯誤是{$a}';
$string['pluginismisconfigured'] = '學習歷程檔案外掛設定錯誤，跳過。錯誤是：{$a}';
$string['portfolio'] = '學習歷程檔案';
$string['portfolios'] = '學習歷程檔案';
$string['queuesummary'] = '當前排隊的轉移';
$string['returntowhereyouwere'] = '回到你原來的地方';
$string['save'] = '儲存';
$string['selectedformat'] = '選擇的匯出格式';
$string['selectedwait'] = '選出來等待？';
$string['selectplugin'] = '選擇目的地';
$string['showhide'] = '顯示/隱藏';
$string['singleinstancenomultiallowed'] = '只有一個單一學習歷程檔案外掛是可用的，它不支援每個工作階段的多次匯出，並且這兒已經有一個處於活動狀態的匯出在這工作階段中使用這外掛！';
$string['somepluginsdisabled'] = '有些學習歷程檔案外掛整個被關閉，因為它們若不是設定錯誤，就是倚賴其他東西，它是：';
$string['sure'] = '你確定要刪除"{$a}"？這動作無法復原。';
$string['thirdpartyexception'] = '學習歷程檔案匯出時發生異常（{$a}）。已經擷取到到並重新丟出，但這個一定要修復';
$string['transfertime'] = '轉移時間';
$string['unknownplugin'] = '不知道(也許也許曾經有過，但被管理員移除)';
$string['wait'] = '等待';
$string['wanttowait_high'] = '不建議你等待這個轉移完成，但是若你確定且知道你在做什麼，你可以等下去。';
$string['wanttowait_moderate'] = '你是否要等待這一轉移？它可能要花好幾分鐘。';
