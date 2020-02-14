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
 * Strings for component 'hsuforum', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   hsuforum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accessible'] = '可進入';
$string['activityoverview'] = '以下是新討論區貼文';
$string['addanewtopic'] = '新增討論';
$string['addareply'] = '新增回覆';
$string['addyourdiscussion'] = '新增您的討論';
$string['advancedsearch'] = '進階搜尋';
$string['ajaxrequesterror'] = '完成請求失敗，請再嘗試。';
$string['all'] = '所有';
$string['allforums'] = '所有討論區';
$string['allowanonymous'] = '允許以匿名方式張貼';
$string['allowanonymous_help'] = '如果勾選此選項，每個帖子的作者名稱將會在瀏覽討論區時刪除。';
$string['allowdiscussions'] = '設定學生張貼權限';
$string['allowsallsubscribe'] = '這個討論區允許每個人自由選擇要訂閱或是不要';
$string['allowsdiscussions'] = '本討論區允許每個人提出新議題';
$string['allsubscribe'] = '訂閱所有討論區';
$string['allunsubscribe'] = '取消所有討論區的訂閱';
$string['alreadyfirstpost'] = '在這議題上已經有第一篇貼文';
$string['anonymousalternatename'] = '匿名';
$string['anonymousfirstname'] = '匿名';
$string['anonymousfirstnamephonetic'] = '匿名';
$string['anonymouslastname'] = '用戶';
$string['anonymouslastnamephonetic'] = '用戶';
$string['anonymousmiddlename'] = '用戶';
$string['anonymousrecentactivity'] = '在此討論區中也許會有最近活動，但詳細資料不能在匿名討論區中顯示';
$string['anonymouswarning'] = '移動此討論話題有可能會透露匿名資料。您確定要這樣做嗎？';
$string['anyfile'] = '任何檔案';
$string['areapost'] = '訊息';
$string['articledateformat'] = '%l:%M%P %b %e, %Y';
$string['attachment_help'] = '你可以隨意附加一個以上的檔案到討論區貼文上，如果你附加一張圖片，它將會顯示在訊息之後。';
$string['attachmentnopost'] = '您不能在沒有貼文編號的情況下匯出附件';
$string['attachments'] = '附件';
$string['attachments:x'] = '附件：{$a}';
$string['author'] = '作者';
$string['blockafter'] = '貼文數目限制的封鎖';
$string['blockafter_help'] = '學生若在某一段指定的期間張貼的貼文超過某一數量，則會被封鎖。但用戶若有mod/hsuforum:postwithoutthrottling 權限，則不受貼文數量的限制。';
$string['blockperiod'] = '封鎖的時段';
$string['blockperioddisabled'] = '不要封鎖';
$string['blockperiod_help'] = '學生若在某一段指定的期間，貼文超過某一數量，則會被封鎖。但用戶若有mod/hsuforum:postwithoutthrottling 權限，則不受貼文數量的限制。';
$string['blogforum'] = '以部落格的形式來顯示的標準討論區';
$string['bynameondate'] = '由{$a->name}發表於{$a->date}';
$string['byx'] = '由{$a}';
$string['cannnotdeletesinglediscussion'] = '抱歉，但您不能刪除此討論！';
$string['cannotadd'] = '無法在這討論區新增討論議題';
$string['cannotadddiscussion'] = '必須是群組成員才能參與這個討論區';
$string['cannotadddiscussionall'] = '您沒有權限新增討論主題給所有參與者。';
$string['cannotaddsubscriber'] = '不能新增編號 {$a} 的訂閱者到這討論區';
$string['cannotaddteacherforumto'] = '無法添加轉換的教師討論區到這課程的最上節。';
$string['cannotcreatediscussion'] = '不能建立新的議題';
$string['cannotcreateinstanceforteacher'] = '不能為教師討論區建立新課程模組實例。';
$string['cannotdeletepost'] = '你不能刪除此貼文!';
$string['cannoteditposts'] = '你不能編輯其他人的貼文!';
$string['cannotfinddiscussion'] = '無法在這討論區找到這討論議題';
$string['cannotfindfirstpost'] = '無法找到此討論區的第一篇貼文';
$string['cannotfindorcreateforum'] = '無法為這網站找到或建立主要新聞討論區。';
$string['cannotfindparentpost'] = '無法找到貼文{$a}的置頂主貼文';
$string['cannotmakeprivatereplies'] = '抱歉，但您不能在此討論區使用私人回覆。';
$string['cannotmovefromsingleforum'] = '不能從簡單單一議題的討論區移動討論議題。';
$string['cannotmovenotvisible'] = '討論區不可見';
$string['cannotmovetonotexist'] = '你不能移動這討論區--它並不存在!';
$string['cannotmovetonotfound'] = '在這課程中沒有找到此討論區';
$string['cannotmovetosingleforum'] = '不能移動討論議題到簡單單一議題的討論區。';
$string['cannotpurgecachedrss'] = '無法清除來自資源或討論區的RSS匯集的快取---請檢查你的檔案授權。';
$string['cannotremovesubscriber'] = '不能將編號 {$a} 的訂閱者從這一討論區移除!';
$string['cannotreply'] = '你不能回應到這一篇貼文';
$string['cannotsplit'] = '來自這討論區的議題不能被拆開';
$string['cannotsubscribe'] = '抱歉，你必須是群組成員才能訂閱。';
$string['cannottrack'] = '不能停止追蹤這討論區';
$string['cannotunsubscribe'] = '不能將你從這討論區取消訂閱';
$string['cannotupdatepost'] = '你不能更新這貼文';
$string['cannotviewpostyet'] = '因為您還未張貼發表過，所以還不能讀取其他學生在這議題上的貼文。';
$string['cannotviewusersposts'] = '這位用戶沒有提出貼文';
$string['cansubscribediscerror'] = '您不能訂閱此討論';
$string['cleanreadtime'] = '以閱讀的時刻來標記"已閱讀"的貼文';
$string['clicktocollapse'] = '點擊以隱藏帖子信息及任何回覆';
$string['clicktoexpand'] = '點擊以顯示帖子信息及任何回覆';
$string['collapseall'] = '開闔所有';
$string['completiondiscussions'] = '學生必須提出議題';
$string['completiondiscussionsgroup'] = '需要議題';
$string['completiondiscussionshelp'] = '需要議題才能完成';
$string['completionposts'] = '學生必須提出議題或回應他人：';
$string['completionpostsgroup'] = '需要貼文';
$string['completionpostshelp'] = '需要提出議題或回應才算完成。';
$string['completionreplies'] = '學生必須貼文回應：';
$string['completionrepliesgroup'] = '需要回應';
$string['completionreplieshelp'] = '需要回應來完成這活動';
$string['completionusegradeerror'] = '不能需要成績因為此討論區不是作評核的。您可以移除完成要求或設定此討論區為評核';
$string['configcleanreadtime'] = '在每天的時段要從\'已閱讀\'表格中清除舊貼文';
$string['configdigestmailtime'] = '系統每天會給那些希望以文摘形式接受郵件的人發送文摘。這個選項控制著每日發送文摘郵件的時間(下一個在此時間後運行的cron程序將會發出這些信件)。';
$string['configenablerssfeeds'] = '這個開關可以開啟所有討論區的RSS彙集功能，但您仍然需要在每個討論區的設定中把它打開。';
$string['confighiderecentposts'] = '設定為「是」以停止在課程主頁面上顯示最近的討論區帖子。';
$string['configlongpost'] = '任何貼文超過這個長度(依字元數，但不包括HTML)，將被認為太長。顯示在網站首頁、社會互動格式課程頁面、或用戶資料表中的貼文，會在 hsuforum_shortpost 和 hsuforum_shortpost 兩個數值之間，.加以裁剪。';
$string['configmanydiscussions'] = '每一頁最多顯示幾個議題';
$string['configmaxattachments'] = '每一貼文最多可有幾個附件';
$string['configmaxbytes'] = '本網站所有討論區的附件的最大容量(課程限制或本地設定只能設得更小)';
$string['configoldpostdays'] = '任何貼文經過這些日子之後，會視為已閱讀';
$string['configreplytouser'] = '當討論區的貼文寄出後，要不要包含該用戶的電子郵件信箱，這樣收件人可以私下回信，而不必透過討論區? 即使設為"是"，用戶仍然可以在他們的個人資料表中將他們的電子郵件設為隱藏';
$string['configrssarticlesdefault'] = '如果已開啟RSS彙集，設定默認文章數目（議題或帖子）';
$string['configrsstypedefault'] = '如果啟用RSS訂閱，設去默認活動類型。';
$string['configshortpost'] = '任何貼文小於這個長度(依據字元數，但不含HTML)，將被認為過短(見下面)';
$string['configtrackingtype'] = '閱讀追蹤的默認設定';
$string['configusermarksread'] = '如果設為\'是\'，用戶必須手動將讀過的貼文標記為已讀。如果設為\'否\'，被讀過的貼文將會自動被標記為己讀。';
$string['confirmsubscribe'] = '您真的要訂閱這個討論區"{$a}"？';
$string['confirmunsubscribe'] = '您真的要取消訂閱這個討論區"{$a}"？';
$string['couldnotadd'] = '由於不明錯誤， 無法新增您的貼文';
$string['couldnotdeletereplies'] = '抱歉，因為已經有人回應它，故無法刪除';
$string['couldnotupdate'] = '由於不明錯誤，無法更新您的貼文';
$string['createdbynameondate'] = '由{$a->name}在{$a->date}時創建';
$string['crontask'] = '高級討論區郵件及維護工作';
$string['csv'] = 'CSV';
$string['date'] = '日期';
$string['default'] = '默認';
$string['delete'] = '刪除';
$string['deleteattachments'] = '刪除附件';
$string['deleteattachmentx'] = '刪除 {$a}';
$string['deleteddiscussion'] = '這個討論主題已被刪除';
$string['deletedpost'] = '貼文已被刪除';
$string['deletedposts'] = '這些貼文已被刪除';
$string['deletesure'] = '您確定要刪除此貼文嗎?';
$string['deletesureplural'] = '您確定要刪除此帖子和所有回應嗎?({$a}篇張貼)';
$string['digestmailheader'] = '這是您在{$a->sitename} 討論區的每日摘要。要更改您的默認討論區電郵偏好設定，請前往 {$a->userprefs}。';
$string['digestmailpost'] = '更改討論區每日摘要的偏好設定';
$string['digestmailprefs'] = '您的個人資料';
$string['digestmailsubject'] = '{$a} : 討論區摘要';
$string['digestmailtime'] = '寄送摘要郵件頻率（小時）';
$string['digestsentusers'] = '討論區摘要已經順利以email寄給 {$a} 個用戶';
$string['disallowsubscribe'] = '不允許訂閱';
$string['disallowsubscribeteacher'] = '不允許訂閱(老師除外)';
$string['discussion'] = '議題';
$string['discussiondisplay'] = '顯示討論';
$string['discussionmoved'] = '這個討論區被移至 {$a}';
$string['discussionmovedpost'] = '此話題已經移到</a>討論區<a href="{$a->forumhref}">{$a->forumname}</a>中<a href="{$a->discusshref}">這裡了';
$string['discussionname'] = '議題名稱';
$string['discussions'] = '議題';
$string['discussionsortkey:created'] = '創建日期';
$string['discussionsortkey:lastreply'] = '最近';
$string['discussionsortkey:replies'] = '最積極';
$string['discussionsortkey:subscribe'] = '已訂閱';
$string['discussionsstartedby'] = '由{$a}發起的議題';
$string['discussionsstartedbyrecent'] = '由{$a}最近所發起的議題';
$string['discussionsstartedbyuserincourse'] = '由{$a->fullname}在{$a->coursename}所發起的議題';
$string['discussionsubscribers'] = '議題的訂閱者';
$string['discussionsummary'] = '{$a}中的所有討論區議題的表格。標題上的姓氏及名字是合併了用戶的姓氏、名字及圖片。';
$string['discussion:x'] = '議題：{$a}';
$string['discussthistopic'] = '討論這議題';
$string['displaydiscussionreplies'] = '顯示議題回應';
$string['displayend'] = '顯示的結束時間';
$string['displayend_help'] = '指定一討論區貼文在某一特定日期之後可以隱藏。注意，管理員永遠可以看到討論區貼文。';
$string['displayperiod'] = '顯示的期間';
$string['displaystart'] = '顯示的開始時間';
$string['displaystart_help'] = '指定一討論區貼文在某一特定日期之後可以顯示。注意，管理員永遠可以看到討論區貼文。';
$string['displaywordcount'] = '顯示字數';
$string['displaywordcount_help'] = '決定是否要顯示每一貼文的總字數';
$string['eachuserforum'] = '每人僅限發表一主題';
$string['edit'] = '編輯';
$string['editedby'] = '編修者: {$a->name}-原發表於{$a->date}';
$string['editedpostupdated'] = '{$a}的貼文已經被更新';
$string['editing'] = '編輯中';
$string['editingpost'] = '編輯帖子';
$string['emaildigest_0'] = '您將收到每個論壇的帖子的一封電子郵件。';
$string['emaildigest_1'] = '您每天將會收到一封包含了每個討論區帖子的完整內容的摘要電郵。';
$string['emaildigest_2'] = '您每天將會收到一封包含了每個討論區帖子的標題的摘要電郵。';
$string['emaildigestcompleteshort'] = '完整帖子';
$string['emaildigestdefault'] = '默認({$a})';
$string['emaildigestoffshort'] = '沒有摘要';
$string['emaildigestsubjectsshort'] = '只有標題';
$string['emaildigesttype'] = '摘要電郵的選項';
$string['emaildigesttype_help'] = '您會在每個討論區中收到的提示類型：

