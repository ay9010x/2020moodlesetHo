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
 * Strings for component 'qtype_varnumericset', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_varnumericset
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmoreanswerblanks'] = '空格{no}的更多答案';
$string['addmorevariants'] = '為更多變數新增{$a}更多空格';
$string['addmorevars'] = '為變數新增{$a}更多空格';
$string['ae_numericallycorrect'] = '您的答案是差不多正確。您有正確的數值並正確地四捨五入。';
$string['ae_numericallycorrectandwrongformat'] = '您的答案是差不多正確。您有正確的數值並正確地四捨五入，但不是以科學計數法的方式。';
$string['ae_roundingincorrect'] = '您的答案是差不多正確，但錯誤地四捨五入。';
$string['ae_roundingincorrectandwrongformat'] = '您的答案是差不多正確，但錯誤地四捨五入及不是以科學計數法的方式。';
$string['ae_toomanysigfigs'] = '您的答案是差不多正確，但提供太多有效數字。';
$string['ae_toomanysigfigsandwrongformat'] = '您的答案是差不多正確，但提供太多有效數字及不是以科學計數法的方式。';
$string['ae_wrongbyfactorof10'] = '您的答案是差不多正確，但您的分子10是錯誤。';
$string['ae_wrongbyfactorof10andwrongformat'] = '您的答案是差不多正確，但您的分子10是錯誤及不是以科學計數法的方式。';
$string['answer'] = '答案：{$a}';
$string['answermustbegiven'] = '如果有成績或回饋的話，您必須填寫答案';
$string['answerno'] = '答案{$a}';
$string['autofirehdr'] = '當答案{$a} 是部份錯誤，提供回饋及部份分數。';
$string['calculatewhen'] = '何時計算已連算的數值';
$string['cannotrecalculate'] = '抱歉，不能重新計算已運算的變數因為表格上有錯誤。請修改錯誤然後重新計算數值。';
$string['checknumerical'] = '如果數值上正確';
$string['checkpowerof10'] = '如果10倍是關上';
$string['checkrounding'] = '如果四捨五入是錯誤的';
$string['checkscinotation'] = '如果需要科學記數法但沒有使用';
$string['correctansweris'] = '正確答案是：{$a}';
$string['correctansweriserror'] = '{$a->answer} <sup>+</sup>/<sub>-</sub> {$a->error}';
$string['correctanswerissigfigs'] = '{$a->answer} ({$a->sigfigs}有效數字';
$string['correctanswers'] = '正確答案';
$string['error'] = '可接受誤差 +/-';
$string['errorreportedbyexpressionevaluator'] = '表達式求值錯誤 ：{$a}';
$string['expectingassignment'] = '您必須做用數學表達式來分配數值至「已運算變數」';
$string['expectingvariablename'] = '在這裡期待一個變數名稱';
$string['expressionevaluatesasinfinite'] = '結果是無限';
$string['expressionevaluatesasnan'] = '結果不是一個數字';
$string['expressionmustevaluatetoanumber'] = '您應要輸入表達式以在此評估數值，不是作業。';
$string['filloutoneanswer'] = '您必須只少提供一個可能答案。不會使用留空的答案。 \'*\' 可以用作通配符配對所有數字。第一個配對答案將會用作決定分數及意見';
$string['forallanswers'] = '給所有答案';
$string['hintoverride'] = '';
$string['illegalthousandseparator'] = '您在您的答案使用了非法的千位分隔符 "{$a->thousandssep}" 。我們只接受點數分隔符 "{$a->decimalsep}"';
$string['notenoughanswers'] = '此題型需要只少{$a}答案';
$string['notolerancehere'] = '您可以輸入寬差來配對任何回答。';
$string['notvalidnumber'] = '您沒有輸入以可識別格式的數字';
$string['notvalidnumberprepostfound'] = '請只輸入有效的數字';
$string['options'] = '選項';
$string['pleaseenterananswer'] = '請輸入答案';
$string['pluginname'] = '可變數字集';
$string['pluginnameadding'] = '增加可變數字集題目';
$string['pluginnameediting'] = '編輯可變數字集題目';
$string['pluginname_help'] = '要回答題目，回答者輸入一個數字。

在題目中使用的單位及用作計算答案的數字是由預設組中選取，可以預先以數學表達式計算。

所有表達式皆是在題目建立時計算，隨機函數中的數值亦會是相同。至於沒有變數的題目，表達式將在過程中以每個用戶都不同隨機的數值計算。';
$string['pluginnamesummary'] = '尤許數值回答，題目可以有多個變數，表達式中的每個變數為先前評估';
$string['preandpostfixesignored'] = '尤許數值回答，題目可以有多個變數，表達式中的每個變數為先前評估';
$string['questiontext'] = '題目文字及嵌入變數';
$string['questiontext_help'] = '您可以以題目文字、一般意見及答案意見及提示的方式嵌入變數名稱及表達式。

任何以雙括號完結的項目將會在顯示前評估。例如，當您輸入[[a]]，將顯示變數的數值； [[log(a)]]會顯示log的a。

您可以使用printf編碼來指定如何顯示結果。例如， [[a,.3e]] ，將會以科學記數法及4個帶效數字的形式顯示。';
$string['randomseed'] = '串來充當隨機種子';
$string['recalculatenow'] = '現在重新計算';
$string['requirescinotation'] = '需要科學記數法';
$string['sigfigs'] = '有效數字';
$string['syserrorpenalty'] = '每個錯誤扣減';
$string['unspecified'] = '沒指明';
$string['usesuperscript'] = '使用下標條目';
$string['varheader'] = '變數{no}';
$string['variables'] = '變數';
$string['variant'] = '變數的數值{$a}';
$string['variants'] = '變數的數值';
$string['variants_help'] = '在此輸入"預設變數"的數值或 如果這是一個"計算變數"，您可以在此看見顯示的已計算的數值。

至於"預設變數"，您必須在至少一道題目中輸入數值及輸入相同數目的格數。

Moodle會從已填寫的預設變數數值中，自動決定每道題目有多少變數。如果沒有預設變數，我們將會假設為5個題目變數。您不需要填寫用最後的空格，有需要的時候您可以增加題目中的變數數值。';
$string['varname'] = '名稱或作業';
$string['varname_help'] = '至於「預設變數」，在意您只輸入變數的名稱，例如 \'a\'。然後在下列的每道題目中輸入此變數的數值。

或對於「已計算變數」，輸入變數名稱及從表述中分配其數值，例如\'b = a^4\' （\'a\'是原先界定的變數）。

如果您留空此框，下列的數值將會被無視。';
$string['varnumericset'] = '變數數值集';
$string['varnumericset_help'] = '要回答題目，回答者輸入數字。

在此題目中用作計算的數字是從預設集中選擇，亦可以預先從數式中運算。

所有表達是在創建題目時計算，隨機函數的數值將會是每個用戶皆相同。至於沒有變數的題目，表達在過程中計算及每個用戶的隨機數值，查看「變數數字」的題型。';
$string['varnumericsetsummary'] = '允許數字回覆，題目可以有多個「變數」，表達中的變數會預先評估。';
$string['vartypecalculated'] = '已計算變數';
$string['vartypepredefined'] = '預設變數';
$string['youmustprovideavalueforallvariants'] = '請在所有變數中預設輸入相同數目的空隔。例如，您需要的題目變數中預設變數的數值';
$string['youmustprovideavalueforatleastonevariant'] = '您必須在此提供一個數值。';
