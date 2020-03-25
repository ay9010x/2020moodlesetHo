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
 * Strings for component 'tool_installaddon', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_installaddon
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['acknowledgement'] = '銘謝';
$string['acknowledgementtext'] = '我知道在安裝外掛之前，將這個網站做完整的備份是我的責任。我接受並理解到外掛(特別是，但非侷限於，源自非官方的程式碼)可能包含安全上的漏洞，會使網站掛掉，或造成私人資料的洩漏或喪失。';
$string['featuredisabled'] = '這一網站已經關閉了外掛安裝器。';
$string['installaddon'] = '安裝外掛!';
$string['installaddons'] = '安裝外掛';
$string['installfromrepo'] = '從Moodle的外掛套件目錄安裝外掛';
$string['installfromrepo_help'] = '你將會被重新導向 Moodle外掛目錄來搜尋並安裝一個外掛。注意，你的網站的完整名稱、網址、和Moodle 版本將會同時送出，以讓安裝過程變得更容易。';
$string['installfromzip'] = '從ZIP壓縮檔安裝外掛程式';
$string['installfromzipfile'] = 'ZIP包裹';
$string['installfromzipfile_help'] = '這外掛ZIP包裹必須包含恰好一個目錄，其名稱要符合這外掛。這壓縮的內容將會被抽取到一個給這外掛類型的適當位置 。若這包裹已經從Moodle外掛目錄被下載，那它將會有這一結構。';
$string['installfromzip_help'] = '除了直接從 Moodle外掛目錄安裝外掛之外，另一個方法是上傳這外掛的ZIP 包裹。這ZIP包裹應該和從Moodle外掛包裹下載的包裹有相同的結構。';
$string['installfromzipinvalid'] = '外掛的zip壓縮包裹必須包含指一個目錄，其名稱要符合外掛的名稱。現在所提供的檔案不是一個有效的外掛zip壓縮包裹。';
$string['installfromziprootdir'] = '重新命名這根目錄';
$string['installfromziprootdir_help'] = '某些ZIP壓縮包裹，比如那些由Github產生的，可能包含一個不正確的根目錄名稱。如果是這樣，可以在此輸入正倔名稱。';
$string['installfromzipsubmit'] = '從這ZIP壓縮檔安裝外掛';
$string['installfromziptype'] = '外掛類型';
$string['installfromziptype_help'] = '若外掛有正確宣告他們的組成構件的名稱，這安裝器能夠自動偵測這一外掛。如果自動偵測失敗，請改用手動選擇你即將安裝的外掛的正確類型。警告：若指定的外掛類型不正確，安裝程序會嚴重失敗。';
$string['permcheck'] = '要確定外掛類型根目錄位置是可透過網頁伺服器程序寫入的。';
$string['permcheckerror'] = '當檢查寫入權限時發現錯誤';
$string['permcheckprogress'] = '正在檢查寫入的權限....';
$string['permcheckrepeat'] = '再檢查一次';
$string['permcheckresultno'] = '外掛類型位置<em>{$a->path}</em> 是不可寫入的';
$string['permcheckresultyes'] = '外掛類型位置<em>{$a->path}</em> 是可寫入的';
$string['pluginname'] = '外掛安裝器';
$string['remoterequestalreadyinstalled'] = '這兒有一個請求要從這網站的Moodle外掛目錄安裝外掛{$a->name} ({$a->component}) 版本 {$a->version} 。然而，這一外掛套件 <strong>已經安裝</strong> 在這網站上。';
$string['remoterequestconfirm'] = '這兒有一個請求要從這網站的Moodle外掛目錄安裝外掛<strong>{$a->name} </strong> ({$a->component}) 版本 {$a->version} 。若你繼續，這外掛 ZIP包裹將會被下載做驗證。目前還不會安裝任何東西。';
$string['remoterequestinvalid'] = '這兒有一請求要求從這網站的Moodle外掛目錄中安裝一外掛。不幸的是這請求是無效的，所以這外掛無法安裝。';
$string['remoterequestnoninstallable'] = '這邊收到一個請求，要從從這一網站的Moodle 外掛目錄中安裝外掛{$a->name} ({$a->component}) 版本 {$a->version} 。然而，在外瓜安裝的預先檢查程序是失敗的(理由代碼：{$a->reason})';
$string['remoterequestpermcheck'] = '這兒有一個請求要從這網站的Moodle外掛目錄安裝外掛<strong>{$a->name} </strong> ({$a->component}) 版本 {$a->version} 。然而，這外掛類型的位置<strong>{$a->typepath}</strong>是<strong>不可寫入的</strong>。

你需要給予這網頁伺服器用戶有對外掛類型位置有寫入的權限，然後按下繼續按鈕來重複這個檢查。';
$string['remoterequestpluginfoexception'] = '糟了...當試著獲取有關這外掛{$a->name}({$a->component}) 版本{$a->version}的訊息時發生錯誤。這外掛無法安裝。請打開除錯模式來看這錯誤的細節。';
$string['typedetectionfailed'] = '無法偵測到外掛類型，請以手動選擇外掛類型';
$string['typedetectionmismatch'] = '這選出的外掛的類型不符合外掛{$a}所宣告的類型。';
