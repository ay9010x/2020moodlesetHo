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
 * Strings for component 'assign', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   assign
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityoverview'] = '您有作業需要注意';
$string['addattempt'] = '允許另一次的繳交';
$string['addnewattempt'] = '新增一個繳交管道';
$string['addnewattemptfromprevious'] = '依據先前的作業新增一個繳交管道';
$string['addnewattemptfromprevious_help'] = '這將會複製你先前作業的內容，到一個新作業上，供讓你修改使用。';
$string['addnewattempt_help'] = '這將會建立一個新的空白作業，供你使用。';
$string['addsubmission'] = '繳交作業';
$string['allocatedmarker'] = '指派評分者';
$string['allocatedmarker_help'] = '被指派來評這份作業的評分者';
$string['allowsubmissions'] = '允許學生繼續繳交該項作業。';
$string['allowsubmissionsanddescriptionfromdatesummary'] = '作業的詳細說明和繳交的表單將從<strong> {$a} </ strong>開始可以使用';
$string['allowsubmissionsfromdate'] = '開始繳交時間';
$string['allowsubmissionsfromdate_help'] = '如果啟用了此選項，在此日期前，學生不能繳交作業。如果停用此選項，則學生馬上就可以繳交作業。';
$string['allowsubmissionsfromdatesummary'] = '這個作業將從<strong> {$a} </ strong>開始可以繳交';
$string['allowsubmissionsshort'] = '允許變更作業';
$string['alwaysshowdescription'] = '隨時顯示作業說明';
$string['alwaysshowdescription_help'] = '如果停用，上述作業說明將在開始繳交時間起，才會讓學生看到。';
$string['applytoteam'] = '將分數和回饋套用到整個群組';
$string['assign:addinstance'] = '新增作業';
$string['assign:editothersubmission'] = '編輯另一學生的作業';
$string['assign:exportownsubmission'] = '匯出自己的作業';
$string['assignfeedback'] = '回饋外掛';
$string['assignfeedbackpluginname'] = '回饋外掛';
$string['assign:grade'] = '作業評分';
$string['assign:grantextension'] = '准許延期';
$string['assign:manageallocations'] = '指派評分者要評閱那些作業';
$string['assign:managegrades'] = '檢查並公布分數';
$string['assignmentisdue'] = '作業繳交已截止';
$string['assignmentmail'] = '{$a->grader}已經給您的作業 “{$a->assignment}”意見回饋。
您可以在作業後面看到它。網址如下：
{$a->url}';
$string['assignmentmailhtml'] = '{$a->grader}已經在您繳交的作業 “<i>{$a->assignment}</i>"上提供回饋。<br />
<br />　您可以在<a href="{$a->url}">繳交的作業</a>的後面看到這些回饋。';
$string['assignmentmailsmall'] = '{$a->grader}已經在您繳交的作業 “<i>{$a->assignment}</i>"上提供回饋。<br />
<br />　您可以在<a href="{$a->url}">繳交的作業</a>的後面看到這些回饋。';
$string['assignmentname'] = '作業名稱';
$string['assignmentplugins'] = '作業外掛';
$string['assignmentsperpage'] = '每頁的作業數';
$string['assign:receivegradernotifications'] = '接收評分者提交的通知';
$string['assign:releasegrades'] = '公布成績';
$string['assign:revealidentities'] = '揭示學生身份';
$string['assign:reviewgrades'] = '檢查成績';
$string['assignsubmission'] = '作業繳交外掛';
$string['assignsubmissionpluginname'] = '作業繳交外掛';
$string['assign:submit'] = '繳交作業';
$string['assign:view'] = '檢視作業';
$string['assign:viewblinddetails'] = '當啟用彌封評閱時，只能看檢視學生編號';
$string['assign:viewgrades'] = '檢視成績';
$string['attemptheading'] = '第{$a->attemptnumber}次作業提交 : {$a->submissionsummary}';
$string['attempthistory'] = '先前的提交';
$string['attemptnumber'] = '作業提交次數';
$string['attemptreopenmethod'] = '重新開啟作業提交';
$string['attemptreopenmethod_help'] = '決定學生能否重新提交作業。可用選項有：
<ul><li>
1.從不--- 學生不能再繳交作業。</li><li>
2.手動的--- 須要由教師再允許學生交作業。</li><li>
3.自動的，直到通過---- 學生可以一直重複繳交同一作業，直到分數超過在成績簿上設定的通過分數為止。</li></ul>';
$string['attemptreopenmethod_manual'] = '手動的';
$string['attemptreopenmethod_none'] = '決不';
$string['attemptreopenmethod_untilpass'] = '自動的，直到通過';
$string['attemptsettings'] = '繳交設定';
$string['availability'] = '可用性';
$string['backtoassignment'] = '回到作業';
$string['batchoperationconfirmaddattempt'] = '允許被選出的已繳交作業可以再提交一次？';
$string['batchoperationconfirmdownloadselected'] = '要下載選出的提交作業？';
$string['batchoperationconfirmgrantextension'] = '同意所選取的作業延期繳交嗎？';
$string['batchoperationconfirmlock'] = '鎖定所有選取的作業嗎？';
$string['batchoperationconfirmreverttodraft'] = '將選取的作業回復到草稿狀態？';
$string['batchoperationconfirmsetmarkingallocation'] = '為所有選定之作業設定由誰評分';
$string['batchoperationconfirmsetmarkingworkflowstate'] = '為所有選定之作業設定評分流程狀態';
$string['batchoperationconfirmunlock'] = '將所有選取的作業解除鎖定嗎？';
$string['batchoperationlock'] = '鎖定作業';
$string['batchoperationreverttodraft'] = '回復為草稿';
$string['batchoperationsdescription'] = '將選擇的...';
$string['batchoperationunlock'] = '解除鎖定作業';
$string['batchsetallocatedmarker'] = '為{$a}位選出的用戶安排評分者';
$string['batchsetmarkingworkflowstateforusers'] = '為{$a}位選出的用戶設定評分流程狀態';
$string['blindmarking'] = '彌封評閱';
$string['blindmarkingenabledwarning'] = '已經為這一活動啟用彌封(糊名)計分方式';
$string['blindmarking_help'] = '糊名評分是在評分時對評分者隱藏學生的身份識別資料。一旦此作業已經有人繳交或被評分了，則此糊名評分的設定將被鎖住。';
$string['changefilters'] = '變更過濾器';
$string['changegradewarning'] = '該作業有已經評分的作業，修改成績不會自動重新計算每個已繳交作業的分數。如果您確定要修改分數，那麼您必須對目前已繳交的作業重新評分。';
$string['changeuser'] = '變更用戶';
$string['choosegradingaction'] = '計分動作';
$string['choosemarker'] = '選擇.....';
$string['chooseoperation'] = '選擇操作';
$string['clickexpandreviewpanel'] = '點選以擴展審查面板';
$string['collapsegradepanel'] = '摺疊計分面板';
$string['collapsereviewpanel'] = '摺疊審查面板';
$string['comment'] = '評語';
$string['completionsubmit'] = '學生必須繳交，才算完成此活動';
$string['configshowrecentsubmissions'] = '在最新活動報告中，所有人都可以看到作業繳交通知。';
$string['confirmbatchgradingoperation'] = '您確定您要對 {$a->count}位學生進行{$a->operation}操作嗎?';
$string['confirmsubmission'] = '您確定要繳交作業並請求評分嗎？一旦這麼做，您將不能再修改作業。';
$string['conversionexception'] = '無法轉換作業。例外情況是：{$a}。';
$string['couldnotconvertgrade'] = '無法轉換用戶{$a}的作業成績。';
$string['couldnotconvertsubmission'] = '無法為用戶{$a}轉換繳交的作業。';
$string['couldnotcreatecoursemodule'] = '無法建立課程模組。';
$string['couldnotcreatenewassignmentinstance'] = '無法建立新的作業實例。';
$string['couldnotfindassignmenttoupgrade'] = '找不到需要升級的舊作業實例。';
$string['currentattempt'] = '這是第{$a}次繳交';
$string['currentattemptof'] = '這是第{$a->attemptnumber}次繳交(允許繳交 {$a->maxattempts} 次)';
$string['currentgrade'] = '目前成績單中的分數';
$string['cutoffdate'] = '拒收作業時間';
$string['cutoffdatecolon'] = '拒收日期：{$a}';
$string['cutoffdatefromdatevalidation'] = '拒收繳交時間必須在開始繳交時間之後。';
$string['cutoffdate_help'] = '若設定，這一作業模組在這日期之後，將不再接受繳交作業，除非有寬延時間';
$string['cutoffdatevalidation'] = '拒收繳交時間必須在規定繳交時間之後。';
$string['defaultlayout'] = '回復預設的版面格式';
$string['defaultsettings'] = '預設作業設定';
$string['defaultsettings_help'] = '這些定義了所有新作業的預設設定。';
$string['defaultteam'] = '預設群組';
$string['deleteallsubmissions'] = '刪除所有繳交的作業';
$string['description'] = '作業說明';
$string['downloadall'] = '下載全部繳交的作業';
$string['downloadasfolders'] = '以分開的資料夾方式下載提交的檔案';
$string['downloadasfolders_help'] = '若啟用，當提交作業多於一個檔案時，那麼作業可能會被下載到不同的資料夾中。每一作業會放在分開的資料夾，並保有下層資料夾的結構，且檔案不會被重新命名。';
$string['downloadselectedsubmissions'] = '下載選出的提交作業';
$string['duedate'] = '規定繳交時間';
$string['duedatecolon'] = '截止日期： {$a}';
$string['duedate_help'] = '此為繳交作業的規定期限。此時間點過後仍可交作業，但會被記上"遲交"。為了預防在某一日期之後，還一直有人交作業，你可以設定一個完全不收件的日期。';
$string['duedateno'] = '沒有規定繳交時間';
$string['duedatereached'] = '此作業的規定繳交時間已經過了';
$string['duedatevalidation'] = '規定繳交時間必須在開始繳交時間之後';
$string['editaction'] = '動作...';
$string['editattemptfeedback'] = '為第{$a}次繳交的作業編輯分數和回饋';
$string['editingpreviousfeedbackwarning'] = '你正在為一個先前繳交的作業編輯回饋。這是第{$a->attemptnumber} 次，總共有 {$a->totalattempts}次。.';
$string['editingstatus'] = '編修狀態';
$string['editsubmission'] = '修改我已繳交的作業';
$string['editsubmission_help'] = '更改你所繳交的作業';
$string['editsubmissionother'] = '編輯繳交的作業{$a}';
$string['enabled'] = '已啟用';
$string['errornosubmissions'] = '沒有可下載的作業';
$string['errorquickgradingvsadvancedgrading'] = '評分並未儲存，因為此作業正使用進階評分方法';
$string['errorrecordmodified'] = '由於在您載入此頁時，有人修改過一筆或多筆記錄，導致評分資料沒有被儲存。';
$string['eventallsubmissionsdownloaded'] = '繳交的作業已經全部被下載';
$string['eventassessablesubmitted'] = '一個作業已經提交';
$string['eventbatchsetmarkerallocationviewed'] = '已檢視過批次(整批)標記配置之設定。';
$string['eventbatchsetworkflowstateviewed'] = '批次設定計分工作流程狀態檢視';
$string['eventextensiongranted'] = '一個寬延期限已經被設定';
$string['eventfeedbackupdated'] = '回饋已更新';
$string['eventfeedbackviewed'] = '回饋已經檢視';
$string['eventgradingformviewed'] = '評分格式已經檢視';
$string['eventgradingtableviewed'] = '評分表格已經檢視';
$string['eventidentitiesrevealed'] = '身分已經被揭露';
$string['eventmarkerupdated'] = '分配的評分者已經被更新';
$string['eventrevealidentitiesconfirmationpageviewed'] = '已檢視過身份確認頁。';
$string['eventstatementaccepted'] = '這用戶已經接受這作業繳交聲明';
$string['eventsubmissionconfirmationformviewed'] = '提交確認格式已經檢視';
$string['eventsubmissioncreated'] = '(作業)已繳。';
$string['eventsubmissionduplicated'] = '這用戶複製他的繳交作業';
$string['eventsubmissionformviewed'] = '提交格式已經檢視';
$string['eventsubmissiongraded'] = '這份繳交的作業已經被計分';
$string['eventsubmissionlocked'] = '這個作業已為某使用者鎖住';
$string['eventsubmissionstatusupdated'] = '此份作業已更新';
$string['eventsubmissionstatusviewed'] = '評分狀態已經檢視';
$string['eventsubmissionunlocked'] = '某使用者已將此作業開啟(解鎖)';
$string['eventsubmissionupdated'] = '這用戶已經儲存一個繳交作業';
$string['eventsubmissionviewed'] = '提交已經檢視';
$string['eventworkflowstateupdated'] = '工作流的狀態已經被更新';
$string['expandreviewpanel'] = '擴展審查面板';
$string['extensionduedate'] = '展延到期日';
$string['extensionnotafterduedate'] = '展延日期必須在到期遲交時間之後';
$string['extensionnotafterfromdate'] = '展延日期必須在必須開始繳交時間之後';
$string['feedback'] = '回饋';
$string['feedbackavailablehtml'] = '{$a->username}已經對您的作業“<i>{$a->assignment}</i>”給了回饋意見。<br /><br />您可以在<a href="{$a->url}">作業繳交</a>下方看到。';
$string['feedbackavailablesmall'] = '{$a->username} 已經給 {$a->assignment} 作業回饋了';
$string['feedbackavailabletext'] = '{$a->username}已經對您的作業“{$a->assignment}”給了回饋意見。您可以在作業下方看到：{$a->url}';
$string['feedbackplugin'] = '回饋外掛';
$string['feedbackpluginforgradebook'] = '回饋外掛會將評語加到成績單中';
$string['feedbackpluginforgradebook_help'] = '只有一個作業的回饋外掛可以將回饋內容放到成績單。';
$string['feedbackplugins'] = '回饋外掛';
$string['feedbacksettings'] = '回饋的設定';
$string['feedbacktypes'] = '回饋類型';
$string['filesubmissions'] = '繳交檔案';
$string['filter'] = '篩選';
$string['filternone'] = '沒有篩選';
$string['filternotsubmitted'] = '尚未提交';
$string['filterrequiregrading'] = '需要評分';
$string['filtersubmitted'] = '已繳交';
$string['gradeabovemaximum'] = '分數必須小於或等於{$a}。';
$string['gradebelowzero'] = '分數必須是大於或等於零。';
$string['gradecanbechanged'] = '分數可以被更改';
$string['gradechangessaveddetail'] = '對分數和回饋的更改已經被儲存';
$string['graded'] = '已評分';
$string['gradedby'] = '已評分由';
$string['gradedon'] = '評分標準';
$string['gradelocked'] = '這一分數在這成績簿中是被鎖定或覆蓋的';
$string['gradeoutof'] = '得分(配分{$a})';
$string['gradeoutofhelp'] = '評分';
$string['gradeoutofhelp_help'] = '請輸入此學生的作業分數，可以包含小數。';
$string['gradersubmissionupdatedhtml'] = '{$a->username}已經更新<i>“{$a->assignment}”</i>作業。新的內容可以 <a href="{$a->url}">點按此處</a> 查閱。';
$string['gradersubmissionupdatedsmall'] = '{$a->username}已經更新繳交的作業 {$a->assignment}。';
$string['gradersubmissionupdatedtext'] = '{$a->username}在{$a->timeupdated}更新了作業“{$a->assignment}”可以在這裡查看：    {$a->url}';
$string['gradestudent'] = '評學生：（id={$a->id}，姓名={$a->fullname}）。';
$string['gradeuser'] = '評分 {$a}';
$string['grading'] = '評分';
$string['gradingchangessaved'] = '更改的分數已經被儲存';
$string['gradingmethodpreview'] = '評分標準';
$string['gradingoptions'] = '選項';
$string['gradingstatus'] = '評分狀態';
$string['gradingstudent'] = '評分學生';
$string['gradingsummary'] = '評閱摘要';
$string['grantextension'] = '准許延期';
$string['grantextensionforusers'] = '准許學生{$a}延期';
$string['groupsubmissionsettings'] = '群組繳交作業設定';
$string['hiddenuser'] = '參與者';
$string['hideshow'] = '隱藏/顯示';
$string['instructionfiles'] = '指引文件';
$string['introattachments'] = '附加的檔案';
$string['introattachments_help'] = '用於此作業的附加的檔案，比如說，答案樣版，可能會加上去。這些檔案的下載連結將會顯示在這作業頁的說明的下方。';
$string['invalidfloatforgrade'] = '該評分格式不對：{$a}';
$string['invalidgradeforscale'] = '所提供的分數對於目前的量尺不適用';
$string['lastmodifiedgrade'] = '最後修改的(得分)';
$string['lastmodifiedsubmission'] = '最後修改的(作業)';
$string['latesubmissions'] = '遲交的作業';
$string['latesubmissionsaccepted'] = '寬延繳交直到 {$a}';
$string['loading'] = '裝載中...';
$string['locksubmissionforstudent'] = '禁止這位學生再繳交作業：（id={$a->id}, 姓名={$a->fullname}）。';
$string['locksubmissions'] = '鎖定作業';
$string['manageassignfeedbackplugins'] = '管理作業的回饋外掛';
$string['manageassignsubmissionplugins'] = '管理作業的繳交外掛';
$string['marker'] = '評分者';
$string['markerfilter'] = '評分者過濾器';
$string['markerfilternomarker'] = '無標記';
$string['markingallocation'] = '使用評分人員分配';
$string['markingallocation_help'] = '此功能如果啟用，依工作流程，評分者可以被指派給特定學生(群)';
$string['markingworkflow'] = '使用評分工作流程';
$string['markingworkflow_help'] = '如果啟用此功能，在未發布給學生之前，分數會走完一系列工作流程。這麼一來即可分成多次評分，並可以同時將分數發布給所有學生。';
$string['markingworkflowstate'] = '評分工作流程狀態';
$string['markingworkflowstate_help'] = '可能的工作流程可能包含(依你同意)：
未評分：評分未開始
評分中：評分已開始但未結束
評分完成：評分者已結束評分，但可能需要回頭覆查或訂正
覆查中：分數在老師手上，正進行品質審查
準備公布中：負責評分的老師滿意此評分，但仍在等待，之後才會給學生查分數
已公布：學生可以查分數，看回饋';
$string['markingworkflowstateinmarking'] = '正在評分中';
$string['markingworkflowstateinreview'] = '正在檢查評分結果';
$string['markingworkflowstatenotmarked'] = '沒被評分的';
$string['markingworkflowstatereadyforrelease'] = '已準備好公布';
$string['markingworkflowstatereadyforreview'] = '評分已完成';
$string['markingworkflowstatereleased'] = '已經公布';
$string['maxattempts'] = '最大提交次數';
$string['maxattempts_help'] = '學生所能繳交作業的最大數量。超過這個數字，學生的作業無法再開啟。';
$string['maxgrade'] = '最高成績';
$string['maxperpage'] = '每一頁最多呈現幾個作業';
$string['maxperpage_help'] = '評分者最多可以顯示幾個作業在作業計分頁面上。當該課程選課人數很多時，可以防止超過時間限制。';
$string['messageprovider:assign_notification'] = '作業的通知';
$string['modulename'] = '作業';
$string['modulename_help'] = '作業活動模組讓老師能傳達任務、蒐集作品，並可以評分和回饋。學生可以繳交任何數位內容〈檔案〉，例如文書處理的文件、試算表、圖片或聲音和影片剪輯。此外，作業還可以要求學生直接在文字編輯器內輸入文字。作業還可以只是用來提醒學生去完成“真實世界”的作業，例如手工作品，而不需要任何電子檔案。批改作業時，老師可以寫評語，也可以上傳檔案回饋學生，例如加了批注的學生作業、有評語的檔案或是語音回饋。可以用數字或等第對作業評分，也可以用自訂的量尺或進階的評分方式〈例如評量規準〉。最終成績將會記錄在成績單中。';
$string['modulenameplural'] = '作業';
$string['moreusers'] = '{$a}更多....';
$string['multipleteams'] = '你同時是屬於不同的群組的成員';
$string['multipleteamsgrader'] = '你同時是屬於不同的群組的成員，因此無法提交作業。';
$string['mysubmission'] = '我的作業：';
$string['newsubmissions'] = '已繳交的作業';
$string['noattempt'] = '沒有繳交作業';
$string['nofiles'] = '沒有檔案。';
$string['nofilters'] = '沒有過濾器';
$string['nograde'] = '沒有成績。';
$string['nolatesubmissions'] = '沒有再收到遲交的作業。';
$string['nomoresubmissionsaccepted'] = '只接受已被寬延期限的學生的繳交作業';
$string['noonlinesubmissions'] = '這個作業不需要您在網上繳交任何東西';
$string['nosavebutnext'] = '往後';
$string['nosubmission'] = '這個作業還沒人繳交';
$string['nosubmissionsacceptedafter'] = '作業繳交期限為';
$string['noteam'] = '不屬於任何群組';
$string['noteamgrader'] = '你不是任何群組的成員，因此無法提交作業。';
$string['notgraded'] = '尚未評分';
$string['notgradedyet'] = '尚未評分';
$string['notifications'] = '通知';
$string['notsubmittedyet'] = '尚未繳交作業';
$string['nousers'] = '沒有用戶';
$string['nousersselected'] = '沒有選擇用戶';
$string['numberofdraftsubmissions'] = '草稿';
$string['numberofparticipants'] = '參與者';
$string['numberofsubmissionsneedgrading'] = '需要評分';
$string['numberofsubmittedassignments'] = '已繳交';
$string['numberofteams'] = '群組';
$string['offline'] = '不需要線上繳交';
$string['open'] = '打開';
$string['outlinegrade'] = '評分： {$a}';
$string['outof'] = '{$a->current}，共有{$a->total}';
$string['overdue'] = '<font color="red">已經超過應繳交時間: {$a}</font>';
$string['page-mod-assign-view'] = '作業模組主頁和繳交頁面';
$string['page-mod-assign-x'] = '任何作業模組頁面';
$string['paramtimeremaining'] = '剩下{$a}';
$string['participant'] = '參與者';
$string['pluginadministration'] = '作業管理';
$string['pluginname'] = '作業';
$string['preventsubmissionnotingroup'] = '需要以群組方式提交作業';
$string['preventsubmissionnotingroup_help'] = '若啟動，不屬於一個群組的用戶將無法提交作業';
$string['preventsubmissions'] = '禁止用戶繼續繳交這一作業';
$string['preventsubmissionsshort'] = '禁止更改作業';
$string['previous'] = '向前';
$string['quickgrading'] = '快速評分';
$string['quickgradingchangessaved'] = '評分的變更已經儲存';
$string['quickgrading_help'] = '快速評分模式允許您直接在作業列表後面對每個學生進行評分。快速評分與進階評分不相容，當需要多項評分時，不推薦使用此方式。';
$string['quickgradingresult'] = '快速評分';
$string['recordid'] = '識別碼';
$string['reopenuntilpassincompatiblewithblindmarking'] = '重新開放，直到彌封評閱無法進行，因為在學生身分被揭示之前，分數不會釋出到成績簿中。';
$string['requireallteammemberssubmit'] = '要求所有群組成員都要繳交';
$string['requireallteammemberssubmit_help'] = '如果啟用，學生組的所有成員都必須點擊“提交”按鈕，這項任務將被視為提交前組提交。如果禁用，組提交將被視為學生組的任何成員點擊“提交”按鈕提交。';
$string['requiresubmissionstatement'] = '要求學生接受繳交作業的聲明';
$string['requiresubmissionstatement_help'] = '針對整個網站繳交的作業，要求學生接受繳交作業的聲明。如果未啟用此設置，繳交作業的聲明可在每項作業的設定才啟用或停用。';
$string['revealidentities'] = '揭示學生身份';
$string['revealidentitiesconfirm'] = '您確定要釋出此項作業的學生身分？此項操作將無法取消。一旦學生身分被釋出，分數將會出現在成績簿中。';
$string['reverttodraft'] = '將作業回復為草稿狀態。';
$string['reverttodraftforstudent'] = '將該學生的作業回復到草稿狀態：（學生ID={$a->id}，姓名={$a->fullname}）。';
$string['reverttodraftshort'] = '將作業回復為草稿';
$string['reviewed'] = '已檢視的';
$string['saveallquickgradingchanges'] = '保存所有快速評分的變更';
$string['saveandcontinue'] = '儲存並繼續';
$string['savechanges'] = '儲存更改';
$string['savegradingresult'] = '分數';
$string['savenext'] = '儲存後顯示下一位';
$string['savingchanges'] = '儲存變更...';
$string['scale'] = '量尺';
$string['search:activity'] = '作業 - 活動資訊';
$string['selectedusers'] = '被選出的用戶';
$string['selectlink'] = '選擇...';
$string['selectuser'] = '選擇{$a}';
$string['sendlatenotifications'] = '若有作業遲交，要通知評分者';
$string['sendlatenotifications_help'] = '如果啟用，當學生遲交作業時，評分者（通常是教師）會收到一條訊息，訊息傳送方式可以再設定。';
$string['sendnotifications'] = '若有作業繳交，要通知評分者';
$string['sendnotifications_help'] = '如果啟用，當學生較早、準時和較晚繳交作業時，評分者（通常是教師）會收到一條訊息，訊息傳送方式可以再設定。';
$string['sendstudentnotifications'] = '通知學生';
$string['sendstudentnotificationsdefault'] = '"通知學生"的預設設定';
$string['sendstudentnotificationsdefault_help'] = '為計分表單上的"通知學生"勾選方格設定預設值。';
$string['sendstudentnotifications_help'] = '若啟用，學生會收到有關更新的分數和回饋的訊息。';
$string['sendsubmissionreceipts'] = '發送作業繳交收據給學生';
$string['sendsubmissionreceipts_help'] = '此開關將啟用繳交收據。當學生每次繳交作業成功，將會收到一份通知。';
$string['setmarkerallocationforlog'] = '設定評分人員分配 : (編號={$a->id}，姓名={$a->fullname}，評分者={$a->marker})';
$string['setmarkingallocation'] = '設定指派的評分人員';
$string['setmarkingworkflowstate'] = '設定評分流程狀態';
$string['setmarkingworkflowstateforlog'] = '設定評分流程狀態 : (編號={$a->id}，姓名={$a->fullname}，狀態={$a->state})';
$string['settings'] = '作業設定';
$string['showrecentsubmissions'] = '顯示最近的繳交作業';
$string['status'] = '狀態';
$string['studentnotificationworkflowstateerror'] = '計分流程狀態必須被"釋出"以便通知學生。';
$string['submission'] = '繳交作業';
$string['submissioncopiedhtml'] = '你已複製了您先前繳交的作業給<i>{$a->assignment}</i>\'.</p>
<p>，你可以看到你的<a href="{$a->url}">assignment submission</a>.</p>的狀態。';
$string['submissioncopiedsmall'] = '你拷貝了你先前繳交的作業{$a->assignment}';
$string['submissioncopiedtext'] = '你已複製了您先前繳交的作業給{$a->assignment}\'，你可以在{$a->url}看到你繳交作業的狀態。';
$string['submissiondrafts'] = '學生須點按繳交按鈕';
$string['submissiondrafts_help'] = '如果啟用，學生將需要點按繳交按鈕來宣告他們繳交的作業是最終版本。這允許學生可以在系統保存作業的草稿。如果在學生已經繳交作業後，該設定從“否” 更改為“是”，那麼這些作業會被視為最終版本。';
$string['submissioneditable'] = '學生可以編編輯這一繳交的作業';
$string['submissionempty'] = '沒有繳交任何東西';
$string['submissionlog'] = '學生: {$a->fullname}, 狀態: {$a->status}';
$string['submissionmodified'] = '你有現存的提交資料。請離開這頁在試一次。';
$string['submissionmodifiedgroup'] = '這一提交作業已經被某人修改過，請離開這頁再試一次。';
$string['submissionnotcopiedinvalidstatus'] = '這提交的作業沒有被複製，因為在重新開放之前，它已經被編輯過了。';
$string['submissionnoteditable'] = '學生不能編輯這一繳交的作業';
$string['submissionnotready'] = '此作業還沒有準備好接受繳交：';
$string['submissionplugins'] = '作業繳交外掛';
$string['submissionreceipthtml'] = '<p>您已經繳交了作業“<i>{$a->assignment}</i>”</p>
<p>您可以查看您所<a href="{$a->url}">提交的作業</a>的狀態。</p>';
$string['submissionreceiptotherhtml'] = '你繳給
\'<i>{$a->assignment}</i>\' 的作業已提交。<br /><br />
你可以看到你的<a href="{$a->url}">assignment submission</a>狀態。';
$string['submissionreceiptothersmall'] = '你繳給 {$a->assignment} 的作業已提交。';
$string['submissionreceiptothertext'] = '你繳給 {$a->assignment} 的作業已提交。你可以在{$a->url}看到你繳交作業的狀態。';
$string['submissionreceipts'] = '發送作業繳交收據';
$string['submissionreceiptsmall'] = '您已經繳交 {$a->assignment} 作業了';
$string['submissionreceipttext'] = '您已經繳交了“{$a->assignment}”的作業。

