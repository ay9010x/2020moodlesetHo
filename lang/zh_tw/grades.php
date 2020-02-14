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
 * Strings for component 'grades', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   grades
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activities'] = '活動';
$string['addcategory'] = '新增類別';
$string['addcategoryerror'] = '無法新增類別';
$string['addexceptionerror'] = '錯誤發生在新增使用者帳號例外的處理時:評分項目';
$string['addfeedback'] = '加入回饋';
$string['addgradeletter'] = '增加一個文字等第';
$string['addidnumbers'] = '加入ID 號碼';
$string['additem'] = '加入評分項目';
$string['addoutcome'] = '新增一個能力指標';
$string['addoutcomeitem'] = '加入能力指標項目';
$string['addscale'] = '增加一個量尺';
$string['adjustedweight'] = '調整的加權量';
$string['aggregateextracreditmean'] = '成績的平均值(含額外加分)';
$string['aggregatemax'] = '最高成績';
$string['aggregatemean'] = '成績的平均值';
$string['aggregatemedian'] = '成績的中位數';
$string['aggregatemin'] = '最低成績';
$string['aggregatemode'] = '成績的眾數';
$string['aggregatenotonlygraded'] = '包含空白分數';
$string['aggregateonlygraded'] = '排除空白分數';
$string['aggregateonlygraded_help'] = '空白成績是指在成績簿中沒有成績。一份還未評分的作業，或一個還未參加的測驗等都會導致空白成績的出現。

此設定決定空白成績是要不包含在彙總中，還是要以最低分（例如評分範圍為0-100的作業的最低分是0）計算。';
$string['aggregateoutcomes'] = '在成績彙總中包含能力指標';
$string['aggregateoutcomes_help'] = '如果啟用此項，能力指標將被包含在成績彙總中。這可能導致非期望的類別總分。';
$string['aggregatesonly'] = '切換到只彙總';
$string['aggregatesubcatsupgradedgrades'] = '注意：在網站升級時，彙總的設定中"彙總包含下層類別"已經被移除。由於"彙總包含下層類別"曾經在此課程中使用，建議你檢查這成績簿的變更。';
$string['aggregatesum'] = '原始總分';
$string['aggregateweightedmean'] = '所有分數的加權平均數';
$string['aggregateweightedmean2'] = '所有分數的簡單加權平均數';
$string['aggregation'] = '彙總';
$string['aggregationcoef'] = '彙總係數';
$string['aggregationcoefextra'] = '額外加分';
$string['aggregationcoefextra_help'] = '當使用“原始總分”或“簡單加權平均分”為彙總算法，且勾選了“額外加分”時，這計分項的最高分不會被加到這類別的最高分。這可能導致雖然不是所有計分項目都得到了滿分，但這類別分數卻達到滿分。如果網站管理員有啟用分數可以超過最高分，就可能使得分超過最高分。

當使用“平均分（含額外加分）”彙總算法，且額外加分設定為大於0的數值時，在計算完平均分後，額外加分會被乘以額外加分值，然後再加入總分中。';
$string['aggregationcoefextrasum'] = '額外加分';
$string['aggregationcoefextrasumabbr'] = '+';
$string['aggregationcoefextrasum_help'] = '如果額外加分框被勾選，此成績項的最高分不會被加入類別最高分，這可能會導致拿到類別的最高分（如果網站管理員允許，也可能超過最高分），但並不是所有成績項都是最高分。';
$string['aggregationcoefextraweight'] = '額外加分的加權量';
$string['aggregationcoefextraweight_help'] = '如果額外加分的加權量被設為一個大於0的值，那麼該成績項在彙總時會被當作加分。此值會被當作一個因數與成績相乘，然後再被加入總分，用來計算平均數。';
$string['aggregationcoefweight'] = '計分項目的加權量';
$string['aggregationcoefweight_help'] = '計分項目的加權量是用來表示在同一類別中，不同計分項目的相對重要性。適當的項目加權量更能反映學生的真正學習成就。教師通常給予較花時間的、較難的、需要較高層次思考的計分項目有較高的加權量。';
$string['aggregation_help'] = '彙總算法決定了一個類別中的成績如何合併計算。例如：

