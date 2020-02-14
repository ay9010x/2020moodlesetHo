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
 * Strings for component 'blog', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   blog
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addnewentry'] = '新增文章';
$string['addnewexternalblog'] = '登記外面部落格';
$string['assocdescription'] = '如果您正在寫一篇關於某課程 和/或 某活動模組的部落格，請在這裡選擇它們。';
$string['associated'] = '已關聯{$a}';
$string['associatewithcourse'] = '關於課程{$a->coursename}的部落格';
$string['associatewithmodule'] = '關於{$a->modtype}：{$a->modname}的部落格';
$string['association'] = '關聯';
$string['associations'] = '關聯';
$string['associationunviewable'] = '其他人看不到這篇文章，除非它與某課程關聯，或者“發布到”欄位被修改';
$string['autotags'] = '新增這些標籤';
$string['autotags_help'] = '在此輸入一個或多個本地標籤（用英文逗點分隔）。所有從您的外面的部落格拷貝到本地部落格的文章都會被自動加上這些標籤';
$string['backupblogshelp'] = '如果開啟此項設定，部落格資料將在網站自動備份時加入備份。';
$string['blockexternalstitle'] = '外面的部落格';
$string['blocktitle'] = '部落格標籤區塊的標題';
$string['blog'] = '部落格';
$string['blogaboutthis'] = '關於此{$a->type}的部落格';
$string['blogaboutthiscourse'] = '新增一篇關於此課程的文章';
$string['blogaboutthismodule'] = '寫篇關於此{$a}的文章';
$string['blogadministration'] = '部落格管理';
$string['blogdeleteconfirm'] = '要刪除這篇部落格文章\'{$a}\'嗎？';
$string['blogdisable'] = '部落格被禁用！';
$string['blogentries'] = '部落格文章';
$string['blogentriesabout'] = '關於{$a}的部落格文章';
$string['blogentriesbygroupaboutcourse'] = '由{$a->group}發表的關於{$a->course}部落格文章';
$string['blogentriesbygroupaboutmodule'] = '由{$a->group}發表的關於{$a->mod}的部落格文章';
$string['blogentriesbyuseraboutcourse'] = '由{$a->user}發表關於{$a->course}的部落格文章';
$string['blogentriesbyuseraboutmodule'] = '由{$a->user}發表關於此{$this->mod}的部落格文章';
$string['blogentrybyuser'] = '{$a}發表的部落格文章';
$string['blogpreferences'] = '部落格設定';
$string['blogs'] = '部落格';
$string['blogscourse'] = '課程部落格';
$string['blogssite'] = '網站部落格';
$string['blogtags'] = '部落格標籤';
$string['cannotviewcourseblog'] = '您沒有在此課程檢視部落格的權限';
$string['cannotviewcourseorgroupblog'] = '您沒有在此課程/群組檢視部落格的權限';
$string['cannotviewsiteblog'] = '您沒有檢視所有網站部落格的權限';
$string['cannotviewuserblog'] = '您沒有閱讀用戶部落格的必要權限';
$string['configexternalblogcrontime'] = 'Moodle多久檢查一次外面部落格的新文章。';
$string['configmaxexternalblogsperuser'] = '每位用戶可以有幾篇外面的部落格文章鏈結到他們的Moodle部落格中。';
$string['configuseblogassociations'] = '允許將部落格文章與課程及課程模組關聯。';
$string['configuseexternalblogs'] = '允許用戶指定外面的部落格的來源。Moodle會週期性檢查部落格來源中的新文章，將其拷貝到用戶的本地部落格。';
$string['courseblog'] = '課程部落格： {$a}';
$string['courseblogdisable'] = '沒有啟用課程部落格';
$string['courseblogs'] = '使用者只有在人們分享一個課程時才能夠檢視部落格';
$string['deleteblogassociations'] = '刪除部落格關聯';
$string['deleteblogassociations_help'] = '如果勾選，那麼部落格文章將不再與這課程或任何課程活動或資源有關連。但部落格文章本身不會被刪除。';
$string['deleteentry'] = '刪除文章';
$string['deleteexternalblog'] = '取消這外面的部落格的註冊';
$string['deleteotagswarn'] = '您確定要將這些標籤從所有部落格文章上移除，並從這系統中將它移除？';
$string['description'] = '描述';
$string['description_help'] = '輸入一到兩句話來簡介您的外面的部落格的內容。（如果不提供描述，那麼會直接使用您外部部落格所記錄的描述）。';
$string['donothaveblog'] = '抱歉，您沒有自己的部落格。';
$string['editentry'] = '編輯部落格文章';
$string['editexternalblog'] = '編輯這個外面的部落格';
$string['emptybody'] = '部落格文章不能空白';
$string['emptyrssfeed'] = '你輸入的網址沒有指向一個有效的 RSS feed';
$string['emptytitle'] = '部落格標題不能空白';
$string['emptyurl'] = '你必須指定一網址給一有效的RSS feed';
$string['entrybody'] = '文章內容';
$string['entrybodyonlydesc'] = '文章說明';
$string['entryerrornotyours'] = '這篇文章不是您的';
$string['entrysaved'] = '您的文章已經儲存了';
$string['entrytitle'] = '文章標題';
$string['eventblogassociationadded'] = '已建立部落格關聯';
$string['eventblogentriesviewed'] = '已檢視部落格文章';
$string['evententryadded'] = '已添加部落格文章';
$string['evententrydeleted'] = '已添加部落格文章';
$string['evententryupdated'] = '部落格文章更新了';
$string['externalblogcrontime'] = '外面的部落格的 cron 排程';
$string['externalblogdeleteconfirm'] = '取消這外面的部落格的註冊？';
$string['externalblogdeleted'] = '外面的部落格已經取消註冊';
$string['externalblogs'] = '外面部落格';
$string['feedisinvalid'] = '這消息饋送(feed)是無效的';
$string['feedisvalid'] = '這消息饋送(feed)是有效的';
$string['filterblogsby'] = '過濾文章由...';
$string['filtertags'] = '用來過濾的標籤';
$string['filtertags_help'] = '您可以使用此功能來篩選您想要的文章。如果您在這裡指定的標籤（以逗號分隔），那麼只有帶著這些標籤的文章會從外面部落格拷貝過來。';
$string['groupblog'] = '群組部落格： {$a}';
$string['groupblogdisable'] = '沒有啟用群組部落格';
$string['groupblogentries'] = '部落格文章和{$a->groupname}的{$a->coursename}關聯';
$string['groupblogs'] = '使用者只能夠在人們分享一個群組時檢視部落格';
$string['incorrectblogfilter'] = '不正確的部落格過濾類型';
$string['intro'] = '這個 RSS 內容會自動由一或多個部落格產生。';
$string['invalidgroupid'] = '無效的群組編號';
$string['invalidurl'] = '這網址無法前往';
$string['linktooriginalentry'] = '鏈結到原初的部落格文章';
$string['maxexternalblogsperuser'] = '每位用戶最多可以有幾個外面的部落格';
$string['myprofileuserblogs'] = '檢視所有部落格文章';
$string['name'] = '名稱';
$string['name_help'] = '輸入您的外面部落格的名稱（如果不提供名稱，會直接使用外部部落格的標題）。';
$string['noentriesyet'] = '目前沒有內容';
$string['noguestpost'] = '訪客無法發佈部落格文章！';
$string['nopermissionstodeleteentry'] = '你沒有刪除這篇部落格文章的權限';
$string['norighttodeletetag'] = '您沒有權限刪除這個標籤 - {$a}';
$string['nosuchentry'] = '沒有這樣的部落格文章';
$string['notallowedtoedit'] = '您沒有編輯這篇文章的權限';
$string['numberofentries'] = '文章數量： {$a}';
$string['numberoftags'] = '要顯示幾個標籤';
$string['page-blog-edit'] = '部落格編輯頁面';
$string['page-blog-index'] = '部落格列表頁面';
$string['page-blog-x'] = '全部部落格頁面';
$string['pagesize'] = '每頁要顯示幾篇部落格文章';
$string['permalink'] = '永久連結';
$string['personalblogs'] = '使用者只能夠檢視自己的部落格';
$string['preferences'] = '部落格偏好';
$string['publishto'] = '發佈到';
$string['publishtocourse'] = '和您選修同一個課程的用戶';
$string['publishtocourseassoc'] = '相關課程的成員';
$string['publishtocourseassocparam'] = '{$a}的成員';
$string['publishtogroup'] = '與您同一個群組的用戶';
$string['publishtogroupassoc'] = '在相關課程裡您的群組成員';
$string['publishtogroupassocparam'] = '在{$a}的群組成員';
$string['publishto_help'] = '有三個選擇：<br/>
* 自己（草稿）——只有您和管理員能查看此部落格<br/>
* 網站裡的所有人——所有在此網站註冊的人都能閱讀此部落格<br/>
* 世界上的任何人——任何人，包括訪客，都能閱讀此部落格。';
$string['publishtonoone'] = '您自己（草稿）';
$string['publishtosite'] = '網站上任何人';
$string['publishtoworld'] = '世界任何人';
$string['readfirst'] = '請先閱讀這個';
$string['relatedblogentries'] = '相關部落格文章';
$string['retrievedfrom'] = '擷取自';
$string['rssfeed'] = '部落格RSS feed';
$string['searchterm'] = '搜尋: {$a}';
$string['settingsupdatederror'] = '發生一個錯誤，部落格設定無法更新';
$string['siteblog'] = '網站部落格：{$a}';
$string['siteblogdisable'] = '未啟用網站部落格';
$string['siteblogheading'] = '網站部落格';
$string['siteblogs'] = '所有網站使用者可以檢視所有部落格文章';
$string['tagdatelastused'] = '上次使用的日期標籤';
$string['tagparam'] = '標籤: {$a}';
$string['tags'] = '標籤';
$string['tagsort'] = '顯示標籤時的排序方式';
$string['tagtext'] = '標籤文字';
$string['timefetched'] = '最後一次同步時間';
$string['timewithin'] = '顯示指定天數內使用的標籤';
$string['updateentrywithid'] = '更新文章';
$string['url'] = 'RSS feed網址';
$string['url_help'] = '輸入你的外面部落格的 RSS feed';
$string['useblogassociations'] = '啟用部落格關聯';
$string['useexternalblogs'] = '啟用外面的部落格';
$string['userblog'] = '使用者部落格：{$a}';
$string['userblogentries'] = '由{$a}寫的部落格文章';
$string['valid'] = '有效';
$string['viewallblogentries'] = '所有關於{$a}的文章';
$string['viewallmodentries'] = '檢視關於此{$a->type}的所有文章';
$string['viewallmyentries'] = '檢視所有我發表的文章';
$string['viewblogentries'] = '關於此{$a->type}的所有文章';
$string['viewblogsfor'] = '檢視所有文章...';
$string['viewcourseblogs'] = '查看所有與此課程有關的文章';
$string['viewentriesbyuseraboutcourse'] = '檢視用戶 {$a}寫的關於這課程的文章';
$string['viewgroupblogs'] = '檢視關於群組的文章';
$string['viewgroupentries'] = '群組文章';
$string['viewmodblogs'] = '檢視關於模組的文章';
$string['viewmodentries'] = '模組文章';
$string['viewmyentries'] = '我的文章';
$string['viewmyentriesaboutcourse'] = '檢視我的關於這課程的文章';
$string['viewmyentriesaboutmodule'] = '檢視我的關於{$a}的文章';
$string['viewsiteentries'] = '檢視網站所有文章';
$string['viewuserentries'] = '檢視用戶{$a}的所有文章';
$string['worldblogs'] = '全世界都能夠檢視您的文章';
$string['wrongpostid'] = '錯誤的部落格貼文識別編號';