＊默認 －跟從您在用戶簡歷中的摘要設定。如果您更新您的簡歷，變更亦會在此反映。
＊沒有摘要－ 您將會在每個討論區帖子收到一封電郵
＊摘要－ 完整帖子－您每天將會收到一封包含了每個討論區帖子的完整內容的摘要電郵
＊摘要－只有標題－您每天將會收到一封包含了每個討論區帖子的標題的摘要電郵。';
$string['emaildigestupdated'] = '討論區{$a->forum}\'的摘要電郵的選項已更改為\'{$a->maildigesttitle}\'。{$a->maildigestdescription}';
$string['emaildigestupdated_default'] = '您的默認簡歷設定\'{$a->maildigesttitle}\' 已用作討講區\'{$a->forum}\'。{$a->maildigestdescription}';
$string['emptymessage'] = '您的貼文有誤，若非內容空白，便是附件檔案過大。您的修改並未被儲存(更新)。';
$string['erroremptymessage'] = '貼文的訊息不能是空白';
$string['erroremptysubject'] = '貼文的主旨不能是空白';
$string['errorenrolmentrequired'] = '你必須選修這一課程，才能存取這些內容。';
$string['errortimestartgreater'] = '開始時間不能大於結束時間';
$string['errorwhiledelete'] = '在刪除紀錄時發生錯誤。';
$string['eventassessableuploaded'] = '已張貼部份內容';
$string['eventcoursesearched'] = '已搜尋的課程';
$string['eventdiscussioncreated'] = '已創建的議題';
$string['eventdiscussiondeleted'] = '已刪除的議題';
$string['eventdiscussionmoved'] = '已移除的議題';
$string['eventdiscussionupdated'] = '已更新的議題';
$string['eventdiscussionviewed'] = '已查看的議題';
$string['eventpostcreated'] = '已創建的帖子';
$string['eventpostdeleted'] = '已刪除的帖子';
$string['eventpostupdated'] = '已更新的帖子';
$string['eventreadtrackingdisabled'] = '已停用閱讀追蹤';
$string['eventreadtrackingenabled'] = '已開啟閱讀追蹤';
$string['eventsubscribersviewed'] = '已查看訂閱者';
$string['eventsubscriptioncreated'] = '已建立訂閱';
$string['eventsubscriptiondeleted'] = '已刪除訂閱';
$string['eventuserreportviewed'] = '已查看用戶報告';
$string['everyonecanchoose'] = '每個人可選擇是否要訂閱';
$string['everyonecannowchoose'] = '現在每個人可選擇是否要訂閱';
$string['everyoneisnowsubscribed'] = '現在每個人都訂閱了這個討論區';
$string['everyoneissubscribed'] = '每人都被設定訂閱本討論區';
$string['existingsubscribers'] = '目前的訂閱者';
$string['expandall'] = '全部擴展';
$string['export'] = '匯出';
$string['exportattachments'] = '匯出附件';
$string['exportdiscussion'] = '匯出全部議題至檔案';
$string['exportformat'] = '匯出格式';
$string['forcessubscribe'] = '這個討論區強迫每個人都訂閱';
$string['forum'] = '討論區';
$string['forumauthorhidden'] = '作者(隱藏)';
$string['forumblockingalmosttoomanyposts'] = '您快到達張貼的篇數限制，在過去 {$a->blockperiod}期限內，您已經張貼了{$a->numposts}篇，而上限為{$a->blockafter}篇。';
$string['forumbodyhidden'] = '你不能檢視這篇貼文，也許是因為你還沒在這議題上發表意見，或是最大編輯時間還沒有過去，或是這議題還沒開始或議題已經過期。';
$string['forumintro'] = '討論區簡介';
$string['forumname'] = '討論區名稱';
$string['forumposts'] = '討論區文章';
$string['forums'] = '討論區';
$string['forumsubjecthidden'] = '主題(隱藏)';
$string['forumtracked'] = '追蹤未閱讀文章';
$string['forumtrackednot'] = '不追蹤未閱讀文章';
$string['forumtype'] = '討論區型態';
$string['forumtype_help'] = '<p>有五種不同類型的討論區可供選擇：</p>
　　
*<b>單一簡單議題</b> - 一個簡單的話題，全部在一頁上。對於簡短、集中的討論很有用處(不能使用在分隔的群組)。

