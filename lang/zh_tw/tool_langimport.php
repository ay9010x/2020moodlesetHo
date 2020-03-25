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
 * Strings for component 'tool_langimport', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_langimport
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['install'] = '安裝被選出的語言包';
$string['installedlangs'] = '已安裝的語言包';
$string['langimport'] = '語言匯入工具';
$string['langimportdisabled'] = '語言匯入功能已被禁用。您必須在檔案系統層級，手動更新您的語言包。您這樣做之後，不要忘記清除字串快取。';
$string['langpackinstalled'] = '語言包\'{$a}\'已經安裝成功';
$string['langpackinstalledevent'] = '已經安裝的語言包';
$string['langpacknotremoved'] = '發生一個錯誤，語言包\'{$a}\'沒有完全解除安裝。請檢查檔案權限。';
$string['langpackremoved'] = '語言包{$a}已被卸除';
$string['langpackremovedevent'] = '語言包已卸除';
$string['langpackupdated'] = '語言包\'{$a}\'已經順利更新';
$string['langpackupdatedevent'] = '語言包已經更新';
$string['langpackupdateskipped'] = '跳過更新\'{$a}\'語言包';
$string['langpackuptodate'] = '語言包\'{$a}\'已經是最新的';
$string['langupdatecomplete'] = '語言包更新完成';
$string['missingcfglangotherroot'] = '未設定環境參數 $CFG->langotherroot';
$string['missinglangparent'] = '缺少<em>{$a->lang}</em>的父語言<em>{$a->parent}</em>。';
$string['noenglishuninstall'] = '英文語言包不能被解除安裝';
$string['nolangupdateneeded'] = '您的所有語言包都已經是最新的了，不需要再更新。';
$string['pluginname'] = '語言包';
$string['purgestringcaches'] = '清除字串快取';
$string['remotelangnotavailable'] = '由於 Moodle 無法連接到 download.moodle.org，因此無法自動安裝語言包。請您從 <a href="https://download.moodle.org/langpack/">:download.moodle.org/langpack</a> 下載適當的 語言zip檔案，並將它們複製到您的{$a} 目錄中，再以手動將它解壓縮。';
$string['selectlangs'] = '選擇要解除安裝的語言';
$string['uninstall'] = '卸除被選出的語言包';
$string['uninstallconfirm'] = '您準備要完全卸除語言包<strong>{$a}</strong>，您確定嗎？';
$string['updatelangs'] = '更新全部已安裝的語言包';
