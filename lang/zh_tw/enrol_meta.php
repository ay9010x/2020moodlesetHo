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
 * Strings for component 'enrol_meta', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_meta
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addgroup'] = '加入群組。';
$string['coursesort'] = '課程清單排序';
$string['coursesort_help'] = '這可用來決定那些可以連結的課程清單的排列順序是依照指定順序(例如：網站管理>課程>管理課程與類別>排列順序)或是自動依照字母順序來排列。';
$string['creategroup'] = '建立新群組';
$string['defaultgroupnametext'] = '{$a->name}課程 {$a->increment}';
$string['linkedcourse'] = '連結課程';
$string['meta:config'] = '設定課程中繼資料實例';
$string['meta:selectaslinked'] = '選擇課程作為後設鏈結的';
$string['meta:unenrol'] = '將停權的用戶退選';
$string['nosyncroleids'] = '不同步的角色';
$string['nosyncroleids_desc'] = '預設情況，所有課程層次的角色指派都會從父課程同步到子課程中。在這裡選出的角色不會被包含在同步過程中。在下一次執行cron排程時，會更新可以同步的角色。';
$string['pluginname'] = '課程後設鏈結選課';
$string['pluginname_desc'] = '課程後設鏈結選課外掛可在兩個不同課程間同步選課和角色。';
$string['syncall'] = '同步所有已經選課的用戶';
$string['syncall_desc'] = '若啟用，所有已經選課的用戶都會被同步，無論它們是否參與父課程。若停用，則至少有一個已經同步角色的用戶才會被選到子課程。';
