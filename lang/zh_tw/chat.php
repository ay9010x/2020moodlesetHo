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
 * Strings for component 'chat', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   chat
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activityoverview'] = '你有即將到來的聊天排程';
$string['ajax'] = '使用Ajax版本';
$string['autoscroll'] = '自動捲動';
$string['beep'] = '呼叫';
$string['bubble'] = '鬆散';
$string['cantlogin'] = '無法登入到聊天室！！';
$string['chat:addinstance'] = '新增一個聊天室';
$string['chat:chat'] = '進出聊天室';
$string['chat:deletelog'] = '刪除聊天記錄';
$string['chat:exportparticipatedsession'] = '匯出"您參加過的"聊天室對話時段內容';
$string['chat:exportsession'] = '匯出"所有"聊天室對話時段內容';
$string['chatintro'] = '簡介文字';
$string['chatname'] = '聊天室名稱';
$string['chat:readlog'] = '檢視聊天記錄';
$string['chatreport'] = '聊天室對話排程';
$string['chat:talk'] = '在聊天室發言';
$string['chattime'] = '下次聊天時間';
$string['compact'] = '緊湊';
$string['composemessage'] = '撰寫訊息';
$string['configmethod'] = '使用"ajax聊天方法"會提供一個以ajax為基礎的聊天介面，它會定期聯絡伺服器來更新。

使用"普通聊天方法"需要 client 端必須定期聯絡伺服器才能更新，它不需要設定即可到處可用，但當聊天者較多時，它可能會對伺服器造成相當大的負載。

使用"伺服器 daemon" 需要存取 unix 介面，但可以得到反應比較快的聊天環境。';
$string['confignormalupdatemode'] = '聊天室的更新一般是使用HTTP 1.1的<b>保持連線</b>的功能，但它對於伺服器來說負荷相當重。

另一更進階的方法是使用<b>串流</b>策略來饋送更新資料給用戶。使用<b>串流</b>模式會比較好一些(類似chatd法)，但也許您的伺服器並不支援。';
$string['configoldping'] = '用戶隔了多久不發言後(以秒計)，將認定為離線了？這只是一個上限，通常離線會很快被偵測到。

你的伺服器會要求更低的數值。若你使用正常方法，就是不要低於2*chat_refresh_room(用戶列表刷新頻率)。';
$string['configrefreshroom'] = '聊天室自動刷新的間隔時間(秒)，如果設得短，聊天看起來反應較快，但聊天的人數多時，會大量增加伺服器的負荷。若你使用<em>串流</em>方式刷新，你可以選擇較高的刷新頻率--試試2。';
$string['configrefreshuserlist'] = '用戶列表的刷新頻率(秒)？';
$string['configserverhost'] = '伺服器 daemon 所在的主機名稱';
$string['configserverip'] = '與以上主機名稱相對應的數字 IP位址';
$string['configservermax'] = '最多允許多少位客戶';
$string['configserverport'] = '要使用伺服器daemon時的連接埠';
$string['coursetheme'] = '課程主題';
$string['currentchats'] = '開啟聊天室對話時段';
$string['currentusers'] = '當前用戶';
$string['deletesession'] = '刪除這次對話時段內容';
$string['deletesessionsure'] = '你確定要刪除這次對話時段的內容嗎?';
$string['donotusechattime'] = '不要公布任何聊天時間';
$string['enterchat'] = '點選這裡進入聊天室';
$string['entermessage'] = '輸入您的訊息';
$string['errornousers'] = '找不到任何用戶！';
$string['eventmessagesent'] = '訊息已送出';
$string['eventsessionsviewed'] = '檢視對話時段';
$string['explaingeneralconfig'] = '這些設定<strong>總是</strong>被使用';
$string['explainmethoddaemon'] = '這些設定<strong>只有</strong>當您選擇聊天室模式為"聊天伺服器daemon" 才有效';
$string['explainmethodnormal'] = '這些設定<strong>只有</strong>當您選擇聊天室模式為"普通模式"才有效';
$string['generalconfig'] = '一般設定';
$string['idle'] = '閒置';
$string['inputarea'] = '輸入區';
$string['invalidid'] = '找不到這個聊天室！';
$string['list_all_sessions'] = '列出所有的對話時段';
$string['list_complete_sessions'] = '列出剛結束的對話時段';
$string['listing_all_sessions'] = '列出所有的對話時段。';
$string['messagebeepseveryone'] = '{$a} 呼叫所有人';
$string['messagebeepsyou'] = '{$a} 呼叫您';
$string['messageenter'] = '{$a} 剛加入這次聊天';
$string['messageexit'] = '{$a} 已退出這次聊天';
$string['messages'] = '訊息';
$string['messageyoubeep'] = '您呼叫了{$a}';
$string['method'] = '聊天方式';
$string['methodajax'] = 'Ajax方法';
$string['methoddaemon'] = '聊天伺服器daemon';
$string['methodnormal'] = '普通方法';
$string['modulename'] = '聊天室';
$string['modulename_help'] = '這聊天室活動模組讓參與者可以有以文字為基礎的、及時同步的相互討論。

