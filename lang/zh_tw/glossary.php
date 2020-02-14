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
 * Strings for component 'glossary', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   glossary
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addcomment'] = '新增評論';
$string['addentry'] = '新增條目';
$string['addingcomment'] = '增加一條評論';
$string['alias'] = '同義字或別名';
$string['aliases'] = '同義字或別名';
$string['aliases_help'] = '<p align="center"><b>同義字(別名)</b></p>
<p>辭彙表中的每一個詞條都可以有一系列相關的同義字(或別名)。</p>
<p><b>每行輸入一個同義字(或別名)</b> (不是以逗號分割).</p>
<p>同義字和別名提供了訪問詞條的另外一種方式。例如，如果您使用辭彙表自動鏈結篩檢程式，則在決定哪些字詞會被自動鏈結時，同義字也會(和主名稱一樣)被使用。</p>';
$string['allcategories'] = '所有類別';
$string['allentries'] = '全部';
$string['allowcomments'] = '允許對條目加上評論';
$string['allowcomments_help'] = '若啟用，所有具有撰寫評論權限的用戶將可以對詞彙表的條目加上評論。';
$string['allowduplicatedentries'] = '允許有重複的條目';
$string['allowduplicatedentries_help'] = '若啟用，則允許有多個條目使用相同的概念名稱。';
$string['allowprintview'] = '允許列印預覽';
$string['allowprintview_help'] = '<p>若啟用，會提供一鏈結給學生使用這辭彙表的友善列印版本。</p>
<p>教師總是可以使用友善列印版本。</p>';
$string['andmorenewentries'] = '和{$a}個更新的條目';
$string['answer'] = '答案';
$string['approvaldisplayformat'] = '核准時的顯示格式';
$string['approvaldisplayformat_help'] = '當你在核准詞彙表條目時，你可能希望使用不同的顯示格式。';
$string['approve'] = '核准';
$string['areaattachment'] = '附件';
$string['areaentry'] = '定義';
$string['areyousuredelete'] = '您確定要刪除這條目嗎？';
$string['areyousuredeletecomment'] = '您確定要刪除這評論嗎？';
$string['areyousureexport'] = '您確定要匯出這條目到';
$string['ascending'] = '遞增排序';
$string['attachment'] = '附件';
$string['attachment_help'] = '<p align="center"><b>詞條的附件</b></p>
<p>您可以給每個詞條添加“一個”附件。請把這附件檔案上傳到伺服器上，並和詞條一起顯示。</p>
<p>當您想要分享一個圖片或Word文檔時，這個功能是很有用的。</p>
<p>這個檔案可以是任何類型，當然它最好使用標準的3字母副檔名，如.doc、.jpg和.png等等。這會讓別人下載它時更加方便。</p>
<p>如果您修改了詞條，並上傳了新的檔案，則該詞條原先的檔案將會被替換。</p>
<p>如果您修改了一個有附件的詞條，且將此項留空，則原先的附件會被保留。</p>';
$string['author'] = '作者';
$string['authorview'] = '依作者瀏覽';
$string['back'] = '返回';
$string['cachedef_concepts'] = '概念連結';
$string['cantinsertcat'] = '不能加入類別';
$string['cantinsertrec'] = '不能加入記錄';
$string['cantinsertrel'] = '不能加入類別-條目 關係';
$string['casesensitive'] = '要區分字母大小寫';
$string['casesensitive_help'] = '<p>此選項設定了當對此條目產生自動鏈結時，是否要求兩者的大小寫完全符合。</p>
<p>例如，如果開啟此項，則討論區中的“html”將不會鏈結到辭彙表中的條目“HTML”。</p>';
$string['cat'] = '類別';
$string['categories'] = '類別';
$string['category'] = '類別';
$string['categorydeleted'] = '已被刪除的類別';
$string['categoryview'] = '依類別瀏覽';
$string['changeto'] = '變換為{$a}';
$string['cnfallowcomments'] = '預設詞彙表是否接受對任一條目的評論';
$string['cnfallowdupentries'] = '預設辭彙表中是否允許有(概念名稱)重複的條目';
$string['cnfapprovalstatus'] = '預設學生所提交的條目的待審狀態';
$string['cnfcasesensitive'] = '當條目被連結時，預設是否要區分字母大小寫';
$string['cnfdefaulthook'] = '當辭彙表第一次被觀看時，預設以何種方式顯示條目';
$string['cnfdefaultmode'] = '選擇當詞彙表第一次被瀏覽時，預設的顯示框架架';
$string['cnffullmatch'] = '當進行超連結時，預設是否目標文字要完全符合條目的概念';
$string['cnflinkentry'] = '預設條目是否要被自動連結';
$string['cnflinkglossaries'] = '預設一個辭彙表是否要被自動超連結';
$string['cnfrelatedview'] = '選擇自動超連結和項目瀏覽時的顯示格式。';
$string['cnfshowgroup'] = '指明要不要顯示群組分隔符號';
$string['cnfsortkey'] = '選擇預設的排列主鍵';
$string['cnfsortorder'] = '選擇預設的排序方向(漸升或漸降)';
$string['cnfstudentcanpost'] = '預設學生是否能提交條目';
$string['cnftabs'] = '為這一詞彙表格式選擇可見的分頁';
$string['comment'] = '評論';
$string['commentdeleted'] = '這評論已經被刪除';
$string['comments'] = '評論';
$string['commentson'] = '評論在';
$string['commentupdated'] = '評論已經更新';
$string['completionentries'] = '學生必須建立的條目數：';
$string['completionentriesgroup'] = '群組需要的條目數';
$string['concept'] = '概念';
$string['concepts'] = '概念';
$string['configenablerssfeeds'] = '此設定將啟動所有詞彙表的RSS彙集功能，您仍然需要在每一個詞彙表的設定中開啟RSS彙集。';
$string['current'] = '目前的排列 {$a}';
$string['currentglossary'] = '目前的詞彙表';
$string['date'] = '日期';
$string['dateview'] = '按日期瀏覽';
$string['defaultapproval'] = '預設為已審核通過';
$string['defaultapproval_help'] = '這個選項允許教師設定學生所提交條目的預設狀態。它可以是自動處於已審核狀態(立即可以被所有人看到)，或者需要等待教師逐一審核。';
$string['defaulthook'] = '預設鉤子';
$string['defaultmode'] = '預設模式';
$string['defaultsortkey'] = '預設的排序依據';
$string['defaultsortorder'] = '預設的排序方向';
$string['definition'] = '定義';
$string['definitions'] = '定義';
$string['deleteentry'] = '刪除條目';
$string['deletenotenrolled'] = '刪除來自於未選課用戶的條目';
$string['deletingcomment'] = '刪除評論';
$string['deletingnoneemptycategory'] = '刪除這個類別，將不會一併刪除它所包含的條目，這些條目將被標示成"未分類"。';
$string['descending'] = '遞減排序';
$string['destination'] = '匯入條目的目的地';
$string['destination_help'] = '條目可以被匯入到：

