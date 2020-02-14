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
 * Strings for component 'qcreate', language 'zh_tw', branch 'MOODLE_30_STABLE'
 *
 * @package   qcreate
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityclosed'] = '活動結束了。';
$string['activitygrade'] = '這個活動您已經獲得總成績的 {$a->grade} / {$a->outof} 了。';
$string['activityname'] = '活動名稱';
$string['activityopen'] = '活動開始了，';
$string['addminimumquestionshdr'] = '各題型要求的數量(選擇性設定)';
$string['addmorerequireds'] = '新增更多必要的題型';
$string['allandother'] = '要允許所有題型，勾選\'{$a}\'及不要勾選其他。';
$string['allowall'] = '全部題型都可以';
$string['allowedqtypes'] = '列入評分的題型';
$string['allowedqtypes_help'] = '您可以在此指定允許的題型。如果您選擇「允許所有題型」，學生可以在「總計已評分題目」指定的所有題型上限。';
$string['allquestions'] = '0 - (全部試題)';
$string['alreadydone'] = '您已經建立了{$a}題這類型試題。';
$string['alreadydoneextra'] = '您建立了{$a}題各類型試題，';
$string['alreadydoneextraone'] = '您已額外建立了一題這類型試題。';
$string['alreadydoneone'] = '您已建立了一題這類型試題。';
$string['and'] = '{$a}及';
$string['andmorenewquestions'] = '和 {$a} 更多新題目。';
$string['automaticgrade'] = '因為您已經完成要求建立 {$a->required} 題中的  {$a->done}  題了，所以您獲得這些試題自動評分部分的 {$a->grade} / {$a->outof} 分數。';
$string['availability'] = '活動期間';
$string['betterthangrade'] = '試題獲得評分分數等於或高過';
$string['clickhere'] = '點按這裡建立\'{$a}\'類型的試題。';
$string['clickhereanother'] = '點按這裡建立\'{$a}\'類型的另一題。';
$string['close'] = '活動結束';
$string['closeon'] = '結束於';
$string['comma'] = '{$a},';
$string['comment'] = '評論';
$string['completionquestions'] = '學生必須建立：';
$string['completionquestionsgroup'] = '必須要製作試題';
$string['completionquestions_help'] = '如果啟用，當學生已建立該數目的題目（不論已否評分），此活動將會視為完成。';
$string['confirmdeletequestion'] = '您確定您要刪除這試題？';
$string['createdquestions'] = '建立試題';
$string['creating'] = '建立試題';
$string['deletegrades'] = '刪除已創建的題目及手動成績。';
$string['donequestionno'] = '您已完成 {$a->done} 中 {$a->no} 題型 \'{$a->qtypestring}\'. 下列為清單。';
$string['eventeditpageviewed'] = '已檢視編輯試題共創的頁面';
$string['eventoverviewviewed'] = '試題共創活動概觀';
$string['eventquestiongraded'] = '已評分的試題';
$string['eventquestionregraded'] = '重新評分的試題';
$string['exportgood'] = '匯出好試題';
$string['exportgoodquestions'] = '匯出高於指定分數的試題';
$string['exportnaming'] = '匯出的試題名稱前面加上';
$string['exportquestions'] = '試題匯出成檔案';
$string['exportselection'] = '只匯出這些試題';
$string['extraqdone'] = '您已額外建立了一題試題。';
$string['extraqgraded'] = '以下任何類型中的一種試題都將列入評分';
$string['extraqsdone'] = '您建立了{$a->extraquestionsdone}題各類型試題。';
$string['extraqsgraded'] = '以下任何類型的 {$a->extrarequired}題試題將列入評分';
$string['fullstop'] = '{$a}.';
$string['grade'] = '成績';
$string['gradeallautomatic'] = '所有成績皆為自動，沒有手動評分。';
$string['gradeallmanual'] = '成績完全由老師評分，沒有自動評分。';
$string['gradeavailablehtml'] = '{$a->username}已評核了您創建的題目\'<i>{$a->questionname}</i>\'的 \'<i>{$a->qcreate}</i>\'<br /><br />
您可以在此<a href="{$a->url}">活動頁面中查看</a>。';
$string['gradeavailablesmall'] = '{$a->username}已評核了您創建的題目{$a->qcreate}';
$string['gradeavailabletext'] = '{$a->username}已評核了您創建的題目{$a->qcreate}

