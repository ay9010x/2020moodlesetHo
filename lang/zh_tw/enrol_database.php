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
 * Strings for component 'enrol_database', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   enrol_database
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['database:config'] = '配置資料庫選課實例';
$string['database:unenrol'] = '將被停權的用戶退選';
$string['dbencoding'] = '資料庫編碼';
$string['dbhost'] = '資料庫主機';
$string['dbhost_desc'] = '請輸入資料庫伺服器的IP位址或網域名稱，若使用ODBC，請使用一個系統的DSN名稱';
$string['dbname'] = '資料庫名稱';
$string['dbname_desc'] = '如果在資料庫主機使用DSN名稱，此處予以空白';
$string['dbpass'] = '資料庫密碼';
$string['dbsetupsql'] = '資料庫設定指令';
$string['dbsetupsql_desc'] = '設定某些資料庫的SQL指令。通常用來設定通信編碼-例如在MySQL和PostgreSQL中：<em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = '使用sybase引號';
$string['dbsybasequoting_desc'] = 'Sybase風格的單引號逸出字元-Oracle、MS SQL和其他某些資料庫需要。不要在MySQL上使用！';
$string['dbtype'] = '資料庫伺服器類型';
$string['dbtype_desc'] = 'ADOdb資料庫驅動名稱，也就是外部資料庫引擎的類型。';
$string['dbuser'] = '資料庫用戶';
$string['debugdb'] = '除錯ADOdb';
$string['debugdb_desc'] = '除錯ADOdb與外部資料庫的連結-像是在登入時顯示空白頁面，就使用使項目。不適用於正式使用的網站！';
$string['defaultcategory'] = '預設的新課程類別';
$string['defaultcategory_desc'] = '自動建立的課程預設類別。當尚未指定類別id或類別id不存在時使用。';
$string['defaultrole'] = '預設角色';
$string['defaultrole_desc'] = '如果外部資料表中尚未指定其他角色，就會預設為指派此角色。';
$string['ignorehiddencourses'] = '忽略隱藏的課程';
$string['ignorehiddencourses_desc'] = '若啟用，用戶將無法選修那些被設定為學生不能用的課程。';
$string['localcategoryfield'] = '本地分類欄位';
$string['localcoursefield'] = '本地資料表的課程欄位';
$string['localrolefield'] = '本地資料表的角色欄位';
$string['localuserfield'] = '本地資料表的用戶欄位';
$string['newcoursecategory'] = '新課程分類欄位';
$string['newcoursefullname'] = '新課程的全名欄位';
$string['newcourseidnumber'] = '新課程編號欄位';
$string['newcourseshortname'] = '新課程簡稱欄位';
$string['newcoursetable'] = '遠端新課程資料表';
$string['newcoursetable_desc'] = '指定一個資料表名稱，它應該包含所有要自動新建的課程。留空表示不建立任何課程。';
$string['pluginname'] = '外部資料庫選課';
$string['pluginname_desc'] = '您可以使用幾乎所有類型的外部資料庫控制您的選課。您的外部資料庫至少要有一個課程編號欄位和一個用戶編號欄位。它們會和本地課程表和用戶列表中您選擇的欄位匹配。';
$string['remotecoursefield'] = '遠端資料表的課程欄位';
$string['remotecoursefield_desc'] = '用來和課程資料表匹配的遠端表中的欄位名稱。';
$string['remoteenroltable'] = '遠端用戶選課資料表';
$string['remoteenroltable_desc'] = '指定包含用戶選課資訊的表格名稱。留空白表示同步任何用戶的選課。';
$string['remoteotheruserfield'] = '遠端資料表的其他用戶欄位';
$string['remoteotheruserfield_desc'] = '這一欄位在遠端資料表上所用的名稱，是我們用來標示"其他用戶"的腳色指派。';
$string['remoterolefield'] = '遠端資料表的角色欄位';
$string['remoterolefield_desc'] = '用來和角色資料表匹配的遠端資料表的欄位名稱';
$string['remoteuserfield'] = '遠端資料表的用戶欄位';
$string['remoteuserfield_desc'] = '用來和用戶資料表匹配的遠端資料表中的欄位名稱';
$string['settingsheaderdb'] = '外部資料庫連線';
$string['settingsheaderlocal'] = '本地欄位對應';
$string['settingsheadernewcourses'] = '建立新課程';
$string['settingsheaderremote'] = '同步遠端選課';
$string['templatecourse'] = '新課程模版';
$string['templatecourse_desc'] = '可選：自動記練的課程可以從模版課程拷貝設定。在此輸入模版課程的簡稱。';