* <strong>當前辭彙表：</strong>將匯入的詞條添加到當前開啟的辭彙表之中。

* <strong>新辭彙表：</strong>建立一個新的辭彙表，其資訊取自要匯入入的XML檔，而匯入的詞條會添加到新辭彙表當中。';
$string['disapprove'] = '取消核准';
$string['displayformat'] = '顯示格式';
$string['displayformatcontinuous'] = '連續顯示且不要作者';
$string['displayformatdefault'] = '預設為與顯示格式相同';
$string['displayformatdictionary'] = '簡明模式,字典風格';
$string['displayformatencyclopedia'] = '百科全書';
$string['displayformatentrylist'] = '條目列表';
$string['displayformatfaq'] = '常見問題';
$string['displayformatfullwithauthor'] = '全文(含作者)';
$string['displayformatfullwithoutauthor'] = '全文(不含作者)';
$string['displayformat_help'] = '<p align="center"><b>詞條的顯示格式</b></p>

<p>這個選項設定了辭彙表中詞條的顯示方式。可選的預設格式有：</p>
<blockquote>
<dl>
<dt><b>簡單字典</b>:</dt>
<dd>看上去像一部傳統的字典，其中有多個不相干條目。不顯示作者，且附件顯示為一個鏈結。</dd>

<dt><b>連續(不含作者)</b>:</dt>
<dd>逐一顯示詞條，其間不加任何分割，但會顯示編輯圖示。</dd>

<dt><b>詳細資訊(含作者)</b>:</dt>
<dd>如論壇一樣的顯示格式，包括了作者資訊。附件顯示為一個鏈結。</dd>

<dt><b>詳細資訊(不含作者)</b>:</dt>
<dd>如論壇一樣的顯示格式，不包含作者資訊。附件顯示為一個鏈結。</dd>

<dt><b>百科全書</b>:</dt>
<dd>同“詳細資訊(含作者)”類似，但附件中的圖片會直接顯示在文章中。</dd>

<dt><b>常見問題</b>:</dt>
<dd>對於顯示一系列常見問題很有用。它自動添加“問題”和“答案”等字樣。</dd>
</dl>
</blockquote>

