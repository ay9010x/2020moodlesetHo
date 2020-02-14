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
 * Strings for component 'logstore_legacy', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   logstore_legacy
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['eventlegacylogged'] = '舊式事件紀錄';
$string['loglegacy'] = '紀錄舊式資料';
$string['loglegacy_help'] = '這一外掛會將日誌資料紀錄到舊的日誌資料表(mdl_log)中。
這一功能已經被更新、更豐富、更有效率的日誌外掛套件所取代。因此你應該只有在你有舊的自訂的報告，它需要查詢舊的資料表時才使用這外掛。
寫入舊的日誌資料表將增加系統的負荷，因此建議你在不需要用到它時，關閉它以提高系統效能。';
$string['pluginname'] = '舊式日誌';
$string['pluginname_desc'] = '一個日誌外掛套件，可在舊式日誌資料表上儲存日誌條目。';
$string['taskcleanup'] = '舊的日誌資料表清理';
