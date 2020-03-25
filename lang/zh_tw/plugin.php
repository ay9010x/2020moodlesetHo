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
 * Strings for component 'plugin', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   plugin
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = '動作';
$string['availability'] = '可用性';
$string['cancelinstallall'] = '取消新的安裝 ({$a})';
$string['cancelinstallhead'] = '取消外掛的安裝';
$string['cancelinstallinfo'] = '以下的外掛還沒完全的安裝好，所以它們的安裝可能會被刪除。為了徹底刪除，這外掛的資料夾現在必須從這伺服器中移除。請確定這正是你所要做的，以防止資料意外刪除(比如你自己修改的程式碼)。';
$string['cancelinstallinfodir'] = '要刪除的資料夾：{$a}';
$string['cancelinstallone'] = '取消這一安裝';
$string['cancelupgradeall'] = '取消升級({$a})';
$string['cancelupgradehead'] = '還原外掛的先前版本';
$string['cancelupgradeone'] = '取消這一升級';
$string['checkforupdates'] = '檢查可用的更新';
$string['checkforupdateslast'] = '上次檢查完成於{$a}';
$string['dependencyavailable'] = '可用的';
$string['dependencyfails'] = '失敗';
$string['dependencyinstall'] = '安裝';
$string['dependencyinstallhead'] = '安裝缺少的組件';
$string['dependencyinstallmissing'] = '安裝缺少的組件 ({$a})';
$string['dependencymissing'] = '缺少';
$string['dependencyunavailable'] = '無法使用的';
$string['dependencyupload'] = '上傳';
$string['dependencyuploadmissing'] = '上傳的ZIP壓縮檔';
$string['detectedmisplacedplugin'] = '外掛套件 "{$a->component}"是安裝在不正確的位置 "{$a->current}", 期待的位置是 "{$a->expected}"。';
$string['displayname'] = '外掛套件名稱';
$string['err_response_curl'] = '無法提取可用的更新資料，意外的cURL錯誤';
$string['err_response_format_version'] = '回應格式的非預期版本，請重新檢查可用的更新。';
$string['err_response_http_code'] = '無法獲得可用的更新資料，意外的HTTP回應代碼';
$string['filterall'] = '顯示全部';
$string['filtercontribonly'] = '只顯示額外的外掛套件';
$string['filterupdatesonly'] = '只顯示可更新的';
$string['misdepinfoplugin'] = '外掛訊息';
$string['misdepinfoversion'] = '版本訊息';
$string['misdepsavail'] = '可用的缺少的組件';
$string['misdepsunavail'] = '無法使用的缺少的組件';
$string['misdepsunavaillist'] = '沒有找到版本可以滿足相關的要求：{$a}';
$string['misdepsunknownlist'] = '沒有在外掛目錄裡： <strong>{$a}</strong>';
$string['moodleversion'] = 'Moodle{$a}';
$string['noneinstalled'] = '沒有安裝這一類型的外掛';
$string['notdownloadable'] = '無法下載這包裹';
$string['notdownloadable_help'] = '更新用的壓縮檔包裹無法下載。請參看文件頁面以找到更詳細說明。';
$string['notes'] = '備註';
$string['notwritable'] = '外掛套件檔案無法寫入';
$string['notwritable_help'] = '您已經啟用自動更新部署，且發現這外掛有可用的更新。然而，因為網頁伺服器無法寫入這外掛套件檔案，因此目前無法自動安裝這一更新。
您需要請系統管理員把這外掛資料夾和它所有的檔案都改成可寫入的，這樣才能自動安裝可用的更新。';
$string['otherplugin'] = '{$a->component}';
$string['otherpluginversion'] = '{$a->component} ({$a->version})';
$string['overviewall'] = '所有外掛';
$string['overviewext'] = '額外的外掛';
$string['overviewupdatable'] = '可用的更新';
$string['packagesdebug'] = '已經啟用除錯輸出';
$string['packagesdownloading'] = '下載{$a}';
$string['packagesextracting'] = '提取{$a}';
$string['packagesvalidating'] = '驗證 {$a}';
$string['packagesvalidatingfailed'] = '由於驗證失敗，已放棄安裝';
$string['packagesvalidatingok'] = '驗證成功，安裝可以繼續';
$string['plugincheckall'] = '所有外掛';
$string['plugincheckattention'] = '需要注意的外掛';
$string['pluginchecknone'] = '沒有外掛需要你的注意';
$string['pluginchecknotice'] = '此頁面顯示在這升級過程中可能需要您留意的外掛套件。

