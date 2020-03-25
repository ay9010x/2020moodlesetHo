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
 * Strings for component 'gradingform_guide', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   gradingform_guide
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addcomment'] = '新增常用的評語';
$string['addcriterion'] = '新增規準';
$string['alwaysshowdefinition'] = '向學生顯示指引的定義';
$string['backtoediting'] = '回到編輯';
$string['clicktocopy'] = '點選以複製這一文字到規準的回饋';
$string['clicktoedit'] = '點選即可編輯';
$string['clicktoeditname'] = '點選以編輯規準名稱';
$string['comment'] = '評語';
$string['comments'] = '經常使用的評語';
$string['commentsdelete'] = '刪除評語';
$string['commentsempty'] = '點選以編輯評語';
$string['commentsmovedown'] = '向下移';
$string['commentsmoveup'] = '向上移';
$string['confirmdeletecriterion'] = '你確定你要刪除這一項目？';
$string['confirmdeletelevel'] = '你確定你要刪除這一層次？';
$string['criterion'] = '判斷規準名稱';
$string['criteriondelete'] = '刪除規準';
$string['criterionempty'] = '點選可編輯規準';
$string['criterionmovedown'] = '往上移';
$string['criterionmoveup'] = '往下移';
$string['criterionname'] = '規準名稱';
$string['criterionremark'] = '{$a}判斷規準的備註';
$string['definemarkingguide'] = '定義給分指引';
$string['description'] = '說明';
$string['descriptionmarkers'] = '給評分者的說明';
$string['descriptionstudents'] = '給學生的說明';
$string['err_maxscoreisnegative'] = '這最大分數是無效的，不允許使用負數';
$string['err_maxscorenotnumeric'] = '規準的最大分數必須是數字';
$string['err_nocomment'] = '評論不可以是空白';
$string['err_nodescription'] = '給學生的說明不能是空白的';
$string['err_nodescriptionmarkers'] = '給評分者的說明不能是空白的';
$string['err_nomaxscore'] = '規準的最大分數不能是空白的';
$string['err_noshortname'] = '規準名稱不能是空白的';
$string['err_scoreinvalid'] = '針對{$a->criterianame}所給的分數是無效的，因為最大分數是：{$a->maxscore}';
$string['err_scoreisnegative'] = '針對 \'{$a->criterianame}\'所給的分數是無效的，因為不允許有負數';
$string['err_shortnametoolong'] = '評分規準的名稱必須少於256個字母';
$string['gradingof'] = '{$a}評分中';
$string['guide'] = '評分指引';
$string['guidemappingexplained'] = '警告：你的給分指引的最高分是{$a->maxscore}分，但是在你的活動裡的最高分設定是{$a->modulegrade}分。

在你的給分指引裡的最高分數會以比例調整這活動模組的最高分。

中間分數將會分別轉換並四捨五入到最接近的可用分數。';
$string['guidenotcompleted'] = '請為每一規準提供一有效的分數';
$string['guideoptions'] = '給分指引選項';
$string['guidestatus'] = '當前給分指引狀態';
$string['hidemarkerdesc'] = '隱藏評分者規準說明';
$string['hidestudentdesc'] = '隱藏學生規準說明';
$string['insertcomment'] = '插入常用的評語';
$string['maxscore'] = '最高分數';
$string['name'] = '名稱';
$string['needregrademessage'] = '這給分指引的定義在這學生被評分之後有所改變。這學生無法看到這一給分指引，直到你檢查這給分指引並更新這分數。';
$string['pluginname'] = '給分指引';
$string['previewmarkingguide'] = '預覽給分指引';
$string['regrademessage1'] = '你即將要儲存更改到一個已經用來評分的給分指引。

請指出是否現有的分數需要被重新檢視。若你設定為是，那這給分指引將會向學生隱藏，直到他們的項目被重新評分。';
$string['regrademessage5'] = '你即將要儲存重要更改到一個已經用來評分的給分指引。

成績簿上的分數還不會被改變，但是這給分指引將會向學生隱藏，直到他們的項目被重新評分。';
$string['regradeoption0'] = '不要標示重新計分';
$string['regradeoption1'] = '標示為重新計分';
$string['restoredfromdraft'] = '注意：此人最後一次作答的分數，沒被適當地儲存，因此草稿分數已經被回存。

若你要取消這些更改，請使用以下的"取消"按鈕。';
$string['save'] = '儲存';
$string['saveguide'] = '儲存給分指引，並讓它成為可用狀態';
$string['saveguidedraft'] = '儲存成草稿';
$string['score'] = '分數';
$string['showmarkerdesc'] = '向評分者顯示規準的說明';
$string['showmarkspercriterionstudents'] = '對學生顯示每一規準的分數';
$string['showstudentdesc'] = '向學生顯示規準的說明';
