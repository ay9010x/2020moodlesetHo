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
 * Strings for component 'qtype_ddmarker', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   qtype_ddmarker
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmoreitems'] = '新增{no}個空白項目';
$string['alttext'] = '替代文字';
$string['answer'] = '答案';
$string['bgimage'] = '背景圖像';
$string['clearwrongparts'] = '把放置錯誤的項目移回到圖像下方的預設起點位置';
$string['coords'] = '座標';
$string['correctansweris'] = '正確答案是：{$a}';
$string['draggableimage'] = '可拖拉圖像';
$string['draggableitem'] = '可拖拉項目';
$string['draggableitemheader'] = '可拖拉項目{$a}';
$string['draggableitemtype'] = '類型';
$string['draggableword'] = '可拖拉的文字';
$string['dropbackground'] = '讓拖拉項目放在上面的背景圖像';
$string['dropzone'] = '放置區 {$a}';
$string['dropzoneheader'] = '放置區';
$string['dropzones'] = '放置區';
$string['dropzones_help'] = '放置區域是以輸入座標的方式來界定，當你輸入座標時，上方的預覽會立即更新，所以你可以用嘗試並改進的方式來移動各樣東西的位置。以下是各種形式的放置區的寫法；注意，要使用英文的逗點及分號。

*圓形： 圓心的xy座標; 半徑<br>例如：<code>80, 100; 50</code>
*多角形： 每一個角的座標x1, y1; x2, y2; ...; xn, yn<br>例如：<code>20, 60; 100, 60; 20, 100</code>
*四方形：左上角的xy座標 ;寬, 高<br>例如：<code>20, 60; 80, 40</code>';
$string['followingarewrong'] = '下列的標誌被放在錯誤的區域：{$a}';
$string['followingarewrongandhighlighted'] = '下列的標誌放置錯誤：{$a}。被醒目提示的標誌現在已顯示在正確位置。
點選這標誌可以提示這允許的區域。';
$string['formerror_nobgimage'] = '你需要選擇一個圖像作為這一拖拉區域的背景。';
$string['formerror_noitemselected'] = '你已經指定一個放置區，但還沒有選擇一個必須拖放到這放置區的標誌。';
$string['formerror_nosemicolons'] = '你的座標字串中沒有分號。 {$a->shape}的座標應該寫成{$a->coordsstring}。';
$string['formerror_onlysometagsallowed'] = '在一個標誌的說明中只能有"{$a}"個標籤';
$string['formerror_onlyusewholepositivenumbers'] = '請只使用"正整數"來指定形狀的x,y座標和/或寬與高。您的 {$a->shape}座標應該寫成 - {$a->coordsstring}。';
$string['formerror_polygonmusthaveatleastthreepoints'] = '就多角形而言，你至少需要指定三個端點。您的 {$a->shape}座標應該寫成 - {$a->coordsstring}。';
$string['formerror_repeatedpoint'] = '你將相同的座標寫了兩次，請刪除重複的。您的{$a->shape}座標應該寫成 - {$a->coordsstring}。';
$string['formerror_shapeoutsideboundsofbgimage'] = '你所定義的形狀超出了這背景圖像的界線';
$string['formerror_toomanysemicolons'] = '你所指定的座標中有太多以分號分隔的部分。您的{$a->shape}座標應該寫成 - {$a->coordsstring}。';
$string['formerror_unrecognisedwidthheightpart'] = '無法辨識你所指定的寬與高。 您的{$a->shape}座標應該寫成 - {$a->coordsstring}。';
$string['formerror_unrecognisedxypart'] = '無法辨識你所指定的x,y座標。 您的{$a->shape}座標應該寫成 -
{$a->coordsstring}。';
$string['infinite'] = '可重複被選';
$string['marker'] = '標誌';
$string['marker_n'] = '標誌{no}';
$string['markers'] = '標誌';
$string['nolabel'] = '沒有標籤文字';
$string['noofdrags'] = '數目';
$string['pleasedragatleastonemarker'] = '你的作答還沒有完成，你必須至少放一個標誌到這圖像上';
$string['pluginname'] = '拖放標誌題';
$string['pluginnameadding'] = '新增拖放標誌題';
$string['pluginnameediting'] = '編輯拖放標誌題';
$string['pluginname_help'] = '拖放標誌題要求作答者依照試題敘述把一些文字標籤拖放到指定的背景圖上的放置區。';
$string['pluginnamesummary'] = '標誌是被拖放到一個背景圖像上';
$string['previewareaheader'] = '預覽';
$string['previewareamessage'] = '選擇一個背景圖檔，輸入各種標誌的文字標籤，並在背景圖像上界定放置區，最後界定各個標誌與放置區的對應關係。';
$string['refresh'] = '刷新預覽';
$string['shape'] = '形狀';
$string['shape_circle'] = '圓圈';
$string['shape_circle_coords'] = 'x,y;r (在此 x,y 圓心的xy座標，而 r 是半徑)';
$string['shape_circle_lowercase'] = '圓圈';
$string['shape_polygon'] = '多邊形';
$string['shape_polygon_coords'] = 'x1,y1;x2,y2;x3,y3;x4,y4....(在此 x1, y1是第一個頂點的x,y座標， x2,y2是第二個頂點的x,y座標，等等 。你不需要重複第一個頂點的座標來關閉這個多邊形)';
$string['shape_polygon_lowercase'] = '多邊形';
$string['shape_rectangle'] = '四方形';
$string['shape_rectangle_coords'] = 'x,y; w,h（其中x,y是四方形左上角的XY坐標，w和h是四方形的寬度和高度）';
$string['shape_rectangle_lowercase'] = '四方形';
$string['showmisplaced'] = '若有放置區沒有正確標誌放進裡面，就做醒目提示';
$string['shuffleimages'] = '每次試題被作答之後，拖拉項目就重新隨機排列';
$string['stateincorrectlyplaced'] = '說明哪些個標誌是放置錯誤';
$string['summariseplace'] = '{$a->no}. {$a->text}';
$string['summariseplaceno'] = '放置區{$a}';
$string['ytop'] = '頂';
