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
 * Strings for component 'workshopallocation_scheduled', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   workshopallocation_scheduled
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['currentstatus'] = '當前狀態';
$string['currentstatusexecution'] = '狀態';
$string['currentstatusexecution1'] = '已經在{$a->datetime}執行';
$string['currentstatusexecution2'] = '將在{$a->datetime}再執行一次';
$string['currentstatusexecution3'] = '將在{$a->datetime}執行';
$string['currentstatusexecution4'] = '等待執行';
$string['currentstatusnext'] = '下一個執行';
$string['currentstatusnext_help'] = '在某些情況下，即使已經配置過，還會再次自動執行配置。例如，作業截止日期被延後時。';
$string['currentstatusreset'] = '重置';
$string['currentstatusreset_help'] = '勾選此核取方塊，並保存表單，將導致重置當前狀態。前次配置的所有資訊都會被刪除，這樣就能再次配置（如果在上面啟用）。';
$string['currentstatusresetinfo'] = '勾選這表格和儲存這表單以重置這執行結果';
$string['currentstatusresult'] = '最近執行結果';
$string['enablescheduled'] = '啟用規劃的配置';
$string['enablescheduledinfo'] = '在提交階段結束之後，自動地配置作品。';
$string['pluginname'] = '事先規劃的配置';
$string['randomallocationsettings'] = '配置設定';
$string['randomallocationsettings_help'] = '在此定義隨機配置方式的參數。隨機配置外掛會使用它們來進行實際的作業配置。';
$string['resultdisabled'] = '規劃的配置已關閉';
$string['resultenabled'] = '規劃的配置已啟動';
$string['resultexecuted'] = '成功';
$string['resultfailed'] = '無法自動的配置作品';
$string['resultfailedconfig'] = '規劃的配置設定錯誤';
$string['resultfaileddeadline'] = '工作坊沒有界定作業提交的截止日期';
$string['resultfailedphase'] = '工作坊不是在提交階段';
$string['resultvoid'] = '沒有作品被配置';
$string['resultvoiddeadline'] = '不可以在提交截止日之後';
$string['resultvoidexecuted'] = '這配置已經被執行了';
$string['scheduledallocationsettings'] = '規劃的配置的設定';
$string['scheduledallocationsettings_help'] = '若啟用，規劃的配置方式將會在提交階段結束後，自動配置作業進行評量。此階段的結束時間在工作坊設定的“提交截止日期”中定義。

本質上，是使用此表單中預定義的參數來執行隨機配置。這意味意著規劃的配置工作看起來就像是教師在提交結束階段使用下面的配置設定來親自執行隨機配置。

注意，如果您在提交截止日期前手動轉換工作坊到評量階段，規劃的配置 *不會* 執行。這種情況下，您不得不親自配置作業。

規劃的配置方式在和自動的階段切換功能一起使用時，特別有用。';
$string['setup'] = '設定規劃的配置';
