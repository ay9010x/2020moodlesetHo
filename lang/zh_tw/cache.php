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
 * Strings for component 'cache', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   cache
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actions'] = '動作';
$string['addinstance'] = '添加實例';
$string['addlocksuccess'] = '已成功添加一個新的鎖定實例';
$string['addnewlockinstance'] = '添加一個新的鎖定實例';
$string['addstore'] = '添加{$a}儲存';
$string['addstoresuccess'] = '成功添加一個新的{$a}儲存';
$string['area'] = '區域';
$string['cacheadmin'] = '快取管理';
$string['cacheconfig'] = '配置';
$string['cachedef_calendar_subscriptions'] = '行事曆訂閱';
$string['cachedef_capabilities'] = '系統所有功能的清單';
$string['cachedef_completion'] = '活動完成狀態';
$string['cachedef_config'] = '配置設置';
$string['cachedef_coursecat'] = '給特定用戶的課程類目清單';
$string['cachedef_coursecatrecords'] = '課程類目紀錄';
$string['cachedef_coursecattree'] = '課程類目樹';
$string['cachedef_coursecontacts'] = '課程聯絡人清單';
$string['cachedef_coursemodinfo'] = '有關每一課程的模組和學習單元的累積訊息';
$string['cachedef_databasemeta'] = '資料庫後設資料';
$string['cachedef_eventinvalidation'] = '事件無效';
$string['cachedef_externalbadges'] = '給特定用戶的外部獎章';
$string['cachedef_grade_categories'] = '分數類別';
$string['cachedef_groupdata'] = '課程團體訊息';
$string['cachedef_htmlpurifier'] = 'HTML淨化器--清除過的內容';
$string['cachedef_langmenu'] = '可用語言的清單';
$string['cachedef_locking'] = '鎖定';
$string['cachedef_navigation_expandcourse'] = '導覽可以展開的課程清單';
$string['cachedef_observers'] = '事件觀察者';
$string['cachedef_plugin_functions'] = '外掛可用的呼叫';
$string['cachedef_plugin_manager'] = '外掛套件訊息管理者';
$string['cachedef_questiondata'] = '試題定義';
$string['cachedef_repositories'] = '倉儲實例資料';
$string['cachedef_string'] = '語言字串快取';
$string['cachedef_suspended_userids'] = '列出每一課程的已休學用戶';
$string['cachedef_tagindexbuilder'] = '以加標籤的項目的搜尋結果';
$string['cachedef_tags'] = '標籤收藏和區域';
$string['cachedef_temp_tables'] = '暫時資料表快取';
$string['cachedef_userselections'] = '在整個Moodle中持續做用戶選擇所用的資料';
$string['cachedef_yuimodules'] = 'YUI模組定義';
$string['cachelock_file_default'] = '預設檔案鎖定';
$string['cachestores'] = '快取儲存';
$string['caching'] = '快取';
$string['component'] = '元件';
$string['confirmlockdeletion'] = '確認鎖定刪除';
$string['confirmstoredeletion'] = '確認儲存刪除';
$string['default_application'] = '預設為應用程式要儲存';
$string['defaultmappings'] = '當沒有對映出現時所用的儲存';
$string['defaultmappings_help'] = '若你沒有對映一或多個儲存到快取定義時，將會被使用的預設儲存。';
$string['default_request'] = '預設為請求要儲存';
$string['default_session'] = '預設為對話要儲存';
$string['defaultstoreactions'] = '預設為儲存不能被修改';
$string['definition'] = '定義';
$string['definitionsummaries'] = '已知的快取的定義';
$string['delete'] = '刪除';
$string['deletelock'] = '刪除鎖定';
$string['deletelockconfirmation'] = '你確定你要刪除這{$a}鎖定？';
$string['deletelockhasuses'] = '你無法刪除這一鎖定實例，因為他已經被用在一或多個儲存。';
$string['deletelocksuccess'] = '已成功刪除鎖定';
$string['deletestore'] = '刪除儲存';
$string['deletestoreconfirmation'] = '你確定你要刪除這"{$a}"儲存？';
$string['deletestorehasmappings'] = '你不能刪除這一儲存，因為它有對映。請在刪除這儲存之前刪除所有對映。';
$string['deletestoresuccess'] = '已成功地刪除這快取儲存';
$string['editdefinitionmappings'] = '{$a}定義儲存對映';
$string['editdefinitionsharing'] = '編輯為{$a}分享的定義';
$string['editmappings'] = '編輯對映';
$string['editsharing'] = '編輯共享';
$string['editstore'] = '編輯儲存';
$string['editstoresuccess'] = '已成功編輯這快取儲存';
$string['ex_configcannotsave'] = '無法儲存設快取設定到檔案中';
$string['ex_nodefaultlock'] = '無法找到一個預設的鎖定實例';
$string['ex_unabletolock'] = '無法獲得鎖定來進行快取';
$string['ex_unmetstorerequirements'] = '在目前你無法使用這一儲存。請參考這文件以決定它的需求。';
$string['gethit'] = '取用 - 有找到';
$string['getmiss'] = '取用 - 沒找到';
$string['inadequatestoreformapping'] = '這儲存不符合所有已知定義的需求。這一儲存的定義是不適當的，將不使用選出的儲存，而改用預設的儲存。';
$string['invalidlock'] = '無效的鎖定';
$string['invalidplugin'] = '無效的外掛套件';
$string['invalidstore'] = '提供的快取儲存無效';
$string['localstorenotification'] = '這一快取可以安全地對應到位於每一個網頁伺服器的儲存位置。';
$string['lockdefault'] = '預設';
$string['locking'] = '鎖定';
$string['locking_help'] = '鎖定是限制一個進程一次只能存取一個快取資料，以防止這資料被複寫過去。鎖定的方法決定如何上鎖及如何被檢查。';
$string['lockingmeans'] = '鎖定機制';
$string['lockmethod'] = '鎖定方法';
$string['lockmethod_help'] = '這適當要求這一儲存時，所用的鎖定方法';
$string['lockname'] = '名稱';
$string['locknamedesc'] = '名稱必須是獨一的，且只能包含a-z,A-Z。';
$string['locknamenotunique'] = '你選擇的名稱不是獨一的，請選擇一個獨一無二的名稱。';
$string['locksummary'] = '快取鎖定的摘要';
$string['locktype'] = '類型';
$string['lockuses'] = '使用';
$string['mappingdefault'] = '(預設)';
$string['mappingfinal'] = '最後儲存';
$string['mappingprimary'] = '基礎儲存';
$string['mappings'] = '儲存對映';
$string['mode'] = '模式';
$string['mode_1'] = '應用';
$string['mode_2'] = '對話';
$string['mode_4'] = '請求';
$string['modes'] = '模式';
$string['nativelocking'] = '這一外掛會處理它自己的鎖定';
$string['none'] = '無';
$string['plugin'] = '外掛';
$string['pluginsummaries'] = '安裝快取儲存';
$string['purge'] = '清除';
$string['purgedefinitionsuccess'] = '已經成功清除這請求的定義';
$string['purgestoresuccess'] = '已經成功清除這請求的儲存';
$string['requestcount'] = '以{$a}個請求測試';
$string['rescandefinitions'] = '重新掃瞄定義';
$string['result'] = '結果';
$string['set'] = '設定';
$string['sharedstorenotification'] = '這一快取可以安全地對應到位於每一個網頁伺服器的儲存位置。';
$string['sharing'] = '共享';
$string['sharing_all'] = '每一個';
$string['sharing_help'] = '若你有一個群集的設定，這將允許你決定快取資料要如何分享；若你有多個網站所有設定都使用相同的儲存，並希望分享這些資料。

