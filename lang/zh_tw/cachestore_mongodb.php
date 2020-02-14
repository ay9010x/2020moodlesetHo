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
 * Strings for component 'cachestore_mongodb', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   cachestore_mongodb
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['database'] = '資料庫';
$string['database_help'] = '要使用的資料庫名稱';
$string['extendedmode'] = '使用擴展鍵';
$string['extendedmode_help'] = '如果啟動此選項，鍵盤所有的鍵都可以與此外掛程式配合使用。此指令並非來自內部，但如您選擇此項目，可供您易於以手動的方式搜尋MongoDB外掛。啟動此功能會在銀幕上方多出一列標題，所以建議只有在有必要時才啟動之。';
$string['password'] = '密碼';
$string['password_help'] = '當進行連結時所使用的用戶密碼';
$string['pleaseupgrademongo'] = '你正在使用舊版本的PHP mongo擴展(<1.3)。未來將會放棄對於舊版Mongo擴展的支援，請考慮將它升級。';
$string['pluginname'] = 'MongoDB';
$string['replicaset'] = '副本集合。';
$string['replicaset_help'] = '設定要連結的副本的名稱。如果這有寫上去，將在seeds使用ismaster資料庫指令來決定主伺服器，這樣驅動器可能會停止連結到一伺服器，即使這伺服器沒有在清單上。';
$string['server'] = '伺服器';
$string['server_help'] = '這是你要使用的伺服器的連接字串。你可以使用逗點分隔的字串來指定多個伺服器。';
$string['testserver'] = '測試伺服器';
$string['testserver_desc'] = '這是要連接到測試用的伺服器的字串。若指定一個測試用的伺服器，那就可以在網站管理區塊的快取效能頁面測試MongDB的效能。
例如： mongodb://127.0.0.1:27017';
$string['username'] = '用戶名稱';
$string['username_help'] = '當進行連結時所使用的用戶名稱';
$string['usesafe'] = '使用安全';
$string['usesafe_help'] = '若啟用，這使用安全選項將會使用在插入、取得、移除的操作上。若你有指定一個副本集合，它將會強制使用。';
$string['usesafevalue'] = '使用安全值';
$string['usesafevalue_help'] = '為了使用上的安全，你可以選擇提供一指定的數值。';