<hr />
<p>Moodle管理員可以創建新的格式，方法請參考<b>mod/glossary/formats/README.txt</b>中內容。</p>';
$string['displayformats'] = '顯示格式';
$string['displayformatssetup'] = '顯示格式的設定';
$string['duplicatecategory'] = '重複的類別';
$string['duplicateentry'] = '重複的條目';
$string['editalways'] = '可隨時編輯';
$string['editalways_help'] = '此選項允許您設定是否總是允許學生修改他們的條目。您可以選擇：
<ul>
<li><b>是：</b>條目是隨時可以編輯的。</li>
<li><b>否：</b>條目只可在貼上後一段時間內可以編輯(通常30分鐘以內)。</li>
</ul>';
$string['editcategories'] = '編輯類別';
$string['editentry'] = '編輯條目';
$string['editingcomment'] = '編輯評論';
$string['entbypage'] = '每頁可顯示多少條目';
$string['entries'] = '條目';
$string['entrieswithoutcategory'] = '未分類條目';
$string['entry'] = '條目';
$string['entryalreadyexist'] = '條目已存在';
$string['entryapproved'] = '該條目已通過審核';
$string['entrydeleted'] = '條目被刪除';
$string['entryexported'] = '條目已成功匯出';
$string['entryishidden'] = '(該條目目前被隱藏)';
$string['entryleveldefaultsettings'] = '條目層次的預設';
$string['entrysaved'] = '這條目已經儲存';
$string['entryupdated'] = '這條目已經更新';
$string['entryusedynalink'] = '這條目將被自動連結';
$string['entryusedynalink_help'] = '若網站管理員已經開啟整體辭彙表的自動鏈結功能，而此選項也被勾選
時，此詞彙表的概念字及同義字會產生自動連結。

這個功能，將允許課程中其他部分的內容，若出現與條目相同的辭彙時，會自動加上指向該條目的鏈結。這範圍包括了討論區的貼文、內部資源、每週總結、心得報告等等。

如果您不希望某一部分文字(如討論區的貼文)被自動加上鏈結，就需要在文字前後添加<nolink>和</nolink>標記。';
$string['errcannoteditothers'] = '您無法編輯其他用戶的條目';
$string['errconceptalreadyexists'] = '這個概念已經存在，在這個辭彙表中不允許重複。';
$string['errdeltimeexpired'] = '你不可以刪除這個。已經超過時間!';
$string['erredittimeexpired'] = '已經超過這條目的可編輯時間';
$string['errorparsingxml'] = '解析檔案時發生錯誤，請確認 XML 語法正確。';
$string['eventcategorycreated'] = '類別已被建立';
$string['eventcategorydeleted'] = '類別已被刪除';
$string['eventcategoryupdated'] = '類別已被更新';
$string['evententryapproved'] = '條目已被核准';
$string['evententrycreated'] = '條目已被建立';
$string['evententrydeleted'] = '條目已被刪除';
$string['evententrydisapproved'] = '條目已被駁回';
$string['evententryupdated'] = '條目已被更新';
$string['evententryviewed'] = '條目已被檢視';
$string['explainaddentry'] = '新增條目到目前的詞彙表。<br> 概念和定義是必填的欄位';
$string['explainall'] = '在一頁中顯示所有條目';
$string['explainalphabet'] = '使用此索引瀏覽詞彙表';
$string['explainexport'] = '點選下方按鈕以匯出詞彙表條目。請將它下載並安全的保存。當您需要時，您可以將它們匯入這一或其他課程。

