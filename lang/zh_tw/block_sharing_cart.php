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
 * Strings for component 'block_sharing_cart', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   block_sharing_cart
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backup'] = '複製到共享推車';
$string['bulkdelete'] = '大批刪除';
$string['clipboard'] = '複製這一共享項目';
$string['confirm_backup'] = '你是否要複製這一活動到共享推車？';
$string['confirm_delete'] = '你確定要刪除？';
$string['confirm_delete_selected'] = '你確定要刪除所有這些被選出的項目？';
$string['confirm_restore'] = '你是否要複製這一項目到課程？';
$string['confirm_userdata'] = '你是否要在這一活動的副本中包含用戶資料？';
$string['copyhere'] = '複製這裡';
$string['forbidden'] = '你沒有權限去存取這一共享項目';
$string['invalidoperation'] = '已刪除一個無效的操作';
$string['movedir'] = '移進資料夾';
$string['notarget'] = '找不到目標';
$string['pluginname'] = '共享推車';
$string['recordnotfound'] = '找不到共享項目';
$string['requireajax'] = '共享推車需要用到AJAX';
$string['requirejs'] = '使用共享推車需要在你的瀏覽器上啟用 JavaScript';
$string['restore'] = '複製到課程';
$string['settings:userdata_copyable_modtypes'] = '哪些類型的模組可以複製用戶資料';
$string['settings:userdata_copyable_modtypes_desc'] = '當複製活動到共享推車時，您可以在視窗中選擇是否複製用戶的資料，如果選擇複製用戶的資料的話操作者會有<strong>moodle/backup:userinfo</strong>,
<strong>moodle/backup:anonymise</strong> 及 <strong>moodle/restore:userinfo</strong> 權限
(在默認設置中，只有管理只有以上權限)';
$string['settings:workaround_qtypes'] = '題型的解決方法';
$string['settings:workaround_qtypes_desc'] = '當點選題型後，將會修復題型的解決方法問題。如果要修復的問題已經存在，但看來與資料不一致，此解決方法將不會重用電有資料，而是複製一個新的題型。此做法可以避免一此修復錯誤，例如：<i>error_question_match_sub_missing_in_db</i>.';
$string['sharing_cart'] = '共享推車';
$string['sharing_cart:addinstance'] = '添加一個共享推車區塊';
$string['sharing_cart_help'] = '<h2 class="helpheading">操作</h2>
<dl style="margin-left:0.5em;">
<dt>從課程複製至共享推車</dt>
    <dd>在每個資源／活動中您將看見一個小的「複製至共享推車」的接鍵
        點擊按鍵以傳送資源／活動的副本到共享推車
       用戶資料將不會被複製</dd>

<dt>從共享推車課程複製課程</dt>
    <dd>在共享推車中點擊「複製課程」按鍵並選擇每個部份的目標標記</dd>
<dt>在共享推車中建立文件夾</dt>
    <dd>在共享推車的項目中點擊「移動至課程」的按鍵。如果沒有文件夾，輸入框將會出現以輸入新文件夾的的名稱。當您點擊「修改」後，您亦可以從下滑式菜單中選擇已有文件夾。
    </dd>
</dl>';
$string['unexpectederror'] = '發生意外的錯誤';
