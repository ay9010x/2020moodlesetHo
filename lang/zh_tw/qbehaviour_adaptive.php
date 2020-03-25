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
 * Strings for component 'qbehaviour_adaptive', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qbehaviour_adaptive
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['disregardedwithoutpenalty'] = '這次的提交無效，會被忽視且不扣分';
$string['gradingdetails'] = '這次提交的分數：{$a->raw}/{$a->max}';
$string['gradingdetailsadjustment'] = '算入先前的嘗試次，這一題給<strong>{$a->cur}/{$a->max}</strong>分';
$string['gradingdetailspenalty'] = '這次提交導致被扣{$a} 分';
$string['gradingdetailspenaltytotal'] = '到目前為止總扣分: {$a}';
$string['gradingdetailswithadjustment'] = '這次提交的分數： {$a->raw}/{$a->max}。 若加上先前的作答次，可得到 <strong>{$a->cur}/{$a->max}</strong>。';
$string['gradingdetailswithadjustmentpenalty'] = '這次提交的分數： {$a->raw}/{$a->max}。 若加上先前的作答次，可得到 <strong>{$a->cur}/{$a->max}</strong>。這次的提交導致被扣 {$a->penalty}分。';
$string['gradingdetailswithadjustmenttotalpenalty'] = '這次提交的分數： {$a->raw}/{$a->max}。 若加上先前的作答次，可得到 <strong>{$a->cur}/{$a->max}</strong>。這次的提交導致被扣 {$a->penalty}分。到目前為止總共被扣{$a->totalpenalty}分。';
$string['gradingdetailswithpenalty'] = '這次提交的分數： {$a->raw}/{$a->max}。 這次的提交導致被扣 {$a->penalty}分。';
$string['gradingdetailswithtotalpenalty'] = '這次提交的分數： {$a->raw}/{$a->max}。 這次的提交導致被扣 {$a->penalty}分。到目前為止總共被扣{$a->totalpenalty}分。';
$string['notcomplete'] = '沒有完成';
$string['pluginname'] = '直到答對法(答錯有扣分)';
