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
 * Strings for component 'workshop', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   workshop
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aggregategrades'] = '重新計算分數';
$string['aggregation'] = '成績彙總';
$string['allocate'] = '分配作業';
$string['allocatedetails'] = '預計：{$a->expected}<br />已繳交：{$a->submitted}<br />待分配：{$a->allocate}';
$string['allocation'] = '分配作業';
$string['allocationconfigured'] = '分配設定';
$string['allocationdone'] = '分配完成';
$string['allocationerror'] = '分配錯誤';
$string['allowedfiletypesforoverallfeedback'] = '回饋附件所允許的檔案類型';
$string['allowedfiletypesforoverallfeedback_help'] = '你可以輸入一個以逗點隔開的檔案副檔名的清單，來限制回饋附件的檔案類型。例如，\'png, jpg, jpeg, gif\'。若這欄位是空的，那任何檔案類型都可以。

額外支援的檔案副檔名，可以在伺服器管理中設定。';
$string['allowedfiletypesforsubmission'] = '繳交作業的附件可接受的檔案類型';
$string['allowedfiletypesforsubmission_help'] = '你可以輸入一個以逗點隔開的檔案副檔名的清單，來限制繳交作業附件的檔案類型。例如，\'png, jpg, jpeg, gif\'。若這欄位是空的，那任何檔案類型都可以。

