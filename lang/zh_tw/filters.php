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
 * Strings for component 'filters', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   filters
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actfilterhdr'] = '啟用的過濾器';
$string['addfilter'] = '新增過濾器';
$string['anycategory'] = '任何類別';
$string['anycourse'] = '任何課程';
$string['anyfield'] = '任何欄位';
$string['anyrole'] = '任何角色';
$string['anyvalue'] = '不限';
$string['applyto'] = '應用到';
$string['categoryrole'] = '類別角色';
$string['contains'] = '包含';
$string['content'] = '內容';
$string['contentandheadings'] = '內容和標題';
$string['coursecategory'] = '課程類目';
$string['courserole'] = '課程角色';
$string['courserolelabel'] = '課程類別：{$a->categoryname} 課程名稱：{$a->coursename} 角色名：{$a->rolename} 名字：{$a->label}';
$string['courserolelabelerror'] = '{$a->label} 錯誤，課程：{$a->coursename} 不存在';
$string['coursevalue'] = '課程數值';
$string['datelabelisafter'] = '{$a->label} 晚於 {$a->after}';
$string['datelabelisbefore'] = '{$a->label} 早於 {$a->before}';
$string['datelabelisbetween'] = '{$a->label} 在 {$a->after} 與 {$a->before} 之間';
$string['defaultx'] = '預設{$a}';
$string['disabled'] = '停用';
$string['doesnotcontain'] = '不包含';
$string['endswith'] = '結尾是';
$string['filterallwarning'] = '把過濾器同時用在標題和內容上，會大量增加你的伺服器的負荷。
請謹慎地使用這"應用到"設定，這主要是用於"多語言過濾器"上面。';
$string['filtersettings'] = '過濾器設定';
$string['filtersettingsforin'] = '$a->filter}在{$a->context}的設定';
$string['filtersettings_help'] = '這一頁面讓你在這網站的特定部分，將過濾器設開或關。

某些過濾器也讓你作本地設定，這種情況時，過濾器名稱旁邊會有一設定鏈結。';
$string['filtersettingsin'] = '在{$a}的過濾器設定';
$string['firstaccess'] = '第一次登入';
$string['globalrolelabel'] = '{$a->label} 是 {$a->value}';
$string['isactive'] = '啟用？';
$string['isafter'] = '晚於';
$string['isanyvalue'] = '任意值';
$string['isbefore'] = '早於';
$string['isdefined'] = '已定義';
$string['isempty'] = '空的';
$string['isequalto'] = '等於';
$string['isnotdefined'] = '未定義';
$string['isnotequalto'] = '不等於';
$string['limiterfor'] = '{$a}欄位分隔符號';
$string['neveraccessed'] = '從未被存取過';
$string['nevermodified'] = '從未被修改過';
$string['newfilter'] = '篩選';
$string['nofiltersenabled'] = '在這網站沒有過濾器被啟用。';
$string['off'] = '關閉';
$string['offbutavailable'] = '關閉，但是可用';
$string['on'] = '開啟';
$string['profilefilterfield'] = '個人資料表欄位名稱';
$string['profilefilterlimiter'] = '個人資料表欄位分隔符號';
$string['profilelabel'] = '<b>{$a->label}</b>: <b>{$a->profile}</b> <b>{$a->operator}</b> <b>{$a->value}</b>';
$string['profilelabelnovalue'] = '<b>{$a->label}</b>: <b>{$a->profile}</b> <b>{$a->operator}</b>';
$string['removeall'] = '移除所有的篩選條件';
$string['removeselected'] = '移除勾選的條件';
$string['selectlabel'] = '{$a->label} {$a->operator} {$a->value}';
$string['startswith'] = '開頭是';
$string['tablenosave'] = '上表中的更改已自動儲存';
$string['textlabel'] = '{$a->label} {$a->operator} {$a->value}';
$string['textlabelnovalue'] = '{$a->label} {$a->operator}';
$string['valuefor'] = '{$a} 的值';
