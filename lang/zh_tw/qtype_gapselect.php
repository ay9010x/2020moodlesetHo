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
 * Strings for component 'qtype_gapselect', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_gapselect
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmorechoiceblanks'] = '新增{no}個空白選項';
$string['answer'] = '答案';
$string['choices'] = '選項';
$string['choicex'] = '選項{no}';
$string['combinedcontrolnamegapselect'] = '下拉方格';
$string['combinedcontrolnamegapselectplural'] = '下拉方格';
$string['correctansweris'] = '正確答案是：{$a}';
$string['errorblankchoice'] = '請檢查這些選項：選項{$a}是空白的';
$string['errormissingchoice'] = '請檢查這試題文字： {$a} 並沒有出現在選項當中！只有選項之中有的選項編號才做為空位代號。';
$string['errornoslots'] = '試題文字必須包含佔位符號，像是[[1]]，來顯示要填入的字詞是放在哪個位置上';
$string['errorquestiontextblank'] = '你必須輸入一些試題文字(文章)，並以[[1]]、[[2]]來標示缺漏的字詞';
$string['group'] = '選項分組';
$string['pleaseputananswerineachbox'] = '請在每一個方格中放一個答案';
$string['pluginname'] = '選擇式的克漏字';
$string['pluginnameadding'] = '新增一選擇式的克漏字試題';
$string['pluginnameediting'] = '編輯一選擇式的克漏字試題';
$string['pluginname_help'] = '選擇式的克漏字試題是要求學生從下拉選單中找出正確的字來填入文章中的缺漏字詞。
你要輸入一篇文章，像是"我家的[[1]]吃了我買的[[2]]"，文章中的[[1]]和[[2]]代表缺漏的字詞，以及其編號，然後你要在下方選項中輸入可能的答案來對應到空位1和空位2。

試題若有四個空位，那前四個選項就是正確答案，然後你可以為每一空位添加多個額外的選項，好讓試題更難一些。

這些選項可以被分組，同一組別的所有選項會放在同一個下拉選單中，你還可以將同一組別的選項隨機排列其出現的順序。';
$string['pluginnamesummary'] = '使用下拉選單來填入試題文章中所缺漏的文字';
$string['shuffle'] = '選項隨機排列？';
$string['tagsnotallowed'] = '{$a->tag}i是不允許的。(只有{$a->allowed}是被允許的)';
$string['tagsnotallowedatall'] = '{$a->tag}是不允許的。 (這兒不允許使用HTML)';
