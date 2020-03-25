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
 * Strings for component 'debug', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   debug
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['authpluginnotfound'] = '找不到身分認證外掛套件{$a}。';
$string['blocknotexist'] = '{$a}區塊不存在';
$string['cannotbenull'] = '{$a}不能為空！';
$string['cannotdowngrade'] = '不能將{$a->plugin}從{$a->oldversion} 降級到{$a->newversion}。';
$string['cannotfindadmin'] = '找不到具有管理員權限的用戶！';
$string['cannotinitpage'] = '無法完全初始化頁面：無效的{$a->name} id {$a->id}';
$string['cannotsetuptable'] = '{$a}資料表無法成功建立！';
$string['codingerror'] = '偵測到原始碼錯誤，必須由程式設計師修復：{$a}';
$string['configmoodle'] = 'Moodle尚未設定好，您需要先編輯config.php檔案。';
$string['erroroccur'] = '在此過程中發生一個錯誤';
$string['invalidarraysize'] = '在{$a}的參數中陣列的大小不正確';
$string['invalideventdata'] = '提交的事件資料不正確：{$a}';
$string['invalidparameter'] = '檢測到無效的參數，操作無法繼續 - {$a}';
$string['invalidresponse'] = '檢測到無效的回應值';
$string['missingconfigversion'] = '因設置表格未包含版本編號，所以無法繼續進行，抱歉。';
$string['modulenotexist'] = '{$a}模組不存在';
$string['morethanonerecordinfetch'] = '在fetch()中找到多項紀錄！';
$string['mustbeoveride'] = '抽象方法{$a}必須被覆蓋。';
$string['noadminrole'] = '找不到管理員腳色';
$string['noblocks'] = '沒有安裝任何的區塊！';
$string['nocate'] = '無分類！';
$string['nomodules'] = '未找到模組！！';
$string['nopageclass'] = '已經匯入{$a}，但是未找到頁面類別';
$string['noreports'] = '沒有可以瀏覽的報告';
$string['notables'] = '沒有資料表！';
$string['phpvaroff'] = 'PHP設定變數量 \'{$a->name}\' 應該設定為 Off - {$a->link}';
$string['phpvaron'] = 'PHP伺服器設定 \'{$a->name}\' 未開啟 - {$a->link}';
$string['sessionmissing'] = '物件{$a}在session中遺失';
$string['sqlrelyonobsoletetable'] = 'SQL語句使用了廢棄的資料表：{$a}！必須由開發者來修改您的程式碼。';
$string['withoutversion'] = '主要version.php檔案不存在、不可讀取或是毀損';
$string['xmlizeunavailable'] = 'xmlize功能不可使用';