* 平均數 - 所有分數的總和除以分數個數<br />
* 中位數 - 把所有分數按大小排序後，處於中間位置的數值<br />
* 最低分<br />
* 最高分<br />
* 眾數 - 出現頻率最高的分數<br />
* 原始總分 - 所有依加權量調整的分數值的總和。';
$string['aggregationhintdropped'] = '(被放棄)';
$string['aggregationhintexcluded'] = '(被排除)';
$string['aggregationhintextra'] = '(額外加分)';
$string['aggregationhintnovalue'] = '(空白)';
$string['aggregationofa'] = '{$a}的彙總';
$string['aggregationposition'] = '彙總位置';
$string['aggregationposition_help'] = '此設定決定類別和成績的總分是要顯示在成績簿報表的第一欄還是最後一欄。';
$string['aggregationsvisible'] = '可用的彙總類型';
$string['aggregationsvisiblehelp'] = '選擇所有可以使用的成績彙總類型。按住Ctrl按鍵可以選擇多項。';
$string['allgrades'] = '按分類統計所有分數';
$string['allstudents'] = '所有學生';
$string['allusers'] = '所有用戶';
$string['autosort'] = '自動排序';
$string['availableidnumbers'] = '可使用的ID編號';
$string['average'] = '平均';
$string['averagesdecimalpoints'] = '欄平均分數的小數點位數';
$string['averagesdecimalpoints_help'] = '此設定決定每個平均分數顯示的小數位數，又或者是使用類別或成績項的小數位數設定（繼承）。';
$string['averagesdisplaytype'] = '欄平均分數的顯示方式';
$string['averagesdisplaytype_help'] = '此設定決定平均分數顯示為數值、百分比還是文字等第，或者是使用類別或成績項的顯示設定（繼承）。';
$string['backupwithoutgradebook'] = '備份時不包括成績薄設定';
$string['badgrade'] = '提供的成績是無效的';
$string['badlyformattedscale'] = '請輸入若干個用英文逗號分隔的值（至少要有兩個值）。';
$string['baduser'] = '提供的用戶是無效的';
$string['bonuspoints'] = '獎勵分數';
$string['bulkcheckboxes'] = '大量的核取方塊';
$string['calculatedgrade'] = '計算出的成績';
$string['calculation'] = '計算方法';
$string['calculationadd'] = '增加計算方法';
$string['calculationedit'] = '編輯計算方法';
$string['calculation_help'] = '成績計算方法是一個用來決定成績的公式。該公式應該以等號（=）開始，且可以使用常見的數學運算，比如max、min和sum。如果需要，你也可以包含其它評分項目，你只需在公式中輸入它們的ID號，並用兩個方括號括起來。';
$string['calculationsaved'] = '計算方法已存檔';
$string['calculationview'] = '檢視計算方法';
$string['cannotaccessgroup'] = '無法存取所選群組的成績，抱歉。';
$string['categories'] = '類別';
$string['categoriesanditems'] = '類別和項目';
$string['category'] = '類別';
$string['categoryedit'] = '編修類別';
$string['categoryname'] = '類別名稱';
$string['categorytotal'] = '類別總分';
$string['categorytotalfull'] = '{$a->category}總分';
$string['categorytotalname'] = '類別總分名稱';
$string['changedefaults'] = '更改預設值';
$string['changereportdefaults'] = '更改報表預設值';
$string['chooseaction'] = '請選擇一個動作...';
$string['choosecategory'] = '選擇類別';
$string['combo'] = '分頁和下拉選單';
$string['compact'] = '精簡';
$string['componentcontrolsvisibility'] = '在活動設定中控制是否隱藏該成績項。';
$string['contract'] = '學習契約類別';
$string['contributiontocoursetotal'] = '貢獻到課程總分';
$string['controls'] = '控制';
$string['courseavg'] = '課程平均';
$string['coursegradecategory'] = '課程成績類別';
$string['coursegradedisplaytype'] = '課程成績顯示方式';
$string['coursegradedisplayupdated'] = '課程成績顯示方式已更新';
$string['coursegradesettings'] = '課程成績設定';
$string['coursename'] = '課程名稱';
$string['coursescales'] = '課程量尺';
$string['coursesettings'] = '課程設定';
$string['coursesettingsexplanation'] = '課程設定決定這課程的參與者會看到什麼樣的成績簿';
$string['coursesiamtaking'] = '我選修的課程';
$string['coursesiamteaching'] = '我教學的課程';
$string['coursetotal'] = '課程總分';
$string['createcategory'] = '建立類別';
$string['createcategoryerror'] = '無法建立一個新類別';
$string['creatinggradebooksettings'] = '建立成績簿的設定';
$string['csv'] = 'CSV';
$string['currentparentaggregation'] = '父類別彙總算法';
$string['curveto'] = '調整成';
$string['decimalpoints'] = '整體的小數點';
$string['decimalpoints_help'] = '此設定指定顯示在每個成績中的小數位數。成績計算仍按照5位小數的精確度進行，不受此設定影響。';
$string['default'] = '預設';
$string['defaultprev'] = '預設({$a})';
$string['deletecategory'] = '刪除類別';
$string['disablegradehistory'] = '停用成績的歷史紀錄';
$string['disablegradehistory_help'] = '關閉成績相關資料表中修改歷程的追蹤。這樣作可以些微加速伺服器的速度，以及節省資料庫的空間。';
$string['displaylettergrade'] = '顯示文字等第';
$string['displaypercent'] = '顯示百分比';
$string['displaypoints'] = '顯示原始分數';
$string['displayweighted'] = '顯示加權成績';
$string['dropdown'] = '下拉選單';
$string['droplow'] = '去掉最低分';
$string['droplowestvalue'] = '設定分數在幾分以下要去掉';
$string['droplowestvalues'] = '去掉{$a}個最低分數';
$string['droplow_help'] = '這一設定，讓你能夠指定要從彙整統計中去掉幾個最低分數。';
$string['dropped'] = '去掉的';
$string['dropxlowest'] = '去掉X個最低分';
$string['dropxlowestwarning'] = '提醒: 如果您要使用去掉x個最低分數，則此類別的所有項目要使有相同的配分。
假若項目間配分彼此有不同，其統計結果將無法預測。';
$string['duplicatescale'] = '複製量尺';
$string['edit'] = '編輯';
$string['editcalculation'] = '編輯計算方法';
$string['editcalculationverbose'] = '{$a->category}{$a->itemmodule} {$a->itemname}編輯計算';
$string['editfeedback'] = '編修回饋';
$string['editgrade'] = '編修成績';
$string['editgradeletters'] = '編輯文字等第';
$string['editoutcome'] = '編輯能力指標';
$string['editoutcomes'] = '編輯能力指標';
$string['editscale'] = '編輯量尺';
$string['edittree'] = '編輯類別和項目';
$string['editverbose'] = '編輯{$a->category}{$a->itemmodule} {$a->itemname}';
$string['enableajax'] = '啟用AJAX';
$string['enableajax_help'] = '在評分者報告上增加一層的AJAX功能，這將簡化和加速常用的操作。這取決於在用戶的瀏覽器端是否有啟用Javascript功能。';
$string['enableoutcomes'] = '啟用能力指標';
$string['enableoutcomes_help'] = '啟用能力指標(也稱為競爭力、目標、水平或標準)，表示我們可以用一個或多個量尺來評量教學目標所強調的重要學習成果。啟用能力指標後，全站都可以使用這種特別的評分方法。';
$string['encoding'] = '編碼';
$string['encoding_help'] = '請選擇用於這資料的字母編碼方式。(標準編碼方式是UTF-8)。如果誤選了錯誤的編碼方式，你會在匯入資料前，預覽資料時會注意到。';
$string['errorcalculationbroken'] = '可能是迴圈式的相互參照或是不完整的計算公式';
$string['errorcalculationnoequal'] = '公式必須由等號開始(=1+2)';
$string['errorcalculationunknown'] = '無效的公式';
$string['errorgradevaluenonnumeric'] = '收到非數值的評分';
$string['errornocalculationallowed'] = '這項不允許被計算';
$string['errornocategorisedid'] = '無法取得一個未分類的編號';
$string['errornocourse'] = '無法取得課程資訊';
$string['errorreprintheadersnonnumeric'] = '收到非數值的轉載標題';
$string['errorsavegrade'] = '無法儲存成績，抱歉。';
$string['errorsettinggrade'] = '在儲存用戶ID為 {$a->userid}之{$a->itemname}成績時發生錯誤';
$string['errorupdatinggradecategoryaggregateonlygraded'] = '在更新成績類別(編號{$a->id})的設定"只彙整非空白的分數"時，發生錯誤。';
$string['errorupdatinggradecategoryaggregateoutcomes'] = '在更新成績類別(編號{$a->id})的"包含能力指標在彙整統計"設定時，發生錯誤。';
$string['errorupdatinggradecategoryaggregation'] = '在更新成績類別(編號{$a->id})的彙整統計類型時，發生錯誤。';
$string['errorupdatinggradeitemaggregationcoef'] = '在更新成績類別(編號{$a->id})的彙整係數(加權量或額外計分)時，發生錯誤。';
$string['eventgradedeleted'] = '分數已刪除';
$string['eventgradeviewed'] = '成績已在成績簿上被檢視';
$string['eventusergraded'] = '用戶已被評分';
$string['excluded'] = '已排除';
$string['excluded_help'] = '若被勾選，此分數將不會包含到彙整統計。';
$string['expand'] = '展開類別';
$string['export'] = '匯出';
$string['exportalloutcomes'] = '匯出所有能力指標';
$string['exportfeedback'] = '匯出時包含回饋內容';
$string['exportformatoptions'] = '匯出格式選項';
$string['exportonlyactive'] = '排除休學的用戶';
$string['exportonlyactive_help'] = '在匯出時，只包含有選課且沒有休學的學生。';
$string['exportplugins'] = '匯出外掛';
$string['exportsettings'] = '匯出設定值';
$string['exportto'] = '匯出到';
$string['externalurl'] = '外部網址';
$string['externalurl_desc'] = '若使用外部的成績簿，必須在此指定網址。';
$string['extracreditvalue'] = '{$a}的額外加分值';
$string['extracreditwarning'] = '提醒：設定一類別中所有項目為額外加分，會將它們從成績計算中排除。因為這一類別沒有分數加總。';
$string['feedback'] = '回饋';
$string['feedbackadd'] = '增加回饋';
$string['feedbackedit'] = '編修回饋';
$string['feedbackforgradeitems'] = '給{$a}的回饋';
$string['feedback_help'] = '這方格是用來對此分數加上補充說明、評論等等。';
$string['feedbacks'] = '回饋';
$string['feedbacksaved'] = '回饋已儲存';
$string['feedbackview'] = '檢視回饋內容';
$string['finalgrade'] = '最終成績';
$string['finalgrade_help'] = '若覆蓋被勾選了，那教師可以對最後成績進行加分或修正分數。';
$string['fixedstudents'] = '固定住學生姓名欄位';
$string['fixedstudents_help'] = '藉著固定住學生姓名欄位，可讓成績報表在水平捲動，不會看不見學生的名字。';
$string['forceimport'] = '強制匯入';
$string['forceimport_help'] = '要強制匯入分數，即使這匯入檔在被匯出之後，分數已被更新過';
$string['forceoff'] = '強制：關';
$string['forceon'] = '強制：開';
$string['forelementtypes'] = '給選擇的{$a}';
$string['forstudents'] = '給學生';
$string['full'] = '全部';
$string['fullmode'] = '切換到完整檢視';
$string['generalsettings'] = '一般設定';
$string['grade'] = '成績';
$string['gradeadministration'] = '成績管理';
$string['gradealreadyupdated'] = '有{$a}個分數沒有被匯入，因為在匯入檔的分數比評分者報告的還舊。若不敢怎樣都要進行分數匯入，請使用強制匯入選項。';
$string['gradeanalysis'] = '成績分析';
$string['gradebook'] = '成績簿';
$string['gradebookcalculationsfixbutton'] = '接受分數的更改，並修正計算公式的錯誤';
$string['gradebookcalculationsuptodate'] = '在這成績簿上的計算公式是最新的。你可能需要重新載入這一頁面才能看到改變。';
$string['gradebookcalculationswarning'] = '注意：已經偵測到在這成績簿上，在計算要顯示的分數時會有一些錯誤。它建議這錯誤可以藉由點選以下的按鈕來修正這一錯誤，這會導致一些分數被更改。

