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
 * Strings for component 'block_configurable_reports', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   block_configurable_reports
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activitypost'] = '活動貼文';
$string['activityview'] = '活動檢視';
$string['addreport'] = '添加報告';
$string['anyone'] = '任何人';
$string['anyone_summary'] = '在此學校的任何用戶都可以看到此一報告';
$string['availablemarks'] = '可用的配分';
$string['average'] = '平均';
$string['badconditionexpr'] = '不正確的條件敘述式';
$string['badsize'] = '不正確的大小，它必須以%或px為單位';
$string['badtablewidth'] = '不正確的寬度，它必須是以%為單位或用絕對值';
$string['bar'] = '長條圖';
$string['barsummary'] = '長條圖';
$string['blockname'] = '可設定的報表';
$string['calcs'] = '計算';
$string['categories'] = '分類';
$string['categoryfield'] = '分類欄';
$string['categoryfieldorder'] = '分類欄次序';
$string['ccoursefield'] = '分類欄條件';
$string['cellalign'] = '內容顯示位置';
$string['cellsize'] = '儲存格大小';
$string['cellwrap'] = '儲存格換行';
$string['column'] = '欄位';
$string['columnandcellproperties'] = '欄位及屬性';
$string['columncalculations'] = '計算欄';
$string['columns'] = '各個欄位';
$string['comp_calcs'] = '計算';
$string['comp_calcs_help'] = '<p>您可於此欄位增加計算結果，例如註冊於此課程之平均人數</p>
<p>更多協助: <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛文件</a></p>';
$string['comp_calculations'] = '計算';
$string['comp_calculations_help'] = '您可以在此新增計算欄，例如：就讀此課程的平均人數';
$string['comp_columns'] = '各個欄位';
$string['comp_columns_help'] = '<p>您可位依照報告的型式於此選擇您報告的不同欄位</p>

<p>更多協助: <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛文件</a></p>';
$string['comp_conditions'] = '條件';
$string['comp_conditions_help'] = '<p>您可以在此界定條件，例如：只有在此分類的課程，只有從西班牙的用戶 </p>

<p>當您使用多過一個條件時，您可以增加邏輯符號</p>

<p>更多幫助： <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛程式的文檔</a></p>';
$string['comp_customsql'] = '特制SQL';
$string['comp_customsql_help'] = '<p>增加一個SQL問句，請使用prefix_代替Moodle資料庫的字首$CFG->prefix</p>
<p>例子: SELECT * FROM prefix_course</p>

<p>您可以在此找到大量SQL報告 <a href="http://docs.moodle.org/en/ad-hoc_contributed_reports" target="_blank">特設報告</a></p>

<p>因為此區塊支授 Tim Hunt\'s CustomSQL查詢報告，您可以使用任何查詢.</p>

<p>如果您在報告中使用時間標記，緊記增加「時間過濾」 </p>

<p>有關使用過濾： <a href="http://docs.moodle.org/en/blocks/configurable_reports/#Creating_a_SQL_Report" target="_blank">創建SQL查詢報告的教學</a></p>';
$string['comp_filters'] = '過濾器';
$string['comp_filters_help'] = '<p>在此您可以選擇顯示的過濾器</p>

<p>過濾器容許用戶選擇報告中的欄過濾報告結果</p>

<p>有關在SQL報告中使用過濾器，請到 <a href="http://docs.moodle.org/en/blocks/configurable_reports/#Creating_a_SQL_Report" target="_blank">創建SQL查詢報告的教學</a></p>

<p>更多協助 <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛程式的文檔</a></p>';
$string['componenthelp'] = '零件支援';
$string['comp_ordering'] = '排序';
$string['comp_ordering_help'] = '<p>您可以在此使用欄及指令選擇報告的排序</p>

<p>更多協助 <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛程式的文檔</a></p>';
$string['comp_permissions'] = '允許';
$string['comp_permissions_help'] = '<p>您可以在此選擇誰可以查看報告</p>

<p>當您使用多於一個條件時，可以增加邏輯符號以計算最後允許</p>

