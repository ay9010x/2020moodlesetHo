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
 * Strings for component 'group', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   group
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addedby'] = '由{%a}添加';
$string['addgroup'] = '增加用戶進入群組';
$string['addgroupstogrouping'] = '將小組加到分群中';
$string['addgroupstogroupings'] = '新增/移除 群組';
$string['adduserstogroup'] = '新增/移除 使用者';
$string['allocateby'] = '分配組員';
$string['anygrouping'] = '﹝任何群組﹞';
$string['autocreategroups'] = '自動建立群組';
$string['backtogroupings'] = '回到分群';
$string['backtogroups'] = '回到群組';
$string['badnamingscheme'] = '至少需要包含一個\'@\' 或\'#\' 字元';
$string['byfirstname'] = '以名字排序';
$string['byidnumber'] = '以ID排序';
$string['bylastname'] = '以姓氏排序';
$string['createautomaticgrouping'] = '建立自動分群';
$string['creategroup'] = '建立群組';
$string['creategrouping'] = '建立分群';
$string['creategroupinselectedgrouping'] = '在分群中建立小組';
$string['createingrouping'] = '自動建立的小組分群';
$string['createorphangroup'] = '建立孤立群組';
$string['databaseupgradegroups'] = 'Groups version is now';
$string['defaultgrouping'] = '預設分群';
$string['defaultgroupingname'] = '分群';
$string['defaultgroupname'] = '群組';
$string['deleteallgroupings'] = '刪除所有分群';
$string['deleteallgroups'] = '刪除所有群組';
$string['deletegroupconfirm'] = '您確定要刪除這個 \'{$a}\' 群組嗎？';
$string['deletegrouping'] = '刪除分群';
$string['deletegroupingconfirm'] = '您確定要刪除\'{$a}\'分群?(分群中的小組不會被刪除)';
$string['deletegroupsconfirm'] = '你確定你要刪除下列的群組？';
$string['deleteselectedgroup'] = '刪除選擇的群組';
$string['editgroupingsettings'] = '修改分群設定';
$string['editgroupsettings'] = '修改群組設定';
$string['enrolmentkey'] = '課程註冊密碼';
$string['enrolmentkeyalreadyinuse'] = '這一選課密鑰已經由其他群組所使用';
$string['enrolmentkey_help'] = '選課密鑰使得只有知道密鑰的人才能存取這課程。

若指定一個"群組選課密鑰"，那輸入密鑰時，用戶不只會進入這個課程，也會被自動指派為該組成員。

注意：必須在"自行選課"設定上啟用"群組選課密鑰"，且也必須指定一個這一課程的選課密鑰。';
$string['erroraddremoveuser'] = '新增/移除 {$a} 到群組 錯誤';
$string['erroreditgroup'] = '新增/更新 {$a} 群組 錯誤';
$string['erroreditgrouping'] = '新增/更新分群 {$a} 時發生錯誤';
$string['errorinvalidgroup'] = '錯誤的群組 {$a}';
$string['errorremovenotpermitted'] = '你沒有權限去移除自動加上去的群組成員{$a}';
$string['errorselectone'] = '在選擇這選項之前，請選擇一個單一群組。';
$string['errorselectsome'] = '在選擇這選項之前，請選擇一個或多個群組。';
$string['evenallocation'] = '注意：為了確保分組均衡，每組的實際人數與您訂定的人數不同。';
$string['eventgroupcreated'] = '群組已建立';
$string['eventgroupdeleted'] = '群組已刪除';
$string['eventgroupingcreated'] = '分群已建立';
$string['eventgroupingdeleted'] = '分群已刪除';
$string['eventgroupinggroupassigned'] = '群組已經指派到分群';
$string['eventgroupinggroupunassigned'] = '群組已經不屬於分群';
$string['eventgroupingupdated'] = '分群已更新';
$string['eventgroupmemberadded'] = '群組成員已添加';
$string['eventgroupmemberremoved'] = '群組成員已移除';
$string['eventgroupupdated'] = '群組已更新';
$string['existingmembers'] = '現在的成員：{$a}';
$string['filtergroups'] = '過濾群組的依據：';
$string['group'] = '群組';
$string['groupaddedsuccesfully'] = '群組 {$a} 已順利新增';
$string['groupaddedtogroupingsuccesfully'] = '群組{$a->groupname} 已成功加入到分群 {$a->groupingname} 。';
$string['groupby'] = '自動建立群組依據';
$string['groupdescription'] = '群組的描述說明';
$string['groupinfo'] = '關於所選取的群組資訊';
$string['groupinfomembers'] = '關於所選取的成員資訊';
$string['groupinfopeople'] = '所選取的人員資訊';
$string['grouping'] = '分群';
$string['groupingaddedsuccesfully'] = '分群{$a}已新增成功';
$string['groupingdescription'] = '分群的說明';
$string['grouping_help'] = '一個分群是在一個課程內某些分組的集合。若選出一個分群，那被指派到這分類之下各個組的學生，將可以一起工作。';
$string['groupingname'] = '分群名稱';
$string['groupingnameexists'] = '這分群名稱"{$a}"已經存在這課程中，請使用其他名稱';
$string['groupings'] = '分群';
$string['groupingsection'] = '分群存取';
$string['groupingsection_help'] = '一個分群是在一個課程內某些分組的集合。若選出一個分群，那被指派到這分群之下各個組的學生，將可以存取這一章節。';
$string['groupingsonly'] = '只分群';
$string['groupmember'] = '群組成員';
$string['groupmemberdesc'] = '群組成員的標準角色。';
$string['groupmembers'] = '群組成員';
$string['groupmemberssee'] = '觀看群組成員';
$string['groupmembersselected'] = '所選擇的群組成員';
$string['groupmode'] = '群組模式';
$string['groupmodeforce'] = '強制為群組模式';
$string['groupmodeforce_help'] = '如果強制使用群組模式，那這課程群組模式會被使用在這一課程的每一個活動上。

