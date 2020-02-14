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
 * Strings for component 'qtype_multianswer', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_multianswer
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['confirmquestionsaveasedited'] = '我確認我要把這試題儲存為已經編輯過';
$string['confirmsave'] = '確認然後儲存{$a}';
$string['correctanswer'] = '正確答案';
$string['correctanswerandfeedback'] = '正確答案和回饋';
$string['decodeverifyquestiontext'] = '解碼並校驗這題目的文字';
$string['layout'] = '版面配置';
$string['layouthorizontal'] = '橫式單選按鈕';
$string['layoutselectinline'] = '在文字行間的下拉選單';
$string['layoutundefined'] = '未定義的版面配置';
$string['layoutvertical'] = '直排式圓形單選按鈕';
$string['nooptionsforsubquestion'] = '無法取得問題的選項部份# {$a->sub} (question->id={$a->id})';
$string['noquestions'] = '這克漏字試題(多個答案)"<strong>{$a}</strong>"沒有包含任何題目';
$string['pleaseananswerallparts'] = '請回答這一試題的所有小題';
$string['pluginname'] = '鑲入答案題(克漏字)';
$string['pluginnameadding'] = '新增一鑲入答案題(克漏字)';
$string['pluginnameediting'] = '編輯鑲入答案題(克漏字)';
$string['pluginname_help'] = '克漏字題型包含一篇文章，文章內鑲有多個試題，如選擇題和簡答題。<br/>
它適合用來測量需要以上下文進行推理的語文理解能力。';
$string['pluginnamesummary'] = '這種題型非常有變通性，但是只能以輸入文字的方式建立題目，文字中以特殊代碼來建立選擇題、簡答題、和數值題。';
$string['qtypenotrecognized'] = '試題型態 {$a} 無法識別';
$string['questiondefinition'] = '題目定義';
$string['questiondeleted'] = '試題已刪除';
$string['questioninquiz'] = '<ul>
  <li>新增或刪除試題， </li>
  <li>在文章中更改試題順序，</li>
  <li>更改它們的題型 (數值題、簡答題、選擇題)。 </li></ul>';
$string['questionnotfound'] = '無法找到試題的題目部份 #{$a}';
$string['questionsadded'] = '已經加入試題';
$string['questionsaveasedited'] = '這試題會儲存成編輯過形式';
$string['questionsless'] = '在這克漏字題中，少於{$a} 個試題儲存在資料庫中';
$string['questionsmissing'] = '這試題的文字中必須至少包含一個鑲入答案(空白)';
$string['questionsmore'] = '在這克漏字題中，多於{$a} 個試題儲存在資料庫中';
$string['questiontypechanged'] = '題型已變更';
$string['questiontypechangedcomment'] = '至少有一種題型曾經被更改過。<br />你是否新增、刪除、或移動一個試題？<br />請再看一看';
$string['questionusedinquiz'] = '這一試題是用在 {$a->nb_of_quiz} 測驗卷上，總作答次為 : {$a->nb_of_attempts}';
$string['storedqtype'] = '已經儲存的試題類型{$a}';
$string['subqresponse'] = '部件{$a->i}: {$a->response}';
$string['unknownquestiontypeofsubquestion'] = '未知的試題型態：題目部份# {$a->sub} 的 {$a->type}';
$string['warningquestionmodified'] = '<b>警告</b>';
$string['youshouldnot'] = '<b>你不可以</b>';