*<b>每個人張貼一個議題</b> - 每個人都只能發起一個新議題(其他人可以回應)。當您希望每個人都能夠發表一個話題，比如談談他們自己的想法同時允許其他人回復時，這種方式比較有用。

*<b>Q&A討論區</b> - 學生需要先發表自己的答案之後，才能看到其他人的答案。這適合用來做教學時的課堂問答。
　　
*<b>以部落格形式顯示的標準討論區</b> - 一個開放的討論區，任何人都可以隨時開始一個新的議題，而這議題會單獨顯示在一頁面，伴隨著"討論這主題"的連結。

*<b>一般用途的標準討論區</b> - 一個開放的討論區，任何人都可以隨時開始一個新的話題。這是最好的通用討論區。　　';
$string['general'] = '一般';
$string['generalforum'] = '一般用途的標準討論區';
$string['generalforums'] = '一般討論區';
$string['grade'] = '等級';
$string['gradetype'] = '等級類型';
$string['gradetype_help'] = '等級類型是用作判定成績方法

＊沒有：討講區不是評分
＊人手：討講區是由教師透過成績簿人手評分
＊評分： 使用評分以生成成績';
$string['gradetypemanual'] = '人手';
$string['gradetypenone'] = '沒有';
$string['gradetyperating'] = '評分';
$string['hiddenforumpost'] = '隱藏討論區帖子';
$string['hideadvancededitor'] = '隱藏進階編輯';
$string['hiderecentposts'] = '隱藏最近帖子';
$string['hsuforum:addinstance'] = '新增討論區';
$string['hsuforum:addnews'] = '新增新聞';
$string['hsuforum:addquestion'] = '新增問題';
$string['hsuforum:allowforcesubscribe'] = '允許強制訂閱';
$string['hsuforum:allowprivate'] = '允連用戶私下回應';
$string['hsuforum:canposttomygroups'] = '可以發佈到所有您能進入的群組';
$string['hsuforum:createattachment'] = '創建附件';
$string['hsuforum:deleteanypost'] = '刪除任何帖子（任何時間）';
$string['hsuforum:deleteownpost'] = '刪除自己的帖子（限期內）';
$string['hsuforum:editanypost'] = '編輯任何帖子';
$string['hsuforum:exportdiscussion'] = '匯出整個議題';
$string['hsuforum:exportownpost'] = '匯出自己的帖子';
$string['hsuforum:exportpost'] = '匯出帖子';
$string['hsuforum:managesubscriptions'] = '管理訂閱';
$string['hsuforum:movediscussions'] = '移動議題';
$string['hsuforum:postwithoutthrottling'] = '從帖子門檻中豁免';
$string['hsuforum:rate'] = '評分帖子';
$string['hsuforum:replynews'] = '回應新聞';
$string['hsuforum:replypost'] = '回應帖子';
$string['hsuforum:revealpost'] = '在匿名討論區中透露自已';
$string['hsuforum:splitdiscussions'] = '分割議題';
$string['hsuforum:startdiscussion'] = '開始新議題';
$string['hsuforum:viewallratings'] = '查看所有由個人給予的原評分';
$string['hsuforum:viewanyrating'] = '查看任何人收到的總評分';
$string['hsuforum:viewdiscussion'] = '查看議題';
$string['hsuforum:viewflags'] = '查看議題旗子';
$string['hsuforum:viewhiddentimedposts'] = '查看隱藏的定時帖子';
$string['hsuforum:viewposters'] = '查看討論區的海報';
$string['hsuforum:viewqandawithoutposting'] = '經常看到問與答帖子';
$string['hsuforum:viewrating'] = '查看自己收到的總評分';
$string['hsuforum:viewsubscribers'] = '查看訂閱者';
$string['id'] = '身份';
$string['inforum'] = '在{$a} 裡';
$string['inprivatereplyto'] = '私下回覆至';
$string['inreplyto'] = '回覆至';
$string['introblog'] = '在這一討論區的貼文，是自動從用戶在此課程的部落格複製來的，因為這些部落格條目已經關閉';
$string['intronews'] = '一般消息與公告';
$string['introsocial'] = '一個開放可隨意聊聊的討論區';
$string['introteacher'] = '僅限教師可參與討論區';
$string['invalidaccess'] = '這一頁不能正確地存取';
$string['invaliddigestsetting'] = '已提供無效郵件摘要設定';
$string['invaliddiscussionid'] = '議題的編號不正確或已經不存在';
$string['invalidforcesubscribe'] = '無效的強制訂閱模式';
$string['invalidforumid'] = '討論區編號不正確';
$string['invalidparentpostid'] = '上層貼文編號不正確';
$string['invalidpostid'] = '無效的貼文編號- {$a}';
$string['javascriptdisableddisplayformat'] = '您的瀏覽器已停用Javascript。請開啟Javascript並重新載入頁面或選擇不同的議題顯示';
$string['jsondecodeerror'] = '不能解譯回應，請再嘗試。';
$string['lastpostbyx'] = '最後的帖子在{$a->time}由{$a->name}';
$string['lastposttimeago'] = '最後{$a}';
$string['learningforums'] = '學習討論區';
$string['loadingeditor'] = '載入編輯器中...';
$string['loadmorediscussions'] = '載入更多議題';
$string['longpost'] = '長篇文章';
$string['manageforumsubscriptions'] = '管理討論區訂閱';
$string['manualwarning'] = '尚未支援活動評分。成績只能透過成績簿使用';
$string['manydiscussions'] = '每頁的討論話題數';
$string['markalldread'] = '將此討論的所有文章標示為己閱讀';
$string['markallread'] = '將此討論區的所有所有文章標示為已閱讀';
$string['markread'] = '標示為已閱讀';
$string['markreadbutton'] = '標示為<br />已閱讀';
$string['markunread'] = '標示為未閱讀';
$string['markunreadbutton'] = '標示為<br />未閱讀';
$string['maxattachments'] = '附件的最大數量';
$string['maxattachments_help'] = '這一設定可以限制討論區貼文的附加檔案的數量。';
$string['maxattachmentsize'] = '最大附件大小';
$string['maxattachmentsize_help'] = '　　<p align="center"><b>最大附件尺寸</b></p>
　　
　　<p>附件的檔尺寸是可以限制的，創建討論區的人可以設置它。</p>
　　
　　<p>有時，您可以提交一個比這個尺寸大的檔，但這個檔不會被保存下來，且您會看到一個錯誤資訊。</p>';
$string['maxtimehaspassed'] = '抱歉, 超過可編輯本篇內容({$a})的時限!';
$string['message'] = '訊息';
$string['messageinboundattachmentdisallowed'] = '由於已包含附件及此討論區不允許附件，因此不能張貼您的回應';
$string['messageinboundfilecountexceeded'] = '由於附件數目已超過討論區附件數目的上限，因此不能張貼您的回應。({$a->forum->maxattachments})';
$string['messageinboundfilesizeexceeded'] = '由於附件容量已超過討論區附件容量的上限，因此不能張貼您的回應({$a->maxbytes})。';
$string['messageinboundforumhidden'] = '由於討論區現時不能使用，因此不能張貼您的回應。';
$string['messageinboundnopostforum'] = '由於您沒有在 {$a->forum->name}討論區發帖的權限，因此不能張貼您的回應。';
$string['messageinboundthresholdhit'] = '由於您已經超過此討論區的發帖門檻，因此不能張貼您的回應。';
$string['messageisrequired'] = '信息是需要的';
$string['messageplaceholder'] = '輸入您的帖子';
$string['messageprovider:digests'] = '訂閱的討論區摘要';
$string['messageprovider:posts'] = '訂閱的討論區貼文';
$string['missingsearchterms'] = '下面的搜尋字串將只對應此訊息中的HTML標示語言.';
$string['modeflatfirstname'] = '以用戶名字表列回應內容';
$string['modeflatlastname'] = '以用戶姓氏表列回應內容';
$string['modeflatnewestfirst'] = '表列回應內容, 最新在前';
$string['modeflatoldestfirst'] = '表列回應內容, 最舊在前';
$string['modenested'] = '回應訊息將往右縮排';
$string['modethreaded'] = '回應訊息以樹狀結構呈現';
$string['modulename'] = '高級討論區';
$string['modulename_help'] = '討論區可以讓參與者進行非同步的討論，也就是說討論是發生在一段很長的時間中。

