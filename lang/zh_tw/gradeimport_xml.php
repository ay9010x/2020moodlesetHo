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
 * Strings for component 'gradeimport_xml', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   gradeimport_xml
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['errbadxmlformat'] = '錯誤 - XML格式錯誤';
$string['errduplicategradeidnumber'] = '錯誤 - 課程中有兩個成績項目的id編號都是\'{$a}\'，這是不可能的。';
$string['errduplicateidnumber'] = '錯誤 - id編號重復';
$string['errincorrectgradeidnumber'] = '錯誤 - 從檔案匯入的id編號 \'{$a}\'與所有成績項目都不符合。';
$string['errincorrectidnumber'] = '錯誤 - id號不正確';
$string['errincorrectuseridnumber'] = '錯誤 - 從檔案匯入的id編號 \'{$a}\'與所有用戶都不符合。';
$string['error'] = '發生錯誤';
$string['errorduringimport'] = '當試著匯入{$a}檔時，發生錯誤。';
$string['fileurl'] = '遠端檔案的URL';
$string['fileurl_help'] = '這網址檔案欄位是用來從遠端伺服器上取得資料，比如學生訊息系統。';
$string['importxml'] = 'XML匯入';
$string['importxml_help'] = '分數可以經由一個包含用戶編號和活動編號的XML檔案匯入。要獲知匯入用的正確格式，你可以先匯出一些分數到XML檔案，然後查看該檔案所顯示的格式。';
$string['pluginname'] = 'XML檔案';
$string['xml:publish'] = '發布從XML檔匯入的成績';
$string['xml:view'] = '以XML檔匯入成績';
