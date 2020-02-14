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
 * Strings for component 'reservation', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   reservation
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addparticipant'] = '新增申請';
$string['after10min'] = '事件開始後10分鐘';
$string['after12h'] = '事件開始後12小時';
$string['after1d'] = '事件開始後1天';
$string['after1h'] = '事件開始後1小時';
$string['after1w'] = '事件開始後1週';
$string['after2d'] = '事件開始後2天';
$string['after2h'] = '事件開始後2小時';
$string['after2w'] = '事件開始後2週';
$string['after30min'] = '事件開始後30分鐘';
$string['after3w'] = '事件開始後3週';
$string['after4h'] = '事件開始後4小時';
$string['after4w'] = '事件開始後4週';
$string['after5min'] = '事件開始後5分鐘';
$string['after6h'] = '事件開始後6小時';
$string['alreadybooked'] = '你已經預定';
$string['atstart'] = '在事件開始';
$string['autohide'] = '預定清單自動隱藏';
$string['availablerequests'] = '可用的座位';
$string['badcourse'] = '課程不存在';
$string['badparent'] = '預定關連到其他預定資料現在不回復.連接已被移除.若需重新連接須手動進行.';
$string['badsection'] = '段落不存於課程中  "{$a}"';
$string['badteachers'] = '被指定的講師郵件 ({$a}) 找不到';
$string['badteachersmail'] = '被指定的講師郵件 ({$a}) 是假的';
$string['badtimeclose'] = '時間關閉是假的';
$string['badtimeend'] = '時間結束是假的';
$string['badtimeopen'] = '時間開啟是假的';
$string['badtimestart'] = '時間開始是假的';
$string['by'] = '依據';
$string['bydate'] = '事件日期';
$string['byname'] = '名字';
$string['bysection'] = '主題/週次';
$string['cancelledon'] = '被取消在';
$string['checkclashes'] = '檢查位置和時間的衝突';
$string['clashesreport'] = '衝突報告';
$string['cleanview'] = '指檢視當前的預定';
$string['close'] = '關閉';
$string['closed'] = '已經關閉';
$string['config'] = '預定模組整體設定';
$string['configautohide'] = '用來設定什麼時候，這些預選必須從預選名單(mod/reservation/index.php)中隱藏。這如果用在公開的名單中會很有用，它可以顯示一清乾淨的名單。';
$string['configcheckclashes'] = '用來設定是否在預選編輯頁啟用"檢查時間和地點的衝突"按鈕。';
$string['configdownload'] = '用來設定預設檔案格式，以供下載所有申請和預選名單使用。';
$string['configfields'] = '用來設定顯示在預選表中的欄位';
$string['configlocations'] = '在這一頁你可以為選課管理標準地點';
$string['configmaxoverbook'] = '用來設定在預選時，可以超量預選的百分比。';
$string['configmaxrequests'] = '用來設定在預選編輯頁中的下拉選單的限制。';
$string['configpubliclists'] = '用來設定預選名單是公開(不需要登入即可看到)或不公開。';
$string['configsortby'] = '用來設定預選名單如何排序。';
$string['configsublimits'] = '用來設定在預選編輯頁可以有多少sublimits rules row';
$string['description'] = '說明';
$string['duration10h'] = '10小時';
$string['duration10min'] = '10分鐘';
$string['duration11h'] = '11小時';
$string['duration12h'] = '12小時';
$string['duration15min'] = '15分鐘';
$string['duration20min'] = '20分鐘';
$string['duration2h'] = '2小時';
$string['duration30min'] = '30分鐘';
$string['duration3h'] = '3小時';
$string['duration45min'] = '45分鐘';
$string['duration4h'] = '4小時';
$string['duration5h'] = '5 小時';
$string['duration5min'] = '5 分鐘';
$string['duration60min'] = '60分鐘';
$string['duration6h'] = '6小時';
$string['duration7h'] = '7 小時';
$string['duration8h'] = '8 小時';
$string['duration90min'] = '90分鐘';
$string['duration9h'] = '9小時';
$string['enablenote'] = '啟用用戶筆記';
$string['equal'] = '等於';
$string['err_sublimitsgreater'] = '錯誤! 配額限制的總和大於最大可允許的申請數';
$string['err_timeendlower'] = '錯誤! 設定的事件結束日期早於開始日期';
$string['err_timeopengreater'] = '錯誤! 預約的開始日期設在結束日期之後';
$string['events'] = '行事曆事件';
$string['eventsettings'] = '事件設定';
$string['explainconfig'] = '管理員可以為預約模組，此此進行整體設定。';
$string['fields'] = '顯示出來的欄位';
$string['fullview'] = '也檢視已經刪掉的預定';
$string['gradedmail'] = '{$a->teacher}針對你的預約 \'{$a->reservation}\'已經貼出一些回饋

