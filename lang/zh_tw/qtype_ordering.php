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
 * Strings for component 'qtype_ordering', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_ordering
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['absoluteposition'] = '絕對位置';
$string['addingordering'] = '增加排序題';
$string['addmoreanswers'] = '新增{$a}個項目';
$string['allornothing'] = '全有或全無';
$string['answer'] = '項目文字';
$string['answerheader'] = '拖拉項目{no}';
$string['correctorder'] = '這些項目的正確順序應該如下：';
$string['defaultanswerformat'] = '預設答案格式';
$string['defaultquestionname'] = '拖拉下列的項目到正確的順序位置上';
$string['editingordering'] = '編輯排序題';
$string['gradedetails'] = '成績的詳細資料';
$string['gradingtype'] = '成績類型';
$string['gradingtype_help'] = '選擇計算成績的類型。

**全有或全無**
如果所有項目都在正確的位置，可以得到滿分。否則，分數將會是零。

**絕對位置**
如果項目同樣是正確答案的位置上，項目將會視為正確。題目的最高分數將會與項目的顯示數目**一樣**。

**與之後的項目有關（除了最後）**
如果項目同樣是正確答案的位置上，項目將會視為正確。在最後位置的項目將不被評核。所以題目的最高分數將會比項目的顯示數目**少一分**。

**與之後的項目有關（包括最後）**
如果項目同樣是正確答案的位置上，項目將會視為正確。這包括了最後的答案不會有跟隨的項目。題目的最高分數將會與項目的顯示數目**一樣**。

**與之前及之後的項目有關**
如果項目與之前及之後的項目相同，項目將會視為正確。第一個項目的前方不會有任何項目，而最後的項目亦不會尾隨的項目。因此，每個項目將會有兩個可能的分數，題目的最高分數將會與項目顯示數目的**兩倍**。

**與之前所有及之後的項目有關**
如果項目的前方與正確答案相同，並跟從正確答案中的所有項目。前方項目的順序及之後項目的順廁都不緊要。因此，如果 顯示了***n*** 個項目，每個項目的的得分是***(n - 1)***，題目的最高分數是***n x (n - 1)***或等於***(n² - n)***。

**最長的順序子集**
分數將是項目的最長順序子集。題目的最高分數與顯示題目的數目相同。子集最少要有兩個項目。子集不一定以第一個頂目作開始（但可以這樣做）及不一定是連續（但可以這樣做）。當多於一個相同長度的子集，開始的項目將從由左至右搜尋顯示為正確。其他項見會視為錯誤。


**最長的連續子集**
分數將是項目的最長連續子集。題目的最高分數與顯示題目的數目相同。子集最少要有兩個項目。子集不一定以第一個頂目作開始（但可以這樣做）及必須是連續。當多於一個相同長度的子集，開始的項目將從由左至右搜尋顯示為正確。其他項見會視為錯誤。';
$string['horizontal'] = '水平的';
$string['layouttype'] = '項目的排列方式';
$string['layouttype_help'] = '選擇這些項目是要以垂直或水平方式顯示';
$string['longestcontiguoussubset'] = '最長的連續子集';
$string['longestorderedsubset'] = '最長的順序子集';
$string['noresponsedetails'] = '抱歉，沒有正確的排列順序可以用來計分。';
$string['noscore'] = '沒有分數';
$string['notenoughanswers'] = '重組題必須至少有{$a}個項目供學生排列';
$string['pluginname'] = '重新排序';
$string['pluginnameadding'] = '新增一個重組題';
$string['pluginnameediting'] = '編輯一個重組題';
$string['pluginname_help'] = '有幾個項目以錯誤雜亂的順序顯示出來，學生需要把這些項目拖放排列成一個有意義的順序。';
$string['pluginnamesummary'] = '把錯雜的項目重新排列成有意義的順序。';
$string['relativeallpreviousandnext'] = '與之前所有及之後的項目有關';
$string['relativenextexcludelast'] = '與之後的項目有關（除了最後）';
$string['relativenextincludelast'] = '與之後的項目有關（包括最後）';
$string['relativeonepreviousandnext'] = '與之前及之後的項目有關';
$string['removeeditor'] = '移除HTML編輯器';
$string['removeitem'] = '移除可拖拉項目';
$string['scoredetails'] = '這是此回答中每個項目的分數';
$string['selectall'] = '選出所有項目';
$string['selectcontiguous'] = '選出項目的一個連續性的下層集合';
$string['selectcount'] = '下層集合的大小';
$string['selectcount_help'] = '當這一試題出現在一測驗上時，將會顯示出幾個項目供學生排列。';
$string['selectrandom'] = '選出項目的一個隨機下層集合';
$string['selecttype'] = '項目選擇類型';
$string['selecttype_help'] = '選擇是否要顯示所有項目或是項目的下層集合。';
$string['showgrading'] = '計分細節';
$string['vertical'] = '垂直的';
