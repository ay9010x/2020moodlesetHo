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
 * Strings for component 'qtype_match', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_match
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['answer'] = '答案{$a}';
$string['availablechoices'] = '可用的選項';
$string['blanksforxmorequestions'] = '增加 {no} 組空白配合項目';
$string['correctansweris'] = '正確答案是：{$a}';
$string['deletedchoice'] = '[已刪除的選項]';
$string['deletedsubquestion'] = '在這次作答開始之後，這一考題的這部分已被刪除。';
$string['filloutthreeqsandtwoas'] = '您必須至少提供兩個問題和三個答案。你可以藉由增加空白小題來提供額外的錯誤答案。若問題與對應的答案都空白時，該小題會被忽略。';
$string['nomatchinganswer'] = '您必須指定一個答案來配對這一問題"{$a}"';
$string['nomatchinganswerforq'] = '您必須為這個題目指定一個答案。';
$string['notenoughqsandas'] = '你必須提供至少{$a->q}個問題和{$a->a}個答案';
$string['notenoughquestions'] = '您必須提供至少{$a}個配對的題目與答案。';
$string['pleaseananswerallparts'] = '請回答這一試題的所有小題';
$string['pluginname'] = '配合題';
$string['pluginnameadding'] = '新增一配合題';
$string['pluginnameediting'] = '編輯一配合題';
$string['pluginname_help'] = '配合題要求作答者將一清單上的名字或敘述(問題)和另外一清單上的名字或敘述(答案)依照某種規則正確地配對起來。';
$string['pluginnamesummary'] = '一小題的答案都需要從可能答案的清單中選出來。';
$string['shuffle'] = '隨機排列';
$string['shuffle_help'] = '若啟用，每次作答時，答案項的順序都會重新隨機排列。它需要在測驗活動設定時啟用"在試題內隨機排列"。';