詳細情形請參見在 <a href="{$a->url}">成績簿計算公式的更改</a>版本{$a->gradebookversion} 和 {$a->currentversion}i之間的更改。';
$string['gradebookhiddenerror'] = '成績簿目前設定為對學生隱藏所有項目';
$string['gradebookhistories'] = '成績的歷史紀錄';
$string['gradebooksetup'] = '成績簿的設定';
$string['gradebook_simplesetup'] = '回復至簡易介面';       // by YCJ
$string['gradebook_originalsetup'] = '切換成進階介面';     // by YCJ
$string['moodlesetsetup'] = '簡易設定';    // by YCJ
$string['gradeboundary'] = '文字等第的分界線';
$string['gradeboundary_help'] = '這一設定決定在分數在幾分到幾分之間，應該指派到哪一個文字等第(A、B、C)。';
$string['gradecategories'] = '成績類別';
$string['gradecategory'] = '成績類別';
$string['gradecategoryonmodform'] = '成績類別';
$string['gradecategoryonmodform_help'] = '這一設定是用來控制要將這一活動(計分項目)的分數放在成績簿中的哪一類別裡。';
$string['gradecategorysettings'] = '成績類別設定';
$string['gradedisplay'] = '成績顯示';
$string['gradedisplaytype'] = '成績顯示類型';
$string['gradedisplaytype_help'] = '此設定決定在評分者和用戶報告中成績要如何顯示。

* 實得分數--依據對錯及各題配分累加所得分數<br />
* 百分比--實際獲得配分和最高總配分的比值<br />
* 等第 - 用字母或文字來表示一個範圍的分數。';
$string['gradedon'] = '分數：{$a}';
$string['gradeexport'] = '匯出成績';
$string['gradeexportcolumntype'] = '{$a->name} ({$a->extra})';
$string['gradeexportcustomprofilefields'] = '匯出成績時自訂個人資料欄位';
$string['gradeexportcustomprofilefields_desc'] = '在匯出成績時，要包含這些自訂的個人資料欄位，各欄位之間要以逗點隔開。';
$string['gradeexportdecimalpoints'] = '輸出成績保留小數點位數';
$string['gradeexportdecimalpoints_desc'] = '匯出時顯示的小數位數，可在匯出時更改這個設定。';
$string['gradeexportdisplaytype'] = '成績匯出的顯示類型';
$string['gradeexportdisplaytype_desc'] = '在匯出時，成績可以顯示為實得分數、百分比或者文字等第(A,B,C等)。這設定可在匯出時再次設定以覆蓋原來的。';
$string['gradeexportdisplaytypes'] = '成績匯出顯示類型';
$string['gradeexportuserprofilefields'] = '成績匯出時用戶個人資料欄位';
$string['gradeexportuserprofilefields_desc'] = '在成績匯出中，包含這些用戶個人資料欄位，欄位之間以逗點隔開';
$string['gradeforstudent'] = '{$a->student}<br />{$a->item}{$a->feedback}';
$string['gradegrademinmax'] = '把最低分和最高分放大標示';
$string['gradehelp'] = '成績的輔助說明';
$string['gradehistorylifetime'] = '成績歷史記錄要保留幾天';
$string['gradehistorylifetime_help'] = '這會指定更改成績相關的資料表的歷史紀錄的保留時間，建議您保留越久越好。但如果碰到效能降低或資料庫空間限制的問題，可以嘗試調降數值。';
$string['gradeimport'] = '成績匯入';
$string['gradeimportfailed'] = '提交成績時成績匯入失敗。詳情為：';
$string['gradeitem'] = '評分項目';
$string['gradeitemaddusers'] = '不參與評分？？？？？';
$string['gradeitemadvanced'] = '評分項目的進階選項';
$string['gradeitemadvanced_help'] = '選擇在編輯評分項目時應被顯示為進階選項的所有元素';
$string['gradeitemislocked'] = '此活動已經在成績簿中被鎖定。在這活動上的分數變更將不會被複製到成績簿中，除非它先被解除鎖定。';
$string['gradeitemlocked'] = '評分項目已被鎖定';
$string['gradeitemmembersselected'] = '被選出的評分項目';
$string['gradeitemminmax'] = '在評分項目的設定上，指定最低分和最高分';
$string['gradeitemnonmembers'] = '沒有評分項目';
$string['gradeitemremovemembers'] = '移除評分項目';
$string['gradeitems'] = '評分項目';
$string['gradeitemsettings'] = '評分項目的設定';
$string['gradeitemsinc'] = '將包含的評分項目';
$string['gradeletter'] = '文字等第';
$string['gradeletter_help'] = '文字等第是以英文字母，如，A，B，C，...，或文字，如，優，甲，乙，丙，...等來代表各種層次的分數。';
$string['gradeletternote'] = '要刪除一個等第，只需要清空上面三項中的任何一項，再送出即可。';
$string['gradeletteroverridden'] = '預設的分數等第已經被重寫過';
$string['gradeletters'] = '文字等第';
$string['gradelocked'] = '成績已鎖定';
$string['gradelong'] = '{$a->grade} / {$a->max}';
$string['grademax'] = '最高成績';
$string['grademax_help'] = '這設定決定當使用數值類型時的最高分數。以活動為基礎的計分項目可以在活動設定頁裡設定最高分。';
$string['grademin'] = '最低成績';
$string['grademin_help'] = '這設定決定當使用數值成績類型時的最低分數。';
$string['gradeoutcomeitem'] = '成績能力指標項';
$string['gradeoutcomes'] = '能力指標';
$string['gradeoutcomescourses'] = '課程能力指標';
$string['gradepass'] = '及格分數';
$string['gradepassgreaterthangrade'] = '及格分數不能高於最大可能分數{$a}';
$string['gradepass_help'] = '這個設定決定了通過所需要的最低分數。

