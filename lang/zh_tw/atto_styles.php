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
 * Strings for component 'atto_styles', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   atto_styles
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['config'] = '樣式設置';
$string['config_desc'] = '以JSON格式的Atto的外掛樣式的配置
<hr />
例如：<br />
{<br />
    "標題": "藍格",<br />
    "類型": "分段",<br />
    "分類": "藍格"<br />
},{<br />
    "標題": "紅字",<br />
    "類型": "一致",<br />
    "分類": "紅"<br />
}<br />
<hr />
<em>標題</em> 屬性 指定了Atto外掛樣式的名稱<br />
<br />

標題亦支援了Moodle多種語言過濾器(如有), 但額外的雙引號需要用反斜杠。<br />

請查看外掛README檔案作例子。
<br />
<hr />
<em>類型</em> 屬性有兩種數值：
 "分段" or "一致".<br />
<br />

"分段" 會生成有已提供的分類的 div 標籤並會成為標準的分段元素。這會取代現有的分段元素及可能在已選文字外使用。<br />

<br />
"一致" 會生成有已提供的分類的跨度標籤並會成為標準的一致元素。只會在已選文字中使用
<hr />

<em>分類</em>屬性使用 CSS分類名稱 ，並會在一致／分段文字中應用。<br />
<br />
多個分類可以界定每個項目，以空格作分隔。 會在一致／分段文字中應用。<br />
<br />
不能在此外掛中生成CSS分類定義。您需要增加您的CSS分類定義到您的主題或Moodle更多HTML設定。

<hr />
在引導為主的主題的Moodle安裝中（特別是<em>More</em> and <em>Clean</em>），您可以增加引導CSS分類的樣式。
<br /><br />

例如：<br />

{<br />
    "標題": "Hero unit box",<br />
    "類型": "block",<br />
    "分類": "hero-unit"<br />
},{<br />
    "標題": "Well",<br />
    "類型": "block",<br />
    "分類": "well"<br />
},{<br />
    "標題": "Info text",<br />
    "類型": "inline",<br />
    "分類": "label label-info"<br />
},{<br />
    "標題": "Warning text",<br />
    "類型": "inline",<br />
    "分類": "label label-warning"<br />
}<br /><br />

有關更多引導分類的資料，請前往下列有關引導2.3文檔的網址：
<ul>
<li><a href="http://getbootstrap.com/2.3.2/components.html#labels-badges">Bootstrap 標籤和徽章</a></li>
<li><a href="http://getbootstrap.com/2.3.2/components.html#alerts">Bootstrap 提示</a></li>
<li><a href="http://getbootstrap.com/2.3.2/components.html#misc">Bootstrap 支援課堂 </a></li>
</ul>';
$string['nostyle'] = '沒有樣式';
$string['pluginname'] = '樣式';
$string['settings'] = '樣式設定';
