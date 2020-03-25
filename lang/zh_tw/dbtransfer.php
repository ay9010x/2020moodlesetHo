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
 * Strings for component 'dbtransfer', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   dbtransfer
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['checkingsourcetables'] = '正在檢查來源資料表結構';
$string['copyingtable'] = '正在複製資料表{$a}';
$string['copyingtables'] = '正在複製表格內容';
$string['creatingtargettables'] = '正在目標資料庫建立資料表';
$string['dbexport'] = '資料庫匯出';
$string['dbtransfer'] = '資料庫轉移';
$string['differenttableexception'] = '表{$a}的結構不匹配。';
$string['done'] = '完成';
$string['exportschemaexception'] = '目前資料庫的結構不和所有的install.xml一致。<br/>{$a}';
$string['importschemaexception'] = '目前資料庫的結構不和所有的install.xml一致。<br/>{$a}';
$string['importversionmismatchexception'] = '目前版本{$a->currentver}與會出的版本{$a->schemaver}不一致。';
$string['malformedxmlexception'] = '遇到有錯誤的XML，無法繼續進行。';
$string['tablex'] = '資料表{$a}:';
$string['unknowntableexception'] = '在匯出檔案中發現未知的資料表{$a}。';
