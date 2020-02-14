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
 * Strings for component 'tool_assignmentupgrade', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_assignmentupgrade
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['areyousure'] = '你確定嗎？';
$string['areyousuremessage'] = '你確定你要將這作業 "{$a->name}"升級？';
$string['assignmentid'] = '作業識別編號';
$string['assignmentnotfound'] = '找不到此作業 (id={$a})';
$string['assignmentsperpage'] = '每一頁的作業數';
$string['assignmenttype'] = '作業類型';
$string['backtoindex'] = '回到索引';
$string['batchoperations'] = '批次操作';
$string['batchupgrade'] = '升級多個作業';
$string['confirmbatchupgrade'] = '確認以批次進行作業的升級';
$string['conversioncomplete'] = '作業已經轉換';
$string['conversionfailed'] = '作業轉換沒成功。升級過程日誌是：<br />{$a}';
$string['listnotupgraded'] = '列出還沒被升級的作業';
$string['listnotupgraded_desc'] = '你可以從這裡個別地將作業升級';
$string['noassignmentsselected'] = '你可以從這裡個別地將作業升級';
$string['noassignmentstoupgrade'] = '這裡沒有作業需要升級';
$string['notsupported'] = '不支援';
$string['notupgradedintro'] = '這一頁會列出舊版Moodle所建立的作業，它們都還沒有升級到Moodle2.3的新作業模組。

並不是所有的作業都可升級---如果它們是由自訂的作業類型所建立，那它們就需要先被升級到新作業外掛格式，才能夠完成這升級。';
$string['notupgradedtitle'] = '沒升級的作業';
$string['pluginname'] = '作業模組升級助手';
$string['select'] = '選擇';
$string['submissions'] = '提交';
$string['supported'] = '升級';
$string['unknown'] = '未知';
$string['updatetable'] = '更新資料表';
$string['upgradable'] = '可升級的';
$string['upgradeall'] = '將所有作業升級';
$string['upgradeallconfirm'] = '要將所有作業升級？';
$string['upgradeassignmentfailed'] = '結果：升級失敗。升級過程日誌是：<br/><div class="tool_assignmentupgrade_upgradelog">{$a->log}</div>';
$string['upgradeassignmentsuccess'] = '結果：升級成功';
$string['upgradeassignmentsummary'] = '將作業升級 ：{$a->name} (課程：{$a->shortname})';
$string['upgradeprogress'] = '將作業升級：完成 {$a->current}個 /  總共 {$a->total}個。';
$string['upgradeselected'] = '將選出的作業升級';
$string['upgradeselectedcount'] = '升級{$a}個選出的作業？';
$string['upgradesingle'] = '升級單一作業';
$string['viewcourse'] = '檢視這課程及轉換過的作業';