你可以看這裡:

    {$a->url}';
$string['gradedmailhtml'] = '{$a->teacher}針對你的預約\'<i>{$a->reservation}</i>\' 已經貼出一些回應<br /><br />
你可以 <a href="{$a->url}">在此</a> 看它。';
$string['justbooked'] = '你已經登記在：{$a}';
$string['location'] = '地點';
$string['locations'] = '管理地點';
$string['locationslist'] = '地點清單';
$string['mail'] = '預選 \'{$a->reservation}\'已經關閉。

你可以從下面下載預選名單：

{$a->url}';
$string['mailhtml'] = '預選<em>{$a->reservation}</em> 已經被關閉。<br /><br />
你可以從 <a href="{$a->url}">這裡</a>下載預選名單。';
$string['manualusers'] = '手動預選要顯示給用戶的';
$string['maxoverbook'] = '超量預選最高百分比';
$string['maxrequest'] = '最大預約';
$string['minduration'] = '事件最小期間';
$string['modulename'] = '預選';
$string['modulenameplural'] = '預選';
$string['newlocation'] = '新地點';
$string['noclashes'] = '沒有發現時間和地點的衝突';
$string['nolimit'] = '無預選人數限制';
$string['nomorerequest'] = '沒有更多座位可用';
$string['nooverbook'] = '沒有超量預選';
$string['noreservations'] = '沒有預選';
$string['note'] = '筆記';
$string['noteachers'] = '沒有可用的教師';
$string['notequal'] = '不等於';
$string['notopened'] = '沒有開啟';
$string['novalues'] = '沒有可用的數值給這一欄位';
$string['number'] = '預選人數';
$string['otherlocation'] = '其他指定地點';
$string['overbook'] = '超量預選中';
$string['overbookonly'] = '只有超量預選的座位可用';
$string['overbookstep'] = '超量預選步驟';
$string['pluginadministration'] = '課程預選管理';
$string['pluginname'] = '預選';
$string['publiclists'] = '預選公佈名單';
$string['requestoverview'] = '預選綜覽';
$string['requests'] = '申請';
$string['reservationcancelled'] = '預約已經取消';
$string['reservationclosed'] = '預約已經關閉';
$string['reservationdenied'] = '預約不被允許';
$string['reservation:downloadrequests'] = '可以下載請求清單';
$string['reservation:grade'] = '可以指派分數';
$string['reservation:manualdelete'] = '可以刪除其他用戶的申請';
$string['reservation:manualreserve'] = '可以為其他用戶提交申請';
$string['reservationnotopened'] = '預選還沒開始';
$string['reservation:reserve'] = '可以提交自己的申請';
$string['reservations'] = '預選';
$string['reservationsettings'] = '預選設定';
$string['reservation:viewnote'] = '可以看到申請的註記';
$string['reservation:viewrequest'] = '可以看到申請名單';
$string['reserve'] = '預選';
$string['reservecancel'] = '取消預選';
$string['reserved'] = '已經預選';
$string['reservedon'] = '預選在';
$string['resetreservation'] = '移除所有預選';
$string['save'] = '儲存分數';
$string['selectvalue'] = '選擇一個可用的數值';
$string['showrequest'] = '用戶可以看到申請名單';
$string['sortby'] = '預選名單排序依據';
$string['sublimit'] = '配額限制{$a}';
$string['sublimitrules'] = '配額限制規則';
$string['sublimits'] = '預選';
$string['timeclose'] = '預約結束於';
$string['timeend'] = '結束日期';
$string['timeopen'] = '預約開始於';
$string['timestart'] = '開始日期';
$string['with'] = '和';
$string['withselected'] = '和被選出的....';
$string['yourgrade'] = '你在這預選上的積分是：
{$a->grade}/${a->maxgrade}';
$string['yourscale'] = '你在這預選上的積分是：{$a}';