額外支援的檔案副檔名，可以在伺服器管理中設定。';
$string['allsubmissions'] = '所有的作業({$a})';
$string['alreadygraded'] = '已經評分的';
$string['areaconclusion'] = '結論的文字';
$string['areainstructauthors'] = '對於作業繳交的指示';
$string['areainstructreviewers'] = '關於評價的指示';
$string['areaoverallfeedbackattachment'] = '整體回饋的附件';
$string['areaoverallfeedbackcontent'] = '整體回饋的附件';
$string['areasubmissionattachment'] = '作業附件';
$string['areasubmissioncontent'] = '作業文字';
$string['assess'] = '評價';
$string['assessedexample'] = '已被評價的作業範例';
$string['assessedsubmission'] = '已被評價的作業';
$string['assessingexample'] = '評價中的作業範例';
$string['assessingsubmission'] = '評價中的作業';
$string['assessment'] = '評價';
$string['assessmentby'] = '由<a href="{$a->url}">{$a->name}</a>';
$string['assessmentbyfullname'] = '由{$a} 評價';
$string['assessmentbyyourself'] = '您的評價';
$string['assessmentdeleted'] = '評價的分配已解除';
$string['assessmentend'] = '評價截止時間';
$string['assessmentendbeforestart'] = '評價截止時間不能訂在評價開始時間之前';
$string['assessmentenddatetime'] = '評價截止日期：{$a->daydatetime} ({$a->distanceday})';
$string['assessmentendevent'] = '{$a}（評價截止）';
$string['assessmentform'] = '評價表格';
$string['assessmentofsubmission'] = '<a href="{$a->assessmenturl}">評價</a> of <a href="{$a->submissionurl}">{$a->submissiontitle}</a>';
$string['assessmentreference'] = '參考評價';
$string['assessmentreferenceconflict'] = '您已經為此作業範例提供了一份參考評價，不可能再評價它。';
$string['assessmentreferenceneeded'] = '您必須先為此範例提供一套參考評價。點按“繼續”按鈕開始評價作業。';
$string['assessmentsettings'] = '評價設定';
$string['assessmentstart'] = '評價開始時間';
$string['assessmentstartdatetime'] = '評價開始時間 {$a->daydatetime} ({$a->distanceday})';
$string['assessmentstartevent'] = '{$a}（開始評價）';
$string['assessmentweight'] = '評價的權重';
$string['assignedassessments'] = '需要評價的作業';
$string['assignedassessmentsnone'] = '您沒有需要評價的作業';
$string['backtoeditform'] = '回到編輯中的表單';
$string['byfullname'] = '由<a href="{$a->url}">{$a->name}</a>';
$string['calculategradinggrades'] = '計算評價成績';
$string['calculategradinggradesdetails'] = '預計：{$a->expected}<br />已計算：{$a->calculated}';
$string['calculatesubmissiongrades'] = '計算作業成績';
$string['calculatesubmissiongradesdetails'] = '預計：{$a->expected}<br />已計算：{$a->calculated}';
$string['chooseuser'] = '選擇用戶...';
$string['clearaggregatedgrades'] = '清除所有已彙總的成績';
$string['clearaggregatedgradesconfirm'] = '您確定要清除已計算的作業成績和評價成績？';
$string['clearaggregatedgrades_help'] = '已彙總的作業成績和評價成績將被重設。您可以在成績核定階段重新計算這些成績。';
$string['clearassessments'] = '清除評價';
$string['clearassessmentsconfirm'] = '您確定要清除所有評價成績？清除後，您不能自己要回這些資訊，必須等評閱人重新評價這分配的作業。';
$string['clearassessments_help'] = '已計算的作業成績和評價成績將被重置。評價表格已填寫的資訊仍然保留，但是所有評閱人必須再次打開評價表格並再次儲存才能讓成績重新計算。';
$string['conclusion'] = '結語';
$string['conclusion_help'] = '結語是在活動結束後顯示給參與者看的一段文字。你可以提供一個整體的回饋、摘要、或下一次該怎麼做的建議(例如要求學生在blog上發表對於此一活動的感想)。';
$string['configexamplesmode'] = '在工作坊的範例評價的預設模式';
$string['configgrade'] = '在工作坊中，作業表現的預設最高分(配分)';
$string['configgradedecimals'] = '顯示成績時小數點後預設顯示的位數。';
$string['configgradinggrade'] = '在工作坊中，評鑑表現的預設最高分(配分)';
$string['configmaxbytes'] = '本網站為所有工作坊預設的上傳檔案最大容量（且受限於課程限制和其它本地設定）';
$string['configstrategy'] = '工作坊的預設評分策略';
$string['createsubmission'] = '開始準備你的作業';
$string['daysago'] = '{$a} 天以前';
$string['daysleft'] = '剩下 {$a} 天';
$string['daystoday'] = '今天';
$string['daystomorrow'] = '明天';
$string['daysyesterday'] = '昨天';
$string['deadlinesignored'] = '時間限制對您無效';
$string['deletesubmission'] = '刪除繳交的作業';
$string['editassessmentform'] = '編輯評價表';
$string['editassessmentformstrategy'] = '編輯評價表 ({$a})';
$string['editingassessmentform'] = '正在編輯評價表';
$string['editingsubmission'] = '正在編輯作業';
$string['editsubmission'] = '編輯繳交的作業';
$string['err_multiplesubmissions'] = '編輯此表單的時候，作業的另一個版本已經被保存。每個用戶只可以提交一次。';
$string['err_removegrademappings'] = '無法刪除未使用的成績對應';
$string['err_unknownfileextension'] = '不明的檔案附檔名： {$a}';
$string['err_wrongfileextension'] = '有些檔案 ({$a->wrongfiles})無法被上傳。只有檔案類型 {$a->whitelist}是被允許的。';
$string['evaluategradeswait'] = '請耐心等待，正在核定評價和計算分數。';
$string['evaluation'] = '評鑑能力的計分';
$string['evaluationmethod'] = '評鑑能力的計分方式';
$string['evaluationmethod_help'] = '評鑑能力的計分方法決定如何計算學生評鑑表現的分數。當前只有一個選項：與最佳評量(教師做的評量)進行比較。';
$string['evaluationsettings'] = '評鑑能力計分方式的設定';
$string['eventassessableuploaded'] = '一份作答結果已經被上傳';
$string['eventassessmentevaluated'] = '評鑑評價結果';
$string['eventassessmentevaluationsreset'] = '重設對評價結果的評鑑';
$string['eventassessmentreevaluated'] = '重新評鑑評價結果';
$string['eventphaseswitched'] = '切換階段';
$string['eventsubmissionassessed'] = '已評價的作業';
$string['eventsubmissionassessmentsreset'] = '清除作業的評價結果';
$string['eventsubmissioncreated'] = '已建立的作業';
$string['eventsubmissiondeleted'] = '已刪除的作業';
$string['eventsubmissionreassessed'] = '重新評價的作業';
$string['eventsubmissionupdated'] = '已更新的作業';
$string['eventsubmissionviewed'] = '已檢視的作業';
$string['example'] = '作業範例';
$string['exampleadd'] = '增加作業範例';
$string['exampleassess'] = '評價作業範例';
$string['exampleassessments'] = '需評價的作業範例';
$string['exampleassesstask'] = '評價範例';
$string['exampleassesstaskdetails'] = '預計：{$a->expected}<br />已評：{$a->assessed}';
$string['examplecomparing'] = '正在比較作業範例的評價';
$string['exampledelete'] = '刪除範例';
$string['exampledeleteconfirm'] = '您確定要刪除下面的作業範例？按“繼續”按鈕將刪除此作業。';
$string['exampleedit'] = '編輯範例';
$string['exampleediting'] = '正在編輯範例';
$string['exampleneedassessed'] = '您必須先評價所有的作業範例';
$string['exampleneedsubmission'] = '您必須先提交你的作業，並評價所有的作業範例';
$string['examplesbeforeassessment'] = '在提交自己的作業之後，範例即可使用，而且必須在評價同學的作業之前，完成對範例的評價';
$string['examplesbeforesubmission'] = '必須在提交自己的作業之前，完成對範例的評價';
$string['examplesmode'] = '評價作業範例的模式';
$string['examplesubmissions'] = '作業範例';
$string['examplesvoluntary'] = '對於作業範例的評價是自願性的';
$string['feedbackauthor'] = '給作者的回饋';
$string['feedbackauthorattachment'] = '附件';
$string['feedbackby'] = '{$a}提供的回饋';
$string['feedbackreviewer'] = '給評閱者的回饋';
$string['feedbacksettings'] = '回饋';
$string['formataggregatedgrade'] = '{$a->grade}';
$string['formataggregatedgradeover'] = '<del>{$a->grade}</del><br /><ins>{$a->over}</ins>';
$string['formatpeergrade'] = '<span class="grade">{$a->grade}</span> <span class="gradinggrade">({$a->gradinggrade})</span>';
$string['formatpeergradeover'] = '<span class="grade">{$a->grade}</span> <span class="gradinggrade">(<del>{$a->gradinggrade}</del> / <ins>{$a->gradinggradeover}</ins>)</span>';
$string['formatpeergradeoverweighted'] = '<span class="grade">{$a->grade}</span> <span class="gradinggrade">(<del>{$a->gradinggrade}</del> / <ins>{$a->gradinggradeover}</ins>)</span> @ <span class="weight">{$a->weight}</span>';
$string['formatpeergradeweighted'] = '<span class="grade">{$a->grade}</span> <span class="gradinggrade">({$a->gradinggrade})</span> @ <span class="weight">{$a->weight}</span>';
$string['givengrades'] = '給出的分數';
$string['gradecalculated'] = '計算出的作業表現分數';
$string['gradedecimals'] = '分數的小數位數';
$string['gradegivento'] = '&gt;';
$string['gradeinfo'] = '成績：{$a->received}/{$a->max}';
$string['gradeitemassessment'] = '{$a->workshopname} (評價)';
$string['gradeitemsubmission'] = '{$a->workshopname} (作業)';
$string['gradeover'] = '覆蓋作業成績';
$string['gradereceivedfrom'] = '&lt;';
$string['gradesreport'] = '工作坊的成績報告';
$string['gradetopassgrading'] = '評鑑能力的及格分數';
$string['gradetopasssubmission'] = '作業的及格分數';
$string['gradinggrade'] = '評鑑表現的得分';
$string['gradinggradecalculated'] = '計算出的評鑑表現分數';
$string['gradinggrade_help'] = '此設定指定了作業評價可能獲得的最高成績。';
$string['gradinggradeof'] = '評鑑表現分數(最高分 {$a})';
$string['gradinggradeover'] = '覆蓋評價成績';
$string['gradingsettings'] = '評分的設定';
$string['groupnoallowed'] = '您不能訪問這工作坊中的任何群組';
$string['iamsure'] = '是的，我確定';
$string['info'] = '資訊';
$string['instructauthors'] = '對於作業的指示';
$string['instructreviewers'] = '對於評價的指示';
$string['introduction'] = '說明';
$string['latesubmissions'] = '遲交作業';
$string['latesubmissionsallowed'] = '允許遲交作業';
$string['latesubmissions_desc'] = '允許截止日期後仍能繳交作業';
$string['latesubmissions_help'] = '如果啟用，作者可以在截止日期後或在互評階段提交作業。但不能編輯遲交的作業。';
$string['maxbytes'] = '作業附件的檔案最大容量';
$string['modulename'] = '工作坊';
$string['modulename_help'] = '工作坊活動模組，可以用在學生作品的收集、審查和互評。

