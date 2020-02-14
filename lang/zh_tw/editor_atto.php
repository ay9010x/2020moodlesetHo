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
 * Strings for component 'editor_atto', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   editor_atto
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autosavefailed'] = '目前無法連接伺服器。如果你繳交此頁面，您會遺失您變更的東西。';
$string['autosavefrequency'] = '自動儲存頻率';
$string['autosavefrequency_desc'] = '此為嘗試自動儲存的秒數。atto會依此設定自動儲存編輯器的文字，所以相同用戶回到相同格式時將自動恢復原來的文字。';
$string['autosavesucceeded'] = '草稿已存';
$string['editor_command_keycode'] = 'Cmd + {$a}';
$string['editor_control_keycode'] = 'Ctrl + {$a}';
$string['errorcannotparseline'] = '{$a}列格式不正確。';
$string['errorgroupisusedtwice'] = '{$a}群組被定義了二次。群組名必須是唯一的才對。';
$string['errornopluginsorgroupsfound'] = '沒有外掛及群組。請予以新增。';
$string['errorpluginisusedtwice'] = '外掛{$a}用了二次。外掛只能定義一次才對。';
$string['errorpluginnotfound'] = '外掛{$a}不能用。似乎尚未安裝。';
$string['errortextrecovery'] = '可惜草稿版本未存。';
$string['infostatus'] = '訊息';
$string['pluginname'] = 'Atto HTML編輯器';
$string['plugin_title_shortcut'] = '{$a->title} [{$a->shortcut}]';
$string['recover'] = '復原';
$string['settings'] = 'Atto工具列設定';
$string['subplugintype_atto'] = 'atto外掛';
$string['subplugintype_atto_plural'] = 'atto外掛';
$string['taskautosavecleanup'] = '從資料庫中刪除自動儲存的草稿';
$string['textrecovered'] = '此項文字的草稿已自動儲存。';
$string['toolbarconfig'] = '工具列設定';
$string['toolbarconfig_desc'] = '外掛的清單和它們的排列順序，可以在此配置。

配置的內容包含群組名稱(一個群組一行)，隨後是這一群組所有外掛的排列清單。群組名稱和外掛之間是以等號(=)分隔開來，各外掛之間是以逗號(,)分隔開來。

群組名稱必須是獨一無二的，且必須能夠說明裡面包含哪些按鈕。

按鈕和群組名稱不可以重複，且只可以包含文字或數字字元。';
$string['warningstatus'] = '警告';