這數值被用於在活動和課程完成時，且在成績簿中，及格的分數以綠色顯示，而不及格的分數則以紅色顯示。';
$string['gradepointdefault'] = '分數的預設值';
$string['gradepointdefault_help'] = '此一設定決定一計分項目中可用分數的預設值。';
$string['gradepointdefault_validateerror'] = '此設定必須是一個介於1與最高分數之間之整數。';
$string['gradepointmax'] = '最高分數';
$string['gradepointmax_help'] = '此一設定訂出一個活動裡可用的最高分數。';
$string['gradepointmax_validateerror'] = '此設定必須是介於1與 10000間之整數。';
$string['gradepreferences'] = '成績使用偏好';
$string['gradepreferenceshelp'] = '成績使用偏好的輔助說明';
$string['gradepublishing'] = '啟用成績發佈功能';
$string['gradepublishing_help'] = '在匯出和匯入上啟用發佈功能：即不需要登入Moodle網站，匯出的成績即可藉由網址來作存取。成績可以藉由存取網址來作匯入(意思是一個Moodle網站可以匯入另一個網站發佈的成績)。預設只有網站管理員能夠使用這種功能；在賦予必要的權限給其他的角色之前，請先教導用戶(共用書籤和下載加速器的危險性，IP的限制等等)。';
$string['gradepublishinglink'] = '下載：{$a}';
$string['gradereport'] = '成績報表';
$string['graderreport'] = '評分者報表';
$string['grades'] = '成績';
$string['gradesforuser'] = '{$a->user}的成績';
$string['gradesonly'] = '切換到僅成績';
$string['gradessettings'] = '成績設定';
$string['gradetype'] = '成績類型';
$string['gradetype_help'] = '有4種成績類型：

* 無 - 不可能計分<br />
* 數值 - 一個數值，伴隨最大和最小數值<br />
* 量尺- 在一個列表中依大小排列的項目<br />
* 文字 - 只用於回饋<br />
只有數值和量尺分數可以做彙整統計。<br />
源自於活動的計分項目的分數類型，要在活動設定頁上設定。';
$string['gradevaluetoobig'] = '其中一個分數大於{$a}所允許的最高分數';
$string['gradeview'] = '檢視成績';
$string['gradewasmodifiedduringediting'] = '為{$a->username}的{$a->itemname}打的分數已被忽略，因為它最近被別人更新過。';
$string['gradeweighthelp'] = '成績權重說明';
$string['groupavg'] = '群組平均';
$string['hidden'] = '隱藏';
$string['hiddenasdate'] = '顯示隱藏的成績的提交日期';
$string['hiddenasdate_help'] = '如果用戶不能看到隱藏的成績，那麼顯示提交時間，而不是“-”。';
$string['hidden_help'] = '若勾選，學生會看不到分數，直到教師所設定的日期。
這樣可以讓教師在評分全部完成後，全部成績同時公布。';
$string['hiddenuntil'] = '隱藏到';
$string['hiddenuntildate'] = '隱藏到：{$a}';
$string['hideadvanced'] = '隱藏進階功能';
$string['hideaverages'] = '隱藏平均分數';
$string['hidecalculations'] = '隱藏計算方法';
$string['hidecategory'] = '隱藏';
$string['hideeyecons'] = '隱藏 顯示/隱藏 圖示';
$string['hidefeedback'] = '隱藏回饋';
$string['hideforcedsettings'] = '隱藏強制設定';
$string['hideforcedsettings_help'] = '不要在評分界面中顯示強制設定。';
$string['hidegroups'] = '隱藏群組';
$string['hidelocks'] = '隱藏鎖定';
$string['hidenooutcomes'] = '顯示能力指標';
$string['hidequickfeedback'] = '隱藏快速回饋';
$string['hideranges'] = '隱藏分數範圍';
$string['hidetotalifhiddenitems'] = '如果含有隱藏的成績項，就隱藏總分';
$string['hidetotalifhiddenitems_help'] = '此設定指定是否將包含隱藏項目的總分顯示給學生看或以連字符(-)取代。<br /> 若要顯示，那這總分到底要以排除或包含隱藏項目來計算。<br /> 如果隱藏的項目被排除在外，這總分會不同於老師所看到的總分，因為老師總是能看到以所有項目計算的總分，不管是隱藏的或非隱藏的。<br /> 若隱藏的項目都包括在內，學生可能會去推算隱藏的項目的分數。';
$string['hidetotalshowexhiddenitems'] = '顯示除了隱藏成績項以外的總分';
$string['hidetotalshowinchiddenitems'] = '顯示包含隱藏成績項的總分';
$string['hideverbose'] = '隱藏 {$a->category}{$a->itemmodule} 中的{$a->itemname}';
$string['highgradeascending'] = '最高分數遞增排序';
$string['highgradedescending'] = '最高分數遞減排序';
$string['highgradeletter'] = '高';
$string['identifier'] = '辨認用戶按';
$string['idnumbers'] = 'ID 號碼';
$string['ignore'] = '忽視';
$string['import'] = '匯入';
$string['importcsv'] = '匯入 CSV';
$string['importcsv_help'] = '學生分數可以透過具有下列格式的 csv 檔來匯入：

*這檔案的每一列只包含一筆紀錄。
*每一筆紀錄是一系列以逗點(或其他分隔符號)分隔開來的資料。
*第一筆資料是包含所有欄位名稱的清單，用來界定後續資料的格式
*其中一個欄位名稱必須要包含用戶的身分資料，不管是用戶名稱、ID編號或EMAIL位址。