討論區可以按照多種不同的方式加以組織，比如，強制訂閱的新聞討論區；要先回答才能看到別人貼文的Q&A討論區；每人只能提出一個議題的討論區；好像部落格的討論區。

貼文可以用多種不同的格式瀏覽，也可以包含附件。附加的圖檔會顯示在貼文上。

教師也可以為某課程的所有學生訂閱討論區且不准取消(強制)，或允許取消(自動)，或者一開始就由學生自行決定(自選)，或這完全關閉訂閱功能(關閉)。
訂閱一個討論區後，訂閱者可以透過電子郵件接受到每一個新的貼文。

教師和學生(同儕互評)可以對討論區的貼文進行評比。評比的結果可以彙整起來成一個個人在討論區的最後分數，然後被記錄到成績簿中。

討論區有許多的用途，比如：

* 當作社交空間，讓選距學習的學生彼此互相認識。<br/>
* 當作課程佈告欄(使用新聞討論區並強指訂閱)。<br/>
*  用來討論課程內容和閱讀材料。<br/>
* 用來繼續課堂上面對面沒討論完的議題。<br/>
* 用來做只有教師之間的討論 (使用隱藏的討論區)<br/>
* 當作意見箱，蒐集助教及學生對於教材或教學方式的建議。<br/>
* 作為一對一的個別指導區，讓師生間有私密的溝通 (在討論區中使用分隔的群組，且讓每一人一組)。
* 作為"腦力激盪"的場所，讓學生提出難題，和建議各種解決方案。<br/>';
$string['modulenameplural'] = '高級討論區';
$string['more'] = '還有';
$string['movedmarker'] = '（已移動）';
$string['movethisdiscussionto'] = '搬移這個討論主題至 ...';
$string['mustprovidediscussionorpost'] = '你需要提供一議題編號或貼文編號來匯出。';
$string['myprofileotherdis'] = '高級討論區議題';
$string['myprofileotherpost'] = '高級討論區帖文';
$string['myprofileowndis'] = '我的高級討論區';
$string['myprofileownpost'] = '我的高級討論區帖文';
$string['namesocial'] = '公開討論區';
$string['nested'] = '已嵌套';
$string['newforumposts'] = '最近的討論區帖文';
$string['nextdiscussion'] = '較新的議題';
$string['nextdiscussionx'] = '({$a}) 之後 >';
$string['noattachments'] = '這一貼文沒有附件';
$string['nodiscussionsstartedby'] = '{$a}沒有提出任何議題';
$string['nodiscussionsstartedbyyou'] = '你還沒開始任何討論';
$string['noguestpost'] = '抱歉，不允許訪客張貼文章';
$string['noguesttracking'] = '抱歉,訪客不能設定追蹤選項';
$string['nomorepostscontaining'] = '沒有搜尋到包含"{$a}"的文章';
$string['nonanonymous'] = '非匿名';
$string['noonecansubscribenow'] = '現在不允許訂閱';
$string['nopermissiontosubscribe'] = '你沒有權限去看討論區的訂閱者';
$string['nopermissiontoview'] = '你沒有權限去看這一貼文';
$string['nopostforum'] = '抱歉，您未被允許在此討論區發表文章。';
$string['noposts'] = '沒有任何發表';
$string['nopostsmadebyuser'] = '{$a} 到目前沒有貼文';
$string['nopostsmadebyyou'] = '你沒有任何貼文';
$string['nosubscribers'] = '尚未有人訂閱本討論區';
$string['notexists'] = '議題已經不存在';
$string['nothingnew'] = '{$a}中沒有新的內容';
$string['notingroup'] = '抱歉，您必需是這個群組的成員才可看這個討論區';
$string['notinstalled'] = '討論區模組沒有安裝';
$string['notpartofdiscussion'] = '這貼文不屬於這議題!';
$string['notrackforum'] = '不要追蹤未閱讀的訊息';
$string['notuploadedfile'] = '您上載的檔案出現問題，請再嘗試';
$string['noviewdiscussionspermission'] = '您沒有權限檢視這個討論區的內容';
$string['nowallsubscribed'] = '所有{$a}中的討論區都已經訂閱';
$string['nowallunsubscribed'] = '所有{$a}中的討論區都沒有訂閱';
$string['nownotsubscribed'] = '{$a->name}將<B>不會</B>收到{$a->forum}張貼內容的電子郵件。';
$string['nownottracking'] = '{$a->name}不再追蹤"{$a->forum}"論壇。';
$string['nowsubscribed'] = '{$a->name}將<B>會</B>收到{$a->forum}張貼內容的電子郵件。';
$string['nowtracking'] = '{$a->name}正追蹤"{$a->forum}"中.。';
$string['numposts'] = '{$a}則張貼';
$string['olderdiscussions'] = '過期討論';
$string['oldertopics'] = '過期的主題';
$string['oldpostdays'] = '多少天後可以閱讀';
$string['onereply'] = '一個回覆';
$string['openmode0'] = '不能新增主題或回應留言';
$string['openmode1'] = '不能新增主題, 但可以回應留言';
$string['openmode2'] = '允許新增主題或回應留言';
$string['options'] = '選項';
$string['orderdiscussionsby'] = '依排列';
$string['overviewnumpostssince'] = '自從上次登入後的{$a}篇貼文';
$string['overviewnumunread'] = '總共有{$a}篇尚未閱讀';
$string['page-mod-hsuforum-discuss'] = '討論區模組討論緒頁面';
$string['page-mod-hsuforum-view'] = '討論區模組主要頁面';
$string['page-mod-hsuforum-x'] = '任何討論區模組頁面';
$string['parent'] = '顯示上層文章';
$string['parentofthispost'] = '本留言回應的上一篇討論';
$string['participants'] = '參加者';
$string['plaintext'] = '統文字';
$string['pluginadministration'] = '討論區管理';
$string['pluginname'] = '高級討論區';
$string['postadded'] = '您張貼的內容已經成功地加入討論區.<P>您有 {$a}可以做任何的修改.';
$string['postaddedsuccess'] = '您的文章已經發表成功';
$string['postaddedtimeleft'] = '如果想再變更內容，您有{$a}的時間可以再編輯它。';
$string['postbymailsuccess'] = '恭喜，您已成功新增標題為"{$a->subject}"的帖文。您可以在{$a->discussionurl}此查看。';
$string['postbymailsuccess_html'] = '恭喜，您已成功張貼標題為"{$a->subject}"的<a href="{$a->discussionurl}">討論區帖文</a> 。';
$string['postbyuser'] = '由 {$a->user}的{$a->post}';
$string['postbyx'] = '由{$a}發佈';
$string['postbyxinprivatereplytox'] = '由{$a->author}至{$a->parent} 的私下回應';
$string['postbyxinreplytox'] = '由{$a->author}回覆至{$a->parent}{$a->parentpost}';
$string['postcreated'] = '已創建帖文';
$string['postdeleted'] = '已刪除帖文';
$string['postincontext'] = '在情境下檢視貼文';
$string['postmailinfo'] = '這是張貼在{$a}網站的訊息的複本。

