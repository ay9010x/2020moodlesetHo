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
 * Strings for component 'qtype_gapfill', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_gapfill
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['answerdisplay'] = '顯示答案';
$string['answerdisplay_help'] = '"托放模式"會顯示一個文字列表，讓您把正確的文字拖放到空格裡。<br/>
"填字模式"就像一般填充題，只有空格，沒有可選用的文字，你要直接輸入。<br/>
"下拉模式"會在空格處顯示一下拉選單，裡面有與托放模式相同的文字列表。';
$string['blank'] = '空白';
$string['cannotimport'] = '無法匯入';
$string['casesensitive'] = '區分大小寫';
$string['casesensitive_help'] = '若勾選時，當正確答案是CAT，那cat會被當作錯誤答案離處理';
$string['casesensitive_text'] = '要區分大小寫，也就是CAT不同於cat';
$string['correctanswer'] = '正確答案';
$string['course'] = '課程';
$string['coursenotfound'] = '沒找到課程，請檢察課程簡稱';
$string['courseshortname'] = '課程簡稱';
$string['courseshortname_help'] = '輸入要匯入試題的課程的簡稱。';
$string['delimitchars'] = '分隔字元';
$string['delimitchars_help'] = '把預設的分隔符號[ ]改成新的分隔符號，這對於考程式語言的試題特別有用';
$string['delimitset'] = '分隔符號';
$string['delimitset_text'] = '設定空格的分隔符號，這樣你可以用% %標示空格位置，你可以把句子寫成：The %cat% sat on the %mat%';
$string['disableregex'] = '關閉正規表達式';
$string['disableregex_help'] = '關閉正則表達式處理，並並執行一標準的字串比較。這對於HTML試題會很有用，在這裡角括號 (< 和 >)和數學符號，比如*都會被當作一般文字處理，而不是電腦語言的表達式。';
$string['disableregexset_text'] = '關閉答案的正則表達式的處理';
$string['displaydragdrop'] = '拖放';
$string['displaydropdown'] = '拖放';
$string['displaygapfill'] = '填字題';
$string['duplicatepartialcredit'] = '點數已被排除，因為您有重複的答案';
$string['fixedgapsize'] = '固定空格大小';
$string['fixedgapsize_help'] = '當這題目在作答時，每一空格都將被設定成和最大空格一樣的大小，這會使學生無法以空格大小作為正確答案的線索。舉例來說，若空格答案是 [red] 和 [yellow] ，若空格大小不一，很顯然 yellow 應該放在比較大的空格中。';
$string['fixedgapsizeset_text'] = '比照最大空格來設定每一空格的大小';
$string['gapfill'] = '填字題';
$string['import'] = '匯入';
$string['importexamples'] = '匯入範例';
$string['moreoptions'] = '更多選項';
$string['noduplicates'] = '不可重複';
$string['noduplicates_help'] = '當勾選時，每一個答案都必須是獨一無二的，不能與其他答案重複。這功能在每一欄位上有|運算子時特別有用。
舉例來說，若題目問"奧運獎牌是什麼顏色？"三個答案欄位上每一欄位都有[金|銀|銅]，若這學生在每一欄位都填上"金"，那麼只有第一個欄位會有分數，其他的仍然會被打叉。它就像是為了計分的正確而刪掉重複出現的答案。';
$string['or'] = '或';
$string['pleaseenterananswer'] = '請輸入一個答案';
$string['pluginname'] = '填字題型';
$string['pluginnameadding'] = '添加一個填字試題';
$string['pluginnameediting'] = '編輯填字試題';
$string['pluginname_help'] = '把要填入的字放在方括號中，例如 [cat] sat on the [mat]。如果mat 或 rug 都是可接受的答案，就寫成 [mat|rug]。作答方式有下拉選單及拖放模式，它們都允許顯示可重新隨機排列的答案清單，答案中可以包含錯誤的誘答。';
$string['pluginnamesummary'] = '這是一種依據上下文在空格中填入文字的題型，它的功能比標準的克漏字題型來得少，但語法比較簡單。';
$string['questionsmissing'] = '你沒有包含任何欄位在你的試題文章中';
$string['wronganswers'] = '錯誤選擇';
$string['wronganswers_help'] = '作為誘答的錯誤文字的列表。每一文字以逗點分隔開來，它只能應用在拖放/下拉模式。';
$string['yougotnrightcount'] = '你的正確填答數是{$a->num}。';
