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
 * Strings for component 'portfolio_boxnet', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   portfolio_boxnet
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clientid'] = '客戶編號';
$string['clientsecret'] = '客戶密鑰';
$string['existingfolder'] = '可以放檔案進去的現有資料夾';
$string['folderclash'] = '你要求建立的資料夾已經存在';
$string['foldercreatefailed'] = '無法在 Box.net 建立你的目標資料夾';
$string['folderlistfailed'] = '無法從 Box.net 擷取資料夾清單';
$string['missinghttps'] = '需要使用HTTPS';
$string['missinghttps_help'] = 'Box.net 只有在啟用HTTPS的網站上才能使用';
$string['missingoauthkeys'] = '紹了客戶編號和密鑰';
$string['missingoauthkeys_help'] = '這一外掛沒有客戶編號和密鑰的配置。您可以從box.net發展頁面取得一個。';
$string['newfolder'] = '可放檔案進去的新資料夾';
$string['noauthtoken'] = '無法擷取一個認證憑證來在這次交流中使用';
$string['notarget'] = '你必須指定一個現有的或新的資料夾，以供檔案上傳';
$string['noticket'] = '無法從 Box.net 擷取一票證來開始認證程序';
$string['password'] = '你的 Box.net 密碼(將不會被儲存)';
$string['pluginname'] = 'Box.net';
$string['sendfailed'] = '無法傳送內容到box.net：{$a}';
$string['setupinfo'] = '設定說明';
$string['setupinfodetails'] = '要獲得客戶編號和密鑰。請登入box.net，並訪問 <a href="{$a->servicesurl}">Box developers page</a>。
在"發展工具"下選"建立新應用程式"，然後為你的Moodle網站建立一新應用。客戶編號和密鑰是顯示在這應用程式編輯表單的"\'OAuth2 parameters"的那部分。
除此之外，你也可以提供你的Moodle網站的其他訊息。這些數值可以稍後在"檢視我的應用"頁面上加以編輯。';
$string['sharedfolder'] = '共用的';
$string['sharefile'] = '共用這檔案？';
$string['sharefolder'] = '共用這新資料夾？';
$string['targetfolder'] = '目標資料夾';
$string['tobecreated'] = '要被建立';
$string['username'] = '你的box.net用戶名稱(不會被儲存)';
$string['warninghttps'] = '您的網站要使用HTTPS，才能將box.net當作學習歷程檔案的資料夾';