若要回應請按以下連結。';
$string['postmailnow'] = '<p>您張貼的這篇內容將立刻寄出給所有的訂閱者.</p>';
$string['postmailsubject'] = '{$a->courseshortname}: {$a->subject}';
$string['postoptions'] = '帖文選項';
$string['postrating1'] = '極端獨立型';
$string['postrating2'] = '折衷型';
$string['postrating3'] = '極端交流型';
$string['posts'] = '貼文';
$string['postsfor'] = '張貼為';
$string['postsmadebyuser'] = '貼文的作者是 {$a}';
$string['postsmadebyuserincourse'] = '此貼文是{$a->fullname} 在 {$a->coursename}課程中所寫';
$string['posttoforum'] = '貼文到討論區中';
$string['posttomygroups'] = '張帖副本至所有群組';
$string['posttomygroups_help'] = '張帖訊息至所有您能進入的群組。您沒有連接的參加者將不能看到此帖文';
$string['postupdated'] = '已經更新您的貼文';
$string['postwasupdated'] = '已更新此帖文';
$string['potentialsubscribers'] = '潛在的訂閱者';
$string['prevdiscussionx'] = '< 過去({$a})';
$string['previousdiscussion'] = '較舊的討論';
$string['print'] = '列印';
$string['privatereplies'] = '容許私人回覆';
$string['privaterepliesdisabledglobally'] = '私人回覆已在全球停用';
$string['privatereplies_help'] = '有了這特徵，教師可以發送私人回覆至討論區帖文。此回覆僅張貼或回覆帖文的學生可見，其他學生將不能看到。';
$string['privatereply'] = '私人回覆';
$string['privatereplybyx'] = '由{$a}的私人回覆';
$string['privatereply_help'] = '如果勾選，此帖文幾會變成僅有回應帖文的用戶可見。還有，沒有人能夠回覆此帖文。';
$string['processingdigest'] = '正在為用戶{$a}處理郵寄貼文摘要';
$string['processingpost'] = '正在處理貼文{$a}';
$string['prune'] = '分割';
$string['prunedpost'] = '已經從這貼文建立一個新議題';
$string['pruneheading'] = '分割討論區，並且移動此貼文到新討論區';
$string['qandaforum'] = 'Q & A 型討論區';
$string['qandanotify'] = '這是一個\'問與答"型的討論區，為了看到這些問題的其他回應，您必須先貼上您的答案';
$string['re'] = '回應';
$string['readtherest'] = '閱讀這一議題的其他貼文';
$string['replies'] = '回應';
$string['repliesmany'] = '至今有 {$a} 篇回應';
$string['repliesone'] = '至今有 {$a} 篇回應';
$string['reply'] = '回應';
$string['replybuttontitle'] = '回覆至{$a}';
$string['replybyx'] = '由{$a}回覆';
$string['replyforum'] = '回應到討論區中';
$string['reply_handler'] = '透過電郵回覆至高級討論區';
$string['reply_handler_name'] = '回覆至高級討論區';
$string['replytopostbyemail'] = '您可以透過電郵回覆';
$string['replytouser'] = '使用電子郵件信箱回覆';
$string['replytox'] = '回覆至{$a}';
$string['resetdigests'] = '刪除所有每個用戶的討論區摘要偏好設定';
$string['resetforums'] = '移除這些討論區的所有貼文';
$string['resetforumsall'] = '刪除所有的貼文';
$string['resetsubscriptions'] = '刪除所有討論區的訂閱';
$string['resettrackprefs'] = '刪除所有討論區中的追蹤偏好';
$string['reveal'] = 'RSS新進貼文篇數';
$string['reveal_help'] = '如果勾選，您的姓名將會顯示於帖文中而您將不會是匿名';
$string['rssarticles'] = 'RSS最近文章的數目';
$string['rssarticles_help'] = '　　<p align="center"><b>在RSS中包含的文章數目</b></p>
　　
　　<p>這個選項允許您設置在RSS種子中包含的文章數目。</p>
　　
　　<p>對於大多數討論區來說，5至20之間就可以了，如果您的討論區用戶真的很多，可以設大一些。</p>';
$string['rsssubscriberssdiscussions'] = '議題的RSS彙集';
$string['rsssubscriberssposts'] = '貼文的RSS彙集';
$string['rsstype'] = '這一活動的RSS彙集';
$string['rsstypedefault'] = 'RSS彙集類型';
$string['rsstype_help'] = '　　<p align="center"><b>討論區的RSS種子</b></p>
　　
　　<p>這個選項允許您開啟這個討論區的RSS種子。</p>
　　
　　<p>您可以選擇兩種類型：</p>
　　
　　<ul>
　　<li><b>話題：</b>在這種情況下，RSS種子中會包括新的話題。</li>
　　
　　<li><b>帖子：</b>在這種情況下，RSS種子中會包含每一篇新的帖子。</li>
　　</ul>';
$string['search'] = '搜尋';
$string['searchdatefrom'] = '貼文必須在此日期之後';
$string['searchdateto'] = '貼文必須在此日期之前';
$string['searchforumintro'] = '請在下方一個或多個欄位中輸入你要搜尋的字串';
$string['searchforums'] = '搜尋';
$string['searchfullwords'] = '這些字要被視為一個完整的字句';
$string['searchnotwords'] = '不要包括這些字';
$string['searcholderposts'] = '搜尋較舊貼文...';
$string['searchphrase'] = '字串要完全符合';
$string['searchresults'] = '搜尋結果';
$string['searchsubject'] = '這些字要出現在主旨中';
$string['searchuser'] = '這個名字要與作者符合';
$string['searchuserid'] = '作者的Moodle帳號';
$string['searchwhichforums'] = '挑選你要搜尋的討論區';
$string['searchwords'] = '這些字可以出現在貼文的任何位置';
$string['seeallposts'] = '檢視這一用戶的所有貼文';
$string['shortpost'] = '簡短張貼';
$string['showbookmark'] = '允許帖文使用書籤功能';
$string['showbookmarkdisabledglobally'] = '書籤已在外掛程式的層面上停用';
$string['showbookmark_help'] = '如果啟用，討論區帖文將可以使用書籤功能';
$string['showdiscussionsubscribers'] = '顯示／隱藏議題訂閱者';
$string['showrecent'] = '在課程頁面中顯示最近的帖文';
$string['showrecent_help'] = '如果啟用將會在課程頁面中顯示最近的帖文';
$string['showsubscribers'] = '顯示/編輯目前訂閱者';
$string['showsubstantive'] = '允許標記為獨立存在';
$string['showsubstantivedisabledglobally'] = '獨立存在的標籤已在外掛程式的層面上停用';
$string['showsubstantive_help'] = '如果啟用將會教師標籤獨立存在的帖文';
$string['singleforum'] = '單一簡單討論主題';
$string['smallmessage'] = '{$a->user} 貼在 {$a->forumname}';
$string['sortdiscussions'] = '分類議題';
$string['sortdiscussionsby'] = '分類';
$string['splitprivatewarning'] = '您正在分拆私人回覆。分拆後，此帖文將不會是私人';
$string['startedby'] = '開始於';
$string['startedbyx'] = '開始於{$a}';
$string['startedbyxgroupx'] = '開始於 {$a->name}為群組{$a->group}';
$string['subject'] = '主旨';
$string['subjectbyprivateuserondate'] = '由{$a->author} 在{$a->date}私人{$a->subject} (private)';
$string['subjectbyuserondate'] = '{$a->subject} 由{$a->author} 在{$a->date}';
$string['subjectisrequired'] = '標題是需要的';
$string['subjectplaceholder'] = '您的標題';
$string['submit'] = '遞交';
$string['subscribe'] = '訂閱本討論區';
$string['subscribeall'] = '全部訂閱本討論區';
$string['subscribed'] = '已經訂閱';
$string['subscribedisc'] = '訂閱此議題';
$string['subscribeenrolledonly'] = '抱歉，只有選課的用戶才允許訂閱討論區貼文的通知。';
$string['subscribenone'] = '取消所有人的訂閱';
$string['subscribers'] = '訂閱者';
$string['subscribeshort'] = '訂閱';
$string['subscribestart'] = '傳送此討論區的新帖文提示';
$string['subscribestop'] = '我不想收到有關此討論區的新帖文提示';
$string['subscription'] = '訂閱';
$string['subscriptionauto'] = '自動訂閱';
$string['subscriptiondisabled'] = '關閉訂閱';
$string['subscriptionforced'] = '強迫訂閱';
$string['subscription_help'] = '訂閱討論區代表您將會收到此討論區的新帖文提示。通常您可以選擇您要否訂閱，但有時候會強制訂閱令所有人都能收到提示。';
$string['subscriptionmode'] = '訂閱模式';
$string['subscriptionmode_help'] = '當參與者訂閱一個討論區，它表示他們將透過電郵收到討論區的貼文的通知。

