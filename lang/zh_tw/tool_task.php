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
 * Strings for component 'tool_task', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_task
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['asap'] = '儘快';
$string['blocking'] = '封鎖';
$string['component'] = '來自於';
$string['corecomponent'] = '核心';
$string['default'] = '預設';
$string['disabled'] = '關閉';
$string['disabled_help'] = 'Cron不會執行被關閉的工作排程，但是它們仍然可以經由CLI工具以手動執行。';
$string['edittaskschedule'] = '編輯工作排程：{$a}';
$string['faildelay'] = '故障延遲';
$string['lastruntime'] = '最近執行';
$string['nextruntime'] = '下次執行';
$string['plugindisabled'] = '外掛已關閉';
$string['pluginname'] = '工作排程配置';
$string['resettasktodefaults'] = '把工作排程重設為預設';
$string['resettasktodefaults_help'] = '這將會放棄任何本地所做的修改，並將此工作回復到原初的設定。';
$string['scheduledtaskchangesdisabled'] = '在Moodle的配置中已經禁止修改工作排程的清單';
$string['scheduledtasks'] = '排定的工作';
$string['taskdisabled'] = '工作排程已關閉';
$string['taskscheduleday'] = '日';
$string['taskscheduleday_help'] = '用來排執行工作時間的"日"欄位。這一欄位必須使用與 unix cron相同的格式。一些範例像:<br/>
<ul><li><strong>*</strong>每一天</li>
<li><strong>*/2</strong> 每隔 2天</li>
<li><strong>1</strong>每個月的第1天</li>
<li><strong>1,15</strong> 每個月的第1和第15天</li>
</ul>';
$string['taskscheduledayofweek'] = '星期幾';
$string['taskscheduledayofweek_help'] = '用來排執行工作時間的"星期幾"欄位。這一欄位必須使用與 unix cron相同的格式。一些範例像:<br/>
<ul>
<li><strong>*</strong> 每一天</li>
<li><strong>0</strong> 每個星期日</li>
<li><strong>6</strong> 每個星期六</li>
<li><strong>1,5</strong>每個星期一和五</li>
</ul>';
$string['taskschedulehour'] = '時';
$string['taskschedulehour_help'] = '用來排執行工作時間的"時"欄位。這一欄位必須使用與 unix cron相同的格式。一些範例像:<br/>
<ul>
<li><strong>*</strong> 每一小時</li>
<li><strong>*/2</strong> 每2小時</li>
<li><strong>2-10</strong>從上午2時到10時的每一小時 (包含)</li>
<li><strong>2,6,9</strong>上午2時， 6時和 9時</li>
</ul>';
$string['taskscheduleminute'] = '分';
$string['taskscheduleminute_help'] = '用來排執行工作時間的"分"欄位。這一欄位必須使用與 unix cron相同的格式。一些範例像:<br/>
<ul>
<li><strong>*</strong> 每一分鐘</li>
<li><strong>*/5</strong> 每隔 5分鐘</li>
<li><strong>2-10</strong> 介於 2點到10 點(包含)之間的每一分鐘</li>
<li><strong>2,6,9</strong> 整點過後的第2, 6 和 9 分鐘</li>
</ul>';
$string['taskschedulemonth'] = '月';
$string['taskschedulemonth_help'] = '用來排執行工作時間的"月"。這一欄位必須使用與 unix cron相同的格式。一些範例像:<br/>
<ul>
<li><strong>*</strong> 每一個月</li>
<li><strong>*/2</strong>每隔2個月</li>
<li><strong>1</strong>每到一月</li>
<li><strong>1,5</strong> 每到一月和五月</li>
</ul>';
