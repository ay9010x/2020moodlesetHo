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
 * Strings for component 'tool_generator', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_generator
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bigfile'] = '大檔案{$a}';
$string['courseexplanation'] = '這一工具可用來建立標準測試課程，它包含許多學習單元、活動和檔案。

它的目的是提供標準化的測量，來檢查各種系統元件(比如備份和還原)的穩定性和效能。

使用這功能建立的課程會佔據龐大的資料庫和檔案空間(十幾GB)，你需要刪除這課程(並等待清理程式的執行)才能再度釋放出空間。

**不要在實際運作的系統上使用這功能**。只能使用在發展者的伺服器上。(為了避免誤用，這功能是關閉的，除非你選擇了發展者除錯層次))';
$string['coursesize_0'] = 'XS(約10KB;  約1秒建立)';
$string['coursesize_1'] = 'S(約10MB;  約30秒建立)';
$string['coursesize_2'] = 'M(約100MB;  約5分建立)';
$string['coursesize_3'] = 'L(約1GB;  約1小時建立)';
$string['coursesize_4'] = 'XL(約10GB;  約4小時建立)';
$string['coursesize_5'] = 'XXL(約20GB;  約8小時建立)';
$string['coursewithoutusers'] = '所選出的課程沒有用戶';
$string['createcourse'] = '建立課程';
$string['createtestplan'] = '建立測試計畫';
$string['creating'] = '建立課程';
$string['done'] = '完成({$a})';
$string['downloadtestplan'] = '下載測試計畫';
$string['downloadusersfile'] = '下載用戶檔案';
$string['error_nocourses'] = '這兒沒有課程可以產生測試計畫';
$string['error_noforumdiscussions'] = '所選出的課程不包含討論區的議題';
$string['error_noforuminstances'] = '所選出的課程不包含討論區模組實例';
$string['error_noforumreplies'] = '所選出的課程不包含討論區的回應';
$string['error_nonexistingcourse'] = '這指定的課程不存在';
$string['error_nopageinstances'] = '所選出的課程不包含頁面模組實例';
$string['error_notdebugging'] = '在此伺服器上無法使用，因為除錯模式不是設為\'程式發展者\'。';
$string['error_nouserspassword'] = '你必須在config.php 設定 $CFG->tool_generator_users_password 才能產生測試計畫';
$string['fullname'] = '測試課程:{$a->size}';
$string['maketestcourse'] = '製作測試用課程';
$string['maketestplan'] = '製作JMeter測試計畫';
$string['notenoughusers'] = '這選出的課程沒有足夠的用戶';
$string['pluginname'] = '發展資料產生器';
$string['progress_checkaccounts'] = '檢查用戶帳號({$a})';
$string['progress_coursecompleted'] = '課程已完成({$a})個';
$string['progress_createaccounts'] = '建立用戶帳號 ({$a->from} - {$a->to})';
$string['progress_createassignments'] = '建立作業中({$a})';
$string['progress_createbigfiles'] = '建立大檔案({$a})';
$string['progress_createcourse'] = '建立課程{$a}';
$string['progress_createforum'] = '建立討論區({$a}貼文)';
$string['progress_createpages'] = '建立頁面 ({$a})';
$string['progress_createsmallfiles'] = '建立小檔案({$a})';
$string['progress_enrol'] = '報名用戶到課程（{$a}）';
$string['progress_sitecompleted'] = '網站已完成({$a})';
$string['shortsize_0'] = 'XS特小';
$string['shortsize_1'] = 'S小';
$string['shortsize_2'] = 'M中';
$string['shortsize_3'] = 'L大';
$string['shortsize_4'] = 'XL特大';
$string['shortsize_5'] = 'XXL超大';
$string['sitesize_0'] = 'XS (~10MB; 3 個課程，~30 秒可建立完成)';
$string['sitesize_1'] = 'S (~50MB; 8個課程，~2分鐘可建立完成)';
$string['sitesize_2'] = 'M (~200MB; 73個課程，~10分鐘可建立完成)';
$string['sitesize_3'] = 'L (~1\'5GB; 277個課程，~1\'5 小時可建立完成)';
$string['sitesize_4'] = 'XL (~10GB; 1065個課程，~5小時可建立完成)';
$string['sitesize_5'] = 'XXL (~20GB; 4177個課程，~10小時可建立完成)';
$string['size'] = '課程大小';
$string['smallfiles'] = '小檔案';
$string['targetcourse'] = '測試目標課程';
$string['testplanexplanation'] = '這一工具可以建立一個 JMeter 測試計畫，並附有用戶身分檔案。

這一測試計畫是設計來與{$a}共同使用，它使在一特定的Moodle環境中更容易執行測試計畫、蒐集執行的訊息，和比較這些結果。因此你將需要下載它，並使用它的 test_runner.sh 腳本，或者遵循它的安裝和使用說明。

你必須在 config.php 為這課程用戶設定一密碼(例如， $CFG->tool_generator_users_password = \'moodle\';)。 這裡不設定密碼的預設值以防止無意中使用了這工具。你必須使用這一更新的密碼選項，tool_generator 產生但沒有設一個 $CFG->tool_generator_users_password的值。

它是 tool_generator 的一部份，所以它能和這課程和這網站產生器產生的課程協同運作良好。它也可以被用在至少包含下列條件的任何課程：

* 足夠的選課用戶(視你選擇的測試計畫大小而定)，且其密碼都被重設為"moodle"。
* 一個頁面模組實例。
* 一個討論區模組實例，且至少含有一議題和一個回應。

由於JMeter所產生的測試計畫可以非常的大，所以你在執行大型測試計畫時，可能要考慮你的伺服器的性能。這裝載階段已經依照執行緒(用戶)人數調整過，以減少這類的問題，但是負荷仍是很大。

**不要在一個正式使用的系統上執行測試計畫**
這一功能只是建立一個檔案來匯入JMeter，所以它本身沒有危險性，但是你絕對不要在一正式運作的網站上執行這一測試計畫。';
$string['testplansize_0'] = 'XS ({$a->users} 個用戶，{$a->loops}個迴圈及{$a->rampup}累加時間)';
$string['testplansize_1'] = 'S ({$a->users} 個用戶，{$a->loops}個迴圈及{$a->rampup}累加時間)';
$string['testplansize_2'] = 'M({$a->users} 個用戶，{$a->loops}個迴圈及{$a->rampup}累加時間)';
$string['testplansize_3'] = 'L ({$a->users} 個用戶，{$a->loops}個迴圈及{$a->rampup}累加時間)';
$string['testplansize_4'] = 'XL ({$a->users} 個用戶，{$a->loops}個迴圈及{$a->rampup}累加時間)';
$string['testplansize_5'] = 'XXL ({$a->users} 個用戶，{$a->loops}個迴圈及{$a->rampup}累加時間)';
$string['updateuserspassword'] = '更新課程用戶密碼';
$string['updateuserspassword_help'] = 'JMeter 需要以課程用戶身分登入，你可以在 config.php 使用 $CFG->tool_generator_users_password 設定這用戶的密碼；這一設定會依照 $CFG->tool_generator_users_password更新這課程用戶的密碼。 這點在當你使用的課程不是由 tool_generator 所產生的，或是當你創建測試課程時，忘了設定 $CFG->tool_generator_users_password ，就很有用處。';