你可以藉由先匯出一些分數來獲得這種檔案的正確格式，然後將這一檔案加以編輯並儲存成一個CSV檔案。
﹑';
$string['importcustom'] = '匯入成自訂的能力指標（此課程專用）';
$string['importerror'] = '發生一個錯誤，這個程式碼呼叫參數錯誤。';
$string['importfailed'] = '匯入失敗，沒有資料被匯入';
$string['importfeedback'] = '匯入回饋';
$string['importfile'] = '匯入檔案';
$string['importfilemissing'] = '沒有接收到檔案，請回到表單，並確定上傳一個有效的檔案';
$string['importfrom'] = '匯入從';
$string['importoutcomenofile'] = '上傳的檔案是空的或者已損壞。請確認這是個正確的檔案。偵測出問題在第{$a}行；可能是第一行的表頭（header）資料沒有足夠的欄位或輸入的檔案沒有表頭（header）。請參考輸出檔案範例的表頭。';
$string['importoutcomes'] = '匯入能力指標';
$string['importoutcomes_help'] = '能力指標的評量結果可以經由CSV檔匯入，其格式如同匯出能力指標的CSV檔。';
$string['importoutcomesuccess'] = '匯入能力指標 "{$a->name}" 編號 #{$a->id}';
$string['importplugins'] = '匯入外掛';
$string['importpreview'] = '匯入預覽';
$string['importsettings'] = '匯入的設定';
$string['importskippednomanagescale'] = '您沒有權限添加一個新量尺，所以能力指標 "{$a}" 將被省略，因為它需要建立一個新量尺。';
$string['importskippedoutcome'] = '簡稱為"{$a}"的能力指標已經存在於這情境。在匯入檔案中的這個將被略過。';
$string['importstandard'] = '匯入為標準的能力指標';
$string['importsuccess'] = '成績匯入成功';
$string['importxml'] = '匯入XML';
$string['includescalesinaggregation'] = '在彙整統計中包含量尺';
$string['includescalesinaggregation_help'] = '您可以更改是否將量尺當作數字，包含在所有課程的所有成績簿的所有彙整分數中。警告：若更改此設定，將會強制所有的彙整分數被重新計算。';
$string['incorrectcourseid'] = '課程代碼不正確';
$string['incorrectcustomscale'] = '(不正確的自訂量尺，請修改)';
$string['incorrectminmax'] = '最低分必須低於最高分';
$string['inherit'] = '繼承';
$string['intersectioninfo'] = '學生/成績資訊';
$string['invalidgradeexporteddate'] = '這匯入的資料是無效的，因為它是一年以前的，或是未來的，或因為格式是無效的。';
$string['item'] = '項目';
$string['iteminfo'] = '項目資訊';
$string['iteminfo_help'] = '這設定提供空間來輸入有關這項目的資訊。這資訊不會顯示在其他地方。';
$string['itemname'] = '項目名稱';
$string['itemnamehelp'] = '被該模組帶入的評分項目名稱';
$string['items'] = '項目';
$string['itemsedit'] = '編輯評分項目';
$string['keephigh'] = '保留最高分';
$string['keephighestvalues'] = '保留{$a}個最高分數';
$string['keephigh_help'] = '如果設定該選項，將只保留X個最高分。X為本選項選擇的值。';
$string['keymanager'] = '密鑰管理器';
$string['lessthanmin'] = '輸入的{$a->username}的{$a->itemname}成績低於最低分。';
$string['letter'] = '文字';
$string['lettergrade'] = '文字等第';
$string['lettergradenonnumber'] = '低分或高分輸入的不是數值：';
$string['letterpercentage'] = '文字等第(百分比)';
$string['letterreal'] = '文字等第(實得分數)';
$string['letters'] = '文字等第';
$string['linkedactivity'] = '已連結的活動';
$string['linkedactivity_help'] = '指定一個與此能力有關聯的活動。這將作為與成績無關的評量標準來評定學生的表現。';
$string['linktoactivity'] = '連結到 {$a->name} 活動';
$string['lock'] = '鎖定';
$string['locked'] = '已鎖定';
$string['locked_help'] = '如勾選，相關的活動就不能再自動更新成績。';
$string['locktime'] = '在何時之後鎖定';
$string['locktimedate'] = '從{$a}之後鎖定';
$string['lockverbose'] = '鎖定{$a->category}{$a->itemmodule}中的{$a->itemname}';
$string['lowest'] = '最低';
$string['lowgradeletter'] = '低';
$string['manualitem'] = '手動項目';
$string['mapfrom'] = '對映自';
$string['mapfrom_help'] = '在這試算表中選擇可以辨識用戶身分的欄位，比如用戶名稱、用戶ID編號或email位址。';
$string['mappings'] = '評分項目對映';
$string['mappings_help'] = '為這試算表的每一個分數欄位，選擇它所對應的計分項目，好把分數匯
進去。';
$string['mapto'] = '對映到';
$string['mapto_help'] = '如同在"對應至"上所選的，選擇相同的身分辨識資料。';
$string['max'] = '最高分';
$string['maxgrade'] = '滿分';
$string['meanall'] = '全部成績';
$string['meangraded'] = '非空成績';
$string['meanselection'] = '選出那些分數計算欄平均數';
$string['meanselection_help'] = '在計算每一類別或計分項目的平均數時，是否將沒有分數的儲存格也一起包括進來。';
$string['median'] = '中位數';
$string['min'] = '最低分';
$string['minimum_show'] = '顯示最低分數';
$string['minimum_show_help'] = '最低分數是用來計算分數和加權量。若沒有顯示，最小分數將會預設為0，且無法被編輯。';
$string['minmaxtouse'] = '用於計算公式中的最低與最高分數';
$string['minmaxtouse_desc'] = '這個設定可以決定在計算顯示在成績單上的成績時，是要使用在打分數時實際給的最低分和最高分，或者是使用在設定計分項目時所指派的最低分和最高分。建議你在此伺服器的離峰時段才修改這一設定，因為它會導致所有的分數都被重新計算過，而造成伺服器很高的負載。';
$string['minmaxtouse_help'] = '這個設定可以決定在計算顯示在成績單上的成績時，是要使用在打分數時實際給的最低分和最高分，或者是使用在設定計分項目時所指派的最低分和最高分。';
$string['minmaxupgradedgrades'] = '注意：因為改變在計算顯示的成績時所用的最低分和最高分而造成的某些不一致，現在這些分數已經被更改過來了。建議你檢查這些更改並決定是否接受。';
$string['minmaxupgradefixbutton'] = '解決不一致性';
$string['minmaxupgradewarning'] = '注意：由於在計算顯示在成績單上的成績時所使用的最低分和最高分被更改了，所以某些分數會出現不一致。建議你點選以下的按鈕來更改分數，以解決不一致的問題。';
$string['missingitemtypeoreid'] = '缺少來自grade_edit_tree_column_select::get_item_cell($item, $params)的第二個參數的序列鍵值(itemtype 或 eid)';
$string['missingscale'] = '必須選擇量尺';
$string['mode'] = '眾數';
$string['modgrade'] = '成績';
$string['modgradecantchangegradetype'] = '你不能更改這分數類型，因為它已經用來評分。';
$string['modgradecantchangegradetypemsg'] = '某些分數已經評出來了，所以不能更改這分數類型。若您希望更改分數最大值，你必須先選擇是否重新計算現有的分數。';
$string['modgradecantchangegradetyporscalemsg'] = '有些分數已經評好了，所以你不能夠更改這分數類型和這量尺';
$string['modgradecantchangeratingmaxgrade'] = '你不能更改這分數最大值，因為它已經用來評分。';
$string['modgradecantchangescale'] = '你不能更改這量尺，因為它已經用來評分。';
$string['modgradecategorycantchangegradetypemsg'] = '這一類目裡的一些計分項目已經被覆寫過了，因此一些分數已經被授予，所以分數類型和量尺無法被更改。如果你希望變更這分數嘴大值，你必須先選擇是否重新依比例調整現有的分數。';
$string['modgradecategorycantchangegradetyporscalemsg'] = '這一類目裡的一些計分項目已經被覆寫過了，因此一些分數已經被授予，所以分數類型和量尺無法被更改。';
$string['modgradecategoryrescalegrades'] = '依比例重新調整已覆寫的分數';
$string['modgradecategoryrescalegrades_help'] = '當變更成績簿上的計分項目的分數最大值時，你也同時需要指定是否要讓現有的百分比分數也更著改變。

如果設為"是"，那任何現有分數將會被依照比例重新調整，這樣百分比分數仍會保持不變。

舉例來說，若這選項是設為"是"，且該計分項目的分數最大值由10改為20，將會使一個6/10(60%)的分數依比例調整成12/20(60%)。若這選項設"否"，這分數將會從6/10(60%)變成6/20(30%)，你就需要以手動方式調整這計分項目以確保分數的正確性。';
$string['modgradedonotmodify'] = '不要修正現有的分數';
$string['modgradeerrorbadpoint'] = '無效的分數值。此數值必須是介於 1 到 {$a} 之間之整數';
$string['modgradeerrorbadscale'] = '所選的量尺無效。請你務必由以下選單選出一個量尺。';
$string['modgrade_help'] = '選出此活動之打成績方式。如選量尺，你可由量尺下拉選單中選出量尺。如使用點數打成績，您可輸入此活動分數最大值。';
$string['modgrademaxgrade'] = '分數最大值';
$string['modgraderescalegrades'] = '重新計算現有分數';
$string['modgraderescalegrades_help'] = '當變更成績簿上的計分項目的分數最大值時，你也同時需要指定是否要讓現有的百分比分數也更著改變。

如果設為"是"，那任何現有分數將會被依照比例重新調整，這樣百分比分數仍會保持不變。

