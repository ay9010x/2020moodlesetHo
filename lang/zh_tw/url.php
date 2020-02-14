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
 * Strings for component 'url', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   url
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['chooseavariable'] = '選擇一變項....';
$string['clicktoopen'] = '點選 {$a} 來開啟資源。';
$string['configdisplayoptions'] = '選擇所有應該可用的選項，現有的設定不會被修改。按住CTRL鍵可以一次選擇多個欄位。';
$string['configframesize'] = '當一網頁或一被上傳的檔案是顯現在一個框架中，這個頂層架框(它包含導覽列)的高度(以像素表示)。';
$string['configrolesinparams'] = '若你要在可用參數變項的清單中，包含本地化的腳色名稱，則啟動它。';
$string['configsecretphrase'] = '這神秘片語是被用來製造加密碼數值，它可被送到某些伺服器作為參數。
這加密碼由當前用戶IP位址，串接上你的神祕片語之後的md5值。舉例：
code=md5(IP.secretphrase)。

請注意，這不是很穩定的，因為IP位址可能改變，且常常與其他電腦共用。
。';
$string['contentheader'] = '內容';
$string['createurl'] = '建立一網址';
$string['displayoptions'] = '可用的顯示選項';
$string['displayselect'] = '顯示';
$string['displayselectexplain'] = '選擇顯示類型，不幸的是，不是所有類型都適合所有的網址。';
$string['displayselect_help'] = '此一設定，還有網址的檔案類型以及瀏覽器是否允許以鑲入方式呈現，決定了這網址會如何呈現。這些選項包括：

* 自動的 - 讓系統依據網址自動為您選擇最佳的顯示方式。
* 鑲入的 - 網址檔案被顯示在Moodle頁面內，有導覽列、檔案說明和任何區塊。
* 以原視窗開啟 - 只有這網址檔案被顯示在瀏覽器視窗
* 用彈出式視窗開啟 - 網址被顯示在一彈出的小的新視窗中(可用滑鼠調整大小)，但沒有工具列和位址列
* 用框架開啟 - 網址被顯示在一框架上端有導覽列和檔案說明
* 以新視窗開啟 - 網址被顯示在另一新瀏覽器視窗，有工具列和位址列。';
$string['externalurl'] = '外部網址';
$string['framesize'] = '架框高度';
$string['invalidstoredurl'] = '不能顯示這一資源，網址無效。';
$string['invalidurl'] = '輸入的網址是無效的';
$string['modulename'] = '網址';
$string['modulename_help'] = '這網址模組可以讓教師提供一網址做為課程資源。任何可以經由線上取得的，比如文件或圖片，都可以鏈結過去。這網址不一定是要網站的首頁。

特定網頁的網址可以在瀏覽器中複製和貼上，或者教師可以使用檔案挑選器，並從倉儲(比如Flickr、YouTube、wikimedia，看本站啟用了哪一個)中挑選一鏈結。

網址有一些顯示用的選項，比如"鑲入"或"在新視窗中開啟"，和"進階選項"來遞送訊息，如學生姓名，到該網址。

注意，這網址也可以透過文字編輯器，被添加到其他資或活動類型。';
$string['modulenameplural'] = '網址';
$string['page-mod-url-x'] = '任何網址模式頁面';
$string['parameterinfo'] = '&amp;參數=變項';
$string['parametersheader'] = '網址變項';
$string['parametersheader_help'] = '有時候內部Moodle變項會自動附加倒這一網址上。輸入你的姓名到每一個文字盒作為參數，然後選擇需要的配對變項。';
$string['pluginadministration'] = '網址模組管理';
$string['pluginname'] = '網址';
$string['popupheight'] = '彈出視窗高度(以像素表示)';
$string['popupheightexplain'] = '指定預設的彈出視窗高度。';
$string['popupwidth'] = '彈出視窗寬度(以像素表示)';
$string['popupwidthexplain'] = '指定預設的彈出視窗寬度。';
$string['printintro'] = '顯示網址的說明';
$string['printintroexplain'] = '在內容之下顯示網址的說明，某些顯示類型可能無法顯示說明，即使他已被啟動。';
$string['rolesinparams'] = '包含角色名稱在參數中';
$string['search:activity'] = '網址';
$string['serverurl'] = '伺服器網址';
$string['url:addinstance'] = '新增一網址資源';
$string['url:view'] = '檢視網址';
