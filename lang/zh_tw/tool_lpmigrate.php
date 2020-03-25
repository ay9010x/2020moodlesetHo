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
 * Strings for component 'tool_lpmigrate', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_lpmigrate
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowedcourses'] = '允許的課程';
$string['allowedcourses_help'] = '選擇要遷移到這新架構的課程。若沒有指定課程，那所有的課程都會被遷移。';
$string['continuetoframeworks'] = '繼續到架構';
$string['coursecompetencymigrations'] = '課程核心能力遷移';
$string['coursemodulecompetencymigrations'] = '課程活動或資源核心能力遷移';
$string['coursemodulesfound'] = '找到的課程活動或資源';
$string['coursesfound'] = '找到的課程';
$string['coursestartdate'] = '課程開始日期';
$string['coursestartdate_help'] = '若啟用，那些開始日期早於指定日期的課程將不會被遷移。';
$string['disallowedcourses'] = '不允許的課程';
$string['disallowedcourses_help'] = '選擇不應該被遷移到新架構的課程';
$string['errorcannotmigratetosameframework'] = '不可以遷移到和原來相同的架構上';
$string['errorcouldnotmapcompetenciesinframework'] = '無法對應到這一架構的任何核心能力';
$string['errors'] = '錯誤';
$string['errorwhilemigratingcoursecompetencywithexception'] = '當遷移這課程核心能力 {$a} 時發生錯誤';
$string['errorwhilemigratingmodulecompetencywithexception'] = '當遷移這活動或資源核心能力 {$a} 時發生錯誤';
$string['excludethese'] = '這些除外';
$string['explanation'] = '這一工具能夠將核心能力架構更新成一較新的版本。它會搜尋使用舊架構的課程和活動的核心能力，然後更新連結，使之指向新架構。

它不建議直接編輯舊的核心能力，因為這將會改變所有已經納入用戶學習計畫的核心能力。

通常做法是你要匯入新版的架構，隱藏舊版架構，然後使用這工具去遷移新課程到新架構。';
$string['findingcoursecompetencies'] = '尋找課程核心能力';
$string['findingmodulecompetencies'] = '尋找活動或資源核心能力';
$string['frameworks'] = '架構';
$string['limittothese'] = '只限於這些';
$string['lpmigrate:frameworksmigrate'] = '遷移架構';
$string['migrateframeworks'] = '遷移架構';
$string['migratefrom'] = '遷移自';
$string['migratefrom_help'] = '選擇目前使用的舊架構';
$string['migratemore'] = '遷移更多';
$string['migrateto'] = '遷移到';
$string['migrateto_help'] = '選擇這架構的新版本，它只可以選擇一個沒有隱藏的架構。';
$string['migratingcourses'] = '遷移課程';
$string['missingmappings'] = '少了對映';
$string['performmigration'] = '進行遷移';
$string['pluginname'] = '核心能力遷移工具';
$string['results'] = '結果';
$string['startdatefrom'] = '開始日期';
$string['unmappedin'] = '在 {$a}沒有對映的';
$string['warningcouldnotremovecoursecompetency'] = '這課程的核心能力不能被移除';
$string['warningcouldnotremovemodulecompetency'] = '這活動或資源的核心能力不能被移除';
$string['warningdestinationcoursecompetencyalreadyexists'] = '目的地課程已經有核心能力';
$string['warningdestinationmodulecompetencyalreadyexists'] = '目的地活動或資源已經有核心能力';
$string['warnings'] = '警告';
