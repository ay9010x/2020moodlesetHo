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
 * Strings for component 'quiz_statistics', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   quiz_statistics
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['actualresponse'] = '實際的回答';
$string['allattempts'] = '所有作答次';
$string['allattemptsavg'] = '所有作答次的平均分數';
$string['allattemptscount'] = '完成計分的作答次的總數';
$string['analysisnameonly'] = '"{$a->name}"';
$string['analysisno'] = '({$a->number}) "{$a->name}"';
$string['analysisnovariant'] = '({$a->number}) "{$a->name}" 變形試題 {$a->variant}';
$string['analysisofresponses'] = '試題分析';
$string['analysisofresponsesfor'] = '{$a}的試題作答分析';
$string['analysisvariant'] = '"{$a->name}" 的變形試題 {$a->variant}';
$string['attempts'] = '作答次';
$string['attemptsall'] = '所有作答次';
$string['attemptsfirst'] = '第一次作答';
$string['backtoquizreport'] = '回到主要統計報告頁';
$string['calculatefrom'] = '計算統計量依據：';
$string['calculatingallstats'] = '計算測驗、試題的統計量，並分析作答資料。';
$string['cic'] = '{$a}的內部一致性係數';
$string['completestatsfilename'] = 'completestats';
$string['count'] = '次數';
$string['counttryno'] = '作答次數{$a}';
$string['coursename'] = '課程名稱';
$string['detailedanalysis'] = '此題的更詳細的作答分析';
$string['detailedanalysisforvariant'] = '對於此試題的變形試題{$a}的更詳細的試題分析';
$string['discrimination_index'] = '鑑別指數';
$string['discriminative_efficiency'] = '鑑別係數';
$string['downloadeverything'] = '下載完整報告，格式為:';
$string['duration'] = '開放期間';
$string['effective_weight'] = '有效的加權';
$string['errordeleting'] = '刪除舊的 {$a}筆紀錄時發生錯誤';
$string['errormedian'] = '在取中位數時，發生錯誤';
$string['errorpowerquestions'] = '取資料計算試題分數的變異量時，發生錯誤';
$string['errorpowers'] = '取資料計算測驗分數的變異量時，發生錯誤';
$string['errorrandom'] = '取試題資料時發生錯誤';
$string['errorratio'] = '({$a})錯誤比率';
$string['errorstatisticsquestions'] = '取資料以計算試題分數的統計量時，發生錯誤';
$string['facility'] = '難易度指數';
$string['firstattempts'] = '第一次作答';
$string['firstattemptsavg'] = '第一次作答的平均分數';
$string['firstattemptscount'] = '第一次做答的完成計分的人數';
$string['frequency'] = '次數分配';
$string['highestattempts'] = '最高分的作答次';
$string['highestattemptsavg'] = '最高分作答次的平均分數';
$string['intended_weight'] = '想要的加權';
$string['kurtosis'] = '({$a})的分數分配的峰度';
$string['lastattempts'] = '最後作答次';
$string['lastattemptsavg'] = '最後作答次的平均分數';
$string['lastcalculated'] = '自從 {$a->lastcalculated}最後一次計算之後，又多了 {$a->count} 個作答紀錄';
$string['maximumfacility'] = '最大值';
$string['median'] = '({$a})的中位數';
$string['medianfacility'] = '中位數';
$string['minimumfacility'] = '最小值';
$string['modelresponse'] = '回答範例';
$string['nameforvariant'] = '{$a->name}的變形試題 {$a->variant}';
$string['negcovar'] = '試題得分與測驗總分成負相關';
$string['negcovar_help'] = '在試題分析時，若某一題目的得分與整份測驗的總分呈負相關(能力高的在該題答錯機率較高，能力低的反而答對機率較高)，它就是一種試題鑑別度為負的，應該刪除的不良試題。

此模組的效率試題加權(effective question weight)計算公式無法處理這種案例。

遇到這種情形，你可以重新編輯測驗卷，把該鑑別力為負的題目的配分(最大分數)改為零分，再重新計分即可。';
$string['nogradedattempts'] = '在這測驗上沒有任何作答次，或者所有的作答次都優需要人工計分的試題。';
$string['nostudentsingroup'] = '還沒有學生在此一群組';
$string['optiongrade'] = '部分得分';
$string['partofquestion'] = '試題的部分';
$string['pluginname'] = '統計';
$string['position'] = '位置';
$string['positions'] = '位置';
$string['questioninformation'] = '試題資訊';
$string['questionname'] = '試題概念名稱';
$string['questionnumber'] = '題號';
$string['questionstatistics'] = '試題統計量';
$string['questionstatsfilename'] = 'questionstats';
$string['questiontype'] = '題型';
$string['quizinformation'] = '測驗卷資訊';
$string['quizname'] = '測驗卷名稱';
$string['quizoverallstatistics'] = '測驗整體統計';
$string['quizstructureanalysis'] = '測驗結構分析';
$string['random_guess_score'] = '隨機猜測分數';
$string['recalculatenow'] = '立刻從新計算';
$string['reportsettings'] = '統計量的計算設定';
$string['response'] = '回答';
$string['skewness'] = '{$a}的分數分配的偏態係數';
$string['slotstructureanalysis'] = '試題編號{$a}的結構分析';
$string['standarddeviation'] = '{$a}的標準差';
$string['standarddeviationq'] = '標準差';
$string['standarderror'] = '{$a}的標準誤';
$string['statistics'] = '統計';
$string['statistics:componentname'] = '測驗的統計報告';
$string['statisticsreport'] = '統計報告';
$string['statisticsreportgraph'] = '試題位置的統計報告';
$string['statistics:view'] = '檢視統計報告';
$string['statsfor'] = '{$a}測驗的統計報告';
$string['variant'] = '變形試題';
$string['whichtries'] = '作答分析的試題：';
