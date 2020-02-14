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
 * Strings for component 'qtype_randomsamatch', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_randomsamatch
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['insufficientoptions'] = '這一試題沒有足夠的選項可以使用，因此在這測驗上無法使用，請告知你的教師。';
$string['nosaincategory'] = '您選擇的這個\'{$a->catname}\'題目類別中，沒有簡答題。請選擇一個不同類別，並製作一些題目。';
$string['notenoughsaincategory'] = '您選擇的這個\'{$a->catname}\'題目類別中，只有 {$a->nosaquestions}題簡答題。請選擇一個不同類別，並製作更多題目，或是減少您的待選題目數。';
$string['pluginname'] = '隨機簡答配合題';
$string['pluginnameadding'] = '新增一個隨機簡答配合題';
$string['pluginnameediting'] = '編輯一個隨機簡答配合題';
$string['pluginname_help'] = '從學生的觀點，它就是一個配合題。

其差別是這些配對用的清單是從當前類別下的選擇題隨機抽選出來的。

在這一類別下需要有足夠未被使用的簡答題，否則會出現錯誤訊息。';
$string['pluginnamesummary'] = '它就像配合題，只是它是由某一類別下的簡答題隨機抽選出來，而以配合題方式作答。';
$string['randomsamatch'] = '隨機抽簡答題組成的配合題';
$string['randomsamatchintro'] = '就以下試題的每一題，從這選單中選出一個配對的答案。';
$string['randomsamatchnumber'] = '要選出的試題數';
$string['subcats'] = '包含下層目錄';
$string['subcats_help'] = '若勾選，試題也會從下層目錄選出。';
