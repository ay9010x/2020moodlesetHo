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
 * Strings for component 'qformat_missingword', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qformat_missingword
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['beginanswernotfound'] = '在匯入的檔案內容中，找不到一個必要的 "{" 字元。';
$string['endanswernotfound'] = '在匯入的檔案內容中，找不到一個必要的 "}" 字元。';
$string['noanswerfound'] = '在試題中找不到答案選項';
$string['pluginname'] = 'Missing word格式(克漏字、填空題)';
$string['pluginname_help'] = 'Missing word格式能讓Moodle將克漏字題和填空題經由純文字檔匯入。你要用{ }標示出遺漏字的位置，用=表示正確答案，用~表示誘答。

克漏字的格式為：一旦我們開始探索我們的身體部位，我們成為{=解剖和生理學 ~反思 ~科學 ~實驗}的學生，在某種意義上，我們保持學生的生活。

填空題的格式為：您可以使用Missing word格式將試題匯入Moodle的題庫和{= 單元學習}活動中。';
