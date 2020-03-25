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
 * Strings for component 'activequiz', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   activequiz
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activequiz:addinstance'] = '新增一個IRS即時測驗活動';
$string['activequiz:attempt'] = '參加IRS即時測驗活動';
$string['activequiz:control'] = '控制即時測驗活動（只限教師）';
$string['activequiz:editquestions'] = '編輯IRS即時測驗試題。';
$string['activequizintro'] = '介紹';
$string['activequiz:seeresponses'] = '查看其他學生的回應以作評分';
$string['activequizsettings'] = '一般設定';
$string['activequiz:viewownattempts'] = '允許學生查看其即時測驗的作答';
$string['activitygrades'] = '活動成績：';
$string['addquestion'] = '加入試題';
$string['addtoquiz'] = '加入';
$string['anonymousresponses'] = '隱匿學生的回答';
$string['anonymousresponses_help'] = '如果隱匿學生的回答，這樣當老師的畫面顯示出來時，學生的姓名和群組名稱才不會被顯示出來。';
$string['anonymoususer'] = '匿名用戶';
$string['assessed'] = '要評分';
$string['assessed_help'] = '勾選此方格以令您的測驗能被評分';
$string['attempt_grade'] = '作答成績';
$string['attemptno'] = '作答次數';
$string['attempts'] = '作答紀錄';
$string['attemptstarted'] = '請點按下方按鈕，將由您自己開始一個測驗作答活動';
$string['attemptstartedalready'] = '您其中的組員已經開始測驗作答活動';
$string['attemptview'] = '檢視作答紀錄';
$string['cantaddquestiontwice'] = '你不能增加相同的題目到測驗中';
$string['cantinitattempts'] = '不能為您初始化測驗作答活動';
$string['closesession'] = '結束活動';
$string['closingsession'] = '結束活動中...';
$string['countdatasetlabel'] = '回答數目';
$string['defaultquestiontime'] = '預設顯示試題的時間';
$string['defaultquestiontime_help'] = '顯示每道題目的預設時間。<br />
每道題目可以個別重新設定';
$string['edit'] = '編輯測驗';
$string['editpage_opensession_error'] = '當活動開始時，您不能修改測驗試題或編排。';
$string['enabledquestiontypes'] = '啟用試題類型';
$string['enabledquestiontypes_info'] = '已啟用用作例子的題型';
$string['endquestion'] = '結束試題';
$string['errorregrade'] = '抱歉，重新評分時出現錯誤';
$string['eventattemptstarted'] = '已開始作答';
$string['eventattemptviewed'] = '已檢視作答';
$string['eventquestionanswered'] = '已回答的題目';
$string['eventquestionmanuallygraded'] = '試題人工評分';
$string['feedbackintro'] = '試題的回饋。請等候老師準備下一題';
$string['firstsession'] = '第一次的分數';
$string['fullanonymize'] = '完全匿名不計成績';
$string['fullanonymize_help'] = '隱匿學生的回答。請注意如果您選擇此選項，此次回答將不被評分及適用於學生。';
$string['gatheringresults'] = '搜集結果中...';
$string['gotosession'] = '前往進行中的活動';
$string['grademethod'] = '活動評分方式';
$string['grademethod_help'] = '這方法會在評分時使用，用作判斷在同一測驗中多個部份的等級。';
$string['gradesettings'] = '成績設定';
$string['groupattendance'] = '允許群組參與';
$string['groupattendance_help'] = '如果啟用此方格，參加測驗的學生可以選擇其出席的組員';
$string['grouping'] = '分群組';
$string['grouping_help'] = '選擇學生的分組方法';
$string['groupmembership'] = '群組成員';
$string['groupworksettings'] = '群組設定';
$string['hide_correct_answer'] = '隱藏正確答案';
$string['hidenotresponded'] = '隱藏沒有作答的';
$string['hidestudentresponses'] = '隱藏答題紀錄';
$string['highestsessiongrade'] = '最高的分數';
$string['indvquestiontime'] = '試題作答時間';
$string['indvquestiontime_help'] = '試題作答時間以秒計算。';
$string['instructorquizinst'] = '<p>請在此頁面等待直至學生已連結。  當 <b>start quiz</b> 已被點擊，測驗會以第一條題目開始</p>
    <p>
