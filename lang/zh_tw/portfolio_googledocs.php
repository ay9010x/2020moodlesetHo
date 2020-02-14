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
 * Strings for component 'portfolio_googledocs', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   portfolio_googledocs
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clientid'] = '客戶編號';
$string['noauthtoken'] = '還沒有收到來自 Google 的認證通行憑證。請確定你已經允許 Moodle 去存取你的Google 帳號。';
$string['nooauthcredentials'] = '需要OAuth憑據';
$string['nooauthcredentials_help'] = '要使用Google雲端硬碟歷程檔案外掛，你需要在這歷程檔案設定中設定OAuth憑證。';
$string['nosessiontoken'] = '沒有session通行憑證，無法匯出到Google。';
$string['oauthinfo'] = '<p>要使用這外掛，您需要在Google註冊你的網站，依照這文件 <a href="{$a->docsurl}">Google OAuth 2.0 設定</a>的描述。</p>
<p>在註冊的過程中，您將需要輸入以下的網址，作為"認證後重新導向的網址"：</p>
<p>{$a->callbackurl}</p>
<p>一旦註冊完，您將會得到用戶端 ID和用戶端密碼，這兩者可用來設定所有的Google雲端硬碟和 Picasa 相本的外掛。</p>';
$string['pluginname'] = 'Google雲端硬碟';
$string['secret'] = '秘密';
$string['sendfailed'] = '這檔案{$a}無法轉移到google';