訂閱模式有四種：

*自選的訂閱--參與者可以自己選擇是否要訂閱。

*強制訂閱--每個人都訂閱，且無法自行取消。

*自動訂閱--每個人在開始時都訂閱，但可以隨時取消訂閱。

*訂閱被關閉--不允許任何人訂閱。

注意：任何訂閱模式的更改，只會影響在更改設定之後的選課者，而不是現有的選課者。';
$string['subscriptionoptional'] = '自由訂閱';
$string['subscriptions'] = '訂閱';
$string['substantive'] = '獨立存在';
$string['switchtoaccessible'] = '轉換到可進入模式';
$string['thisforumisthrottled'] = '這個討論區有一個張貼篇數的限制：在限定的期間內，限制張貼篇數。目前的設定為 {$a->blockperiod}期限內可張貼{$a->blockafter}篇。';
$string['thisisanonymous'] = '此討論區是匿名的';
$string['timedposts'] = '定時張貼';
$string['timestartenderror'] = '結束時間不可能比開始時間早';
$string['toggle:bookmark'] = '書籤';
$string['toggled:bookmark'] = '已書籤';
$string['toggled:subscribe'] = '已訂閱';
$string['toggled:substantive'] = '已標記成獨立存在';
$string['toggle:subscribe'] = '訂閱';
$string['toggle:substantive'] = '獨立存在';
$string['totaldiscussions'] = '帖文：{$a}';
$string['totalposts'] = '總共帖文';
$string['totalpostsanddiscussions'] = '總共帖文: {$a}';
$string['totalrating'] = '評分：: {$a}';
$string['totalreplies'] = '回覆：: {$a}';
$string['totalsubstantive'] = '獨立存在的帖文: {$a}';
$string['trackforum'] = '追蹤未閱讀的訊息';
$string['tracking'] = '追蹤';
$string['trackingoff'] = '關閉';
$string['trackingon'] = '強制';
$string['trackingoptional'] = '自訂';
$string['trackingoptions'] = '追蹤選項';
$string['trackingtype'] = '是否追蹤閱讀情況？';
$string['tree'] = '樹';
$string['unread'] = '新';
$string['unreadposts'] = '未閱帖文';
$string['unreadpostsnumber'] = '{$a}篇未閱讀';
$string['unreadpostsone'] = '1 篇未閱讀';
$string['unreadx'] = '未閱：{$a}';
$string['unsubscribe'] = '取消訂閱 本討論區';
$string['unsubscribeall'] = '取消全部討論區的訂閱';
$string['unsubscribeallconfirm'] = '你現在有訂閱 {$a} 討論區。你真的要取消訂閱所有討論區，並關閉討論區自動訂閱嗎？';
$string['unsubscribealldone'] = '你所有的討稐區訂閱已被移除。你可能仍會從強迫訂閱的討論區收到通知。若你不要從這伺服器接受任何email，請到\'我的個人資料表\'並關掉簡訊的設定。';
$string['unsubscribeallempty'] = '抱歉，你沒有訂閱任何討論區。若你不要從這伺服器接受任何email，請到\'我的個人資料表\'並關掉\'簡訊\'的設定。';
$string['unsubscribed'] = '訂閱已取消';
$string['unsubscribedisc'] = '取消訂閱此議題';
$string['unsubscribeshort'] = '取消訂閱';
$string['useadvancededitor'] = '使用進階編輯';
$string['usermarksread'] = '手動標記閱讀訊息';
$string['validationerrorsx'] = '您的提交有{$a->count} 錯誤：{$a->errors}';
$string['validationerrorx'] = '您的提交有錯誤: {$a}';
$string['viewalldiscussions'] = '檢視所有的議題';
$string['viewposters'] = '查看海報';
$string['warnafter'] = '顯示警告的限制貼文數目';
$string['warnafter_help'] = '學生在一段時間內，若貼文超過上限數目，就會被警告。這設定指出在多少篇貼文後會被警告。用戶若有 mod/hsuforum:postwithoutthrottling 權限則不受貼文數量的限制。';
$string['warnformorepost'] = '警告！在這討論區有一個以上的討論議題---請使用最新的';
$string['xdiscussions'] = '{$a}議題';
$string['xreplies'] = '{$a}回覆';
$string['xunread'] = '{$a} 新';
$string['yournewquestion'] = '您的新問題';
$string['yournewtopic'] = '您的新討論主題';
$string['yourreply'] = '您的回應內容';