您可以活動頁面中查看：


{$a->url}';
$string['graded'] = '已評分';
$string['grade_help'] = '這是匯報至成績簿中的活動總評分。可以設定成「沒有成績」以令到活動成為不評分。';
$string['grademixed'] = '評分是{$a->automatic}%自動，{$a->manual}%老師手動。';
$string['gradequestions'] = '評核題目';
$string['graderatio'] = '自動和老師評分比例';
$string['graderatio_help'] = '您可以在此指定如何分配總得分：自動評分為左邊，手動評分則在右邊。自動評分是由生成題目的系統給予的評分。';
$string['graderatiois'] = '自動和老師評分比例：{$a}';
$string['graderatiooptions'] = '{$a->automatic} / {$a->manual}';
$string['gradesdeleted'] = '已移除題目及手動成績';
$string['grading'] = '評分';
$string['intro'] = '介紹說明';
$string['invalidqcreatezid'] = '無效的題目創建ID';
$string['manualgrade'] = '針對您完成的試題，老師目前給您 {$a->grade} / {$a->outof} 的分數。';
$string['marked'] = '標記';
$string['messageprovider:gradernotification'] = '已創解題目的提示';
$string['messageprovider:studentnotification'] = '已評分題目的提示';
$string['minimumquestions'] = '最少題數';
$string['minimumquestions_help'] = '在這選單，您可以訂定學生應該要建立多少指定題型的試題。';
$string['modulename'] = '試題共創';
$string['modulename_help'] = '試題共創活動允許教師讓學生創建題目、需要題目的數目、可用的題型及指定每種題型需要的題目。';
$string['modulenameplural'] = '試題共創';
$string['needsgrading'] = '需要評分';
$string['needsregrading'] = '需要重新評分';
$string['needtoallowatleastoneqtype'] = '您需要至少允許一種題型';
$string['needtoallowqtype'] = '您需要允許題型 \'{$a}\'以需要創建至少此題型的數目。';
$string['newquestions'] = '新試題已建立';
$string['noofquestionstotal'] = '列入評分的總題數';
$string['noofquestionstotal_help'] = '這是學生必須創建的題目數量。此數目必須等於或大過需要試題的下限。';
$string['noquestions'] = '沒有已創建的題目';
$string['noquestionsabove'] = '在類別\'{$a->categoryname}\'中沒有高於手動成績{$a->betterthan}的題目。';
$string['notgraded'] = '尚未評分';
$string['notifications'] = '通知';
$string['nousers'] = '沒有就讀此課程式群組的用戶';
$string['open'] = '活動開始';
$string['open_help'] = '您可以指明可以創建題目的時間。在開始時間前及完結時間後，學生將不能夠創建題目。';
$string['openmustbemorethanclose'] = '活動的開始時間必須在活動結束時間之前';
$string['openon'] = '開始於';
$string['overview'] = '總覽';
$string['pagesize'] = '每頁顯示多少試題';
$string['pluginadministration'] = '試題共創管理';
$string['pluginname'] = '試題共創';
$string['preview'] = '預覽';
$string['previewquestion'] = '預覽題目';
$string['qcreate'] = 'qcreate';
$string['qcreate:addinstance'] = '新增一個試題共創活動';
$string['qcreatecloses'] = '關閉試題共創活動';
$string['qcreate:grade'] = '評核題目';
$string['qcreateopens'] = '開啟試題共創活動';
$string['qcreate:receivegradernotifications'] = '收取評分者提示';
$string['qcreate:receivestudentnotifications'] = '收取學生提示';
$string['qcreate:submit'] = '提交試題';
$string['qcreate:view'] = '查看創建題目活動';
$string['qsgraded'] = '{$a} 將會評核此題型:';
$string['qtype'] = '試題類型';
$string['qtype_help'] = '在此菜單，您可以指明學生需要創建的題型。';
$string['questions'] = '試題以完成此活動。';
$string['questionscreated'] = '建立了試題{$a}';
$string['questiontogradehtml'] = '{$a->username} 已創建了新題目 <i>\'{$a->questionname}\'</i>
至於 <i>\'{$a->qcreate}\'  在 {$a->timeupdated}</i><br /><br />
是<a href="{$a->url}">在網站中</a>.';
$string['questiontogradesmall'] = '{$a->username} 已為{$a->qcreate}創建了新題目';
$string['questiontogradetext'] = '{$a->username} 在{$a->timeupdated}已為{$a->qcreate}創建了新題目。

