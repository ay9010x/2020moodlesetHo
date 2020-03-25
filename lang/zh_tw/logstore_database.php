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
 * Strings for component 'logstore_database', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   logstore_database
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['buffersize'] = '緩衝區大小';
$string['buffersize_help'] = '在一批次的資料庫操作中，可以插入幾個日誌條目，它能夠增進系統效能。';
$string['conectexception'] = '無法連結到資料庫';
$string['create'] = '建立';
$string['databasecollation'] = '資料庫蒐藏';
$string['databasepersist'] = '持續性的資料庫連結';
$string['databaseschema'] = '資料庫架構';
$string['databasesettings'] = '資料庫設定';
$string['databasesettings_help'] = '外部日誌資料庫 {$a} 的連結細節';
$string['databasetable'] = '資料庫資料表';
$string['databasetable_help'] = '用來存放日誌的資料表的名稱。這一表格的架構應該和logstore_standard 所用的相同。(mdl_logstore_standard_log).';
$string['filters'] = '過濾器日誌';
$string['filters_help'] = '啟用過濾器來排除某些被記錄的動作';
$string['includeactions'] = '包括這些類型的動作';
$string['includelevels'] = '包括伴隨這些教育層級的動作';
$string['logguests'] = '紀錄訪客的動作';
$string['other'] = '其他';
$string['participating'] = '參與';
$string['pluginname'] = '外部資料庫日誌';
$string['pluginname_desc'] = '一個日誌外掛，它將日誌條目儲存在一外部資料表上。';
$string['read'] = '讀取';
$string['tablenotfound'] = '沒找到指定的資料表';
$string['teaching'] = '教學';
$string['testingsettings'] = '測試資料庫設定';
$string['testsettings'] = '測試連結';
$string['update'] = '更新';