舉例來說，若這選項是設為"是"，且該計分項目的分數最大值由10改為20，將會使一個6/10(60%)的分數依比例調整成12/20(60%)。若這選項設"否"，這分數將會從6/10(60%)變成6/20(30%)，你就需要以手動方式調整這計分項目以確保分數的正確性。';
$string['modgradetype'] = '類型';
$string['modgradetypenone'] = '無';
$string['modgradetypepoint'] = '分數';
$string['modgradetypescale'] = '量尺';
$string['morethanmax'] = '鍵入{$a->username} 的{$a->itemname} 成績高於允許的最大值';
$string['moveselectedto'] = '移動選出的項目到';
$string['movingelement'] = '搬移{$a}';
$string['multfactor'] = '乘數';
$string['multfactor_help'] = '這個乘數是用調整同一計分項目裡的所有分數，但仍不能高於該計分項目的配分。

舉例來說，若一測驗的配分是100，旦大部分學生都考不到50，因此把乘數設定為2，那麼所有50以下的分數都乘以2，作為新分數，但是考50或50以上的都改成以100作為新分數。';
$string['multfactorvalue'] = '{$a}的乘數值';
$string['mustchooserescaleyesorno'] = '你必須選擇是否要重新計算現有的分數';
$string['mygrades'] = '用戶選單的成績連結';
$string['mygrades_desc'] = '這一設定允許用戶從用戶選單中連結到一外部成績簿';
$string['mypreferences'] = '我的偏好';
$string['myreportpreferences'] = '我的報表偏好';
$string['navmethod'] = '導覽方法';
$string['neverdeletehistory'] = '永遠不刪除歷史記錄';
$string['newcategory'] = '新類別';
$string['newitem'] = '新計分項目';
$string['newoutcomeitem'] = '新能力指標項目';
$string['no'] = '否';
$string['nocategories'] = '此課程中無法新增或找到評分類別';
$string['nocategoryname'] = '未輸入類別名稱';
$string['nocategoryview'] = '沒有類別可檢視';
$string['nocourses'] = '目前沒有課程';
$string['noforce'] = '不強制';
$string['nogradeletters'] = '沒有設文字等第';
$string['nogradesreturned'] = '未找到成績';
$string['noidnumber'] = '沒有識別編號';
$string['nolettergrade'] = '沒有文字等第給';
$string['nomode'] = '無';
$string['nonnumericweight'] = '收到非數字評分';
$string['nonunlockableverbose'] = '只有{$a->itemname}解封鎖之後，分數才能夠解封鎖。';
$string['nonweightedpct'] = '無加權的%';
$string['nooutcome'] = '沒有能力指標';
$string['nooutcomes'] = '能力指標項目必須連結一個課程量尺，但是這裡沒有針對此課程的能力指標。您想要新增一個嗎？';
$string['nopermissiontoresetweights'] = '沒有權限來重設加權量';
$string['nopublish'] = '不發佈';
$string['noreports'] = '你在這網站上沒有選修任何課程，也沒有擔任任何課程的教學';
$string['norolesdefined'] = '在“管理 > 成績 > 一般設定 > 成績角色”中沒有設定角色';
$string['noscales'] = '能力指標必須連結一個量尺(課程或全站的)，但是這裡卻沒有連結。您想要新增一個嗎？';
$string['noselectedcategories'] = '沒有選擇類別';
$string['noselecteditems'] = '沒有選擇項目';
$string['notenrolled'] = '你目前沒有選修任何課程';
$string['notteachererror'] = '您必須是教師才能使用這項功能';
$string['nousersloaded'] = '沒有用戶被上載';
$string['numberofgrades'] = '成績的數目';
$string['onascaleof'] = '成績範圍在{$a->grademin}到{$a->grademax}之間。';
$string['operations'] = '操作';
$string['options'] = '選項';
$string['others'] = '其他';
$string['outcome'] = '能力指標';
$string['outcomeassigntocourse'] = '指派其他能力指標到此課程';
$string['outcomecategory'] = '在類別中建立能力指標';
$string['outcomecategorynew'] = '新類別';
$string['outcomeconfirmdelete'] = '您確定刪除能力指標"{$a}"嗎?';
$string['outcomecreate'] = '新增能力指標';
$string['outcomedelete'] = '刪除能力指標';
$string['outcomefullname'] = '完整名稱';
$string['outcome_help'] = '指定將在成績單中顯示的能力指標。只有站台啟用及和課程相關的能力指標才可使用。';
$string['outcomeitem'] = '能力指標項目';
$string['outcomeitemsedit'] = '編輯能力指標';
$string['outcomereport'] = '能力指標報表';
$string['outcomes'] = '能標';
$string['outcomescourse'] = '課程使用的能力指標';
$string['outcomescoursecustom'] = '自訂使用的(無法移除)';
$string['outcomescoursenotused'] = '標準未使用的';
$string['outcomescourseused'] = '標準已使用的（無法移除）';
$string['outcomescustom'] = '自訂能力指標';
$string['outcomeshortname'] = '簡短名稱';
$string['outcomesstandard'] = '標準能力指標';
$string['outcomesstandardavailable'] = '可用的標準能力指標';
$string['outcomestandard'] = '標準能力指標';
$string['outcomestandard_help'] = '一個標準能力指標是可用於整個網站的，給所有課程用的。';
$string['overallaverage'] = '總平均';
$string['overridden'] = '覆蓋';
$string['overridden_help'] = '勾選後，此成績項將不能被相關的活動更改。在成績單中編輯了一個成績後，覆蓋核取方塊會被自動勾選。但是，可以取消它使得相關的活動能更改成績。';
$string['overriddennotice'] = '您在這活動的最後成績是手動調整的。';
$string['overridecat'] = '允許類別分數可以用手工覆蓋';
$string['overridecat_help'] = '關閉這一設定可使得用戶無法去覆蓋類別分數';
$string['overridesitedefaultgradedisplaytype'] = '覆蓋網站預設值';
$string['overridesitedefaultgradedisplaytype_help'] = '勾選後，課程的成績等第和分數範圍可以被自由更改，而不是使用網站的預設設定。';
$string['overrideweightofa'] = '覆蓋{$a}的加權量';
$string['parentcategory'] = '父類別';
$string['pctoftotalgrade'] = '%的總分';
$string['percent'] = '百分比';
$string['percentage'] = '百分比';
$string['percentageletter'] = '百分比(文字等第)';
$string['percentagereal'] = '百分比(實得分數)';
$string['percentascending'] = '依百分比遞增排序';
$string['percentdescending'] = '依百分比遞減排序';
$string['percentshort'] = '%';
$string['plusfactor'] = '平移';
$string['plusfactor_help'] = '平移是一個數字，在應用乘數之後，它被添加到這一計分項目的每一分數上。例如，
X*4+2，這個"2"就是平移。';
$string['plusfactorvalue'] = '{$a}的平移值';
$string['points'] = '分數';
$string['pointsascending'] = '依分數遞增排序';
$string['pointsdescending'] = '依分數遞減排序';
$string['positionfirst'] = '首';
$string['positionlast'] = '末';
$string['preferences'] = '偏好';
$string['prefgeneral'] = '一般';
$string['prefletters'] = '文字等第和其分界線';
$string['prefrows'] = '特別列';
$string['prefshow'] = '顯示/隱藏 切換';
$string['previewrows'] = '預覽行數';
$string['profilereport'] = '用戶個人報表';
$string['profilereport_help'] = '使用在用戶個人資料頁面中的成績報告';
$string['publishing'] = '公佈';
$string['publishingoptions'] = '成績公佈的選項';
$string['quickfeedback'] = '快速回饋';
$string['quickgrading'] = '快速評分';
$string['quickgrading_help'] = '如果啟動，當編輯模式被打開時，每個成績都顯示為一個文字輸入框，可以同時編輯多個成績。點擊更新按鈕後，修改會被保存且反白顯示。注意，當在成績單中修改一個成績之後，一個覆蓋標記就會被勾選，這意味著相關的活動不能再改變這個成績。';
$string['range'] = '全距';
$string['rangedecimals'] = '全距的小數位數';
$string['rangedecimals_help'] = '在顯示全距時要有幾位小數';
$string['rangesdecimalpoints'] = '全距的小數點位數';
$string['rangesdecimalpoints_help'] = '這一設定決定在顯示每個全距時，小數點之後要有幾位小數，或者是要採用類別或計分項目的整體小數點設定(繼承)。';
$string['rangesdisplaytype'] = '全距的顯示方式';
$string['rangesdisplaytype_help'] = '此設定決定這全距是要顯示成實得分數、百分比、或文字等第，或者是採用這類別或計分項目過去所用的顯示方式（繼承）。';
$string['rank'] = '排名';
$string['rawpct'] = '原始%';
$string['real'] = '實得分數';
$string['realletter'] = '實得分數(文字等第)';
$string['realpercentage'] = '實得分數(百分比)';
$string['recalculatinggrades'] = '重新計算分數中...';
$string['recovergradesdefault'] = '預設恢復成績';
$string['recovergradesdefault_help'] = '預設上，當一個用戶在一課程中復學時，要復原他的舊分數。';
$string['refreshpreview'] = '刷新預覽';
$string['regradeanyway'] = '不管怎樣重新評分';
$string['removeallcoursegrades'] = '刪除所有成績';
$string['removeallcoursegrades_help'] = '若勾選，所有以手工添加到成績簿的計分項目都會被刪除，連同分數和重寫、排除、隱藏和鎖定的分數也被刪除。只有與活動有關聯的計分項目會保留下來。';
$string['removeallcourseitems'] = '刪除所有的計分項目和類別';
$string['removeallcourseitems_help'] = '若勾選，所有以手工添加到成績簿的類別和計分項目都會被刪除，連同分數和重寫、排除、隱藏和鎖定的分數也被刪除。只有與活動有關聯的計分項目會保留下來。';
$string['report'] = '報表';
$string['reportdefault'] = '報表的預設{$a}';
$string['reportplugins'] = '報表外掛';
$string['reportsettings'] = '報表設定';
$string['reprintheaders'] = '轉載表頭';
$string['resetweights'] = '重設{$a->itemname}的加權量';
$string['resetweightsshort'] = '重設加權量';
$string['respectingcurrentdata'] = '留下目前的組態而不修改';
$string['rowpreviewnum'] = '預覽行數';
$string['rowpreviewnum_help'] = '要匯入的資料可以在確認要匯入之前加以預覽。這一設定是要決定在預覽時要顯示幾列資料。';
$string['savechanges'] = '儲存修正';
$string['savepreferences'] = '儲存偏好';
$string['scaleconfirmdelete'] = '您確定要刪除這個量尺“{$a}”嗎？';
$string['scaledpct'] = '量尺%';
$string['seeallcoursegrades'] = '觀看所有的課程成績';
$string['select'] = '選擇 {$a}';
$string['selectalloroneuser'] = '選擇全部或單一用戶';
$string['selectauser'] = '選擇一個用戶';
$string['selectdestination'] = '選擇{$a}的目標';
$string['separator'] = '分隔符號';
$string['separator_help'] = '選擇用於這一CSV檔的分隔符號。(通常是一個英文逗號)';
$string['sepcolon'] = '冒號';
$string['sepcomma'] = '逗號';
$string['sepsemicolon'] = '分號';
$string['septab'] = '分頁';
$string['setcategories'] = '設定類別';
$string['setcategorieserror'] = '在您給予加權前，您必須先在您的課程中設定類別';
$string['setgradeletters'] = '設定文字等第';
$string['setpreferences'] = '設定偏好';
$string['setting'] = '設定';
$string['settings'] = '設定';
$string['setweights'] = '設定加權量';
$string['showactivityicons'] = '顯示活動的圖示';
$string['showactivityicons_help'] = '是否在活動名稱旁邊顯示活動圖示。';
$string['showallhidden'] = '顯示隱藏的';
$string['showallstudents'] = '顯示所有學生';
$string['showanalysisicon'] = '顯示成績分析圖示';
$string['showanalysisicon_desc'] = '預設是否要顯示成績分析圖示。 如果這活動模組支援它，這成績分析圖示會鏈接到一個頁面，它對於分數以及分數如何獲得，會有更詳細的解釋。';
$string['showanalysisicon_help'] = '如果活動模組支援它，這成績分析圖示會鏈接到一個頁面，它對於分數以及分數如何獲得，會有更詳細的解釋。';
$string['showaverage'] = '顯示平均';
$string['showaverage_help'] = '是否要顯示平均分數欄位？如果它是從少數的分數計算出的平均分數，那麼學生可能會估算出其它學生的成績。如果平均分數是來自任何隱藏的評分項目，為了效能因素，平均數將只是近似值。';
$string['showaverages'] = '是否顯示每一欄的平均值';
$string['showaverages_help'] = '若啟用，評分者報告將會在底下多加一列來顯示每一類別與計分項目的平均數。';
$string['showcalculations'] = '顯示計算方法';
$string['showcalculations_help'] = '若啟用，在編輯時，在每個計分項目和類別旁邊會顯示一個計算器小圖示，提醒你這一計分項目是經過計算的。';
$string['showcontributiontocoursetotal'] = '顯示對課程總成績的貢獻';
$string['showcontributiontocoursetotal_help'] = '是否要顯示一個百分比的欄位，用來說明每一計分項目貢獻到用戶課程總成績的比例(在使用加權之後)。';
$string['showeyecons'] = '顯示 顯示/隱藏 圖示';
$string['showeyecons_help'] = '如果啟動，當編輯模式被打開時，每個成績都會有一個顯示/隱藏圖示，用來控制它對學生是否可見。';
$string['showfeedback'] = '顯示回饋';
$string['showfeedback_help'] = '顯示回饋列嗎？';
$string['showgrade'] = '顯示成績';
$string['showgrade_help'] = '顯示成績列？';
$string['showgroups'] = '顯示群組';
$string['showhiddenitems'] = '顯示隱藏項目';
$string['showhiddenitems_help'] = '指定要完全隱藏計分項目，或可讓學生看到計分項目的名稱。

