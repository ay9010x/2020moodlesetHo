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
 * Strings for component 'cachestore_memcached', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   cachestore_memcached
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bufferwrites'] = '緩衝區寫入';
$string['bufferwrites_help'] = '啟用或關閉"緩衝的輸入或輸出"。啟用緩衝的輸入或輸出會把指令儲存到緩衝記憶體，而不是被送出。任何擷取資料的動作會導致這緩衝被送到遠端的連接。
退出連接或關閉連接也將導致緩衝的資料推送到遠程連接。';
$string['clustered'] = '啟用群集伺服器';
$string['clusteredheader'] = '拆開伺服器';
$string['clustered_help'] = '這是用來允許"讀一次，設定多個"的功能。

這樣做是要建立一個改進的儲存設計，來達到負載平衡的目標。這一儲存設計將會從一個伺服器(通常是本地主機)快速讀取，然後存放到許多伺服器上(在這負載平衡的群集的伺服器)。對於讀取頻繁的快取而言，這會節省很大的網路負載量。

當啟動這一設定，以上所列出的伺服器將會被用來做快速讀取。';
$string['hash'] = '雜湊演算法';
$string['hash_crc'] = 'CRC';
$string['hash_default'] = '預設(一次一個)';
$string['hash_fnv1_32'] = 'FNV1_32';
$string['hash_fnv1_64'] = 'FNV1_64';
$string['hash_fnv1a_32'] = 'FNV1A_32';
$string['hash_fnv1a_64'] = 'FNV1A_64';
$string['hash_help'] = '指定一個用於這項目鍵的雜湊演算法。每種雜湊演算法都各有其優缺點，若你不知道或不在意，請保留預設值。';
$string['hash_hsieh'] = 'Hsieh';
$string['hash_md5'] = 'MD5';
$string['hash_murmur'] = 'Murmur';
$string['isshared'] = '共用的快取';
$string['isshared_help'] = '你的 memcached 伺服器是否也被其他應用軟體使用？

若快取是與其他應用軟體共享，那麼每一鍵將會被個別的清除，以確保只有這一應用軟體所擁有的資料會被清除(其他應用軟體的資料不會被更改)。這會導致清除快取時的效能下降。

如果你為這一應用軟體設定一專用的快取，那整個快取可以安全地刷新，而不必擔心摧毀其他應用軟體的快取資料。這輝加快在清除快取時的速度。';
$string['pluginname'] = 'Memcached';
$string['prefix'] = '前綴鍵';
$string['prefix_help'] = '這可以為你的項目鍵建立一個"域"，它讓你在單一的memcached 安裝上建立多個memcached 儲存。<be />
它不能多過16個字符，以確保不會遇到鍵長度的問題。';
$string['prefixinvalid'] = '無效的前綴。您只能使用AZ，az或0-9-_。';
$string['serialiser_igbinary'] = 'igbinary系列化程序';
$string['serialiser_json'] = 'JSON系列化程序';
$string['serialiser_php'] = '預設PHP系列化程序';
$string['servers'] = '伺服器';
$string['serversclusterinvalid'] = '當群聚伺服器已被啟用，就只需要一個伺服器。';
$string['servers_help'] = '在此設定memcached 調適器所使用的伺服器。
每一行定義一個伺服器，並包含一個伺服器地址和選用的端口和權重。
如果沒有提供端口，則將使用預設端口（11211）。

例如:
<pre>
server.url.com
ipaddress:port
servername:port:weight
</pre>

如果*啟動群集伺服器*已被啟動，那這兒必須只有一個伺服器列在這裡。它通常是本地端機器，像是127.0.0.1或localhost。';
$string['sessionhandlerconflict'] = '警告：有一個memcached實例({$a})已經被配置為使用相同的memcached伺服器作為對話連線(sessinos)。清除所有的快取將會導致對話連線也會被清除。';
$string['setservers'] = '設定伺服器';
$string['setservers_help'] = '這是所有的伺服器的清單，當在快取裡的資料被更改時，它們就會隨之更新。通常是在同一群集裡每一個伺服器的完整、合格的名稱。

它**必須**包含列在以上*伺服器*，即使是使用不同的主機名稱。

伺服器必須每一行定義一個，且包含一伺服器的位址和選用的端口。如果沒有提供端口，將會使用預設的端口(11211)。

例如：
<pre>
server.url.com
ipaddress:port
</pre>';
$string['testservers'] = '測試伺服器';
$string['testservers_desc'] = '指定一個或多個被用來測試 memcache 的伺服器。
若有指定一個測試伺服器，那麼 memcache 的表現可以在管理區塊的快取效能頁面上進行測試。
例如: 127.0.0.1:11211';
$string['upgrade200recommended'] = '我們建議您將您的Memcached PHP擴展升級到版本 2.0.0 或以上。
你現在所使用的Memcached PHP擴展版本，並沒有提供Moodle所需的快取功能。在你升級之後，我們建議您不要配置任何其他應用軟體來使用Moodle所用的Memcached伺服器。';
$string['usecompression'] = '使用壓縮';
$string['usecompression_help'] = '啟用或關閉有效載荷壓縮。當啟用時，檔案長度超過一定界線(目前是100 bytes)，將會在儲存時被壓縮，且在擷取時被解壓縮。';
$string['useserialiser'] = '使用序列化程序';
$string['useserialiser_help'] = '指定要用於serializing non-scalar 的序列化程序。

有效的序列化程序是Memcached ::SERIALIZER_PHP
或Memcached:;SERIALIZER_IGBINARY。
後者只有在memcached的配置了----
啟用 - memcached - igbinary選項，且igbinary擴展也被加載時，才能使用。';
