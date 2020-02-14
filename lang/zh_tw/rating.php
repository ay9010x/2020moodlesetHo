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
 * Strings for component 'rating', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   rating
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aggregateavg'] = '評比平均分數';
$string['aggregatecount'] = '評比次數';
$string['aggregatemax'] = '最高';
$string['aggregatemin'] = '最低';
$string['aggregatenone'] = '不做評比';
$string['aggregatesum'] = '累加總和';
$string['aggregatetype'] = '彙整統計類型';
$string['aggregatetype_help'] = '這彙整統計類型定義了評比是如何合併成為成績簿上的最後分數。

*評比的平均數---以所有評比的算術平均數作為最後分數。
*評比的次數---以被評比的次數(有如按讚的人數)作為最後分數。注意，總和不能超過這活動所設定的最高分
*最大值---最高的評比將成為最後分數。
*最小值---最低的評比將成為最後分數。
*累加總和---將所有評比總加在一起。注意，總和不能超過這活動所設定的最高分。

若選擇"不做評比"，那這活動將不會出現在成績簿上。';
$string['allowratings'] = '允許項目被評比';
$string['allratingsforitem'] = '所有提交的評比';
$string['capabilitychecknotavailable'] = '無法進行權限檢查，除非活動已經被儲存。';
$string['couldnotdeleteratings'] = '抱歉，這個無法刪除，因為有人已經對他做過評比。';
$string['norate'] = '不允許對項目進行評比!';
$string['noratings'] = '沒有評比被提交';
$string['noviewanyrate'] = '你只能看到你自己做的項目的評比結果';
$string['noviewrate'] = '你沒有權限去檢視項目評比結果';
$string['rate'] = '評比';
$string['ratepermissiondenied'] = '你沒有權限去評比這一項目';
$string['rating'] = '評比';
$string['ratinginvalid'] = '評比無效';
$string['ratings'] = '評比';
$string['ratingtime'] = '限制在期限內評比：';
$string['rolewarning'] = '有權限去評比的角色';
$string['rolewarning_help'] = '若要提交評比，用戶需要有moodle/rating:rate 權限，以及任何模組所指定的權限。
被指派下列角色的用戶應該能夠評比項目。
這一角色清單可以透過在"管理區塊"裡的"授權鏈結"來修改。';
$string['scaleselectionrequired'] = '當你選擇一個評比的彙整類型時，你必須也選擇一個量尺或設定一個最大點數。';