<p>控制：</p>
    <ul>
        <li>
            再輪詢
            <ul>
                <li>
                   允許教師再輪詢現時及過去的題目（檢視題目時可用）
                </li>
            </ul>
        </li>
        <li>
           下一條題目
            <ul>
                <li>
                   繼續下一條題目（檢視題目時可用）
                </li>
            </ul>
        </li>
        <li>
          完結題目
            <ul>
                <li>
                    結束現時題目。教師亦可以提早結束限時題目（當題目正在運行時可用） <i>如果題目沒有時間限制，教師需要點擊<b> 結束題目</b></i>
                </li>
            </ul>
        </li>
        <li>
            前往題目
            <ul>
                <li>
                  開啟對話視窗以拍示所有用戶到測驗中的指定題目（檢視題目時可用）
                </li>
            </ul>
        </li>
        <li>
            結束活動
            <ul>
                <li>
                    結束當前活動及學生的所有作答。作答將會自動評分（任何時候都可用）
                </li>
            </ul>
        </li>
        <li>
            重新加載結果
            <ul>
                <li>
                   重新加載在資訊欄中的學生回答。尤許教師查看有多少學生／小組已回應及有多少學生／小組尚未回應（檢視題目時可用）
                </li>
            </ul>
        </li>
        <li>
           隱藏／顯示沒有回應
            <ul>
                <li>
                 隱藏／顯示資訊欄中顯示有多少學生／小組已回應及有多少學生／小組尚未回應（輪詢題目時可用）
                </li>
            </ul>
        </li>
        <li>
            顯示正確答案
            <ul>
                <li>
                    讓教師可以檢視回管正確的題目（檢視題目時可用）。但正確答案將不會顯示在需要人手評分的題型，如論文或繪圖。
                </li>
            </ul>
        </li>
    </ul>
</p>';
$string['instructorsessionsgoing'] = '活動進行中，請點按下列按鈕前往活動';
$string['invalidattemptaccess'] = '你沒有權限進行這一次的作答';
$string['invalidgroupid'] = '學生需要有一個有效的群組編號';
$string['invalid_indvquestiontime'] = '試題時間必須是0以上的整數';
$string['invalid_numberoftries'] = '回答次數必須是0以上的整數';
$string['invalid_points'] = '分數是必要的，且必須是大於0的數目';
$string['invalidquestionattempt'] = '無效題目($a->questionname) 被加到測驗作答中';
$string['isanonymous'] = '即時測驗中的回應皆是匿名的';
$string['joinquiz'] = '參加測驗';
$string['joinquizinstructions'] = '點按下方按鈕參加測驗';
$string['jumptoquesetioninstructions'] = '選擇一道您想使用的試題：';
$string['jumptoquestion'] = '選用試題';
$string['lastsession'] = '最後一次分數';
$string['loading'] = '測驗啟動中';
$string['manualcomment'] = '手動評論';
$string['manualcomment_help'] = '教師在評核作答時可用的回饋';
$string['marks'] = '成績';
$string['marks_help'] = '每道題目的數值等級及整體作答的分數';
$string['modulename'] = 'IRS即時測驗';
$string['modulename_help'] = '<p>IRS即時測驗活動能讓老師建立和管理一個即時的評量活動。所有題庫中的測驗題型都用在IRS即時測驗活動中。</p>

<p> IRS即時測驗允許個人或小組形式。可以以小組形式出席，只有出席活動的組員會獲得分數。題目可以設定為可以多次作答。可以設定每道題目的時間限制以自動完結問題，或教師可以手動結束問題並前往下一道題目。教師亦可以在活動進行時跳到不同題目。教師可以監察小組／個人參與、即時回應及被輪詢的問題。</p>

<p>每次的IRS即時測驗作答將會像一般測驗一樣自動評分（除了論文題及PoodLL 問題），成績將會在成績薄上紀簿。 小組成績將會自動傳送到每個組員的成績簿。</p>

<p>教師可以在學生完成測驗前選擇顯示提示、給予回饋及顯示正確答案。</p>

