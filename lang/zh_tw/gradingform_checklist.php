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
 * Strings for component 'gradingform_checklist', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   gradingform_checklist
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addgroup'] = '增加群組';
$string['alwaysshowdefinition'] = '允許用戶預覽用於此單元的檢查表（否則檢查表只能在評分後看見）';
$string['backtoediting'] = '返回編輯模式';
$string['checked'] = '已檢查';
$string['checkitem'] = '"{$a}" 標記成滿分';
$string['checklist'] = '檢查表';
$string['checklistmapping'] = '分數等級映射規則';
$string['checklistmappingexplained'] = '此檢查表的最低可能分數是 <b>{$a->minscore} 分</b>並會轉換成在此單元的最低等級（除非使用等級尺，否則將會是零）。
最高分數是 <b>{$a->maxscore} points</b>並會轉換成成最高等級。<br />
中期分數將會個別轉換並四捨五入至最接近的等級。<br />
如果使用等級尺，連續整數的分數將會轉換成等級尺中的計量。';
$string['checklistoptions'] = '檢查表選項';
$string['checkliststatus'] = '現今檢查表收態';
$string['confirmdeletegroup'] = '您確定要刪除此群組？';
$string['confirmdeleteitem'] = '您確定要刪除此項目？';
$string['definechecklist'] = '定義檢查表';
$string['description'] = '描述';
$string['enablegroupremarks'] = '允許評分者在每個檢查表群組中添加文字備注';
$string['enableitemremarks'] = '允許評分者在每個檢查表項目中添加文字備注';
$string['err_definitionmax'] = '項目定義不能超過255字元';
$string['err_descriptionmax'] = '群組描述不能超過255字元';
$string['err_nodefinition'] = '不能漏空項目定義';
$string['err_nodescription'] = '不能漏空群組描述';
$string['err_nogroups'] = '檢查表中最少要有一個群組';
$string['err_scoreformat'] = '每個項目的分數必須是有效的正數';
$string['err_scoremax'] = '每個項目的分數不能超過100';
$string['err_totalscore'] = '檢查表中的最高可能分數必須超過零';
$string['gradingof'] = '{$a} 等級';
$string['groupadditem'] = '增加項目';
$string['groupdelete'] = '刪除群組';
$string['groupdescription'] = '群組描述';
$string['groupempty'] = '點擊以修改群組';
$string['groupfeedback'] = '"{$a}" 的群組回饋';
$string['groupmovedown'] = '移下';
$string['groupmoveup'] = '移上';
$string['grouppoints'] = '群組分數';
$string['groupremark'] = '"{$a}" 的竹群組分數';
$string['itemdefinition'] = '項目定義';
$string['itemdelete'] = '刪除項目';
$string['itemempty'] = '點擊以編輯項目';
$string['itemfeedback'] = '"{$a}" 的回饋';
$string['itemremark'] = '"{$a}" 的項目備注';
$string['itemscore'] = '項目分數';
$string['name'] = '名稱';
$string['needregrademessage'] = '檢查表定義將會在評核學生後更改。直至您檢閱檢查表及更新等級，否則學生將不能看見此檢查表';
$string['overallpoints'] = '整體分數';
$string['pluginname'] = '檢查表';
$string['previewchecklist'] = '預覽檢查表';
$string['regrademessage1'] = '您將會儲存檢查表上用作評分的變更。請標明是否需要檢閱已有分數。如果您設定要檢閱已有分數，檢查表將會隱藏，學生只能在評分後才能檢視。';
$string['regrademessage5'] = '您將會儲存檢查表上用作評分的變更。等級數值不會改變，但檢查表將會隱藏，學生只能在評分後才能檢視。';
$string['regradeoption0'] = '不要標記為重新評分';
$string['regradeoption1'] = '標記為重新評分';
$string['restoredfromdraft'] = '注意：上一次的評分沒有正確儲存，因此草稿分數已被修復。如果您想取消以上變更，請使用下方的取消接鍵。';
$string['save'] = '儲存';
$string['savechecklist'] = '儲存檢查表作準備';
$string['savechecklistdraft'] = '儲存為草稿';
$string['scorepostfix'] = '{$a} 分數';
$string['showitempointseval'] = '評測時顯示每個項目的分數';
$string['showitempointstudent'] = '顯示已評分項目的分數';
$string['showremarksstudent'] = '顯示已評分項目的所有備注';
$string['unchecked'] = '未選';
