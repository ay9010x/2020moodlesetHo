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
 * Strings for component 'tool_customlang', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_customlang
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['checkin'] = '儲存字串到語言包';
$string['checkout'] = '由語言包抓取字串到翻譯器';
$string['checkoutdone'] = '字串已經順利抓取到翻譯器中';
$string['checkoutinprogress'] = '正在抓取字串到翻譯器中....';
$string['confirmcheckin'] = '你即將儲存翻譯或修改過的字串到本地語言包。
這將會從翻譯器中匯出客製化的字串到資料目錄，且 Moodle 將會開始使用修改過的字串。
按下"繼續"來進行寫入動作。';
$string['customlang:edit'] = '編輯本地翻譯';
$string['customlang:view'] = '檢視本地翻譯';
$string['filter'] = '篩選字串';
$string['filtercomponent'] = '顯示有這些成分的字串';
$string['filtercustomized'] = '只顯示客製化過的';
$string['filtermodified'] = '只顯示剛修改過的';
$string['filteronlyhelps'] = '只顯示幫助說明檔';
$string['filtershowstrings'] = '顯示字串';
$string['filterstringid'] = '字串辨識代號';
$string['filtersubstring'] = '只顯示字串中包含';
$string['headingcomponent'] = '檔案名稱';
$string['headinglocal'] = '本地客製化';
$string['headingstandard'] = '標準文字';
$string['headingstringid'] = '字串';
$string['markinguptodate'] = '把這客製化標示為最新';
$string['markinguptodate_help'] = '如果你網站上的字串已經客製化之後，英文原初語言包或主要語言包有被修改，那這客製化的翻譯有可能會過時。
請審閱你的客製化翻譯，如果你發現它沒錯，勾選這方格，否則重新編輯它。';
$string['markuptodate'] = '標示為最新';
$string['modifiedno'] = '找不到修改過的字串來儲存';
$string['modifiednum'] = '有{$a} 個剛修改過的字串，你必須把它們寫入硬碟以永久保存。';
$string['nostringsfound'] = '沒發現符合的字串，請修改篩選條件的設定';
$string['placeholder'] = '替換項';
$string['placeholder_help'] = '替換項是字串中的一種特殊代碼，像是 `{$a}` 或 `{$a-something}` 。

當顯示在螢幕時，它們會被數值或文字所替換掉。

翻譯時要將它從原初字串中精確地複製過去。不要去翻譯它們或改變由左到右的文字走向';
$string['placeholderwarning'] = '字串包含一替換項';
$string['pluginname'] = '自訂語言';
$string['savecheckin'] = '儲存並將字串存入檔案(語言包)';
$string['savecontinue'] = '套用更改並繼續編輯';
