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
 * Strings for component 'report_performance', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   report_performance
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['check_backup'] = '自動化備份';
$string['check_backup_comment_disable'] = '備份過程可能會影響網站效能。若啟用，備份應該安排在離峰時段。';
$string['check_backup_comment_enable'] = '備份過程可能會影響網站效能。若啟用，備份應該安排在離峰時段。';
$string['check_backup_details'] = '啟用自動化備份，將會自動在你指定的時間將在這伺服器上的所有課程建立壓縮檔。

在這過程中它將會消耗更多伺服器資源並可能影響效能。';
$string['check_cachejs_comment_disable'] = '若啟用，頁面裝載效能會改進。';
$string['check_cachejs_comment_enable'] = '若關閉。頁面可能速度變慢。';
$string['check_cachejs_details'] = 'Javascript的放入快取和壓縮，大大提高頁面裝載效能。我們強烈建議使用在正式運作的網站上。';
$string['check_debugmsg_comment_developer'] = '如果設定為程式開發者以外，性能可能會略有改進。';
$string['check_debugmsg_comment_nodeveloper'] = '如果設定為程式開發者，性能可能會略受影響。';
$string['check_debugmsg_details'] = '這是提供程式執行中的錯誤訊息，除非您正好是一位程式發展者，否則沒有什麼好處。

一旦你取得錯誤訊息，趕快複製並貼到別處。然後把"除錯"切換回去"無"的狀態。

除錯訊息可能洩漏有關你網站的設定的線索給駭客，並影響效能。';
$string['check_enablestats_comment_disable'] = '統計處理過程可能會影響效能。若啟用，統計設定應該非常小心。';
$string['check_enablestats_comment_enable'] = '統計處理過程可能會影響效能。統計設定應該非常小心。';
$string['check_enablestats_details'] = '若啟用，它將會定期處理日誌檔並蒐集一些統計量。

若你的網站流量大，它可能需要多花一點時間。

在這統計過程，它會消耗更多伺服器資源並可能影響效能。';
$string['check_themedesignermode_comment_disable'] = '若啟用，不會把圖像和樣式單放入快取中，而造成效能顯著地降低。';
$string['check_themedesignermode_comment_enable'] = '若關閉，會把圖像和樣式單放入快取中，而造成效能顯著地改進。';
$string['check_themedesignermode_details'] = '這通常造成Moodle網站運作緩慢。

若啟用主題布景設計者模式，一般而言，它要花至少兩倍CPU時間來運作Moodle。';
$string['comments'] = '評論';
$string['disabled'] = '關閉';
$string['edit'] = '編輯';
$string['enabled'] = '啟用';
$string['issue'] = '問題';
$string['morehelp'] = '更多說明';
$string['performancereportdesc'] = '這一報告會列出可能會影響這網站{$a}效能的問題。';
$string['performance:view'] = '檢視效能報告';
$string['pluginname'] = '效能綜覽';
$string['value'] = '數值';