即將要安裝的新外掛、即將要升級的外掛和少掉的外掛都會被特別明顯標示。

第三方的外掛若有可用的更新，也被特別標示出來。

建議您檢查第三方外掛是否有新的版本可用，並在繼續這次升級 Moodle 之前，更新它們的程式碼。';
$string['plugindisable'] = '停用';
$string['plugindisabled'] = '已停用';
$string['pluginenable'] = '啟用';
$string['pluginenabled'] = '已啟用';
$string['release'] = '釋出';
$string['requiredby'] = '需要用到：{$a}';
$string['requires'] = '需要';
$string['rootdir'] = '目錄';
$string['settings'] = '設定';
$string['source'] = '來源';
$string['sourceext'] = '外掛';
$string['sourcestd'] = '標準';
$string['status'] = '狀態';
$string['status_delete'] = '將會被刪除';
$string['status_downgrade'] = '已經安裝了更高版本！';
$string['status_missing'] = '從磁碟上遺失';
$string['status_new'] = '即將要安裝';
$string['status_nodb'] = '沒有資料庫';
$string['status_upgrade'] = '即將升級';
$string['status_uptodate'] = '已經安裝';
$string['supportedmoodleversions'] = '已支援的Moodle版本';
$string['systemname'] = '識別碼';
$string['type_antivirus'] = '防毒外掛';
$string['type_antivirus_plural'] = '防毒外掛';
$string['type_auth'] = '身分驗證方法';
$string['type_auth_plural'] = '身分認證方法';
$string['type_availability'] = '可用性的限制';
$string['type_availability_plural'] = '可用性的限制';
$string['type_block'] = '區塊';
$string['type_block_plural'] = '區塊';
$string['type_cachelock'] = '快取封鎖管理者';
$string['type_cachelock_plural'] = '快取封鎖管理者';
$string['type_cachestore'] = '快取儲存';
$string['type_cachestore_plural'] = '快取儲存';
$string['type_calendartype'] = '行事曆類型';
$string['type_calendartype_plural'] = '行事曆類型';
$string['type_coursereport'] = '課程報告';
$string['type_coursereport_plural'] = '課程報表';
$string['type_dataformat'] = '資料格式';
$string['type_dataformat_plural'] = '資料格式';
$string['type_editor'] = '編輯器';
$string['type_editor_plural'] = '編輯器';
$string['type_enrol'] = '選課方式';
$string['type_enrol_plural'] = '選課方式';
$string['type_filter'] = '文字過濾器';
$string['type_filter_plural'] = '文字過濾器';
$string['type_format'] = '課程格式';
$string['type_format_plural'] = '課程格式';
$string['type_gradeexport'] = '成績匯出方式';
$string['type_gradeexport_plural'] = '成績匯出方式';
$string['type_gradeimport'] = '成績匯入方式';
$string['type_gradeimport_plural'] = '成績匯入方式';
$string['type_gradereport'] = '成績單報表';
$string['type_gradereport_plural'] = '成績單報表';
$string['type_gradingform'] = '進階評分方法';
$string['type_gradingform_plural'] = '進階評分方法';
$string['type_local'] = '本地外掛';
$string['type_local_plural'] = '本地外掛';
$string['type_message'] = '簡訊輸出';
$string['type_message_plural'] = '簡訊輸出';
$string['type_mnetservice'] = 'MNet服務';
$string['type_mnetservice_plural'] = 'MNet服務';
$string['type_mod'] = '活動模組';
$string['type_mod_plural'] = '活動模組';
$string['type_plagiarism'] = '防止抄襲的外掛套件';
$string['type_plagiarism_plural'] = '防止抄襲的外掛套件';
$string['type_portfolio'] = '學習歷程檔案';
$string['type_portfolio_plural'] = '學習歷程檔案';
$string['type_profilefield'] = '個人資料欄位類型';
$string['type_profilefield_plural'] = '個人資料欄位類型';
$string['type_qbehaviour'] = '試題作答計分方式';
$string['type_qbehaviour_plural'] = '試題作答計分方式';
$string['type_qformat'] = '題目匯入/匯出格式';
$string['type_qformat_plural'] = '題目匯入/匯出格式';
$string['type_qtype'] = '試題類型';
$string['type_qtype_plural'] = '試題類型';
$string['type_report'] = '網站報告';
$string['type_report_plural'] = '報告';
$string['type_repository'] = '倉儲';
$string['type_repository_plural'] = '倉儲';
$string['type_search'] = '搜尋引擎';
$string['type_search_plural'] = '搜尋引擎';
$string['type_theme'] = '佈景主題';
$string['type_theme_plural'] = '佈景主題';
$string['type_tool'] = '管理工具';
$string['type_tool_plural'] = '管理工具';
$string['type_webservice'] = '網路服務傳輸協定';
$string['type_webservice_plural'] = '網路服務傳輸協定';
$string['uninstall'] = '解除安裝';
$string['uninstallconfirm'] = '你即將要把外掛套件<em>{$a->name}</em>解除安裝。
這將會把資料庫有關此外掛的一切紀錄刪除，包括它的配置、日誌、這外掛的管理的用戶檔案等等。這將會沒有復原的機會，且Moodle本身沒有建立復原的備份。
你確定你要繼續刪除？';
$string['uninstalldelete'] = '所有與這外掛套件<em>{$a->name}</em> 有關連的資料，已從資料庫中被刪除。
要防止這外掛套件本身重新安裝，它的資料夾<em>{$a->rootdir}</em>必須從你的伺服器中以手工移除。
因為讀寫權限的關係，Moodle本身無法移除這資料夾。';
$string['uninstalldeleteconfirm'] = '所有與這外掛套件<em>{$a->name}</em> 有關連的資料，已從資料庫中被刪除。
要防止這外掛本身重新安裝，它的資料夾<em>{$a->rootdir}</em>必須從你的伺服器中移除。
你現在要移除這外掛資料夾嗎？';
$string['uninstalldeleteconfirmexternal'] = '似乎你的外掛套件已經透過原始碼管理系統{$a}取得最新版本。
若你移除這外掛套件資料夾，你可能會失去這程式碼的重要修改。
請確定你真的要移除這外掛套件資料夾才繼續。';
$string['uninstallextraconfirmblock'] = '這種區塊有{$a->instances} 個案例。';
$string['uninstallextraconfirmenrol'] = '這裡有 {$a->enrolments} 個用戶選課。';
$string['uninstallextraconfirmmod'] = '在{$a->courses} 課程裡，這一模組有{$a->instances}個案例。';
$string['uninstalling'] = '解除安裝{$a->name}';
$string['updateavailable'] = '有新的版本 {$a} 可用！';
$string['updateavailable_moreinfo'] = '更多資訊...';
$string['updateavailable_release'] = '版本{$a}';
$string['updatepluginconfirm'] = '外掛套件更新確認';
$string['updatepluginconfirmexternal'] = '顯然您已經從原始碼管理系統({$a})取得這外掛的最新版本。若您安裝這一外掛，您將不再能從原始碼管理系統取得外掛更新。請在繼續之前，確定您要更新這外掛。';
$string['updatepluginconfirminfo'] = '您即將要安裝這一外掛<strong>{$a->name}</strong>的新版本。包含這一外掛的{$a->version} 版的壓縮包裹將會從<a href="{$a->url}">{$a->url}</a>下載，並解壓縮到您的Moodle的安裝，這樣它就可以更新您的安裝。';
$string['updatepluginconfirmwarning'] = '請注意，Moodle在升級之前，不會自動備份您的資料庫。我們強烈建議您現在就做一個完整的備份，以防備萬一新程式碼有錯誤，而使你的網站無法使用或摧毀你的資料庫。請考慮你冒的風險。';
$string['validationmsg_componentmatch'] = '完整的組件名稱';
$string['validationmsg_componentmismatchname'] = '外掛名稱不符合';
$string['validationmsg_componentmismatchname_help'] = '有些ZIP壓縮包裹，比如由Github所產生的，可能包含一個不正確的根目錄名稱。你必須去修正這根目錄名稱以符合宣稱的外掛名稱。';
$string['validationmsg_componentmismatchname_info'] = '這外掛宣稱它的名稱是\'{$a}\' ，但是這不符合根目錄名稱。';
$string['validationmsg_componentmismatchtype'] = '外掛類型不符合';
$string['validationmsg_componentmismatchtype_info'] = '你選擇了類型\'{$a->expected}\' ，但是這外掛宣稱它的類型是 \'{$a->found}\'。';
$string['validationmsg_filenotexists'] = '沒有找到提取出來的檔案';
$string['validationmsg_filesnumber'] = '在這包裹找不到足夠的檔案';
$string['validationmsg_filestatus'] = '無法抽出所有的檔案';
$string['validationmsg_filestatus_info'] = '試圖抽取檔案{$a->file} 結果導致錯誤 \'{$a->status}\'。';
$string['validationmsg_foundlangfile'] = '發現語言檔案';
$string['validationmsglevel_debug'] = '除錯';
$string['validationmsglevel_error'] = '錯誤';
$string['validationmsglevel_info'] = '好了';
$string['validationmsglevel_warning'] = '警告';
$string['validationmsg_maturity'] = '宣布的成熟水準';
$string['validationmsg_maturity_help'] = '外掛可以宣稱它的成熟水準，若維護者認為這外掛穩定，這宣稱的成熟水準將會是<b>成熟_穩定</b>。所有其他成熟水準(比如alpha 或 beta)應該視為不穩定且要小心。';
$string['validationmsg_missingcomponent'] = '外掛沒有宣稱它的組件名稱';
$string['validationmsg_missingcomponent_help'] = '所有的外掛必須經在由 version.php 檔案的`$plugin->component`聲明他們的完整組件名稱。';
$string['validationmsg_missingexpectedlangenfile'] = '英文語言的檔案名稱不符合';
$string['validationmsg_missingexpectedlangenfile_info'] = '這指定的外掛類型缺少應有的英語語言檔{$a}。';
$string['validationmsg_missinglangenfile'] = '找不到英文語言檔案';
$string['validationmsg_missinglangenfolder'] = '少了英文語言資料夾';
$string['validationmsg_missingversion'] = '外掛沒有聲明它的版本';
$string['validationmsg_missingversionphp'] = '沒有找到 version.php 檔案';
$string['validationmsg_multiplelangenfiles'] = '找到多個英文語言檔案';
$string['validationmsg_onedir'] = '這ZIP壓縮包裹的架構無效';
$string['validationmsg_onedir_help'] = '這ZIP壓縮包裹必須只包含一個根目錄，用來存放外掛的程式碼。而這根目錄的名稱必須符合這外掛套件的名稱。';
$string['validationmsg_pathwritable'] = '檢查安裝路徑是否可寫入';
$string['validationmsg_pluginversion'] = '外掛的版本';
$string['validationmsg_release'] = '外掛的發行次';
$string['validationmsg_requiresmoodle'] = 'Moodle版本要多少以上';
$string['validationmsg_rootdir'] = '要安裝的外掛名稱';
$string['validationmsg_rootdir_help'] = '這ZIP壓縮包裹裡的根目錄名稱就是要安裝的外掛名稱。若這名稱不正確，你可以在安裝這外掛之前';
$string['validationmsg_rootdirinvalid'] = '無效的外掛名稱';
$string['validationmsg_rootdirinvalid_help'] = '在ZIP包裹裡的根目錄名稱違反了正式的語法要求。某些ZIP包裹，比如那些由 Github所產生的，可能包含一個不正確的根目錄名稱。

你需要修正這根目錄名稱以符合這外掛的名稱。';
$string['validationmsg_targetexists'] = '目標位置已經存在且將被移除';
$string['validationmsg_targetexists_help'] = '這外掛所要安裝的目錄已經存在，且將會被這外掛的包裹內容所取代。';
$string['validationmsg_targetnotdir'] = '目標位置已經被一檔案佔據';
$string['validationmsg_unknowntype'] = '不明的外掛類型';
$string['validationmsg_versionphpsyntax'] = '在 version.php檔案上偵測到不支援的語法';
$string['version'] = '版本';
$string['versiondb'] = '目前版本';
$string['versiondisk'] = '新版本';