您可以查看你提交作業的狀態：

 {$a->url}';
$string['submissions'] = '繳交';
$string['submissionsclosed'] = '繳交作業已關閉';
$string['submissionsettings'] = '繳交作業的設定';
$string['submissionslocked'] = '此作業不接受繳交';
$string['submissionslockedshort'] = '不允許更改作業';
$string['submissionsnotgraded'] = '未評分的作業：{$a}';
$string['submissionstatement'] = '作業繳交的聲明';
$string['submissionstatementacceptedlog'] = '用戶{$a}已經接受作業繳交的聲明';
$string['submissionstatementdefault'] = '這一作業，除了我有註明是引注他人作品之外，都是我自己寫的。';
$string['submissionstatement_help'] = '作業提交確認聲明';
$string['submissionstatus'] = '繳交狀態';
$string['submissionstatus_'] = '未繳交';
$string['submissionstatus_draft'] = '草稿（尚未繳交）';
$string['submissionstatusheading'] = '繳交狀態';
$string['submissionstatus_marked'] = '已評分';
$string['submissionstatus_new'] = '沒有繳交的作業';
$string['submissionstatus_reopened'] = '已經重新開啟';
$string['submissionstatus_submitted'] = '已繳交，等待評分中';
$string['submissionsummary'] = '{$a->status}。最後修改時間: {$a->timemodified}';
$string['submissionteam'] = '群組';
$string['submissiontypes'] = '繳交類型';
$string['submitaction'] = '繳交';
$string['submitassignment'] = '繳交作業';
$string['submitassignment_help'] = '當這項作業繳交後，您將不能再做任何修改。';
$string['submitforgrading'] = '繳交等待評分';
$string['submitted'] = '已繳交';
$string['submittedearly'] = '提早{$a}就繳交作業';
$string['submittedlate'] = '過期{$a}才繳交作業';
$string['submittedlateshort'] = '過期{$a}';
$string['subplugintype_assignfeedback'] = '回饋外掛';
$string['subplugintype_assignfeedback_plural'] = '回饋外掛';
$string['subplugintype_assignsubmission'] = '提交外掛';
$string['subplugintype_assignsubmission_plural'] = '提交外掛';
$string['teamname'] = '小組名稱：{$a}';
$string['teamsubmission'] = '學生依群組繳交作業';
$string['teamsubmissiongroupingid'] = '對學生小組進行分群';
$string['teamsubmissiongroupingid_help'] = '這是學生作業的分組方式。如果不設定，分組即會使用預設的方式。';
$string['teamsubmission_help'] = '若啟用，學生將會依據預設的群組或自訂的臨時分群被分派到不同群組中。一個團體的作業將會在群組成員中被分享，且這群組的每一成員都可看到其他人對這座業所做的更改。';
$string['textinstructions'] = '作業指引';
$string['timemodified'] = '最後修改';
$string['timeremaining'] = '剩餘時間';
$string['timeremainingcolon'] = '剩餘時間：{$a}';
$string['togglezoom'] = '局部放大/縮小';
$string['ungroupedusers'] = '"需要以群組方式提交作業"的設定已經被啟動，但是仍有某些用戶沒有被指派到群組中，或者同一個人屬於多個群組，因此無法提交作業。';
$string['unlimitedattempts'] = '無限制的';
$string['unlimitedattemptsallowed'] = '允許無限次數提交作業';
$string['unlimitedpages'] = '無限制的';
$string['unlocksubmissionforstudent'] = '允許學生繳交：(id={$a->id}, 全名={$a->fullname}).。';
$string['unlocksubmissions'] = '解除作業鎖定';
$string['unsavedchanges'] = '為儲存的更改';
$string['unsavedchangesquestion'] = '這兒有分數或回饋的變更還未儲存。你是否要儲存這些變更並繼續？';
$string['updategrade'] = '更新評分';
$string['updatetable'] = '儲存並更新資料表';
$string['upgradenotimplemented'] = '({$a->type} {$a->subtype}) 外掛沒有升級的功能';
$string['userextensiondate'] = '寬延繳交日期到：{$a}';
$string['usergrade'] = '用戶成績';
$string['useridlistnotcached'] = '這分數變更沒被儲存。因為Moodle無法決定這分數要儲存到哪一作業上。';
$string['userswhoneedtosubmit'] = '需要提交作業的用戶：{$a}';
$string['validmarkingworkflowstates'] = '有效的評分工作流程狀態';
$string['viewadifferentattempt'] = '檢視不同的作答';
$string['viewbatchmarkingallocation'] = '查看批次設定評分工作流狀態頁面。';
$string['viewbatchsetmarkingworkflowstate'] = '查看批次設定評分工作流狀態頁面。';
$string['viewfeedback'] = '檢視回饋';
$string['viewfeedbackforuser'] = '檢視用戶回饋：{$a}';
$string['viewfull'] = '檢視全部';
$string['viewfullgradingpage'] = '打開完整評分頁面來提供回饋';
$string['viewgradebook'] = '檢視成績單';
$string['viewgrading'] = '檢視所有繳交的作業';
$string['viewgradingformforstudent'] = '檢視學生（id={$a->id}，全名={$a->fullname}）的評分頁面。';
$string['viewownsubmissionform'] = '檢視自己繳交作業的頁面。';
$string['viewownsubmissionstatus'] = '檢視自己繳交的狀態頁。';
$string['viewrevealidentitiesconfirm'] = '查看揭示學生的身份確認頁面。';
$string['viewsubmission'] = '檢視繳交的作業';
$string['viewsubmissionforuser'] = '檢視用戶作業：{$a}';
$string['viewsubmissiongradingtable'] = '檢視作業評分表。';
$string['viewsummary'] = '檢視摘要';
$string['workflowfilter'] = '工作流程過濾器';
$string['xofy'] = '{$a->y}的{$a->x}';
/* by Mary Chen
 * function : pattern
 */
$string['setpattern'] = '設定為觀摩作業'; 
$string['pattern'] = '觀摩作業';
$string['cancelpattern'] = '取消作業觀摩';
$string['pattern_assign'] = '觀摩作業';
$string['pattern_notice_onlinetext'] = '注意:{$a}學生心得報告類型為[線上文字],無法設定為觀摩作業.';
$string['pattern_notice_nofile'] = '注意:{$a}學生作業未繳交,無法設定為觀摩作業.';
$string['pattern_notice_cancel'] = '注意:{$a}學生作業未設定為觀摩作業,無法取消設定.';
$string['batchoperationconfirmsetpattern'] = '是否要設定為觀摩作業?';
$string['batchoperationconfirmcancelpattern'] = '是否要取消作業觀摩?';