<p>更多協助 <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛程式的文檔</a></p>';
$string['comp_plot'] = '圖';
$string['comp_plot_help'] = '<p>您可以在此跟據報告的欄與數值增加圖表到報告中</p>

<p>更多協助 <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛程式的文檔</a></p>';
$string['comp_template'] = '範本';
$string['comp_template_help'] = '<p>您可以建立範本以修改報告的設計</p>

<p>當建立範本時，您可以使用協助鍵或位於同一頁的資料查看用於標題、頁尾及報告紀錄的替代標記</p>

<p>更多協助 <a href="http://docs.moodle.org/en/blocks/configurable_reports/" target="_blank">外掛程式的文檔</a></p>';
$string['conditionexpr'] = '條件';
$string['conditionexpr_conditions'] = '條件';
$string['conditionexpr_conditions_help'] = '<p>您可以使用邏輯符號結合條件</p>

<p>使用運算符號（and, or) 以輸入有效的邏輯表達式</p>';
$string['conditionexprhelp'] = '輸入有效的條件，例如：(c1 and c2) or (c4 and c3)';
$string['conditionexpr_permissions'] = '條件';
$string['conditionexpr_permissions_help'] = '<p>您可以使用邏輯符號結合條件</p>

<p>使用運算符號（and, or) 以輸入有效的邏輯表達式</p>';
$string['conditions'] = '條件';
$string['configurable_reports:addinstance'] = '增加一個新的可設置報告區塊';
$string['configurable_reports:manageownreports'] = '管理自已的報告';
$string['configurable_reports:managereports'] = '管理報告';
$string['configurable_reports:managesqlreports'] = '管理SQL告';
$string['configurable_reports:myaddinstance'] = '增加一個新的可設置報告區塊至我的主頁';
$string['configurable_reports:viewreports'] = '查看報告';
$string['confirmdeletereport'] = '您確定要刪除此報告？';
$string['coursecategories'] = '分類課程過濾器';
$string['coursecategory'] = '在此分類的課程';
$string['coursechild'] = '課程是兒童的';
$string['coursededicationtime'] = '課程投放時間';
$string['coursefield'] = '課程欄';
$string['coursefieldorder'] = '課程欄順序';
$string['coursemodules'] = '課程單元';
$string['courseparent'] = '家長的課程';
$string['courses'] = '課程';
$string['coursestats'] = '課程統計';
$string['cron'] = '每日自動執行';
$string['crondescription'] = '安排此項查詢可以每天執行(於夜間)';
$string['cron_help'] = '安排此項查詢可以每天執行(於夜間)';
$string['crrepository'] = '報告知識庫';
$string['crrepositoryinfo'] = '遙距分享知識庫及可使用的範本報告（GitHub帳號持有人的姓名 ＋斜線＋知識庫名字）';
$string['currentreportcourse'] = '現在報告課程';
$string['currentreportcourse_summary'] = '已建立報告的課程';
$string['currentuser'] = '現有用戶';
$string['currentusercourses'] = '已就讀課程的現有用戶';
$string['currentusercourses_summary'] = '現有用戶課程的清單（只限可看見的課程）';
$string['currentuserfinalgrade'] = '現有用戶在課程的最終分數';
$string['currentuserfinalgrade_summary'] = '此欄顯示列行現有用戶的最終分數';
$string['currentuser_summary'] = '用戶現正查看報告';
$string['cuserfield'] = '用戶欄條件';
$string['custom'] = '特制';
$string['customdateformat'] = '特制日期格式';
$string['customsql'] = '特制SQL';
$string['datatables'] = '啟用資料表格 JS元件';
$string['datatables_emptytable'] = '表格中沒有資料';
$string['datatables_first'] = '首先';
$string['datatablesinfo'] = '資料表格JS元件（欄排序、固定標題、搜尋、分頁等）';
$string['datatables_info'] = '顯示 _開始_ 到 _完結_ 中 _總數_ 條目';
$string['datatables_infoempty'] = '顯示0至0中的0條目';
$string['datatables_infofiltered'] = '（從最高＿條目中過濾）';
$string['datatables_last'] = '最後';
$string['datatables_lengthmenu'] = '顯示_選單_條目';
$string['datatables_loadingrecords'] = '載入中...';
$string['datatables_next'] = '下一個';
$string['datatables_previous'] = '下一個';
$string['datatables_processing'] = '處理中...';
$string['datatables_search'] = '搜尋：';
$string['datatables_sortascending'] = '：啟動排序列升序';
$string['datatables_sortdescending'] = '：啟動排序列降序';
$string['datatables_zerorecords'] = '沒有找到匹配的記錄';
$string['date'] = '日期';
$string['dateformat'] = '日期格式';
$string['dbhost'] = '資料庫主機';
$string['dbhostinfo'] = '遠端資料主機的名稱（我們將會執行我們的SQL查詢）';
$string['dbname'] = '資料庫名稱';
$string['dbnameinfo'] = '遠端資料庫的名稱（我們將會執行我們的SQL查詢）';
$string['dbpass'] = '資料庫密碼';
$string['dbpassinfo'] = '遠端資料庫密碼（上述使用者名稱的密碼）';
$string['dbuser'] = '資料庫的用戶名稱';
$string['dbuserinfo'] = '遠端資料庫的用戶名稱';
$string['decimals'] = '小數位數目';
$string['direction'] = '方向';
$string['disabled'] = '取消';
$string['displayglobalreports'] = '顯示全球報告';
$string['displayreportslist'] = '顯示在區塊主體的報告清單';
$string['donotshowtime'] = '不要顯示日期資料';
$string['download'] = '下載';
$string['downloadreport'] = '下載報告';
$string['email_message'] = '訊息';
$string['email_send'] = '傳送';
$string['email_subject'] = '標題';
$string['enabled'] = '已啟用';
$string['enableglobal'] = '這是全網站的報告(所有課程都看得到)';
$string['enablejsordering'] = '啟用Javascript排序';
$string['enablejspagination'] = '啟用Javascript 分頁';
$string['endtime'] = '結束日期';
$string['enrolledstudents'] = '註冊的學生';
$string['error_field'] = '檔案不允許';
$string['error_operator'] = '操作者不允許';
$string['error_value_expected_integer'] = '希望結果為整數';
$string['excludedeletedusers'] = '排除已刪除的使用者(只限SQL的報表)';
$string['executeat'] = '排程的工作在幾點執行';
$string['executeatinfo'] = 'Moodle CRON 會在指定時間後執行SQL查詢。每個24小時';
$string['export_csv'] = '以CSV格式匯出';
$string['export_ods'] = '以ODS格式匯出';
$string['exportoptions'] = '匯出選項';
$string['exportreport'] = '匯出報告';
$string['export_xls'] = '以XLS格式匯出';
$string['fcoursefield'] = '課程欄過濾器';
$string['field'] = '欄';
$string['filter'] = '過濾器';
$string['filter_all'] = '全部';
$string['filter_apply'] = '應用';
$string['filtercategories'] = '過濾器分類';
$string['filtercategories_summary'] = '由分類過濾';
$string['filtercoursecategories'] = '分類課程過濾器';
$string['filtercoursecategories_summary'] = '用主分類過濾課程';
$string['filtercoursemodules'] = '課程模組';
$string['filtercoursemodules_summary'] = '過濾課程模組';
$string['filtercourses'] = '課程';
$string['filtercourses_summary'] = '過濾器會顯烈課程清單。每次只能選擇一個課程';
$string['filterenrolledstudents'] = '已註冊課程的學生';
$string['filterenrolledstudents_summary'] = '從已註冊課程的學生中過濾用戶（由帳號）';
$string['filterrole'] = '角色';
$string['filterrole_summary'] = '過濾系統角色(老師，學生，....)';
$string['filters'] = '過濾器';
$string['filter_searchtext'] = '搜尋文字';
$string['filter_searchtext_summary'] = '文本過濾器';
$string['filtersemester'] = '學期(希伯來文)';
$string['filtersemester_list'] = 'סמסטר א,סמסטר ב,סמסטר ג,סמינריון';
$string['filtersemester_summary'] = 'מאפשר סינון לפני סמסטרים (בעברית, למשל: סמסטר א,סמסטר ב)';
$string['filterstartendtime_summary'] = '開始/結束日期過濾器';
$string['filtersubcategories'] = '目錄(含次目錄)';
$string['filtersubcategories_summary'] = '使用：%%FILTER_CATEGORIES:mdl_course_category.path%%';
$string['filteruser'] = '目前課程用戶';
$string['filterusers'] = '系統用戶';
$string['filterusers_summary'] = '由系統用戶名單中用id過濾出一位用戶';
$string['filteruser_summary'] = '由目前用戶名單中用id過濾出一位用戶';
$string['filteryearhebrew'] = '年(希伯來文)';
$string['filteryearhebrew_list'] = 'תשע,תשעא,תשעב,תשעג,תשעד,תשעה';
$string['filteryearhebrew_summary'] = '過濾器用希伯來紀元(תשעג,...)';
$string['filteryearnumeric'] = '年(數字)';
$string['filteryearnumeric_summary'] = '過濾器的年以數字表示(2013, ... )';
$string['filteryears'] = '年份（數字）';
$string['filteryears_list'] = '2010,2011,2012,2013,2014,2015';
$string['filteryears_summary'] = '以年過濾(以數字表示，2012，... )';
$string['finalgradeincurrentcourse'] = '目前課程最後成績';
$string['fixeddate'] = '固定之日期';
$string['footer'] = '頁腳';
$string['forcemidnight'] = '強制午夜';
$string['fsearchuserfield'] = '用戶欄搜尋格';
$string['fuserfield'] = '用戶欄過濾器';
$string['global'] = '全網站報告';
$string['global_help'] = '只要將&courseid=MY_COURSE_ID附加到報告的網址上，全球的報告均可由此平台的任何課程裡查到。';
$string['globalstatsshouldbeenabled'] = '必須啟用網站統計功能，前往管理－＞ 伺服器－＞統計';
$string['groupseries'] = '群組系列';
$string['groupvalues'] = '群組相同數值（加總）';
$string['head_color'] = '圖表背景顏色';
$string['head_data'] = '圖表資料';
$string['header'] = '頁首';
$string['head_size'] = '圖形大小';
$string['height'] = '高度';
$string['importfromrepository'] = '從資源庫中匯入報告';
$string['importreport'] = '匯入報告';
$string['includesubcats'] = '包括子分類';
$string['jsordering'] = 'JavaScript的排序';
$string['jsordering_help'] = 'JavaScript的排序允許您排列報告的表格，不用重新載入頁面。';
$string['label_field'] = '標籤欄';
$string['label_field_help'] = '此欄提供了在圖表上顯示的事物的名稱';
$string['lastexecutiontime'] = '執行時間= {$a} (Sec)';
$string['legacylognotenabled'] = '必須啟用舊式日誌。
前往網週管理／外掛／啟用舊式日誌和日認設定 檢查舊式日誌資料';
$string['limitcategories'] = '限制圖表中的分類';
$string['line'] = '折線圖';
$string['linesummary'] = '多個系列數據的折線圖';
$string['listofsqlreports'] = '當遊標在編輯器中切換全屏編輯時按F11。亦可以使用Esc來結束全屏編輯。<br/><br/><a href="http://docs.moodle.org/en/ad-hoc_contributed_reports" target="_blank">
SQL的報告供稿名單</a>';
$string['managereports'] = '管理報表';
$string['max'] = '最大值';
$string['min'] = '最低限度';
$string['missingcolumn'] = '必要的欄位';
$string['module'] = '模組';
$string['newreport'] = '新報告';
$string['nocalcsyet'] = '尚未有運算';
$string['nocolumnsyet'] = '尚未有欄';
$string['noconditionsyet'] = '尚未有條件';
$string['noexplicitprefix'] = '沒有明確的前綴';
$string['nofiltersyet'] = '尚沒有過濾器';
$string['nofilteryet'] = '尚沒有過濾器';
$string['noorderingyet'] = '尚沒有排序';
$string['nopermissionsyet'] = '尚沒有權限';
$string['noplotyet'] = '尚沒有策劃';
$string['norecordsfound'] = '沒有找到記錄';
$string['noreportsavailable'] = '沒有可用的報告';
$string['norowsreturned'] = '沒有行返回';
$string['nosemicolon'] = '沒有分號';
$string['notallowedwords'] = '不允許的字';
$string['operator'] = '操作者';
$string['ordering'] = '排列';
$string['others'] = '其他';
$string['pagination'] = '分頁';
$string['pagination_help'] = '每頁顯示紀錄的數目。零代表沒有分頁';
$string['parentcategory'] = '主分類';
$string['permissions'] = '主分類';
$string['pie'] = '圓餅圖';
$string['pieareaname'] = '名稱';
$string['pieareavalue'] = '數值';
$string['piesummary'] = '圓餅圖';
$string['plot'] = '點 － 圖表';
$string['pluginname'] = '配置報告';
$string['previousdays'] = '前幾天';
$string['previousend'] = '上一頁結束';
$string['previousstart'] = '上一頁開始';
$string['printreport'] = '列印報告';
$string['puserfield'] = '用戶欄數值';
$string['puserfield_summary'] = '在已選欄中有已選數值的用戶';
$string['queryfailed'] = '查詢失敗<code><pre>{$a}</pre></code>';
$string['querysql'] = 'SQL查詢';
$string['remote'] = '在遙距數據庫中運行';
$string['remotedescription'] = '您想在遠端資料庫中執行此查詢嗎？';
$string['remote_help'] = '您想在遠端資料庫中執行此查詢嗎？';
$string['remotequerysql'] = 'SQL查詢';
$string['report'] = '報告';
$string['reportcategories'] = '1) 選擇一個遙距報告的分類';
$string['report_categories'] = '分類報告';
$string['reportcolumn'] = '其他報告列';
$string['report_courses'] = '課程報告';
$string['reportcreated'] = '已成功建立報告';
$string['reportlimit'] = '報告行限制';
$string['reportlimitinfo'] = '限制報告表格中顯示列的數目（預設是5000列。但最好有限制，讓使用者不會造成資料庫有過重負擔）';
$string['reports'] = '報告';
$string['reportscapabilities'] = '報告功能';
$string['reportscapabilities_summary'] = '已啟用有capability moodle/site:viewreports權限的用戶';
$string['reportsincategory'] = '2) 從清單中選擇報告';
$string['report_sql'] = 'SQL報告';
$string['reporttable'] = '報告表格';
$string['reporttable_help'] = '< p>這是將顯示在報告中記錄的表格的寬度。 </ P>