學生能繳交任何數位內容(檔案)，例如文書處理或試算表的檔案，也可以使用文字編輯器直接在欄位中輸入文字。作品的評價是採用教師制定的多元標準評價表單。為了讓學生明白互評的過程和評價表單的使用，先利用老師提供的範例作業和參考評量試行互評。接下來，學生被賦予機會去評價一或多件他的同儕的作業。如果有必要，繳交和評閱人可以隱匿姓名。

在一個工作坊活動中，學生會獲得兩個成績，一個是他們作業的成績，一個是他們對同儕作業評價的成績。兩個成績都會記錄在成績簿中。';
$string['modulenameplural'] = '工作坊';
$string['mysubmission'] = '我的作業';
$string['nattachments'] = '最多幾個附件';
$string['noexamples'] = '此工作坊還沒有範例';
$string['noexamplesformready'] = '在上傳作業範例之前您必須先設計評價表格';
$string['nogradeyet'] = '還沒有分數';
$string['nosubmissionfound'] = '沒找到此用戶的作業';
$string['nosubmissions'] = '此工作坊還未收到作業';
$string['notassessed'] = '還沒進行評價';
$string['nothingfound'] = '無東西可顯示';
$string['nothingtoreview'] = '沒有東西可以評閱';
$string['notoverridden'] = '不可覆蓋';
$string['noworkshops'] = '這個課程中沒有工作坊活動';
$string['noyoursubmission'] = '您還沒有繳交作品';
$string['nullgrade'] = '-';
$string['overallfeedback'] = '整體回饋';
$string['overallfeedbackfiles'] = '整體回饋附件檔案數限制';
$string['overallfeedbackmaxbytes'] = '整體回饋附件的檔案最大容量';
$string['overallfeedbackmode'] = '整體回饋模式';
$string['overallfeedbackmode_0'] = '關閉';
$string['overallfeedbackmode_1'] = '啟用並自行選用的';
$string['overallfeedbackmode_2'] = '啟用並必需要的';
$string['overallfeedbackmode_help'] = '若啟用，一個文字輸入方格會顯示在評價表的下方。

