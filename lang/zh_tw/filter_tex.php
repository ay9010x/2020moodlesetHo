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
 * Strings for component 'filter_tex', language 'zh_tw', branch 'MOODLE_31_STABLE'
 *
 * @package   filter_tex
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['configconvertformat'] = '如果<i>latex</i>和<i>dvips</i>有伴隨著 <i>convert</i>或  <i>dvisvgm</i>一起出現，請選擇你偏好的圖像格式(<i>convert</i> 會產出 GIF或 PNG圖檔格式； <i>dvisvgm</i> 會產出SVG圖檔格式)。否則，將會使用<i>mimeTeX</i>來建立GIF格式圖像。';
$string['convertformat'] = '輸出圖像的格式';
$string['filtername'] = 'TeX標記法';
$string['latexpreamble'] = 'LaTeX 前文';
$string['latexsettings'] = 'LaTeX 設定';
$string['pathconvert'] = '<i>convert</i> 指令路徑';
$string['pathdvips'] = '<i>dvips</i> 指令路徑';
$string['pathdvisvgm'] = '<i>dvisvgm</i>二進位檔案的路徑';
$string['pathlatex'] = '<i>latex</i> 指令路徑';
$string['pathmimetex'] = '<i>mimetex</i>二進位檔案的路徑';
$string['pathmimetexdesc'] = 'Moodle將使用其自有之二元mimetex，除非有另外指定有效的途徑。';
$string['source'] = 'TeX來源';