<p>如果您使用的模板，此選項沒有任何效果。</ P >';
$string['reporttableui'] = '報告表UI';
$string['reporttableuiinfo'] = '顯示報告表為：簡單滑動HTML表格，用jQuery的列排序或資料表JS元件（列排序，固定頁首，搜索，分頁...）';
$string['report_timeline'] = '時間軸報告';
$string['report_users'] = '用戶報告';
$string['repository'] = '報告庫';
$string['repository_help'] = '您可以從公開資料庫中匯入示例報告。

請注意是有每日呼喚資料庫的限制，

如果連接到資料庫出現了問題，您可以在此手動下載報告<a href="https://github.com/jleyva/moodle-configurable_reports_repository" target="_blank">https://github.com/jleyva/moodle-configurable_reports_repository</a> 。然後使用下列的"匯入報告"功能';
$string['role'] = '角色';
$string['roleincourse'] = '在現時報告課程中有所選角色的用戶';
$string['roleusersn'] = '有此角色的用戶數目...';
$string['searchtext'] = '搜尋文字';
$string['semester'] = '學期（希伯來語）';
$string['serieid'] = '序列';
$string['sessionlimittime'] = '點擊之間的限制（以分鐘）';
$string['sessionlimittime_help'] = '根據點擊之間的限制，界定了如果兩次點擊是否相同的';
$string['setcourseid'] = '設定課程編號';
$string['sharedsqlrepository'] = '共享SQL庫';
$string['sharedsqlrepositoryinfo'] = 'GitHub帳戶擁有者的名稱＋斜線＋資料庫名稱';
$string['sqlsecurity'] = 'SQL安全';
$string['sqlsecurityinfo'] = '取消操作有陳述的SQL查詢來插入數據';
$string['sqlsyntaxhighlight'] = '突出SQL句法';
$string['sqlsyntaxhighlightinfo'] = '在代碼編輯中突出SQL句法(CodeMirror JS 庫)';
$string['startendtime'] = '開始/結束日期過濾器';
$string['starttime'] = '開始日期';
$string['stat'] = '統計';
$string['statsactiveenrolments'] = '啟用（上週）招生';
$string['statslogins'] = '在此平台的登入';
$string['statstotalenrolments'] = '總註冊';
$string['student'] = '學生';
$string['subcategories'] = '分類（包括子分類）';
$string['sum'] = '總';
$string['tablealign'] = '對齊表格';
$string['tablecellpadding'] = '填充表格元件';
$string['tablecellspacing'] = '表單元格間距';
$string['tableclass'] = '表格類別';
$string['tablewidth'] = '表格闊度';
$string['template'] = '範本';
$string['template_marks'] = '範本分數';
$string['template_marks_help'] = '<p>您可以使用任何取代標記:</p>

