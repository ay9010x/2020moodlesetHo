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
 * Strings for component 'local_purgequestioncategory', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   local_purgequestioncategory
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['confirmmessage'] = '您將會清除題目類別。如果繼續的話，此類別、子類別及未用題目將會永久刪除。此動作將不能變更。';
$string['confirmpurge'] = '確認清除題目類別';
$string['iconfirm'] = '我確定，我了解我的行動';
$string['infowithmove'] = '此類別\'{$a->name}\'包括了不能刪除的{$a->subcategories} 子類別、{$a->unusedquestions} 未用題目及題目{$a->usedquestions} （有一些題目尚出現在測驗中）。請選擇其他類別以移動不能刪除的題目。';
$string['infowithoutmove'] = '此類別\'{$a->name}\'包括了{$a->subcategories} 子類別和{$a->unusedquestions} 未用題目';
$string['pluginname'] = '清除題目類別';
$string['purgecategories'] = '清除類別';
$string['purgequestioncategory:purgecategory'] = '刪除題目類別中的所有問題及子類別';
$string['purgethiscategory'] = '清除此類別';
$string['validationcategory'] = '請選擇有效的類別';
$string['validationconfirm'] = '您需要確認此行動';
