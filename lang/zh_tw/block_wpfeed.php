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
 * Strings for component 'block_wpfeed', language 'zh_tw', branch 'MOODLE_30_STABLE'
 *
 * @package   block_wpfeed
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['block_wpfeed_api_url_title'] = '要求WordPress的REST API網址';
$string['block_wpfeed_api_version'] = '您的WordPress 網站 （API版本）';
$string['block_wpfeed_api_version_desc'] = 'WP API <strong>版本 1</strong> 外掛程式知識庫網址: <a href="https://wordpress.org/plugins/json-rest-api/" target="_blank">https://wordpress.org/plugins/json-rest-api/</a> (<em>for WP versions 3.9–4.4.2</em>)<br />WP API <strong>版本 2</strong> 外掛程式知識庫網址: <a href="https://wordpress.org/plugins/rest-api/" target="_blank">https://wordpress.org/plugins/rest-api/</a> (<em>for WP versions 4.4+</em>)';
$string['block_wpfeed_clear_cache'] = '如果您已更新區塊設定，不要忘記';
$string['block_wpfeed_debug_title'] = '調試資料';
$string['block_wpfeed_default_title'] = 'WordPress 摘要';
$string['block_wpfeed_empty_response'] = '清空回覆資料';
$string['block_wpfeed_error_string'] = '錯誤描述';
$string['block_wpfeed_no_posts'] = '在API回應中沒有帖文';
$string['block_wpfeed_request_title'] = '要求資料';
$string['block_wpfeed_response_title'] = '回應資料';
$string['block_wpfeed_settings_cache_interval'] = '緩存間隔';
$string['block_wpfeed_settings_cache_interval_desc'] = 'WP API 以分鐘緩存回應. 最低值為 <code>{$a->min_cache_time}</code>, 0 = 沒有緩存';
$string['block_wpfeed_settings_categories'] = 'WordPress帖文分類id-s';
$string['block_wpfeed_settings_categories_desc'] = '多個分類必須以","分隔。<code>0 = 所有分類</code>';
$string['block_wpfeed_settings_dev_mode'] = '開發人員模式';
$string['block_wpfeed_settings_dev_mode_desc'] = '開發人員的進階資料，只能在<code>開發人員模式</code>中使用';
$string['block_wpfeed_settings_excerpt_length'] = '內容的摘錄長度';
$string['block_wpfeed_settings_excerpt_length_desc'] = '需要縮減的內容長度';
$string['block_wpfeed_settings_new_window'] = '在新視窗中開啟鏈接／帖文';
$string['block_wpfeed_settings_noindex'] = '允許noindex';
$string['block_wpfeed_settings_noindex_desc'] = '新增 <code><noindex></code> 標籤以令區塊在搜尋器，如Google中隱藏。有時候對SEO亦有幫助';
$string['block_wpfeed_settings_post_date'] = '張貼日期格式';
$string['block_wpfeed_settings_post_date_desc'] = '生產帖文的PHP 功能 <code>日期</code> 格式';
$string['block_wpfeed_settings_posts_limit'] = '帖文限制';
$string['block_wpfeed_settings_posts_limit_desc'] = '生產帖文數目';
$string['block_wpfeed_settings_post_type'] = 'WordPress 帖文類型';
$string['block_wpfeed_settings_post_type_desc'] = '您的WordPress 網站的指針帖文。預設是<code>{$a->default_post_type}</code>';
$string['block_wpfeed_settings_session_store'] = '在用戶時段中儲存';
$string['block_wpfeed_settings_session_store_desc'] = '您可以使用此外殻來儲放用戶已儲存的API回覆。
當用戶每次前往您的Moodle網站－回應將會儲存至<code>$SESSION</code>';
$string['block_wpfeed_settings_skin'] = '生產界面';
$string['block_wpfeed_settings_thumbnail_link_desc'] = '將縮圖設置成在帖文上的鏈接';
$string['block_wpfeed_settings_thumbnail_show'] = '顯示帖文縮圖';
$string['block_wpfeed_settings_thumbnail_size'] = '縮圖大小';
$string['block_wpfeed_settings_thumbnail_size_desc'] = '基於WordPress的縮圖尺寸處理器. 當已開啟顯示帖文縮圖後可以使用<br />默認基於WordPress的尺寸:<br /><code>縮圖</code><br /><code>中</code><br /><code>大</code><br /><code>小</code>';
$string['block_wpfeed_settings_thumbnail_width'] = '縮圖闊度';
$string['block_wpfeed_settings_thumbnail_width_desc'] = '由管理者指定的縮圖的HTML 闊度(px)屬性。<code>0 = 自動</code>';
$string['block_wpfeed_settings_title'] = '區塊標題';
$string['block_wpfeed_settings_url_title'] = '前往WPFeed區塊的設定';
$string['block_wpfeed_settings_wp_api_prefix'] = 'WordPress REST API 網址字首';
$string['block_wpfeed_settings_wp_api_prefix_desc'] = '網站網址的要求後字首<strong>沒有斜線</strong>.<br /> API v1的默認設定: <code>{$a->default_api_prefix_v1}</code><br />API v2的默認設定: <code>{$a->default_api_prefix_v2}</code><br />要求 API URL 的結果會像<code>http://yoursite.com/{$a->default_api_prefix_v1}</code> 或 <code>http://yoursite.com/{$a->default_api_prefix_v2}</code>';
$string['block_wpfeed_settings_wp_url'] = 'WordPress 網站網址';
$string['block_wpfeed_settings_wp_url_desc'] = '您的WordPress 網站網址<strong>沒有斜線</strong>. 例子： <em>http://mysite.com</em>';
$string['block_wpfeed_update_cache'] = 'WPFeed緩存更新';
$string['pluginname'] = 'WordPress摘要';
$string['wpfeed:addinstance'] = '新增WPFeed區塊';
$string['wpfeed:myaddinstance'] = '新增簡單HTML區塊至我的Moodle頁面';
