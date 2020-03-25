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
 * Strings for component 'enrol_mnet', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_mnet
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['error_multiplehost'] = '這個主機上已經存在某個MNet選課外掛的實體。 每個主機只能允許有一個實體並且/或「所有主機」共有一個實體也是允許的。';
$string['instancename'] = '選課方法名稱';
$string['instancename_help'] = '您可以選擇重新命名本MNet選課方法的實體。如果您保持本欄位為空，則會使用預設的實體名稱。其中包含遠端主機的名字和指派給它們的使用者角色名稱。';
$string['mnet:config'] = '配置MNet選課實例';
$string['mnet_enrol_description'] = '發佈該服務將允許在 {$a} 上的管理員可以選擇自己伺服器上已建立課程中學生。<br/><ul><li><em>相依性</em>：您必須向 {$a} <strong>發佈</strong>SSO (Service Provicder) 服務。</li><li><em>相依性</em>：您也必須<strong>訂閱</strong> {$a} 的 SSO (Identity Provider) 服務。</li></ul><br/>訂閱該服務將可以將 {$a} 課程中的學生註冊進來。<br/><ul><li><em>相依性</em>：您必須<strong>訂閱</strong> {$a} 的 SSO (Service Provider) 服務。</li><li><em>依賴性</em>：您也必須向 {$a} <strong>發佈</strong> SSO (Identity Provider) 服務。</li></ul><br/>';
$string['mnet_enrol_name'] = '遠端選課服務';
$string['pluginname'] = 'MNet遠端選課';
$string['pluginname_desc'] = '允許遠端MNet主機把其他用戶加入我們的課程中。';
$string['remotesubscriber'] = '遠端主機';
$string['remotesubscriber_help'] = '選擇「所有主機」，會將此課程開放給所有的我們提供MNet遠端選課的主機。或者選擇只開放給一個主機。';
$string['remotesubscribersall'] = '所有主機';
$string['roleforremoteusers'] = '給他們用戶的角色';
$string['roleforremoteusers_help'] = '為來自選定主機的遠端用戶分配的角色。';