<ul>
<li>##reportname## - 來包括報告名稱</li>
<li>##reportsummary## - 來包括報告摘要</li>
<li>##graphs## - 來包括圖表</li>
<li>##exportoptions## - 來包括匯出選項</li>
<li>##calculationstable## - 來包括運算表格</li>
<li>##pagination## -來包括分頁 </li>

</ul>';
$string['templaterecord'] = '紀錄範本';
$string['timeinterval'] = '時間間距';
$string['timeline'] = '時間軸';
$string['timemode'] = '時間模式';
$string['totalrecords'] = '總共紀錄數目 ＝{$a->totalrecords}';
$string['type'] = '報告類型';
$string['typeofreport'] = '報告類型';
$string['typeofreport_help'] = '選擇您想要建立的報告類型。安全理由，SQL報告需要
附加功能';
$string['user'] = '課程用戶（帳號）';
$string['usercompletion'] = '用戶課程進度狀態';
$string['usercompletionsummary'] = '課程進度狀態';
$string['userfield'] = '個人資料欄位';
$string['userfieldorder'] = '用戶欄位次序';
$string['usermodactions'] = '用戶模組動作';
$string['usermodoutline'] = '用戶模組大綱統計';
$string['users'] = '系統用戶（帳號）';
$string['usersincohorts'] = '一或多個校定班級群組成員的用戶';
$string['usersincohorts_summary'] = '只是選擇的校定班級群組成員的用戶';
$string['usersincoursereport'] = '在現時報告課程中的任何用戶';
$string['usersincoursereport_summary'] = '在現時報告課程中的任何用戶';
$string['usersincurrentcourse'] = '在現時報告課程中的用戶';
$string['usersincurrentcourse_summary'] = '在現時報告課程中的所選角色的用戶';
$string['userstats'] = '用戶統計';
$string['value'] = '數值';
$string['value_fields'] = '數值欄位';
$string['value_fields_help'] = '應該在圖表中顯示的欄位。Ctrl+點擊 (在Mac：指令+點擊 )以同時選擇多項。如果您選擇標籤欄或非數字數值，將會被忽視。';
$string['viewreport'] = '查看報告';
$string['width'] = '寬度';
$string['xandynotequal'] = 'X軸及Y軸需要不一樣的';
$string['xaxis'] = 'X軸';
$string['yaxis'] = 'Y軸';
$string['yearhebrew'] = '年份（希伯來語）';
$string['yearnumeric'] = '年份（數字）';
$string['years'] = '年份（數字）';
$string['youmustselectarole'] = '至少需要一個角色';
