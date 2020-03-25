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
 * Strings for component 'qformat_aiken', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qformat_aiken
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Aiken 格式';
$string['pluginname_help'] = 'Aiken格式是把選擇題匯入 Moodle 的測驗模組時，最簡便的方法，它與我們平常所見的選擇題形式最類似。

<h2>編輯原則</h2>
1. 請使用純文字編輯器編寫，編碼使用UTF-8碼(檔首無BOM)。

2. 每一試題，題幹之前"不加題號"(匯入Moodle後會自動加上題號)。在各試題之間"插入一空行"，使形成段落，將試題隔開。

3. 題幹內容不管有多長，都必須放在同一行上，中間不可使用換行標籤(在匯入Moodle後會依螢幕大小自動換行)。

4. 每一選項都以大寫英文字母A,B,C,D當做選項的標號，緊接一個英文句點"." 或一個右括號 ")" 然後再"空一格"，最後面才是選項內容

5. 正確答案必須緊接放在最後一個選項之下，並以大寫 "ANSWER:" 開頭( : 用英文字型)，"空一格"，然後加上代表正確答案的大寫英文字母。


<h2>範例</h2>
<pre>

What is the correct answer to this question？
A. Is it this one？
B. Maybe this answer？
C. Possibly this one？
D. Must be this one！
ANSWER: D

Which LMS has the most quiz import formats？
A) Moodle
B) ATutor
C) Claroline
D) Blackboard
E) WebCT
F) Ilias
ANSWER: A
</pre>';
