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
 * Strings for component 'tool_recyclebin', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_recyclebin
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alertdeleted'] = '\'{$a->name}\' 被刪除了。';
$string['alertemptied'] = '資源回收桶清空了。';
$string['alertrestored'] = '\'{$a->name}\' 被還原回去了。';
$string['autohide'] = '自動隱藏';
$string['autohide_desc'] = '如果回收桶是空的就自動隱藏資源回收桶的連結。';
$string['categorybinenable'] = '啟用類別的資源回收桶';
$string['categorybinexpiry'] = '課程生命期';
$string['categorybinexpiry_desc'] = '刪除的課程將保留在資源回收桶多久？';
$string['coursebinenable'] = '啟用資源回收桶';
$string['coursebinexpiry'] = '項目內容生命期';
$string['coursebinexpiry_desc'] = '刪除的項目內容將保留在資源回收桶多久？';
$string['datedeleted'] = '刪除的日期';
$string['deleteall'] = '刪除全部';
$string['deleteallconfirm'] = '您確定要刪除資源回收桶中全部項目內容？';
$string['deleteconfirm'] = '您確定要刪除資源回收桶中選擇的項目內容？';
$string['deleteexpirywarning'] = '項目內容將於{$a}天後永遠刪除。';
$string['eventitemcreated'] = '項目已經建立';
$string['eventitemcreated_desc'] = '項目編號{$a->objectid}已經建立。';
$string['eventitemdeleted'] = '項目已經刪除';
$string['eventitemdeleted_desc'] = '項目編號{$a->objectid}已經刪除。';
$string['eventitemrestored'] = '項目已經還原';
$string['eventitemrestored_desc'] = '項目編號{$a->objectid}已經還原。';
$string['invalidcontext'] = '提供的情境無效';
$string['noitemsinbin'] = '資源回收桶裏沒有東西了。';
$string['notenabled'] = '抱歉，資源回收桶已經被管理員關閉';
$string['pluginname'] = '資源回收桶';
$string['recyclebin:deleteitems'] = '刪除資源回收桶內容';
$string['recyclebin:restoreitems'] = '還原資源回收桶內容';
$string['recyclebin:viewitems'] = '檢視資源回收桶內容';
$string['taskcleanupcategorybin'] = '清理類別資源回收桶';
$string['taskcleanupcoursebin'] = '清理課程資源回收桶';
