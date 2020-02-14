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
 * Strings for component 'cachestore_memcache', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   cachestore_memcache
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clustered'] = '啟用群集伺服器';
$string['clusteredheader'] = '拆開伺服器';
$string['clustered_help'] = '這是用來允許"讀一次，設定多個"的功能。

這樣做是要建立一個改進的儲存設計，來達到負載平衡的目標。這一儲存設計將會從一個伺服器(通常是本地主機)快速讀取，然後存放到許多伺服器上(在這負載平衡的群集的伺服器)。對於讀取頻繁的快取而言，這會節省很大的網路負載量。

當啟動這一設定，以上所列出的伺服器將會被用來做快速讀取。';
$string['pluginname'] = 'Memcache';
$string['prefix'] = 'Key prefix';
$string['prefix_help'] = '此前綴是用於 memcache 的伺服器上的所有鍵名。

*如果您只一個Moodle的實例使用這台伺服器，你可以保留這個預設值。

*由於密鑰長度的限制，只允許的最多5個字符。';
$string['prefixinvalid'] = '無效的前綴。您只能使用AZ，az或0-9-_。';
$string['servers'] = '伺服器';
$string['serversclusterinvalid'] = '當啟用群集時，只需要用到一個伺服器。';
$string['servers_help'] = '在此設定memcache調適器所使用的伺服器。
每行定義一個伺服器，並包含一個伺服器地址和選用的端口和權重。
如果沒有提供端口，則將使用預設端口（11211）。
例如:
<pre>
server.url.com
ipaddress:port
servername:port:weight
</pre>
如果*啟動群集伺服器*已被啟動，那這兒必須只有一個伺服器清單列在這裡。它通常是本地端機器，像是127.0.0.1或localhost。';
$string['sessionhandlerconflict'] = '警告：有一個memcached實例({$a})已經被配置為使用相同的memcached伺服器作為對話連線(sessinos)。清除所有的快取將會導致對話連線也會被清除。';
$string['setservers'] = '設定伺服器';
$string['setservers_help'] = '這是一個伺服器的清單，當在快取中的資料被更改時，它們也會被更新。它們通常是在群集中每一伺服器的完整名稱。
它**必須**包含在以上*伺服器*的清單中，即使用的是不同的主機名稱。

應該每一行界定一個伺服器，並包含伺服器位址和端口。
若沒有提供端口(port)，那就會使用預設端口(11211)。

例如：
<pre>
server.url.com
ipaddress:port
</pre>';
$string['testservers'] = '測試伺服器';
$string['testservers_desc'] = '指定一個或多個被用來測試 memcache 的伺服器。
若有指定一個測試伺服器，那麼 memcache 的表現可以在管理區塊的快取效能頁面上進行測試。
例如: 127.0.0.1:11211';