請注意，附件(如圖檔)和作者並未被匯出。';
$string['explainimport'] = '您必須指明檔名來匯入，並且定義流程標準。<p> 送出您的請求，來檢視結果。</p>';
$string['explainspecial'] = '顯示沒有由英文字母開頭的條目';
$string['exportedentry'] = '已被匯出的條目';
$string['exportentries'] = '匯出條目';
$string['exportentriestoxml'] = '匯出條目到XML檔案';
$string['exportfile'] = '將條目匯出到檔案';
$string['exportglossary'] = '匯出詞彙表';
$string['exporttomainglossary'] = '匯出到主要詞彙表';
$string['filetoimport'] = '要匯入的檔案';
$string['filetoimport_help'] = '瀏覽並選出您的電腦上的包含要匯入條目的XML檔';
$string['fillfields'] = '概念和定義是必要的欄位';
$string['filtername'] = '詞彙表自動超連結';
$string['fullmatch'] = '要完全符合整個文字';
$string['fullmatch_help'] = '<p align="center"><b>整個詞彙符合</b></p>
<p>如果開啟了自動鏈結功能，再開啟此功能，將導致只有整個詞彙都完全符合時才會建立鏈結。</p>
<p>例如，辭彙表中雖有詞條“construct”，但系統不會在“constructivism”這字彙上建立指向它的鏈結。</p>';
$string['glossary:addinstance'] = '新增一辭彙表';
$string['glossary:approve'] = '核准條目或或取消核准的條目';
$string['glossary:comment'] = '建立評論';
$string['glossary:export'] = '匯出條目';
$string['glossary:exportentry'] = '匯出單一條目';
$string['glossary:exportownentry'] = '匯出你的單一條目';
$string['glossary:import'] = '匯入條目';
$string['glossaryleveldefaultsettings'] = '詞彙表層次的預設設定';
$string['glossary:managecategories'] = '管理類別';
$string['glossary:managecomments'] = '管理評論';
$string['glossary:manageentries'] = '管理條目';
$string['glossary:rate'] = '條目評比';
$string['glossarytype'] = '詞彙表類型';
$string['glossarytype_help'] = '　　<p align="center"><b>定義課程的主辭彙表</b></p>
辭彙表系統允許您從任何二級辭彙表中匯出條目到課程的主辭彙表中。

注意：每門課程只能擁有一個主辭彙表，教師應當指定哪個辭彙表作為主辭彙表，並且只有教師才能更新它。';
$string['glossary:view'] = '檢視詞彙表';
$string['glossary:viewallratings'] = '檢視個人給的所有原始評比';
$string['glossary:viewanyrating'] = '檢視每個人收到的總評比';
$string['glossary:viewrating'] = '檢視你接收到總評比';
$string['glossary:write'] = '建立新條目';
$string['guestnoedit'] = '訪客不可以編輯詞彙表';
$string['importcategories'] = '匯入類別';
$string['importedcategories'] = '已經匯入的類別';
$string['importedentries'] = '已經匯入的條目';
$string['importentries'] = '匯入條目';
$string['importentriesfromxml'] = '從 XML 檔案匯入條目';
$string['includegroupbreaks'] = '包含群組分隔符號';
$string['isglobal'] = '設定為全域詞彙表?';
$string['isglobal_help'] = '　　<p align="center"><b>定義全域辭彙表</b></p>
只有管理員可以定義全域辭彙表，這些辭彙表可以是任何課程的一部分(但通常是放在首頁上)。
　　
全域辭彙表與普通的本地辭彙表之間的差別主要在於自動鏈結時全域辭彙表是針對整個網站的(而不僅僅是辭彙表所涉及的課程)。';
$string['letter'] = '字母';
$string['linkcategory'] = '自動連結這一類別';
$string['linkcategory_help'] = '<p>您可以設定您是否想要此類別名稱被自動鏈結。</p>
<p>注意：對類別名稱的自動鏈結總是區分大小寫的，且要整個詞彙完全符合的。</p>
<p>當參與者點選類名稱鏈結時，會被帶到這詞彙表的"以類別瀏覽"頁面。</p>';
$string['linking'] = '自動連結';
$string['mainglossary'] = '主詞彙表';
$string['maxtimehaspassed'] = '抱歉，但已超過了修改該評論的最大時限 ({$a}) !!';
$string['modulename'] = '詞彙表';
$string['modulename_help'] = '詞彙表活動允許參與者建立並維護一個定義的清單，就像一個詞彙表，或者蒐集並組織資源或訊息。

教師可以允許將檔案附加到詞彙表條目上。附加的圖片檔會顯示在條目上。

條目可以被搜尋，或依據字母順序、類別、日期、或作者來瀏覽。

條目也可以預設為已經核准，或要等待教師核准才可以被其他人看到。

若啟用詞彙表的自動連結過濾器，那在這課程裡出現相同於條目概念的文字都會產生自動鏈結。

教師可以在條目上加上評論。教師、學生(同儕評鑑)都可以對條目加以評比，評比可以彙整成單一分數，記錄到成績簿上。

詞彙表的用途，例如：

* 分工撰寫課程關鍵詞彙的解釋

* 新班級的每個人的自我介紹

* 課程相關的影片、照片、聲音檔的分享區

* 實習科目上可供他人參考的個人心得

