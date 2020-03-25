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
 * Strings for component 'gradingform_btec', language 'zh_tw', branch 'MOODLE_30_STABLE'
 *
 * @package   gradingform_btec
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addcomment'] = '增加常用評論';
$string['addcriterion'] = '增加條件';
$string['alwaysshowdefinition'] = '給顯示BTEC定義';
$string['and'] = '及';
$string['backtoediting'] = '返回編輯';
$string['btecgrading'] = 'BTEC是如何操作';
$string['btecgrading_help'] = 'BTEC評分是二進制及累計的。學生可以取得或不取得一個等級，沒有數字或百分數。

如果學生有此等級的每一個項目及以下的項目，他們只可以取得一個等級。<br />所以如果您取得所有合格的條件，您只可以得到總體合格。如果您取得所有合格及優點的條件，您只可以得到優點。如果您取得所有合格、優點及卓越的條件，您可以得到卓越。如果學生沒有得到全部合格的條件，他們會取得諮詢。';
$string['btecmappingexplained'] = '警告：您的BTEC等級的上限為<b>{$a->maxscore}分，但您的活動分數設定為{$a->modulegrade}。您的BTEC等級的上限將會調整至模組的等級上限。<br />

中期分數將會轉換及四捨五入至最接近的等級。';
$string['btecnotcompleted'] = '請為每個准則提供有效的等級';
$string['btecoptions'] = 'BTEC的評分選項';
$string['btecscale'] = '諮詢，合格，優點，卓越';
$string['btecscale_description'] = '沒有數字或百分比，您只會在取得該等級及較低等級的所有項目時取得此等級。';
$string['btecstatus'] = '現時BTEC的等級狀態';
$string['clicktocopy'] = '點擊以複製此文字到准則反饋中';
$string['clicktoedit'] = '點擊以編輯';
$string['clicktoeditname'] = '點擊以編輯等級（例如：P1, D2 等）';
$string['comments'] = '常用評論';
$string['commentsdelete'] = '刪除評論';
$string['commentsempty'] = '點擊以編輯評論';
$string['commentsmovedown'] = '向下移';
$string['commentsmoveup'] = '向上移';
$string['confirmdeletecriterion'] = '您確定要刪除此項目？';
$string['confirmdeletelevel'] = '您確定要刪除此等級？';
$string['countofpasscriteria'] = '計算合格的准則';
$string['criteriarequirements'] = '完成標準要求';
$string['criterion'] = '准則';
$string['criteriondelete'] = '刪除准則';
$string['criterionempty'] = '點擊以編輯准則';
$string['criterionmovedown'] = '向下移';
$string['criterionmoveup'] = '向上移';
$string['d'] = 'd';
$string['definebtecmarking'] = 'BTEC評分';
$string['definemarkingbtec'] = '定義BTEC評分';
$string['description'] = '說明';
$string['descriptionmarkers'] = '評分者的說明';
$string['descriptionstudents'] = '學生的說明';
$string['duplicateelements'] = '複製條件元件，查看';
$string['endwithadigit'] = '必須以數字作結束；';
$string['err_maxscorenotnumeric'] = '條件最高分數必須是數值';
$string['err_nocomment'] = '評論不能留空';
$string['err_nodescription'] = '學生說明不能留空';
$string['err_nodescriptionmarkers'] = '標記說明不能留空';
$string['err_nomaxscore'] = '不能留空准則的最高分數';
$string['err_noshortname'] = '不能留空准則名稱';
$string['err_scoreinvalid'] = '{$a->criterianame}的分數是無效的，最高分數是{$a->maxscore}。';
$string['gradeheading'] = 'BTEC 等級編輯';
$string['gradelevels'] = '成績等級';
$string['gradelevels_help'] = '准則名稱必須以字母P, M 或 D（合格，優點，卓越）作開端，並跟隨一個數字，例如：P1／M2／D3 等。';
$string['gradingof'] = '{$a}等級';
$string['hidemarkerdesc'] = '隱藏等級准則說明';
$string['hidestudentdesc'] = '隱藏學生准則說明';
$string['level'] = '等級';
$string['m'] = 'm';
$string['maxscore'] = '最高分';
$string['name'] = '名字';
$string['needregrademessage'] = 'BTEC等級定義已在評核此學生後更變更。學生將不能看見BTEC等級，直至您查看BTEC等級及更新等級。';
$string['no'] = '否';
$string['p'] = 'p';
$string['pluginname'] = 'BTEC評分';
$string['previewbtecmarking'] = '預覽BTEC評分';
$string['regrademessage1'] = '您將會儲存已用作評分的BTEC等級。請標明需要檢視的已有等級。如果您設定此選項，BTEC等級將會從學生中隱藏，直至他們的項目被重新評分。';
$string['regrademessage5'] = '您將會儲存已用作評分的BTEC等級。成績簿上的等級將不會被更改，但BTEC等級將會從學生中隱藏，直至他們的項目被重新評分。';
$string['regradeoption0'] = '不要標記成重新評分';
$string['regradeoption1'] = '標記成重新評分';
$string['restoredfromdraft'] = '注意：';
$string['save'] = '儲存';
$string['savebtec'] = '儲存BTEC批改及使其準備好';
$string['savebtecdraft'] = '儲存為草稿';
$string['score'] = '分數';
$string['showdescriptionstudent'] = '顯示被評分者的說明';
$string['showmarkerdesc'] = '顯示標記准則描述';
$string['showmarkspercriterionstudents'] = '顯示每個給予學生的分數';
$string['showstudentdesc'] = '顯示學生准則的描述';
$string['startwithpmd'] = '{$a->level} 必定要以字母 {$a->p},{$a->m} 或 {$a->d}作開端';
$string['yes'] = '是';