在每一活動的群組模式設定會被忽略。';
$string['groupmode_help'] = '<p align="center"><b>分組模式</b></p>
　　
<p>分組模式可以有三種形式：</p>
　　   <ul>
　　      <li>無小組 - 沒有區分小組，每一個人都是一個大社群中的一員；</li>
　　      <li>彼此隔離的小組 - 每一小組的成員都只能看到自己的組員，不能看到其他的小組；</li>
　　      <li>彼此可見的小組 - 每一小組成員都在自己小組中完成工作，但可以看到其他小組的狀況。</li>
　　   </ul>
　
<p>分組模式可以在兩個層次上定義：</p>
<dl>
　　   <dt><b>1. 課程層次</b></dt>
　　   <dd>在課程層次定義的組模式，會預設成為該課程內所有活動的預設模式；<br /><br /></dd>
　　   <dt><b>2. 活動層次</b></dt>
　　   <dd>每個能採用小組模式的活動，都可以定義自己的分組模式。如果課程設定了"<a href="help.php?module=moodle&file=groupmodeforce.html">強制小組模式</a>"，則針對活動的設定會被忽略。</dd>
</dl>
　　';
$string['groupmy'] = '我的群組';
$string['groupname'] = '群組名稱';
$string['groupnameexists'] = '課程中的群組名稱\'{$a}\' 已經存在，請使用另外的名稱';
$string['groupnotamember'] = '抱歉，您不是群組的成員';
$string['groups'] = '群組';
$string['groupscount'] = '群組({$a})';
$string['groupsettingsheader'] = '群組';
$string['groupsgroupings'] = '分組&分群';
$string['groupsinselectedgrouping'] = '群組於：';
$string['groupsnone'] = '沒有任何群組';
$string['groupsonly'] = '只限群組';
$string['groupspreview'] = '預覽群組';
$string['groupsseparate'] = '分割群組';
$string['groupsvisible'] = '可視群組';
$string['grouptemplate'] = '群組@';
$string['hidepicture'] = '隱藏照片';
$string['importgroups'] = '匯入群組';
$string['importgroups_help'] = '群組可以透過文字檔匯入。檔案格式應該像下面：

*每一行包含一筆紀錄<br/>
*每一筆記錄是一系列資料，以但逗點隔開<br/>
*第一筆紀錄包含全部欄為名稱的清單，用以定義這檔案的其他部分的格式<br/>
*一定需要的欄位名稱是群組名稱<br/>
*可選用的欄位名稱是說明、選課密鑰、照片、隱藏照片<br/>
*';
$string['includeonlyactiveenrol'] = '只包含活動選課者';
$string['includeonlyactiveenrol_help'] = '這一設定決定是否只有活動選課的用戶被包含在群組中。若啟用，那些休學的用戶將不會被包含在群組中。';
$string['javascriptrequired'] = '這個頁面需要啟用JavaScript';
$string['members'] = '每一群組成員';
$string['membersofselectedgroup'] = '成員：';
$string['mygroups'] = '我的群組';
$string['namingscheme'] = '命名規則';
$string['namingscheme_help'] = '這小老鼠符號(@)可以用來建立其名稱後面有字母的群組，例如，group@ 將會建立幾個團體，名稱分別為：groupA、groupB、groupC、....


