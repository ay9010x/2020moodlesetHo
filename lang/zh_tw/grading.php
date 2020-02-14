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
 * Strings for component 'grading', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   grading
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['activemethodinfo'] = '"{$a->method}"被選為啟用的評分方式，用於"{$a->area}"領域。';
$string['activemethodinfonone'] = '沒有為"{$a->area}"領域選用進階評分方式，將使用簡單直接評分方式。';
$string['changeactivemethod'] = '改變啟用的評分方式為';
$string['clicktoclose'] = '點選可關閉';
$string['exc_gradingformelement'] = '無法具体舉例評分表單項目';
$string['formnotavailable'] = '你選用了進階評分法，但是評分表單是還無法使用。你可能需要先透過在管理區塊上的鏈結來定義它。';
$string['gradingformunavailable'] = '請注意，進階評分表單在此刻仍無法使用。簡單評分方法將繼續使用，直到這表單有一有效的狀態';
$string['gradingmanagement'] = '進階評分';
$string['gradingmanagementtitle'] = '進階評分： {$a->component} ({$a->area})';
$string['gradingmethod'] = '評分方式';
$string['gradingmethod_help'] = '選擇一進階評分法，它常用於在一給予的情境中計算分數。

若要關閉進階評分，並切換回去到預設的評分機轉，請選擇"簡單直接評分"';
$string['gradingmethodnone'] = '簡單直接評分';
$string['gradingmethods'] = '評分方式';
$string['manageactionclone'] = '從一樣版建立一個新評分表單';
$string['manageactiondelete'] = '刪除當前定義的表單';
$string['manageactiondeleteconfirm'] = '你即將從"{$a->component} ({$a->area})"刪除評分表單"{$a->formname}"和所有相關的訊息。<br />
請確定你了解最後的結果：<br />
*沒有辦法取消這一操作。<br />
*你可以切換到另一種評分方式，它包含"簡單直接評分"，而不需要刪除這一表格。<br />
*所有的有關這評分表要如何填寫的資訊將會消失。 <br />
*儲存在成績簿上的計算結果分數將不會受影響。然而解釋他們是如何被計算將無法使用。<br />
*這表單如果複製到其他活動上，這個操作不會影響它。';
$string['manageactiondeletedone'] = '這表單已經順利刪除';
$string['manageactionedit'] = '編輯當前的表單定義';
$string['manageactionnew'] = '從草稿定義一新的表單';
$string['manageactionshare'] = '把這表單出版成為樣版';
$string['manageactionshareconfirm'] = '你即將把這評分表的副本儲存成衣新的功用樣版。

在你的網站的其他用戶，將可以利用這一樣版在他們的活動中建立新評分表單。';
$string['manageactionsharedone'] = '這表單已經成功儲存成為樣版';
$string['noitemid'] = '不可能計分，計分項目不存在';
$string['nosharedformfound'] = '沒有找到樣版';
$string['searchownforms'] = '包含我自己的表單';
$string['searchtemplate'] = '搜尋評分表單';
$string['searchtemplate_help'] = '在此你可以搜尋一評分表單，並使用它做為新評分表單的樣板。你只要輸入會出現在表單名稱、它的說明、或表單本身任何位置的字詞，即可進行搜尋。若要搜尋一個片語，請在該片語的前後加上雙引號。

預設上，評分表單只有被儲存成"共享的樣板"之後，才會出現在搜尋結果中。你也可以在搜尋結果包含所有你自己的評分表單。
這樣，你可以重複使用你的評分表單，而不必分享它們。只有被標示為"已經可以使用"的表單可以這樣重複使用。';
$string['statusdraft'] = '草稿';
$string['statusready'] = '已經可以使用';
$string['templatedelete'] = '刪除';
$string['templatedeleteconfirm'] = '你即將要刪除這共享的樣板 "{$a}"。刪除一個樣板並不會影響由此樣板建立的現存表單。';
$string['templateedit'] = '編輯';
$string['templatepick'] = '使用這一樣版';
$string['templatepickconfirm'] = '你是否想要將評分表單"{$a->formname}"，當作用於"{$a->component} ({$a->area})"的新計分表單的樣版？';
$string['templatepickownform'] = '將這一表單當作樣版使用';
$string['templatesource'] = '位置: {$a->component} ({$a->area})';
$string['templatetypeown'] = '自己的表單';
$string['templatetypeshared'] = '共用的樣版';
