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
 * Strings for component 'qbehaviour_deferredcbm', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qbehaviour_deferredcbm
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accuracy'] = '正確';
$string['accuracyandbonus'] = '正確+額外加分';
$string['assumingcertainty'] = '你沒有選擇一個確定程度。所以假定為：{$a}';
$string['averagecbmmark'] = '平均CBM分數';
$string['basemark'] = '基本分數{$a}';
$string['breakdownbycertainty'] = '依據肯定程度分別呈現';
$string['cbmbonus'] = 'CBM額外加分';
$string['cbmgradeexplanation'] = '就信心加權計分法，以上分數是相對於在C=1時，全對的最高分數。';
$string['cbmgrades'] = '信心加權計分法(CBM)分數';
$string['cbmgrades_help'] = '在信心加權計分法 (Certainty Based Marking，CBM) 中，每個答對的試題配上C=1(低度肯定)，則給予100%的分數。如果試題答對又配上C=3(高度肯定)，則分數可能高到300%。

迷思概念(答錯卻很有信心)會比(答錯卻知道自己不確定)者得到更低的分數，因此可能導致負的總分。

**正確性**是指只看答對百分比而忽視對於答案的信心，然後再依每一題的配分進行加權。

**正確性**+**信心加權紅利**是比**正確性**更好的測量知識的方法。但是迷思概念會導致負的紅利，這會提供師生一個警告，會仔細檢查錯在什麼地方。';
$string['cbmmark'] = 'CBM分數{$a}';
$string['certainty'] = '肯定程度';
$string['certainty1'] = 'C =1(不確定，<67%)';
$string['certainty-1'] = '不知道';
$string['certainty2'] = 'C = 2 (普通，>67%)';
$string['certainty3'] = 'C = 3 (很確定，>80%)';
$string['certainty_help'] = '依照對自己答案的肯定程度來調整得分的計分方式(信心計分法)需要你指出你對於自己的答案有多高的肯定程度。可用的層次是：


肯定程度     | C=1 (不確定) | C=2 (普通) | C=3 (很確定)
------------------- | ------------ | --------- | ----------------
答對時得分     |   1          |    2      |      3
答錯時得分       |   0          |   -2      |     -6
認為答對的機率 |  <67%        | 67-80%    |    >80%

要了解自己答案的不確定性，才能得到最佳分數，例如若你認為這議題答錯的機率大於1/3，你就該輸入C=1，以避免得到負分的風險。';
$string['certaintyshort1'] = 'C=1';
$string['certaintyshort-1'] = '不知道';
$string['certaintyshort2'] = 'C=2';
$string['certaintyshort3'] = 'C=3';
$string['dontknow'] = '不知道';
$string['foransweredquestions'] = '只就這{$a}個已回答試題的結果';
$string['forentirequiz'] = '就整個測驗({$a}個試題)的結果';
$string['howcertainareyou'] = '你有多少信心說你的答案是對的？{$a->help}: {$a->choices}';
$string['judgementok'] = '好了';
$string['judgementsummary'] = '回答: {$a->responses}。 正確: {$a->fraction}。
 (最佳全距 {$a->idealrangelow} 到 {$a->idealrangehigh})。
當使用這一肯定水準時，你是 {$a->judgement}';
$string['noquestions'] = '沒有回應';
$string['overconfident'] = '過度有信心';
$string['pluginname'] = '延後回饋+信心加權法';
$string['slightlyoverconfident'] = '稍微有信心';
$string['slightlyunderconfident'] = '不太有信心';
$string['underconfident'] = '信心不足';
$string['weightx'] = '加權量{$a}';