這聊天室可以是一次性活動，或是在每天或每週固定時間的活動。

聊天過程的對話內容可以被儲存，並使它可以讓每個人看到，或限制只給有權限看聊天室日誌檔的人看。

聊天室對於無法面對面交談的團體特別有用，比如：

* 讓住在不同地區，但選修通一線上課程的用戶能定期彼此分享經驗。
* 讓暫時缺席的學生能與教師交談，以彌補應有的學習。
* 小組成員在教室外彼此討論作業，或與教師討論。
* 讓年幼學生初次體驗社會網路的世界。
* 邀請專家或畢業生開設一個"問與答的時間"，讓學生盡情發問。
* 正式考試前，讓學生彼此提問，作為考試前的複習。';
$string['modulenameplural'] = '聊天室';
$string['neverdeletemessages'] = '從未刪除訊息';
$string['nextsession'] = '下一個排定的對話時段';
$string['nochat'] = '沒有找到聊天室';
$string['no_complete_sessions_found'] = '沒有找到已經結束的對話時段。';
$string['noguests'] = '該聊天室未對訪客開放';
$string['nomessages'] = '尚無訊息';
$string['nopermissiontoseethechatlog'] = '你沒有權限察看聊天室日誌。';
$string['normalkeepalive'] = '保持連線';
$string['normalstream'] = '串流';
$string['noscheduledsession'] = '沒有排定的對話時段';
$string['notallowenter'] = '您不能進入此聊天室。';
$string['notlogged'] = '您沒有登入！';
$string['oldping'] = '逾時斷線';
$string['page-mod-chat-x'] = '任何聊天模組頁面';
$string['pastchats'] = '過去的聊天室對話時段內容';
$string['pluginadministration'] = '聊天室管理員';
$string['pluginname'] = '聊天室';
$string['refreshroom'] = '刷新聊天室';
$string['refreshuserlist'] = '刷新用戶列表';
$string['removemessages'] = '刪除所有訊息';
$string['repeatdaily'] = '在每天的相同時段';
$string['repeatnone'] = '無回覆-只公告這特定時段';
$string['repeattimes'] = '重複/公布的對話時段';
$string['repeatweekly'] = '每週的相同時段';
$string['saidto'] = '對';
$string['savemessages'] = '保存過去的聊天內容';
$string['search:activity'] = '聊天 - 活動訊息';
$string['seesession'] = '查看這一對話時段內容';
$string['send'] = '傳送';
$string['sending'] = '正在傳送';
$string['serverhost'] = '伺服器名稱';
$string['serverip'] = '伺服器IP';
$string['servermax'] = '最大用戶數';
$string['serverport'] = '伺服器連接埠';
$string['sessions'] = '聊天對話時段';
$string['sessionstart'] = '下一個聊天時段即將在{$a->date}開始，(離現在{$a->fromnow} )';
$string['strftimemessage'] = '%H:%M';
$string['studentseereports'] = '所有人都可查看過去的聊天內容';
$string['studentseereports_help'] = '如果選擇否，那麼只有具有mod/chat:readlog權限的用戶可以看到聊天日誌。';
$string['talk'] = '交談';
$string['updatemethod'] = '更新方式';
$string['updaterate'] = '更新速率：';
$string['userlist'] = '用戶列表';
$string['usingchat'] = '使用聊天室';
$string['usingchat_help'] = '<p align="center"><b>聊天</b></p>
<p>聊天室模組提供了如下模組以讓聊天更加有趣。</p>
<dl>
<dt><b>表情臉型</b></dt>
<dd>所有您可以在Moodle中其他地方使用的表情臉型(情緒圖示)都可以用在聊天室中，它們都能夠被正常顯示出來。例如，:-) = <img alt src="pix/s/smiley.gif" />  </dd>
　　
<dt><b>鏈結</b></dt>
<dd>輸入的Internet位址會自動被轉換為鏈結。</dd>
　　
<dt><b>誇張表情</b></dt>
<dd>在行首添加“/me”或者“:”可以將這行變成誇張表情描述。例如，如果假設您叫Kim，並且您輸入了“:laughs!”或者“/me laughs!”，則所有人都會看到“Kim laughs!”</dd>
　　
<dt><b>喚醒</b></dt>
<dd>您可以通過點擊別人姓名旁的“beep”鏈結來喚醒他。要想一下子喚醒所有的人，可以輸入“beep all”。</dd>
　　
<dt><b>HTML</b></dt>
<dd>如果您知道如何是用HTML，您可以在您輸入的文字中加入HTML，這樣就可以插入圖片、播放聲音或者創建不同顏色和大小的文字。</dd>
　　
</dl>
　　
　　';
$string['viewreport'] = '查看過去對話時段的內容';
