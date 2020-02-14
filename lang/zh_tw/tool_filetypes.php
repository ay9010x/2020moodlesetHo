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
 * Strings for component 'tool_filetypes', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_filetypes
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['addfiletypes'] = '新增一個檔案類型';
$string['corestring'] = '另一種語言的字符串';
$string['corestring_help'] = '這一設定可以被用來從核心mimetypes.php語言檔選擇一個不同的語言字串。一般狀況下它應該留空白。要自訂類型請使用描述欄位。';
$string['defaulticon'] = 'MIME類型的預設圖示';
$string['defaulticon_help'] = '若有多個檔案副檔名有相同的MIME類型，為其中一個附檔名選擇這一選項，這樣它的圖示將會被用來作為代表這MIME類型的圖示。';
$string['deletea'] = '刪除{$a}';
$string['delete_confirmation'] = '你確定你要移除<strong>.{$a}</strong>？';
$string['deletefiletypes'] = '刪除一檔案類型';
$string['description'] = '自訂的描述';
$string['description_help'] = '簡單的檔案類型描述，例如： &lsquo;Kindle ebook&rsquo;。若你的網站支援多種語言，並使用多語言過濾器，你可以在這一欄位上輸入多語言標籤來提供不同語言的描述。';
$string['descriptiontype'] = '描述類型';
$string['descriptiontype_custom'] = '在此表單中指定的自訂的描述';
$string['descriptiontype_default'] = '預設(MIME類型或對應的語言字串)';
$string['descriptiontype_help'] = '有三種方式可用來描述檔案：

*預設上是使用MIME類型。若在 mimetypes.php 中有一語言字串對應到這個MIME類型，它就會被使用。
*你可以在這一表單中指定一個自訂描述。
*你可以在 mimetypes.php 中指定一個語言字串的名稱來使用，以取代MIME類型。';
$string['descriptiontype_lang'] = '其他語言字串(來自mimetypes.php)';
$string['displaydescription'] = '描述';
$string['editfiletypes'] = '編輯現有的檔案類型';
$string['emptylist'] = '沒有檔案類型被界定';
$string['error_addentry'] = '這檔案類型的附檔名、描述、MIME類型、和圖示，絕不可以包含換行符號和分號字元。';
$string['error_defaulticon'] = '另一個檔案附檔名，它具有相同MIME類型，已經被指定使用這預設的圖示。';
$string['error_extension'] = '檔案類型的副檔名(extention)<strong>{$a}</strong>已經存在或是無效。檔案的副檔名必須是獨一的，且不可以包含特殊字元。';
$string['error_notfound'] = '無法找到有副檔名{$a}的檔案類型';
$string['extension'] = '副檔名';
$string['extension_help'] = '檔案副檔名沒有一點， 例如：&lsquo;mobi&rsquo;';
$string['groups'] = '類型群組';
$string['groups_help'] = '從檔案類型群組清單中選擇這一檔案類型所屬的群組，
這裡有一些通用的類目，比如"文件"和"圖像"。';
$string['icon'] = '檔案圖示';
$string['icon_help'] = '圖標檔案名稱。

這一圖標的清單是取自你的Moodle安裝下的 /pix/f 目錄。若需要的話，你可以自行添加圖標到這一目錄中。';
$string['mimetype'] = 'MIME類型';
$string['mimetype_help'] = '與這一檔案類型有關的MIME類型，例如 &lsquo;application/x-mobipocket-ebook&rsquo;';
$string['pluginname'] = '檔案類型';
$string['revert'] = '把{$a}還原到Moodle的預設';
$string['revert_confirmation'] = '你是否確定要把 <strong>.{$a}</strong>還原到Moodle的預設，而放棄你的更改？';
$string['revertfiletype'] = '還原一個檔案類型';
$string['source'] = '類型';
$string['source_custom'] = '自訂';
$string['source_deleted'] = '已刪除';
$string['source_modified'] = '已修改';
$string['source_standard'] = '標準';
