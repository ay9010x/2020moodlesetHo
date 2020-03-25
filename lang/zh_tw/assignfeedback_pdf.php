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
 * Strings for component 'assignfeedback_pdf', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   assignfeedback_pdf
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addquicklist'] = '添加到評論的簡易清單';
$string['allowpdffeedback'] = '啟用';
$string['annotatesubmission'] = '批註提交的作業';
$string['annotationhelp'] = '批註功能的說明';
$string['annotationhelp_text'] = '<table>
<thead><tr><th>控制</th><th>鍵盤快速鍵</th><th>說明</th></tr></thead>
<tr><td>{$a->save}</td><td> </td><td>關閉批註功能而不要產生回應的PDF檔案(注意，所有的批註都會立刻被儲存)</td></tr>
 <tr><td>{$a->generate}</td><td> </td><td>產生一個已被批註的PDF檔案，以供學生下載<td><tr>
 <tr><td>尋找評論</td><td> </td><td>直接跳到先前輸入的評論(在此作業上)並以醒目顏色標示。</td></tr>
 <tr><td>顯示先前的</td><td> </td><td>從這一課程的其他作業取用現成的評論(在一副框中)給這一位學生看。</td></tr>
 <tr><td><-- 前一頁</td><td>p</td><td>檢視前一頁面</td></tr>
 <tr><td>下一頁 --></td><td>n</td><td>檢視下一頁面</td></tr>
 <tr><td>背景顏色</td><td>[ and ]</td><td>變更評論方塊的填充顏色 (也可以使用在評論上按滑鼠右鍵的方式)</td></tr>
 <tr><td>線的顏色</td><td>{ and }</td><td>變更批註的顏色</td></tr>
 <tr><td>選擇戳記</td><td> </td><td>選擇要使用的戳記以供戳記工具使用(可以在伺服器的 \'pix/stamps\'資料夾中添加新的戳記</td></tr>
 <tr><td>{$a->comment}</td><td>c</td><td>在頁面上點選，可以添加一個評論方塊，輸入評論，然後再點選一次就可以儲存。點選在評論上上就可以編輯，拖拉就可以移動。按滑鼠右鍵就可以更改顏色、儲存到簡易清單或刪除 (或以刪除文字來刪除)。</td></tr>
 <tr><td>{$a->line}</td><td>l</td><td>點選 + 拖拉 (或點選, 移動, 點選) 可在頁面上畫出一條直線。</td></tr>
 <tr><td>{$a->rectangle}</td><td>r</td><td>點選 + 拖拉 (或點選, 移動, 點選) 可在頁面上畫出一個四方形。</td></tr>
 <tr><td>{$a->oval}</td><td>o</td><td>點選 + 拖拉 (或點選, 移動, 點選) 可在頁面上畫出一個憜圓形。</td></tr>
 <tr><td>{$a->freehand}</td><td>f</td><td>點選 + 拖拉 可在頁面上畫出一個手畫隨意線條。</td></tr>
 <tr><td>{$a->highlight}</td><td>h</td><td>點選 + 拖拉 (或點選, 移動, 點選) 可在頁面內容上畫出一個半透明的強調顏色。 </td></tr>
 <tr><td>{$a->stamp}</td><td>s</td><td>以點選來插入被選出的戳記(預設大小)。 點選 + 拖拉 可插入不同大小的戳記</td></tr>
 <tr><td>{$a->erase}</td><td>e</td><td>點選在批註 (不是評論)上來擦拭它</td></tr>
 <tr><td>簡易清單</td><td> </td><td>在頁面上使用滑鼠右鍵來插入先前儲存在\'簡易清單\'上的評論。使用 \'x\' 來刪除簡易清單上不要的項目。</td></tr>
 </table>';
$string['backtocommentlist'] = '回到評論清單';
$string['badaction'] = '無效的動作 {$a}';
$string['badannotationid'] = '批註編號是用來區分不同的提交作業或頁面';
$string['badcommentid'] = '評論編號是用來區分不同的提交作業或頁面';
$string['badcoordinate'] = '座標的數量是奇數的---應該是每一個點2個座標';
$string['badpath'] = '路徑指向無效';
$string['badtype'] = '無效的類型 {$a}';
$string['cancel'] = '取消';
$string['clearimagecache'] = '清除頁面圖像的快取';
$string['colourblack'] = '黑';
$string['colourblue'] = '青';
$string['colourclear'] = '淡藍';
$string['colourgreen'] = '綠';
$string['colourred'] = '紅';
$string['colourwhite'] = '白';
$string['colouryellow'] = '黃';
$string['comment'] = '評論';
$string['commentcolour'] = '[,] - 評論的背景顏色';
$string['commenticon'] = 'c - 添加評論 ，按Ctrl可以畫線';
$string['deletecomment'] = '刪除評論';
$string['deleteresponse'] = '刪除回應';
$string['deleteresponseconfirm'] = '您確定您要刪除在 \'{$a->assignmentname}\'作業上給{$a->username}的回應？';
$string['downloadoriginal'] = '下載原初提交作業的PDF檔案';
$string['downloadresponse'] = '下載回應';
$string['draftsaved'] = '草稿已儲存';
$string['emptyquicklist'] = '簡易檢清單上沒任何項目';
$string['emptyquicklist_instructions'] = '在評論上按滑鼠右鍵，可將它複製到簡易清單上';
$string['emptysubmission'] = '沒有任何提交作業';
$string['enabled'] = 'PDF型式之回饋';
$string['enabled_help'] = '此選項允許教師在線上批註學生提交的PDF檔案，並退還此批註過的PDF檔案給學生。';
$string['eraseicon'] = 'e - 擦除直線和形狀';
$string['errorgenerateimage'] = '無法產生影像 - 細節: {$a}';
$string['errormessage'] = '錯誤訊息：';
$string['errornosubmission'] = '企圖為不存在的提交作業建立圖像';
$string['errornosubmission2'] = '無法找到提交的PDF檔';
$string['errortempfolder'] = '無法建立暫時資料夾';
$string['findcomments'] = '尋找評論';
$string['findcommentsempty'] = '沒有評論';
$string['freehandicon'] = 'f - 以手畫線';
$string['generateresponse'] = '產生回應檔案';
$string['gspath'] = 'Ghostscript 路徑';
$string['gspath2'] = '在大多數安裝Linux的系統上，它是\'/usr/bin/gs\'。在安裝Windows的系統上，它有時候像是 \'c:gsbingswin32c.exe\' (要確定在路徑上沒有空格--若有必要，把\'gswin32c.exe\' 和 \'gsdll32.dll\' 複製到一個其路徑沒有空格的新資料夾)。';
$string['highlighticon'] = 'h - 醒目標示文字';
$string['imagefor'] = '{$a}的圖像檔';
$string['jsrequired'] = '在您的瀏覽器上必須啟用Javascript ，教師才能在PDF檔上進行批註';
$string['keyboardnext'] = 'n -下一頁';
$string['keyboardprev'] = 'p - 前一頁';
$string['linecolour'] = '{,} - 直線顏色';
$string['lineicon'] = 'l - 直線';
$string['missingannotationdata'] = '沒有批註資料';
$string['missingcommentdata'] = '沒有評論資料';
$string['missingquicklistdata'] = '沒有簡易清單資料';
$string['next'] = '下一個';
$string['nocomments'] = '沒有評論';
$string['nogroup'] = '沒有群組';
$string['okagain'] = '點選OK再嘗試一次';
$string['openlinknewwindow'] = '在新視窗中開啟連結';
$string['opennewwindow'] = '在新視窗中開啟這一頁';
$string['ovalicon'] = 'o-橢圓形';
$string['pagenumber'] = '頁碼';
$string['pagenumbertoobig'] = '要求的頁碼大於總頁數 ({$a->pageno} > {$a->pagecount})';
$string['pagenumbertoosmall'] = '要求的頁碼數字太小(<1)';
$string['pdf'] = 'PDF回饋';
$string['pluginname'] = 'PDF回饋';
$string['previous'] = '先前';
$string['previousnone'] = '無';
$string['quicklist'] = '評論的簡易清單';
$string['rectangleicon'] = 'r -  四方型';
$string['resend'] = '重新送出';
$string['responsefor'] = '{$a}的回應檔案';
$string['responseok'] = '回應檔案已經產生';
$string['responseproblem'] = '在產生回應檔案時出了問題';
$string['savedraft'] = '儲存批註的草稿';
$string['servercommfailed'] = '伺服器溝通失敗---您是否要重新送出此訊息？';
$string['showpreviousassignment'] = '顯示先前作業';
$string['stamp'] = '選擇戳記';
$string['stampicon'] = 's - 戳記';
$string['test_doesnotexist'] = '這ghostscript路徑指向一個不存在的檔案';
$string['test_empty'] = '這ghostscript路徑是空的---請輸入正確的路徑';
$string['testgs'] = '測試ghostscript路徑';
$string['test_isdir'] = '這ghostscript路徑指向一個資料夾，請在您指定的路徑上包含這個 ghostscript程式';
$string['test_notestfile'] = '找不到測試用的PDF檔';
$string['test_notexecutable'] = '這 ghostscript路徑所指向的檔案是不可以執行的';
$string['test_ok'] = '這 ghostscript路徑似乎沒有問題 -- 請檢查您是否能看到圖像下方的訊息';
$string['viewresponse'] = '在線上檢視回應';
