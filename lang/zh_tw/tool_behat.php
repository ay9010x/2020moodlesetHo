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
 * Strings for component 'tool_behat', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   tool_behat
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aim'] = '這一管理工具可幫助程式發展者和測試撰寫者來建立  .feature檔案，它描述Moodle的功能並自動執行它們。在.feature檔案裡可使用的步驟的定義列在下面。';
$string['allavailablesteps'] = '所有可用的步驟定義';
$string['errorbehatcommand'] = '執行behatCLI指令發生錯誤。試著從CLI以手動執行"{$a}--幫助"以找出這問題的原因。';
$string['errorcomposer'] = '作曲家依賴性還沒安裝。';
$string['errordataroot'] = '$CFG->behat_dataroot沒有設定或是無效。';
$string['errorsetconfig'] = '$CFG->behat_dataroot，$CFG->behat_prefix 和 $CFG->behat_wwwroot 需要在 config.php中設定。';
$string['erroruniqueconfig'] = '$CFG->behat_dataroot，$CFG->behat_prefix 和 $CFG->behat_wwwroot 的值需要和
$CFG->dataroot，$CFG->prefix，$CFG->wwwroot， $CFG->phpunit_dataroot 和 $CFG->phpunit_prefix 的值不同。';
$string['fieldvalueargument'] = '欄位值參數';
$string['fieldvalueargument_help'] = '這一參數必須以一個欄位值來完成。這兒有許多欄位類型，簡單的有checkboxes，selects 或 textareas 或者複雜的有 date selectors。
你可以點選 <a href="http://docs.moodle.org/dev/Acceptance_testing#Providing_values_to_steps" target="_blank">欄位值</a>來看依據你提供的欄位類型應該有的欄位值。';
$string['giveninfo'] = '給出，處裡以便設定這環境';
$string['infoheading'] = '訊息';
$string['installinfo'] = '閱讀{$a}可以找到安裝和測試執行的訊息';
$string['newstepsinfo'] = '閱讀{$a}可以找到關於添加新的步驟定義的訊息';
$string['newtestsinfo'] = '閱讀{$a}可以找到關於如何寫新測驗的訊息';
$string['nostepsdefinitions'] = '這些不是符合這一過濾器的步驟定義';
$string['pluginname'] = '驗收測試';
$string['stepsdefinitionscomponent'] = '區域';
$string['stepsdefinitionscontains'] = '包含';
$string['stepsdefinitionsfilters'] = '步驟定義';
$string['stepsdefinitionstype'] = '類型';
$string['theninfo'] = '然後，檢查以確定其結果符合期待';
$string['unknownexceptioninfo'] = '網頁測試工具Selenium 或瀏覽器有問題，試著將網頁測試工具升級到最新版本。錯誤：';
$string['viewsteps'] = '過濾器';
$string['wheninfo'] = '何時，引發事件的動作';
$string['wrongbehatsetup'] = '這 behat 的設定有些錯誤 ，因此步驟的定義無法被列出： <b>{$a->errormsg}</b><br/><br/>
請檢查下列：<ul>
<li>$CFG->behat_dataroot, $CFG->behat_prefix 和 $CFG->behat_wwwroot 已經在 config.php 中設定，且其數值與 $CFG->dataroot, $CFG->prefix 和 $CFG->wwwroot不同。</li>
<li>你從你的Moodle跟目錄執行 "{$a->behatinit}" 。</li>
<li>相關的程式是安裝在 vendor/ ，且你有 {$a->behatcommand} 檔案的執行權限。</li></ul>';
