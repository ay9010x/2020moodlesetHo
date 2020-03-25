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
 * Strings for component 'filter_mathjaxloader', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   filter_mathjaxloader
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['additionaldelimiters'] = '額外的公式分隔符號';
$string['additionaldelimiters_help'] = 'MathJax過濾器會解析包含在分隔符號裡的公式中的文字。

已認可的分隔符號列表可以添加到這裡（例如 AsciiMath 使用`）。

分隔符號可以包含多個字元，且多個分隔符號之間可以用逗號隔開。';
$string['filtername'] = 'MathJax';
$string['httpsurl'] = 'HTTPS MathJax 網址';
$string['httpsurl_help'] = '當頁面是經由https(安全)上載時，用來存取MathJax程式庫的完整網址。';
$string['httpurl'] = 'HTTP MathJax 網址';
$string['httpurl_help'] = '當頁面是經由http上載時，用來存取MathJax程式庫的完整網址。';
$string['localinstall'] = '本地 MathJax 安裝';
$string['localinstall_help'] = '預設的 MathJax 配置是使用MathJAX的CDN版本，但是若需要的話，MathJAX也可以安裝在本地。

安裝在本地的原因是它可以節省頻寬，或是因為本地代理伺服器的限制。

要進行MathJAX的本地安裝，首先要從http://www.mathjax.org/ 下載完整的MathJAX程式庫。然後將它安裝在網頁伺服器上。最後更新MathJAX過濾器設定，將httpurl 和/或 httpsur指向本地的MathJax.js 網址。';
$string['mathjaxsettings'] = 'MathJax 配置';
$string['mathjaxsettings_desc'] = '這預設的MathJax 配置應該適用於大多數的用戶，但是MathJax 的配置是有高度彈性的，且任何標準MathJax 配置選項都可以在此添加。';
$string['texfiltercompatibility'] = 'TeX 過濾器相容性';
$string['texfiltercompatibility_help'] = '這 MathJax 過濾器可以用來替代TeX表示法過濾器。

為了要支援所有的TeX表示法過濾器所支援的分隔符號，MathJax 應該配置成能夠在字裡行間(inline)顯示出所有公式。';
