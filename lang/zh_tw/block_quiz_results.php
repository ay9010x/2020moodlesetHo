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
 * Strings for component 'block_quiz_results', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   block_quiz_results
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bestgrade'] = '最高分：';
$string['bestgrades'] = '{$a}個最高分：';
$string['bestgroupgrade'] = '平均分數最高的群組：';
$string['bestgroupgrades'] = '{$a}平均分數最高的群組：';
$string['config_format_absolute'] = '絕對分數';
$string['config_format_fraction'] = '得分/滿分';
$string['config_format_percentage'] = '百分比';
$string['config_grade_format'] = '將成績顯示為：';
$string['config_name_format'] = '在顯示結果時的隱私層級：';
$string['config_names_anon'] = '以匿名顯示';
$string['config_names_full'] = '顯示完整姓名';
$string['config_names_id'] = '只顯示辨識號碼';
$string['config_no_quizzes_in_course'] = '這個課程並未包含任何測驗活動。您需要先增加一個測驗活動後，才可以正常地使用這個區塊。';
$string['config_select_quiz'] = '這個區塊要顯示哪一次測驗的成績?';
$string['config_show_best'] = '要顯示多少個最高分(0是取消)?';
$string['config_show_worst'] = '要顯示多少個最低分(0是取消)?';
$string['configuredtoshownothing'] = '這個區塊的設定目前無法顯示任何成績。您可以對它進行設定或將其隱藏。';
$string['config_use_groups'] = '顯示群組成績而非個人成績(只有當測驗支援群組功能時)?';
$string['error_emptyquizid'] = '請配置這一區塊，並選擇要顯示哪個測驗的成績。';
$string['error_emptyquizrecord'] = '目前此區塊設定有問題：選擇的測驗似乎並不存在於資料庫中。';
$string['error_nogroupsexist'] = '目前此區塊設定有問題：它被設定為顯示群組的成績，但是這個課程並未定義群組。';
$string['pluginname'] = '測驗結果';
$string['quiz_results:addinstance'] = '新增一個測驗結果區塊';
$string['worstgrade'] = '最低分：';
$string['worstgrades'] = '{$a}個最低分：';
$string['worstgroupgrade'] = '平均分數最低的群組：';
$string['worstgroupgrades'] = '{$a}個平均分數最低的群組：';
