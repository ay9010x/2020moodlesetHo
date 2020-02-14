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
 * Strings for component 'cachestore_file', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   cachestore_file
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autocreate'] = '自動建立目錄';
$string['autocreate_help'] = '若啟用，在路徑上指定的目錄，若不是已事先存在，將會自動建立。';
$string['path'] = '快取路徑';
$string['path_help'] = '這一目錄將在快取儲存時會用來儲存檔案。若留空白(預設)，將會在Moodledata目錄下自動建立。

它也可以把檔案儲存指向更有效率的磁碟機(比如在記憶體上)上的目錄。';
$string['pluginname'] = '檔案快取';
$string['prescan'] = '預先掃描目錄';
$string['prescan_help'] = '若啟用，當快取第一次被使用時會掃描這目錄，並將對檔案的請求與這掃描的資料核對。

若你的檔案系統運作很慢，而檔案操作造成你系統的瓶頸時，這會很有幫助。';
$string['singledirectory'] = '單一目錄儲存';
$string['singledirectory_help'] = '若啟用，檔案(放入快取的項目)將會儲存在單一目錄，而不是被分割成多個目錄。<br />
啟用這一選項將加快檔案的互動，但是會增加達到檔案系統限制的風險。<br />
勸你只有在下列情形才開啟：<br />
 - 你確知快取的檔案數量很少，不會造成你的檔案系統的問題。<br />
 - 被快取的資料的產生的代價不是很高。如果它是很高，那麼停留在預設狀態，可能才是較好的選擇。';
