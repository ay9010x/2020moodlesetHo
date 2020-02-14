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
 * Strings for component 'choice', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   choice
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addmorechoices'] = '增加更多選項';
$string['allowmultiple'] = '允許選擇一個以上的選項';
$string['allowupdate'] = '是否允許變更選擇';
$string['answered'] = '已回答';
$string['atleastoneoption'] = '您至少要提供一個可能的選項';
$string['cannotsubmit'] = '抱歉，在提交你的投票時發生問題，請再試一次。';
$string['choice'] = '選擇 {$a}';
$string['choiceactivityname'] = '投票：{$a}';
$string['choice:addinstance'] = '新增一個票選活動';
$string['choice:choose'] = '紀錄一個選擇';
$string['choiceclose'] = '結束時間';
$string['choicecloseson'] = '投票關閉於{$a}';
$string['choice:deleteresponses'] = '刪除回應';
$string['choice:downloadresponses'] = '下載回應';
$string['choicefull'] = '此一選項已經額滿，已無可用空間。';
$string['choicename'] = '票選名稱';
$string['choiceopen'] = '開始時間';
$string['choiceoptions'] = '票選的選項';
$string['choiceoptions_help'] = '<p align="center"><b>選項</b></p>
　　
<p>在這裏您可以指定參與者可以選擇的選項。</p>
　　
<p>您可以指定任意數目的選項，不需要將列出的選項位置全部填滿。</p>';
$string['choice:readresponses'] = '查看回應';
$string['choicesaved'] = '您的選擇已經儲存';
$string['choicetext'] = '選項說明';
$string['chooseaction'] = '選擇一個動作...';
$string['closebeforeopen'] = '你指定的投票結束時間早於開始時間';
$string['completionsubmit'] = '用戶進行票選之後，顯示為完成';
$string['description'] = '簡介文字';
$string['displayhorizontal'] = '水平顯示';
$string['displaymode'] = '這些選項的顯示方式';
$string['displayvertical'] = '垂直顯示　';
$string['eventanswercreated'] = '票選已建立';
$string['eventanswerdeleted'] = '投票結果已刪除';
$string['eventanswerupdated'] = '票選已更新';
$string['eventreportdownloaded'] = '投票報告已下載';
$string['eventreportviewed'] = '票選報告已檢視';
$string['expired'] = '抱歉，此項活動已經在{$a}關閉，不再開放使用。';
$string['full'] = '(已滿)';
$string['havetologin'] = '在提出你的票選前，您必須先登入';
$string['includeinactive'] = '包含來自停止活動/已經休學用戶的作答資料';
$string['limit'] = '限制';
$string['limitanswers'] = '限制回答的次數';
$string['limitanswers_help'] = '<p align="center"><b>人數限制</b></p>
　　
<p>這個選項允許您限制選擇某一選項的參與者數目。這功能適合用來做有人數上限的志願分組</p>
　　
<p>一旦限制功能被啟動，每個選項都可以設定一個最高限制數字。當到達此限制數字後，就沒有人可以再選這個選項。如果限制數字為0，則表示沒有人可以選這個選項。</p>
　　
<p>如果限制功能被關閉，則不會限制選擇某一選項的人數。</p>';
$string['limitno'] = '限制{no}';
$string['modulename'] = '票選';
$string['modulename_help'] = '這一票選活動模組能讓教師自擬一個問題，並做出幾個選項，由學生在線上投票選擇。

教師也可以把選項變成組別(任務)名稱，然後限制每組人數，就可以把它變成學生自由選組的活動。

票選的結果可以設定成：1.在學生投票後顯示、2.在指定日期後顯示，3.完全不對學生顯示。顯示結果時可以顯示學生姓名或是匿名。

票選活動可以用於：

* 做單一問題的意見調查，以刺激學生思考。
* 當作只有一個單選擇題的超簡單小考(有正確答案)。
* 當作班會的投票活動，例如票選郊遊地點、聚餐地點、班級幹部等。
* 在進行分組專題研究時，讓學生自由選組。';
$string['modulenameplural'] = '票選活動';
$string['moveselectedusersto'] = '把選出的用戶搬移到.....';
$string['multiplenotallowederror'] = '這一調查不允許選擇多個選項';
$string['mustchooseone'] = '儲存前必須先選擇一個答案，儲存失敗。';
$string['noguestchoose'] = '抱歉，訪客不能參與票選活動。';
$string['noresultsviewable'] = '目前無法檢視票選結果';
$string['notanswered'] = '尚未回答';
$string['notenrolledchoose'] = '抱歉，只有選課的用戶才能投票';
$string['notopenyet'] = '抱歉，這個活動在{$a}之前不能使用';
$string['numberofuser'] = '回應的人數';
$string['numberofuserinpercentage'] = '回應人數的百分比';
$string['option'] = '選項';
$string['optionno'] = '選項{no}';
$string['options'] = '選項';
$string['page-mod-choice-x'] = '任何票選模組頁面';
$string['pluginadministration'] = '票選管理';
$string['pluginname'] = '票選活動';
$string['previewonly'] = '這只是這一活動的可用選項的預覽。你要等到{$a}才可以進行投票。';
$string['privacy'] = '結果檢視設定';
$string['publish'] = '公佈結果';
$string['publishafteranswer'] = '學生投票後顯示結果';
$string['publishafterclose'] = '票選結束後才顯示結果';
$string['publishalways'] = '隨時對學生顯示結果';
$string['publishanonymous'] = '匿名公佈結果，不顯示學生姓名';
$string['publishnames'] = '完全公開結果，顯示學生姓名及其選擇';
$string['publishnot'] = '不向學生公佈結果';
$string['removemychoice'] = '移除我的選擇';
$string['removeresponses'] = '移除全部的票選結果';
$string['responses'] = '答覆';
$string['responsesresultgraphheader'] = '顯示圖表';
$string['responsesto'] = '選擇{$a}的';
$string['results'] = '票選結果';
$string['savemychoice'] = '儲存我的選擇';
$string['search:activity'] = '投票--活動資訊';
$string['showpreview'] = '顯示預覽';
$string['showpreview_help'] = '在投票活動開放投票之前，允許學生預覽可用的選項。';
$string['showunanswered'] = '顯示尚未投票的人';
$string['skipresultgraph'] = '跳過結果圖表';
$string['spaceleft'] = '個可用空間';
$string['spacesleft'] = '個可用空間';
$string['taken'] = '已選';
$string['timerestrict'] = '投票的時間限制';
$string['userchoosethisoption'] = '有選擇這一項的用戶';
$string['viewallresponses'] = '查看{$a}個回應';
$string['withselected'] = '和被選出的';
$string['yourselection'] = '您的選擇';