評分者可以在此寫上對於這作業的整體評量意見，或者對他們的評量結果提供額外的解釋。';
$string['page-mod-workshop-x'] = '任何工作坊模組頁面';
$string['participant'] = '參與者';
$string['participantrevierof'] = '參與者是誰的評閱人';
$string['participantreviewedby'] = '參與者由誰評閱';
$string['phaseassessment'] = '同儕互評階段';
$string['phaseclosed'] = '已關閉';
$string['phaseevaluation'] = '評鑑能力計分階段';
$string['phasesetup'] = '設定階段';
$string['phasesoverlap'] = '繳交作業階段和同儕互評階段不能重疊';
$string['phasesubmission'] = '繳交作業階段';
$string['pluginadministration'] = '工作坊管理';
$string['pluginname'] = '工作坊';
$string['prepareexamples'] = '準備作業範例';
$string['previewassessmentform'] = '預覽';
$string['publishedsubmissions'] = '已發佈的作業';
$string['publishsubmission'] = '發佈作業';
$string['publishsubmission_help'] = '當工作坊關閉後，其他人仍可以看到已發佈的作業。';
$string['reassess'] = '重新評價';
$string['receivedgrades'] = '收到的分數';
$string['recentassessments'] = '工作坊的評價：';
$string['recentsubmissions'] = '工作坊的作業：';
$string['resetassessments'] = '刪除所有的評價';
$string['resetassessments_help'] = '你可以選擇只刪除評價結果，而不影響提交的作業。但如果你要刪除提交的作業，那它們的評價結果同時也會被刪除，而這選項將被忽略。注意，這也包括對於業範例的評價結果。';
$string['resetphase'] = '切換到設定階段';
$string['resetphase_help'] = '若啟用，所有的工作坊將會回到起始的設定階段';
$string['resetsubmissions'] = '刪除所有繳交的作業';
$string['resetsubmissions_help'] = '所有繳交的作業和它們的評價將會被刪除。但這不會影響到作業範例。';
$string['saveandclose'] = '儲存後關閉';
$string['saveandcontinue'] = '儲存並繼續編輯';
$string['saveandpreview'] = '儲存並預覽';
$string['saveandshownext'] = '儲存並顯示下一個';
$string['search:activity'] = '工作坊 - 活動訊息';
$string['selfassessmentdisabled'] = '禁止自我評價';
$string['showingperpage'] = '每頁顯示{$a}項';
$string['showingperpagechange'] = '更改...';
$string['someuserswosubmission'] = '至少有一人尚未繳交作業';
$string['sortasc'] = '升冪排序';
$string['sortdesc'] = '降冪排序';
$string['strategy'] = '評分策略';
$string['strategyhaschanged'] = '在編輯表格期間，此互動評價的評分策略已被修改。';
$string['strategy_help'] = '評分策略決定了使用哪個評價表格和給作業評分的方法。這裡有4個選項：

