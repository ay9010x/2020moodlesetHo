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
 * Strings for component 'assignfeedback_offline', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   assignfeedback_offline
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['confirmimport'] = '確認分數的匯入';
$string['default'] = '預設為啟用';
$string['default_help'] = '若設定，所有的新作業預設將以試算表進行離線計分。';
$string['downloadgrades'] = '下載計分用試算表';
$string['enabled'] = '離線計分試算表';
$string['enabled_help'] = '若啟用，教師在評閱作業時，將能夠下載或上傳包含有學生分數的試算表。';
$string['feedbackupdate'] = '將 "{$a->student}" 的欄位 "{$a->field}"  設定為"{$a->text}"';
$string['gradelockedingradebook'] = '{$a}的分數在成績簿中已經被鎖定';
$string['graderecentlymodified'] = '{$a}的分數已經在Moodle中被更改，且比計分試算表來得新';
$string['gradesfile'] = '計分試算表(CSV格式)';
$string['gradesfile_help'] = '含有更改後分數的計分試算表。這一檔案必須是從這一作業下載的一個CSV檔案，且必須有欄位包含學生的分數和身分辨識資料。檔案必須是以&quot;UTF-8&quot;編碼。';
$string['gradeupdate'] = '將 {$a->student}的分數設為 {$a->grade}';
$string['ignoremodified'] = '若在Moodle上的變更比在試算表上的新，則允許更新紀錄。';
$string['ignoremodified_help'] = '當計分試算表從Moodle下載時，它包含了每一個分數的最近更改日期。

若在這試算表下載之後，有任何分數在Moodle上被更新，預設上，在匯入分數時Moodle將會拒絕覆寫這一更新的訊息。

藉著選用這一選項，Moodle將會關閉安全檢查，並允許多位評分者覆寫彼此的分數。';
$string['importgrades'] = '確認在計分試算表上的變更';
$string['invalidgradeimport'] = 'Moodle無法閱讀你上傳的試算表。請確定它是以用逗點分隔數值的形式來儲存(.csv)，並在嘗試一次。';
$string['nochanges'] = '在上傳的試算表中沒有發現更改過的分數';
$string['offlinegradingworksheet'] = '分數';
$string['pluginname'] = '離線計分試算表';
$string['processgrades'] = '匯入成績';
$string['skiprecord'] = '跳過紀錄';
$string['updatedgrades'] = '更新 {$a}的分數和回饋';
$string['updaterecord'] = '更新紀錄';
$string['uploadgrades'] = '上傳計分試算表';