* 如果設定為"不顯示"，計分項目就會被完全被隱藏起來。
* 如果設定為"顯示"，則計分項目會顯示為灰色，而學生分數會被被隱藏起來。
* 如果設定為"只隱藏直到"，則計分項目會顯示為灰色，而成績會被完全被隱藏起來，直到所設定的日期之後，才將整個項目完整顯示出來。';
$string['showhiddenuntilonly'] = '只隱藏到';
$string['showingaggregatesonly'] = '只顯示彙總';
$string['showingfullmode'] = '顯示完整檢視';
$string['showinggradesonly'] = '只顯示分數';
$string['showlettergrade'] = '顯示文字等第';
$string['showlettergrade_help'] = '顯示文字等第欄？';
$string['showlocks'] = '顯示鎖定';
$string['showlocks_help'] = '若啟用，當進入編輯時，一個鎖定/解除鎖定的圖示會顯示在每一分數旁邊，已控制這分數能否被相關的活動所自動更新。';
$string['shownohidden'] = '不顯示';
$string['shownooutcomes'] = '隱藏能力指標';
$string['shownumberofgrades'] = '顯示平均數及其樣本數';
$string['shownumberofgrades_help'] = '如果啟動，在每個平均分後的括號中顯示該平均分是用多少個成績算出來的。例如 45(34)，表示有34個樣本(分數)，其平均分數為45分。';
$string['showonlyactiveenrol'] = '只顯示活躍的選修者';
$string['showonlyactiveenrol_help'] = '若啟用，只有活躍的選修者會顯示在成績簿上，而休學的用戶將不會被顯示出來。';
$string['showpercentage'] = '顯示百分比';
$string['showpercentage_help'] = '是否顯示每個成績項目的百分比。';
$string['showquickfeedback'] = '顯示快速回饋';
$string['showquickfeedback_help'] = '如果啟動，當編輯模式被打開時，每個成績都會顯示一個虛線邊框的輸入框，使您可以立刻編輯多個成績的回饋資訊。點擊更新按鈕後，更改會被保存且特別突出顯示。注意，當在成績單中修改一個回饋之後，一個覆蓋標記就會被勾選，這意味著相關的活動不能再改變這個回饋。';
$string['showrange'] = '顯示分數全距';
$string['showrange_help'] = '顯示分數全距欄？';
$string['showranges'] = '顯示全距';
$string['showranges_help'] = '若啟用，在成績單將包含一額外橫列來顯示每一類別和計分項目的分數全距。';
$string['showrank'] = '顯示排名';
$string['showrank_help'] = '顯示用戶每項成績在課程中的排名？';
$string['showuserimage'] = '顯示用戶個人照片';
$string['showuserimage_help'] = '是否在成績單中的用戶名後顯示用戶頭像。';
$string['showverbose'] = '顯示{$a->category}{$a->itemmodule}中的{$a->itemname}';
$string['showweight'] = '顯示加權量';
$string['showweight_help'] = '要顯示分數加權量欄位嗎？';
$string['simpleview'] = '簡單檢視';
$string['singleview'] = '{$a}的單一檢視';
$string['sitewide'] = '全站';
$string['sort'] = '排序';
$string['sortasc'] = '以遞增方式排序';
$string['sortbyfirstname'] = '以名字排序';
$string['sortbylastname'] = '以姓氏排序';
$string['sortdesc'] = '以遞減方式排序';
$string['standarddeviation'] = '標準差';
$string['stats'] = '統計';
$string['statslink'] = '統計';
$string['student'] = '學生';
$string['studentsperpage'] = '每頁顯示的學生數';
$string['studentsperpage_help'] = '在評分者報告中每一頁要顯示幾位學生。';
$string['studentsperpagereduced'] = '每頁的學生數從 {$a->originalstudentsperpage} 減少到 {$a->studentsperpage}。考慮增加 PHP 設定中的 max_input_vars 的值 {$a->maxinputvars}。';
$string['subcategory'] = '一般類別';
$string['submissions'] = '提交';
$string['submittedon'] = '已提交{$a}';
$string['sumofgradesupgradedgrades'] = '注意：彙總方法中的"總分"在網站升級中已經被改為"原始總分"。由於"總分"曾經用在這一課程中，所以建議你檢查這一成績簿上的這一改變。';
$string['switchtofullview'] = '切換到完整檢視';
$string['switchtosimpleview'] = '切換到簡單檢視';
$string['tabs'] = '分頁';
$string['topcategory'] = '最高的類別';
$string['total'] = '總分';
$string['totalweight100'] = '總加權等於100';
$string['totalweightnot100'] = '總加權不等於100';
$string['turnfeedbackoff'] = '關閉回饋';
$string['turnfeedbackon'] = '啟動回饋';
$string['typenone'] = '無';
$string['typescale'] = '量尺';
$string['typescale_help'] = '這設定能決定當使用以量尺計分時，所該使用的量尺。
在以活動為基礎的計分項目中，所用的量尺是在活動設定頁上決定。';
$string['typetext'] = '文字';
$string['typevalue'] = '數值';
$string['uncategorised'] = '未分類的';
$string['unchangedgrade'] = '成績未改變';
$string['unenrolledusersinimport'] = '本次匯入資料包含下列用戶的分數，但他們現在沒有選修這一課程：{$a}';
$string['unlimitedgrades'] = '成績無上下限';
$string['unlimitedgrades_help'] = '預設情況，評分時所給的分數不能超過上下限。打開這個選項後，這個限制就不存在了，並且允許在成績單中直接輸入超過100%的成績。建議只在非高峰期打開這個選項，因為它會使所有成績被重新計算，可能會導致較高的伺服器負荷。';
$string['unlock'] = '解除鎖定';
$string['unlockverbose'] = '解鎖 {$a->category}{$a->itemmodule}中的{$a->itemname}';
$string['unused'] = '未使用';
$string['updatedgradesonly'] = '僅匯出新的或有更新的成績';
$string['upgradedgradeshidemessage'] = '不合格通知';
$string['upgradedminmaxrevertmessage'] = '把更改復原';
$string['uploadgrades'] = '上傳成績';
$string['useadvanced'] = '使用進階功能';
$string['usedcourses'] = '已使用的課程';
$string['usedgradeitem'] = '已使用的評分項目';
$string['usenooutcome'] = '不使用能力指標';
$string['usenoscale'] = '不使用量尺';
$string['usepercent'] = '使用百分比';
$string['user'] = '用戶';
$string['userenrolmentsuspended'] = '用戶被停權(休學)';
$string['userfields_show'] = '顯示用戶欄位';
$string['userfields_show_help'] = '在成績報告上顯示附加的用戶欄位，比如說Email位址。這指定要顯示欄位是由網站"顯示用戶身分"的設定所控制。';
$string['usergrade'] = '用戶{$a->fullname}（{$a->useridnumber}）的成績項{$a->gradeidnumber}';
$string['userid'] = '用戶編號';
$string['useridnumberwarning'] = '沒有ID編號的用戶會在匯出時被排除，同時他們也無法被匯入。';
$string['usermappingerror'] = '使用者對應錯誤：無法找到{$a->value}的{$a->field}。';
$string['usermappingerrorcurrentgroup'] = '用戶非此群組一員。';
$string['usermappingerrorusernotfound'] = '用戶比對有誤。無法找到用戶。';
$string['userpreferences'] = '用戶偏好設定';
$string['useweighted'] = '使用加權';
$string['verbosescales'] = '詳細的量尺';
$string['verbosescales_help'] = '是否以其他文字而不只是阿拉伯數字來匯入分數。如果文字和數字兩種都用，則設為"是"。如果只有匯入數字，則設為"否"。';
$string['viewbygroup'] = '群組';
$string['viewgrades'] = '檢視成績';
$string['weight'] = '加權量';
$string['weightcourse'] = '為課程使用加權成績';
$string['weightedascending'] = '依加權百分比遞增排序';
$string['weighteddescending'] = '依加權百分比遞減排序';
$string['weightedpct'] = '加權%';
$string['weightedpctcontribution'] = '加權%貢獻度';
$string['weight_help'] = '一個數值，用以決定多個計分項目在同一個類別或課程中的相對重要性。';
$string['weightofa'] = '{$a}的加權量';
$string['weightorextracredit'] = '加權或額外加分';
$string['weightoverride'] = '加權量調整';
$string['weightoverride_help'] = '將此取消勾選，將會把一個計分項目的加權量重設成它的自動計算的數值。勾選這個將可防止這加權量被自動計算。';
$string['weights'] = '加權量';
$string['weightsadjusted'] = '你的加權量已被調整為總共100';
$string['weightsedit'] = '編輯加權量和額外加分';
$string['weightuc'] = '計算後權量';
$string['writinggradebookinfo'] = '寫作成績簿的設定';
$string['xml'] = 'XML';
$string['yes'] = '是';
$string['yourgrade'] = '您的成績';