* 累計評分 - 針對指定的觀點給予評語和分數

* 評語 - 針對指定的觀點給予評語，但是不給分數

* 錯誤數 - 針對指定的主張給予評語和“是/否”的評價

* 規準 - 按照指定的各項規準給予分層級的評價';
$string['submission'] = '繳交的作業';
$string['submissionattachment'] = '附件';
$string['submissionby'] = '{$a} 的作業';
$string['submissioncontent'] = '作業內容';
$string['submissiondeleteconfirm'] = '您確定要刪除下列的作業嗎？';
$string['submissiondeleteconfirmassess'] = '您確定要刪除下列的作業嗎？注意，這也將會刪除與這提交作業有關的 {$a->count} 個評鑑，它可能影響負責評分的同學的分數。';
$string['submissionend'] = '繳交截止時間';
$string['submissionendbeforestart'] = '繳交截止時間不能訂在開放繳交日之前';
$string['submissionenddatetime'] = '繳交截止日期：{$a->daydatetime} ({$a->distanceday})';
$string['submissionendevent'] = '{$a}（繳交截止）';
$string['submissionendswitch'] = '繳交截止日期後切換到下一個階段';
$string['submissionendswitch_help'] = '如果有訂定繳交截止日而且方塊被勾選的話，這個工作坊在作業繳交截止後，將自動切換到互評階段。如果您啟用此功能，建議也要設立預定的分配方法。即使工作坊處於互評階段，如果作業沒有被分配，是無法評價的。';
$string['submissiongrade'] = '作業表現的得分';
$string['submissiongrade_help'] = '此設定指定了繳交的作業可獲得的最高成績。';
$string['submissiongradeof'] = '作業的分數( 最高{$a} )';
$string['submissionlastmodified'] = '最後修改';
$string['submissionsettings'] = '繳交作業的設定';
$string['submissionsreport'] = '工作坊作業繳交報表';
$string['submissionstart'] = '繳交開始時間';
$string['submissionstartdatetime'] = '開始繳交作業，從{$a->daydatetime} ({$a->distanceday})';
$string['submissionstartevent'] = '{$a}（開放繳交作業）';
$string['submissiontitle'] = '作業標題';
$string['submittednotsubmitted'] = '已繳交({$a->submitted}) / 尚未繳交({$a->notsubmitted})';
$string['subplugintype_workshopallocation'] = '作業同儕互評的分配方法';
$string['subplugintype_workshopallocation_plural'] = '作業同儕互評的分配方法';
$string['subplugintype_workshopeval'] = '評鑑能力的評分方法';
$string['subplugintype_workshopeval_plural'] = '評鑑能力的評分方法';
$string['subplugintype_workshopform'] = '評分策略';
$string['subplugintype_workshopform_plural'] = '評分策略';
$string['switchingphase'] = '正在切換階段';
$string['switchphase'] = '切換階段';
$string['switchphase10info'] = '您將切換此工作坊到<strong>設定階段</strong>。在此階段，用戶不能修改作業和評價。教師可利用這個階段修改互動評價的設定、評分策略或評價表格。';
$string['switchphase20info'] = '您將切換此工作坊到<strong>繳交階段</strong>。在此階段學生可以上傳作業（在作業讀取控制日期內，如果設定了的話）。教師可分配同學間互評。';
$string['switchphase30auto'] = '在 {$a->daydatetime} ({$a->distanceday})之後，工作坊將自動切換到互評階段';
$string['switchphase30info'] = '您將切換此工作坊到<strong>互評階段</strong>。在這個階段，評閱人可以評價已分配給他們的作業（在評價存取控制日期內，如果已經設定的話）。';
$string['switchphase40info'] = '您將切換此工作坊到<strong>成績核定階段</strong>。 在這個階段，用戶無法修改他們的作業和評價。教師可以使用評分核定工具計算最終成績，並給評閱人回饋。';
$string['switchphase50info'] = '您即將關閉此工作坊。這會使計算過的成績顯示在成績單上。學生們可以查看他們的作業和作業的評價。';
$string['taskassesspeers'] = '評價同學的作業';
$string['taskassesspeersdetails'] = '總數：{$a->total}<br />待評：{$a->todo}';
$string['taskassessself'] = '自我評價';
$string['taskconclusion'] = '提供這一活動的結語';
$string['taskinstructauthors'] = '提供對於作業的指示';
$string['taskinstructreviewers'] = '提供對於評價的指示';
$string['taskintro'] = '設定工作坊的說明';
$string['tasksubmit'] = '繳交您的作品';
$string['toolbox'] = '工作坊的工具箱';
$string['undersetup'] = '工作坊正在設定中。請等待，直到活動轉入下一階段。';
$string['useexamples'] = '使用範例';
$string['useexamples_desc'] = '提供作業範例做為練習評價用';
$string['useexamples_help'] = '如果啟用，用戶可以試著評價一或多件範例作業，並且把他們的評價與參考評價進行比較。這個成績不計算在評價成績中。';
$string['usepeerassessment'] = '使用同儕互評';
$string['usepeerassessment_desc'] = '學生們可以評價其他人的作業';
$string['usepeerassessment_help'] = '如果啟用，可以分配其他用戶的作業給任意用戶評價。而且用戶除了自己的作業成績外，還會獲得一個評價的成績。';
$string['userdatecreated'] = '繳交時間是 <span>{$a}</span>';
$string['userdatemodified'] = '修改時間是 <span>{$a}</span>';
$string['userplan'] = '工作坊計劃表';
$string['userplanaccessibilitytitle'] = '工作坊時間表分成{$a}個階段';
$string['userplancurrentphase'] = '當前階段';
$string['userplan_help'] = '工作坊計劃表列出此活動的所有階段和每個階段的任務。不同顏色高亮顯示的是目前階段，並以勾號表示任務完成。';
$string['useselfassessment'] = '使用自評';
$string['useselfassessment_desc'] = '學生們可以評價他們自己的作業';
$string['useselfassessment_help'] = '如果啟用，用戶自己的作業可能會被分配給自己評價。並且，用戶除了自己的作業成績外，還會獲得一個評價成績。';
$string['weightinfo'] = '權重：{$a}';
$string['withoutsubmission'] = '審閱評閱人未上傳自己的作業';
$string['workshop:addinstance'] = '增加一個新的工作坊';
$string['workshop:allocate'] = '分配作業受審閱';
$string['workshop:deletesubmissions'] = '刪除繳交的作業';
$string['workshop:editdimensions'] = '編輯評價表格';
$string['workshop:ignoredeadlines'] = '忽略時間限制';
$string['workshop:manageexamples'] = '管理作業範例';
$string['workshopname'] = '工作坊名稱';
$string['workshop:overridegrades'] = '覆蓋已計算的分數';
$string['workshop:peerassess'] = '同儕互評';
$string['workshop:publishsubmissions'] = '發佈作業';
$string['workshop:submit'] = '繳交';
$string['workshop:switchphase'] = '切換階段';
$string['workshop:view'] = '查看工作坊';
$string['workshop:viewallassessments'] = '查看所有評價';
$string['workshop:viewallsubmissions'] = '查看所有作業';
$string['workshop:viewauthornames'] = '查看作者姓名';
$string['workshop:viewauthorpublished'] = '查看已發佈作業的作者';
$string['workshop:viewpublishedsubmissions'] = '查看已發表的作業';
$string['workshop:viewreviewernames'] = '查看評閱人姓名';
$string['yourassessment'] = '您的評價';
$string['yourassessmentfor'] = '你對於{$a}的評價';
$string['yourgrades'] = '你的成績';
$string['yoursubmission'] = '您的作業';