這井字符號(#)可以用來建立其名稱後面有數字的群組，例如，group# 將會建立幾個團體，名稱分別為：group1、group2、group3、....';
$string['newgrouping'] = '新的分群';
$string['newpicture'] = '新圖片';
$string['newpicture_help'] = '<p align="center"><b>上傳圖片</b></p>

<p>您可以從您的電腦上傳圖片到伺服器上，且這張圖片將會被用在許多地方來代表您。</p>
<p>因此，最好的圖片是您的臉，但您也可以使用任何您喜歡的圖片。</p>
<p>圖片必須是 JPG 或 PNG 格式(即檔名末尾是 .jpg 或 .png)。</p>
<p>您可以用以下四種方法之一取得圖片:</p>

<ol>
<li>使用<B>數位像機</b>，您的照片很快就以正確格式存在電腦中了。</li>
<li>使用<b>掃瞄器</b>掃現有的照片。確定存成.JPG 或.PNG格式。</li>
<li>使用<b>繪圖軟體</b>畫一張自畫像，若您是個藝術家。</li>
<li>最後，您可以<B>從網頁中</B>偷一張圖片。 <a target="google" href="http://images.google.com/">http://images.google.com</a>是搜尋圖片的好地方。一旦找到中意的，按滑鼠的右鍵點選圖片，並從選單中選擇"另存圖片.." (不同的電腦可能稍有不同)。</li>
</ol>

<p>要上傳圖片，從這編輯頁點選"瀏覽"按鈕，並從您的硬碟選擇該圖片檔。</p>
<p>注意: 要確定檔案沒有超過列出的大小限制，否則無法上傳。</p>
<p>然後，在底下點選"更新我的資料" - 這圖片將會裁剪成正方形，並縮放成100x100像素。</p>
<p>然後您會回到您的個人資料頁，這圖片可能沒有改變，若是這樣，請按您瀏覽器上的"重新上傳"即可。</p>';
$string['noallocation'] = '尚未分配';
$string['nogroup'] = '沒有群組';
$string['nogrouping'] = '無分群';
$string['nogroups'] = '這一課程尚未設定群組';
$string['nogroupsassigned'] = '沒有指定群組';
$string['nopermissionforcreation'] = '因為你沒有足夠的權限，所以於無法建立"{$a}"群組';
$string['nosmallgroups'] = '避免上一個小群組';
$string['notingroup'] = '忽視群組中的用戶';
$string['notingrouping'] = '[不是在分群之中]';
$string['notingrouplist'] = '[不在群組裡]';
$string['nousersinrole'] = '在選取中的角色找不到適合的使用者';
$string['number'] = '群組/成員 數';
$string['numgroups'] = '群組數';
$string['nummembers'] = '每一個群組的成員';
$string['othergroups'] = '其他群組';
$string['overview'] = '概要';
$string['potentialmembers'] = '潛在的成員：{$a}';
$string['potentialmembs'] = '潛在成員';
$string['printerfriendly'] = '友善列印的顯示';
$string['random'] = '隨機地';
$string['removefromgroup'] = '從群組{$a}移除用戶';
$string['removefromgroupconfirm'] = '你真的要從群組"{$a->group}"移除用戶{$a->user}"？';
$string['removegroupfromselectedgrouping'] = '從分群中移除小組';
$string['removegroupingsmembers'] = '從分群中移除所有小組';
$string['removegroupsmembers'] = '移除所有群組的成員';
$string['removeselectedusers'] = '移除選擇的用戶';
$string['selectfromgroup'] = '從群組中選擇成員';
$string['selectfromgrouping'] = '從分群中選擇成員';
$string['selectfromrole'] = '選擇成員及其角色';
$string['showgroupsingrouping'] = '顯示分群中的各小組';
$string['showmembersforgroup'] = '顯示群組中的各成員';
$string['toomanygroups'] = '群組裡頭的成員數不足--選定的角色只有{$a}使用者可用';
$string['usercount'] = '用戶數';
$string['usercounttotal'] = '用戶總數({$a})';
$string['usergroupmembership'] = '選出的用戶的組別：';
