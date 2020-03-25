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
 * Strings for component 'antivirus_clamav', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   antivirus_clamav
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clamfailed'] = 'Clam AV 執行時發生錯誤。傳回的錯誤訊息是{$a}。下面是Clam輸出的資訊：';
$string['clamfailureonupload'] = 'ClamAV 上傳失敗';
$string['configclamactlikevirus'] = '把檔案視為病毒';
$string['configclamdonothing'] = '把檔案視為沒問題';
$string['configclamfailureonupload'] = '若你有配置掃毒軟體clam來掃描上傳的檔案，但若配置錯誤，或因為不明理由而無法執行，那它會怎樣？若你選擇：

*"把檔案視為病毒"--檔案將被移到隔離區或被刪除。
*"把檔案視為沒問題"--檔案將會正常地一到目的地資料夾中。
無論是何種方式，管理員都會被警告說掃毒程式clam已經失敗。

如果你選了"把檔案視為病毒"，且因些理由掃毒程式無法執行(通常是因為你輸入的到clam的路徑錯誤)，所有的上傳的檔案都會被移到指定的隔離區或被刪除。設定時請小心。';
$string['configpathtoclam'] = '執行ClamAV的路徑。可能像是 /usr/bin/clamscan 或是 /usr/bin/clamdscan。你需要填入，才能讓防毒軟體ClamAV執行。';
$string['configquarantinedir'] = '若你要ClamAV把受病毒感染的檔案移到一個隔離資料夾，把該資料夾位址寫在這裡。它必須對網頁伺服器而言是可以寫入的。若你留空白，或你輸入一個不存在的或無法寫入的資料夾，被感染的檔案將會被刪除。不要包含尾端斜線。';
$string['invalidpathtoclam'] = 'Moodle已設定執行Clam檢查上傳的檔案，但是提供給Clam AV的路徑{$a}，是無效的。';
$string['pathtoclam'] = 'ClamAV 路徑';
$string['pluginname'] = 'ClamAV 防毒軟體';
$string['quarantinedir'] = '隔離用目錄';
$string['unknownerror'] = 'Clam 發生了不明的錯誤。';