這是進階的設定，請在更改它之前，你確定了解它的目的，';
$string['sharing_input'] = '自訂密鑰(在以下輸入)';
$string['sharingrequired'] = '你必須至紹選擇一個共享的選項';
$string['sharingselected_all'] = '每一個';
$string['sharingselected_input'] = '自訂金鑰';
$string['sharingselected_siteid'] = '網站辨識器';
$string['sharingselected_version'] = '版本';
$string['sharing_siteid'] = '有相同網站編號的各網站';
$string['sharing_version'] = '執行相同版本的各網站';
$string['storeconfiguration'] = '儲存配置';
$string['store_default_application'] = '預設供給應用程式快取用的檔案儲存';
$string['store_default_request'] = '預設供給請求快取用的靜態儲存';
$string['store_default_session'] = '預設供給session快取用的session儲存';
$string['storename'] = '儲存名稱';
$string['storenamealreadyused'] = '您必須為這一個儲存選擇一個唯一的名稱';
$string['storename_help'] = '這可設定儲存的名稱。它可在這系統中辨識儲存，且只能包含a-z,A-Z,0-9-_和空白。它必須是獨一無二的。若你的名字已經被使用，你將會收到錯誤警告。';
$string['storenameinvalid'] = '無效的儲存名稱。你只可以使用a-z，A-Z, 0-9_和空白。';
$string['storenotready'] = '儲存還沒準備好';
$string['storeperformance'] = '快取儲存表現報告 - 每一操作有{$a}個獨一的請求。';
$string['storeready'] = '準備好了';
$string['storerequiresattention'] = '需要注意。';
$string['storerequiresattention_help'] = '這一儲存還沒辦法使用，但是已經有對映。修復這個問題將改善你的系統的效能。請檢查這儲存的背端已經可以使用，且任何PHP的需求都以符合。';
$string['storeresults_application'] = '當作為應用快取時，儲存請求。';
$string['storeresults_request'] = '當作為請求快取時，儲存請求。';
$string['storeresults_session'] = '當作為對話快取時，儲存請求。';
$string['stores'] = '儲存';
$string['storesummaries'] = '配置儲存實例';
$string['supports'] = '支援';
$string['supports_dataguarantee'] = '資料保證';
$string['supports_keyawareness'] = '密鑰覺察';
$string['supports_multipleidentifiers'] = '多重標誌';
$string['supports_nativelocking'] = '鎖定';
$string['supports_nativettl'] = '存活時間';
$string['supports_searchable'] = '以關鍵字搜尋';
$string['tested'] = '已測試過';
$string['testperformance'] = '效能測試';
$string['unsupportedmode'] = '未支援的模式';
$string['untestable'] = '無法測試的';
$string['userinputsharingkey'] = '自訂密鑰以進行分享';
$string['userinputsharingkey_help'] = '在此輸入你自己的私人密鑰。當你在其他網站設定其他儲存進行分享資料時，要確定你在此設定完全相同的密鑰。';
