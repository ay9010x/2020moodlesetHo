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
 * Strings for component 'block_online_users_map', language 'zh_tw', branch 'MOODLE_24_STABLE'
 *
 * @package   block_online_users_map
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['centrelat'] = '起始的緯度';
$string['centrelng'] = '起始的經度';
$string['centreuser'] = '所有用戶地理位置的中心';
$string['configcentrelat'] = '這地圖正中心的起始緯度。以十進位表示。

請用25.033493(不是25° 2\' 0.575\'\'N)';
$string['configcentrelng'] = '這地圖正中心的起始經度。以十進位表示。

請用120.799446 (不是120° 47\' 58.01\'\' E)';
$string['configcentreuser'] = '把地圖移置到現在所有用戶的中心位置。縮放層次設到最頂端，這設定優先於以上的經緯度座標，除非現在的用戶沒以有效的位置。';
$string['configdebug'] = '當執行cron時，顯示除錯訊息。';
$string['configgoogleapikey'] = 'Google地圖API密鑰，從{$a}獲得密鑰';
$string['confignamesonmap'] = '是否將用戶姓名顯示在地圖上？若方格沒被勾選，用戶所在城市將代替名字被顯示。';
$string['configoffline'] = '也顯示離線用戶？';
$string['configtimetosee'] = '要等幾分鐘不活動之後，才認定該用戶是離線了。';
$string['configtype'] = '選擇要使用的地圖提供者';
$string['configupdatelimit'] = '在每次執行 cron 時，最多要更新幾個位置，---這樣才不會衝擊伺服器的效能。

它必須是大於或等於 0 的正整數，若設為 0 時，它會更新所有的紀錄。';
$string['configzoomlevel'] = '這地圖的初始放大層級';
$string['debug'] = '顯示除錯訊息';
$string['googleapikey'] = 'Google地圖API密鑰';
$string['namesonmap'] = '顯示用戶名稱';
$string['offline'] = '顯示離線用戶';
$string['periodnminutes'] = '最近{$a}分鐘';
$string['pluginname'] = '線上用戶地圖';
$string['timetosee'] = '在不活動多久之後移除';
$string['type'] = '要使用的地圖類型';
$string['updatelimit'] = '一次最多更新幾個位置';
$string['zoomlevel'] = '初始的放大層級';