* 教科書上需要更新補充的說明';
$string['modulenameplural'] = '詞彙表';
$string['newentries'] = '新詞彙表條目';
$string['newglossary'] = '新詞彙表';
$string['newglossarycreated'] = '已經建立新詞彙表';
$string['newglossaryentries'] = '新詞彙表條目：';
$string['nocomment'] = '未找到評論';
$string['nocomments'] = '（這一條目上未找到評論）';
$string['noconceptfound'] = '沒有找到概念或定義';
$string['noentries'] = '在此部分沒找到任何條目';
$string['noentry'] = '沒找到條目';
$string['nopermissiontodelcomment'] = '你不能刪除別人的評論!';
$string['nopermissiontodelinglossary'] = '你不能在這詞彙表上寫評論!';
$string['nopermissiontoviewresult'] = '你只可以看到你自己的條目的結果';
$string['notapproved'] = '詞彙表條目還未被核准';
$string['notcategorised'] = '未分類';
$string['numberofentries'] = '條目數';
$string['onebyline'] = '(每行一個)';
$string['page-mod-glossary-edit'] = '詞彙表新增/編輯條目頁面';
$string['page-mod-glossary-view'] = '檢視詞彙表編輯頁面';
$string['page-mod-glossary-x'] = '任何詞彙表頁面';
$string['pluginadministration'] = '詞彙表管理';
$string['pluginname'] = '辭彙表';
$string['popupformat'] = '彈出格式';
$string['print'] = '列印';
$string['printerfriendly'] = '友善列印版本';
$string['printviewnotallowed'] = '不允許列印預覽';
$string['question'] = '問題';
$string['rejectedentries'] = '已拒絕條目';
$string['rejectionrpt'] = '被拒絕條目的報表';
$string['resetglossaries'] = '刪除條目由';
$string['resetglossariesall'] = '從所有詞彙表中刪除條目';
$string['rssarticles'] = 'RSS的新進文章數';
$string['rssarticles_help'] = '這個選項允許您設定包含在RSS彙集的條目數目。

對於大多數辭彙表來說，5到20之間的數目都是可以的。如果您的辭彙表經常更新，可以加大這個數字。';
$string['rsssubscriberss'] = '顯示概念“{$a}”的RSS匯集';
$string['rsstype'] = '這一活動的RSS彙集';
$string['rsstype_help'] = '<p align="center"><b>辭彙表的RSS彙集</b></p>
這個選項允許您啟動辭彙表的RSS匯集。您可以選擇兩種匯集：
<ul>
<li><b>概念含有作者資訊：</b>生成的彙集會包含作者資訊。</li>
<li><b>概念不含作者資訊：</b>生成的彙集不包含作者資訊。</li>
</ul>';
$string['search:activity'] = '詞彙 - 活動訊息';
$string['search:entry'] = '詞彙 - 條目';
$string['searchindefinition'] = '全文檢索';
$string['secondaryglossary'] = '次級詞彙表';
$string['showall'] = '顯示"全部"連結';
$string['showall_help'] = '若啟用，用戶可以立刻瀏覽全部條目。';
$string['showalphabet'] = '顯示"字母順序表"鏈結';
$string['showalphabet_help'] = '若啟用，用戶可以用概念的英文字母順序，如A, B, C...，來瀏覽詞彙表。';
$string['showspecial'] = '顯示"特殊字元"連結';
$string['showspecial_help'] = '若啟用，用戶可以用特殊字元，比如@和#，來瀏覽詞彙表。';
$string['sortby'] = '排序方式：';
$string['sortbycreation'] = '按建立日期';
$string['sortbylastupdate'] = '按最後更新日期';
$string['sortchronogically'] = '依時間先後順序';
$string['special'] = '特殊';
$string['standardview'] = '依字母順序瀏覽';
$string['studentcanpost'] = '允許學生添加條目';
$string['totalentries'] = '條目總數';
$string['usedynalink'] = '自動連結辭彙表的條目';
$string['usedynalink_help'] = '　　<p align="center"><b>啟動辭彙表的自動鏈結</b></p>
<p>啟動這個功能則允許課程中其他部分的內容出現詞條中辭彙的名稱時自動添加指向詞條的鏈結。這包括了討論區的貼文、內部資源、每週總結、心得報告等等。</p>
<p>啟動辭彙表的自動鏈結功能並不意味著鏈結每個詞條——要使用自動鏈結，還要對每個詞條單獨設置。</p>
<p>如果您不希望某一部分文字(如討論區的貼文)被自動鏈結，則需在文字周圍添加<nolink>和</nolink>標記。</p>
<p>注意：分類名稱也會自動鏈結。</p>
　　';
$string['visibletabs'] = '可見的分頁';
$string['waitingapproval'] = '等待審審核中';
$string['warningstudentcapost'] = '(僅適用於非主辭彙)';
$string['withauthor'] = '概念(含作者)';
$string['withoutauthor'] = '概念(不含作者)';
$string['writtenby'] = '作者是';
$string['youarenottheauthor'] = '您不是該評論的作者，故您無權編輯它。';
