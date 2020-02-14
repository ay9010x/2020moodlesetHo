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
 * Strings for component 'portfolio_flickr', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   portfolio_flickr
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['apikey'] = 'API密鑰';
$string['contenttype'] = '內容類型';
$string['err_noapikey'] = '無API密鑰';
$string['err_noapikey_help'] = '這一外掛沒有配置API密鑰。你可以從Flickr服務頁面取的一個。';
$string['hidefrompublicsearches'] = '隱藏這些圖像，不願被公開搜尋？';
$string['isfamily'] = '家人可看到';
$string['isfriend'] = '朋友可看到';
$string['ispublic'] = '公開(任何人都可以看到)';
$string['moderate'] = '中等';
$string['noauthtoken'] = '無法取回一個認證過的通行憑證，以供這一工作階段使用';
$string['other'] = '美術作品、電腦繪圖、CGI、或其他非攝影的圖像';
$string['photo'] = '相片';
$string['pluginname'] = 'Flickr.com';
$string['restricted'] = '限制的';
$string['safe'] = '安全';
$string['safetylevel'] = '安全層級';
$string['screenshot'] = '螢幕抓圖';
$string['set'] = '設定';
$string['setupinfo'] = '安裝指示';
$string['setupinfodetails'] = '若要獲得API密鑰和秘密字串，請登入Flickr 並<a href="{$a->applyurl}">申請一個新密鑰</a>。一但為你產生的新密要和秘密字串，循著這一頁面的"為這應用程式編輯認證程序"鏈結。
選擇"應用程式類型"為"網頁應用"。
進入"召回網址"欄位，放入這值<br /><code>{$a->callbackurl}</code><br />，你也可以提供你的Moodle網站的說明和商標。
這些也可以稍後再設定，在<a href="{$a->keysurl}">這頁面</a>會列出你的Flickr應用軟體。';
$string['sharedsecret'] = '秘密字串';
$string['title'] = '標題';
$string['uploadfailed'] = '無法上傳圖像檔到flick.com:{$a}';
