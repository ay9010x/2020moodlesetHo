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
 * Strings for component 'booktool_wordimport', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   booktool_wordimport
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cannotopentempfile'] = '不能開啟臨時檔案<b>{$a}</b>';
$string['exportbook'] = '匯出書本至Microsoft Word';
$string['exportchapter'] = '匯出此章節至Microsoft Word';
$string['importchapters'] = '從Microsoft Word中匯入';
$string['insertionpoint'] = '在現時章節之前插入';
$string['insertionpoint_help'] = '在現時章節之前插入內容，保存所有現有內容';
$string['nochapters'] = '沒有找到書本章節，所以未能匯出至Microsoft Word';
$string['pluginname'] = '匯入Microsoft Word 檔案';
$string['replacebook'] = '取代電子書';
$string['replacebook_help'] = '匯入前刪除電子書的現有內容';
$string['replacechapter'] = '取代現有章節';
$string['replacechapter_help'] = '以檔案中的第一章節取代本章內容，但保存其作所有章節';
$string['splitonsubheadings'] = '創建建基於子標題的子章節';
$string['splitonsubheadings_help'] = '子標題將會以 "標題2" 的格式創建';
$string['stylesheetunavailable'] = '未能提供XSLT 樣式表 <b>{$a}</b>';
$string['transformationfailed'] = 'XSLT 變更失敗 (<b>{$a}</b>)';
$string['wordfile'] = 'Microsoft Word 檔案';
$string['wordfile_help'] = '上載從 Microsoft Word or LibreOffice中儲存的 <i>.docx</i> 檔案';
$string['wordimport:export'] = '匯出Microsoft Word檔案';
$string['wordimport:import'] = '匯入Microsoft Word檔案';
$string['xsltunavailable'] = '您需要以PHP安裝的XSLT圖書館來儲存此檔案';