可以在這裡找到：

    {$a->url}';
$string['requiredanyplural'] = '需要{$a->no} 任何題型的題目';
$string['requiredanysingular'] = '需要任何題型的題目';
$string['requiredplural'] = '需要此\'{$a->qtypestring}\' 題型中的{$a->no} 道題目';
$string['requiredquestions'] = '必須要建立的試題：';
$string['requiredsingular'] = '需要此題型 \'{$a->qtypestring}\' 的題目';
$string['saveallfeedback'] = '儲存我全部的回饋';
$string['saveallfeedbackandgrades'] = '儲存全部成績和回饋';
$string['selectone'] = '請選擇...';
$string['sendgradernotifications'] = '通知老師';
$string['sendgradernotifications_help'] = '如果啟用，當學生建立一個新試題時，評分者(通常是教師)會收到一則通知訊息。訊息傳送方式可另外設定。';
$string['sendstudentnotifications'] = '通知學生';
$string['sendstudentnotifications_help'] = '如果啟用，當試題被評分後，學生會收到一則通知訊息。';
$string['show'] = '顯示';
$string['showgraded'] = '不用評分的試題';
$string['showneedsregrade'] = '需要重新評分的試題';
$string['showungraded'] = '需要評分的試題';
$string['specifictext'] = '特定文字';
$string['studentaccessaddonly'] = '只能建立';
$string['studentaccessedit'] = '預覽/檢視/另存新題/編輯/刪除';
$string['studentaccessheader'] = '學生試題存取權限';
$string['studentaccesspreview'] = '預覽';
$string['studentaccesssaveasnew'] = '預覽/檢視/另存新題';
$string['studentqaccess'] = '針對自己的試題';
$string['studentqaccess_help'] = '使用此菜單以界定學生在他們自己創建的題目中的權限';
$string['studentshavedone'] = '學生已經建立了 {$a} 題試題。';
$string['synchronizeqaccesstask'] = '同步學生題目的進入權限';
$string['timeclose'] = '活動關閉於 {$a->timeclose}';
$string['timeclosed'] = '活動關閉於 {$a}。';
$string['timecreated'] = '試題建立的時間';
$string['timenolimit'] = '沒有時間限制。';
$string['timeopen'] = '活動開始於{$a->timeopen}';
$string['timeopenclose'] = '活動從{$a->timeopen}開始到{$a->timeclose}關閉';
$string['timeopened'] = '活動開始於 {$a}。';
$string['timewillclose'] = '活動將於 {$a} 關閉。';
$string['timewillopen'] = '活動將於 {$a} 開始。';
$string['timing'] = '活動時間';
$string['todoquestionno'] = '您需要完成此題型\'{$a->qtypestring}\' 中的{$a->stillrequiredno} 道題目。';
$string['totalgrade'] = '總成績';
$string['totalgradeis'] = '總成績：{$a}';
$string['totalrequiredislessthansumoftotalsforeachqtype'] = '總共需要的題型少於總共已評分的題型。<br /> 必須相同或更多。';
$string['updategradestask'] = '更新試題共創活動成績';
$string['username'] = '建立試題者的帳號';
$string['youhavedone'] = '您已經建立了 {$a} 題試題。';
$string['youvesetmorethanonemin'] = '您在題型\'{$a}\'已設定多於一個的題目下限。';
