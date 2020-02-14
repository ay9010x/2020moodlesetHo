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
 * Strings for component 'workshopallocation_random', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   workshopallocation_random
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addselfassessment'] = '添加自我評量';
$string['allocationaddeddetail'] = '要完成的新評量：<strong>{$a->reviewername}</strong>要評量 <strong>{$a->authorname}</strong>的作品';
$string['allocationdeallocategraded'] = '不能對已經評分的評量解除配置：評量者 <strong>{$a->reviewername}</strong>，作品作者：<strong>{$a->authorname}</strong>';
$string['allocationreuseddetail'] = '重用評量：<strong>{$a->reviewername}</strong>繼續做 <strong>{$a->authorname}</strong>的評量者';
$string['allocationsettings'] = '配置設定';
$string['assessmentdeleteddetail'] = '已解除配置的評量: <strong>{$a->reviewername}</strong> 不再評量 <strong>{$a->authorname} 的作業';
$string['assesswosubmission'] = '參與者可以評量，不需要先提交任何東西';
$string['confignumofreviews'] = '預設每一人要隨機分配幾份作業';
$string['excludesamegroup'] = '防止來自同一群組的同學相互評量';
$string['noallocationtoadd'] = '沒有增加配置';
$string['nogroupusers'] = '<p>警告：如果工作坊處在“彼此可見群組”模式或者“相互隔離群組”模式，那麼用戶必須至少屬於一個群組，才能用此工具向他們配置評量任務。不屬於任何群組的用戶仍然可以做自我評量或者刪除已有的評量。</p> <p>這些用戶目前還未被加入任何群組：{$a}</p>';
$string['numofdeallocatedassessment'] = '解除配置{$a}個評量';
$string['numofrandomlyallocatedsubmissions'] = '隨機配置{$a}個作品';
$string['numofreviews'] = '每人要評幾份同儕的作業';
$string['numofselfallocatedsubmissions'] = '自我配置{$a}個作品';
$string['numperauthor'] = '每一提交作品';
$string['numperreviewer'] = '每一評量者';
$string['pluginname'] = '隨機配置';
$string['randomallocationdone'] = '隨機配置已完成';
$string['removecurrentallocations'] = '移除現有的配置';
$string['resultnomorepeers'] = '沒有更多的同儕可用';
$string['resultnomorepeersingroup'] = '在這彼此隔離的群組，沒有更多同儕可用';
$string['resultnotenoughpeers'] = '沒有足夠的同儕可用';
$string['resultnumperauthor'] = '試著對每一作品配置{$a}個評量';
$string['resultnumperreviewer'] = '試著對每一學生配置{$a}個評量';
$string['stats'] = '當前配置統計';
