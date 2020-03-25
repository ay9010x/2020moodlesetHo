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
 * Strings for component 'repository_googledocs', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   repository_googledocs
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clientid'] = '客戶編號';
$string['configplugin'] = '設定Google 雲端硬碟外掛套件';
$string['googledocs:view'] = '檢視Google 雲端硬碟倉儲';
$string['oauthinfo'] = '<p>要使用這外掛，您需要在Google註冊你的網站，依照這文件 <a href="{$a->docsurl}">Google OAuth 2.0 設定</a>的描述。</p>
<p>在註冊的過程中，您將需要輸入以下的網址，作為"認證後重新導向的網址"：</p>
<p>{$a->callbackurl}</p>
<p>一旦註冊完，您將會得到用戶端 ID和用戶端密碼，這兩者可用來設定所有的Google雲端硬碟和 Picasa 相本的外掛。</p>';
$string['pluginname'] = 'Google 雲端硬碟';
$string['secret'] = '密碼';
$string['servicenotenabled'] = '存取方式沒有設定。請確定"驅動API"已經被啟用。';