<p>IRS即時測驗比起Moodke更可以推動小組形式學習。</p>';
$string['modulenameplural'] = 'IRS即時測驗';
$string['nextquestion'] = '下一題';
$string['nochangegroups'] = '在建立分組的時段後或在沒有分組設定下，您不能夠更改群組。';
$string['nochangegroups_label'] = '&nbsp;';
$string['nofeedback'] = '這題沒有回饋內容';
$string['no_questions'] = '沒有試題加到這個測驗中。';
$string['nosession'] = '目前沒有開啟的活動';
$string['notime'] = '沒有時間限制';
$string['notime_help'] = '點擊此欄以取消此題目的時間限制。<p> 教師需要點擊「完結問題」按鍵以結束問題。   </p>';
$string['notresponded'] = '還可以做答幾次';
$string['notries'] = '這一題你已經不能再嘗試了';
$string['numberoftries'] = '作答次數';
$string['numberoftries_help'] = '學生在回答一個試題時，可以嘗試幾次。學生仍然會受到答題時間的限制';
$string['overallgrade'] = '總分：{$a->overallgrade} / {$a->scale}';
$string['percentagedatasetlabel'] = '全部答案的百分比';
$string['pluginadministration'] = 'IRS即時測驗管理';
$string['pluginname'] = 'IRS即時測驗';
$string['points'] = '試題點數';
$string['points_help'] = '這一試題的配分';
$string['qdeleteerror'] = '無法刪除試題';
$string['qdeletesucess'] = '成功刪除試題';
$string['qmoveerror'] = '無法搬移試題';
$string['qmovesuccess'] = '成功搬移試題';
$string['question'] = '試題';
$string['questiondelete'] = '刪除試題 {$a}';
$string['questionedit'] = '編輯試題';
$string['questionfinished'] = '試題完成，等待結果';
$string['questionlist'] = '試題清單';
$string['questionmovedown'] = '把試題{$a}往下移';
$string['questionmoveup'] = '把試題{$a}往上移';
$string['quiznotrunning'] = '此刻測驗活動尚未開始，請等候您的老師啟動。點按[重新載入]按鈕可以再次檢查。';
$string['regradeallgrades'] = '將所有分數重新計分';
$string['reload_results'] = '重新載入結果';
$string['repollquestion'] = '隨機重排試題';
$string['response_attempt_controls'] = '編輯/檢視作答';
$string['responses'] = '檢視答題記錄';
$string['reviewafter'] = '在活動結束後';
$string['reviewoptionsettings'] = '學生可查閱的資料';
$string['savequestion'] = '儲存試題';
$string['scale'] = '最高成績';
$string['scale_help'] = '此數值（整數）將會衡量在測驗中取得的分數至此數值。';
$string['select_group'] = '選擇您的群組';
$string['selectsession'] = '選擇要檢視的活動：&nbsp;&nbsp;&nbsp;&nbsp;';
$string['sessionaverage'] = '平均分數';
$string['sessionclosed'] = '活動已經結束';
$string['sessionname'] = '活動名稱';
$string['sessionname_required'] = '活動名稱一定要填寫';
$string['sessionnametext'] = '<span style="font-weight: bold">活動：</span>';
$string['show_correct_answer'] = '顯示正確答案';
$string['showhistoryduringquiz'] = '顯示作答歷程';
$string['showhistoryduringquiz_help'] = '當檢視問題的回應時，顯示此問題的學生／小組回答紀錄。';
$string['shownotresponded'] = '顯示沒有作答的';
$string['showstudentresponses'] = '顯示答題記錄';
$string['startedon'] = '開始於';
$string['startquiz'] = '開始測驗';
$string['start_session'] = '開始活動';
$string['studentquizinst'] = '<p>請等候老師開始這個測驗。一旦開始第一題，您將會看見一個計時器在倒數計時</p>';
$string['successregrade'] = '成功重新評分';
$string['teacherjoinquizinstruct'] = '如果您想要自己先測試也可以<br />(您可以在新視窗開始一個測驗)';
$string['teacherstartinstruct'] = '啟用即時測驗讓學生可以參加<br />請先在下方訂定活動名稱(這個名稱可以幫助日後尋找結果使用)。';
$string['theattempt'] = '作答紀錄';
$string['theattempt_help'] = '學生是否可以查閱作答紀錄';
$string['timecompleted'] = '完成時間';
$string['timemodified'] = '修改時間';
$string['timertext'] = '您的試題將會結束並自動收回：';
$string['trycount'] = '您還有{$a->tries} 次作答機會。';
$string['unabletocreate_session'] = '無法建立活動';
$string['view'] = '檢視測驗';
$string['viewstats'] = '檢視測驗統計';
$string['waitforquestion'] = '稍候試題將發送出去：';
$string['waitforquestiontime'] = '等待試題的時間';
$string['waitforquestiontime_help'] = '等候試題開始的時間';
$string['waitforrevewingend'] = '教師現正檢視先前的題目，請等候下一道試題開始。';
$string['waitstudent'] = '等待學生連線中';
$string['workedingroups'] = '以群組方式運作';
$string['workedingroups_help'] = '勾選此方格以標明學生將會以小組形式合作。請確保您亦點選以下的分組。';
