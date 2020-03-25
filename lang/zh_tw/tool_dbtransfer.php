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
 * Strings for component 'tool_dbtransfer', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_dbtransfer
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clidriverlist'] = '可以用來轉移的資料庫磁碟機';
$string['cliheading'] = '資料庫轉移----請確定在轉移過程中，沒有人會存取這一伺服器。';
$string['climigrationnotice'] = '資料庫轉移進行中，請等待直到轉移完成，且伺服器管理員更新配置和刪除$CFG->dataroot/climaintenance.html 檔案。';
$string['convertinglogdisplay'] = '要轉換日誌顯示所有的動作';
$string['dbexport'] = '資料庫匯出';
$string['dbtransfer'] = '資料庫轉移';
$string['enablemaintenance'] = '啟動系統維護模式';
$string['enablemaintenance_help'] = '這一選項能讓你在資料庫轉移過程和之後啟用"系統維護模式"。它能防止所有用戶存取直到轉移完成。

請注意，伺服器管理員在更新config.php的設定之後，必須手動刪除$CFG->dataroot/climaintenance.html 檔案，才能恢復正常運作。';
$string['exportdata'] = '匯出資料';
$string['notargetconectexception'] = '抱歉，無法連上目標資料庫';
$string['options'] = '選項';
$string['pluginname'] = '資料庫轉移';
$string['targetdatabase'] = '目標資料庫';
$string['targetdatabasenotempty'] = '目標資料庫絕對不可以包含任何含有所給的接首字的資料表';
$string['transferdata'] = '轉移資料';
$string['transferdbintro'] = '這一腳本將會轉移這資料庫的整個內容到另一資料庫伺服器上';
$string['transferdbtoserver'] = '轉移這一Moodle資料庫到另一伺服器';
$string['transferringdbto'] = '轉移這資料庫到 {$a->dbhost}主機上的 {$a->dbtype} 資料庫 {$a->dbname}';
