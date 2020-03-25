<?php



defined('MOODLE_INTERNAL') || die();


class MoodleExcelWorkbook {
    
    protected $objPHPExcel;

    
    protected $filename;

    
    protected $type;

    
    public function __construct($filename, $type = 'Excel2007') {
        global $CFG;
        require_once("$CFG->libdir/phpexcel/PHPExcel.php");

        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->removeSheetByIndex(0);

        $this->filename = $filename;

        if (strtolower($type) === 'excel5') {
            debugging('Excel5 is no longer supported, using Excel2007 instead');
            $this->type = 'Excel2007';
        } else {
            $this->type = 'Excel2007';
        }
    }

    
    public function add_worksheet($name = '') {
        return new MoodleExcelWorksheet($name, $this->objPHPExcel);
    }

    
    public function add_format($properties = array()) {
        return new MoodleExcelFormat($properties);
    }

    
    public function close() {
        global $CFG;

        foreach ($this->objPHPExcel->getAllSheets() as $sheet){
            $sheet->setSelectedCells('A1');
        }
        $this->objPHPExcel->setActiveSheetIndex(0);

        $filename = preg_replace('/\.xlsx?$/i', '', $this->filename);

        $mimetype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $filename = $filename.'.xlsx';

        if (is_https()) {             header('Cache-Control: max-age=10');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: ');
        } else {             header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
            header('Pragma: no-cache');
        }

        if (core_useragent::is_ie()) {
            $filename = rawurlencode($filename);
        } else {
            $filename = s($filename);
        }

        header('Content-Type: '.$mimetype);
        header('Content-Disposition: attachment;filename="'.$filename.'"');

        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, $this->type);
        $objWriter->save('php://output');
    }

    
    public function send($filename) {
        $this->filename = $filename;
    }
}


class MoodleExcelWorksheet {
    
    protected $worksheet;

    
    public function __construct($name, PHPExcel $workbook) {
                $name = strtr(trim($name, "'"), '[]*/\?:', '       ');
                $name = core_text::substr($name, 0, 31);
                $name = trim($name, "'");

        if ($name === '') {
                        $name = 'Sheet'.($workbook->getSheetCount()+1);
        }

        $this->worksheet = new PHPExcel_Worksheet($workbook, $name);
        $this->worksheet->setPrintGridlines(false);

        $workbook->addSheet($this->worksheet);
    }

    
    public function write_string($row, $col, $str, $format = null) {
        $this->worksheet->getStyleByColumnAndRow($col, $row+1)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $this->worksheet->setCellValueExplicitByColumnAndRow($col, $row+1, $str, PHPExcel_Cell_DataType::TYPE_STRING);
        $this->apply_format($row, $col, $format);
    }

    
    public function write_number($row, $col, $num, $format = null) {
        $this->worksheet->getStyleByColumnAndRow($col, $row+1)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_GENERAL);
        $this->worksheet->setCellValueExplicitByColumnAndRow($col, $row+1, $num, PHPExcel_Cell_DataType::TYPE_NUMERIC);
        $this->apply_format($row, $col, $format);
    }

    
    public function write_url($row, $col, $url, $format = null) {
        $this->worksheet->setCellValueByColumnAndRow($col, $row+1, $url);
        $this->worksheet->getCellByColumnAndRow($col, $row+1)->getHyperlink()->setUrl($url);
        $this->apply_format($row, $col, $format);
    }

    
    public function write_date($row, $col, $date, $format = null) {
        $getdate = usergetdate($date);
        $exceldate = PHPExcel_Shared_Date::FormattedPHPToExcel(
            $getdate['year'],
            $getdate['mon'],
            $getdate['mday'],
            $getdate['hours'],
            $getdate['minutes'],
            $getdate['seconds']
        );

        $this->worksheet->setCellValueByColumnAndRow($col, $row+1, $exceldate);
        $this->worksheet->getStyleByColumnAndRow($col, $row+1)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22);
        $this->apply_format($row, $col, $format);
    }

    
    public function write_formula($row, $col, $formula, $format = null) {
        $this->worksheet->setCellValueExplicitByColumnAndRow($col, $row+1, $formula, PHPExcel_Cell_DataType::TYPE_FORMULA);
        $this->apply_format($row, $col, $format);
    }

    
    public function write_blank($row, $col, $format = null) {
        $this->worksheet->setCellValueByColumnAndRow($col, $row+1, '');
        $this->apply_format($row, $col, $format);
    }

    
    public function write($row, $col, $token, $format = null) {
                if (preg_match("/^([+-]?)(?=\d|\.\d)\d*(\.\d*)?([Ee]([+-]?\d+))?$/", $token)) {
                        return $this->write_number($row, $col, $token, $format);
        } elseif (preg_match("/^[fh]tt?p:\/\//", $token)) {
                        return $this->write_url($row, $col, $token, '', $format);
        } elseif (preg_match("/^mailto:/", $token)) {
                        return $this->write_url($row, $col, $token, '', $format);
        } elseif (preg_match("/^(?:in|ex)ternal:/", $token)) {
                        return $this->write_url($row, $col, $token, '', $format);
        } elseif (preg_match("/^=/", $token)) {
                        return $this->write_formula($row, $col, $token, $format);
        } elseif (preg_match("/^@/", $token)) {
                        return $this->write_formula($row, $col, $token, $format);
        } elseif ($token == '') {
                        return $this->write_blank($row, $col, $format);
        } else {
                        return $this->write_string($row, $col, $token, $format);
        }
    }

    
    public function set_row($row, $height, $format = null, $hidden = false, $level = 0) {
        if ($level < 0) {
            $level = 0;
        } else if ($level > 7) {
            $level = 7;
        }
        if (isset($height)) {
            $this->worksheet->getRowDimension($row+1)->setRowHeight($height);
        }
        $this->worksheet->getRowDimension($row+1)->setVisible(!$hidden);
        $this->worksheet->getRowDimension($row+1)->setOutlineLevel($level);
        $this->apply_row_format($row, $format);
    }

    
    public function set_column($firstcol, $lastcol, $width, $format = null, $hidden = false, $level = 0) {
        if ($level < 0) {
            $level = 0;
        } else if ($level > 7) {
            $level = 7;
        }
        $i = $firstcol;
        while($i <= $lastcol) {
            if (isset($width)) {
                $this->worksheet->getColumnDimensionByColumn($i)->setWidth($width);
            }
            $this->worksheet->getColumnDimensionByColumn($i)->setVisible(!$hidden);
            $this->worksheet->getColumnDimensionByColumn($i)->setOutlineLevel($level);
            $this->apply_column_format($i, $format);
            $i++;
        }
    }

   
    public function hide_gridlines() {
            }

   
    public function hide_screen_gridlines() {
        $this->worksheet->setShowGridlines(false);
    }

   
    public function insert_bitmap($row, $col, $bitmap, $x = 0, $y = 0, $scale_x = 1, $scale_y = 1) {
        $objDrawing = new PHPExcel_Worksheet_Drawing();
        $objDrawing->setPath($bitmap);
        $objDrawing->setCoordinates(PHPExcel_Cell::stringFromColumnIndex($col) . ($row+1));
        $objDrawing->setOffsetX($x);
        $objDrawing->setOffsetY($y);
        $objDrawing->setWorksheet($this->worksheet);
        if ($scale_x != 1) {
            $objDrawing->setResizeProportional(false);
            $objDrawing->getWidth($objDrawing->getWidth()*$scale_x);
        }
        if ($scale_y != 1) {
            $objDrawing->setResizeProportional(false);
            $objDrawing->setHeight($objDrawing->getHeight()*$scale_y);
        }
    }

   
    public function merge_cells($first_row, $first_col, $last_row, $last_col) {
        $this->worksheet->mergeCellsByColumnAndRow($first_col, $first_row+1, $last_col, $last_row+1);
    }

    protected function apply_format($row, $col, $format = null) {
        if (!$format) {
            $format = new MoodleExcelFormat();
        } else if (is_array($format)) {
            $format = new MoodleExcelFormat($format);
        }
        $this->worksheet->getStyleByColumnAndRow($col, $row+1)->applyFromArray($format->get_format_array());
    }

    protected function apply_column_format($col, $format = null) {
        if (!$format) {
            $format = new MoodleExcelFormat();
        } else if (is_array($format)) {
            $format = new MoodleExcelFormat($format);
        }
        $this->worksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col))->applyFromArray($format->get_format_array());
    }

    protected function apply_row_format($row, $format = null) {
        if (!$format) {
            $format = new MoodleExcelFormat();
        } else if (is_array($format)) {
            $format = new MoodleExcelFormat($format);
        }
        $this->worksheet->getStyle($row+1)->applyFromArray($format->get_format_array());
    }
}



class MoodleExcelFormat {
    
    protected $format = array('font'=>array('size'=>10, 'name'=>'Arial'));

    
    public function __construct($properties = array()) {
                foreach($properties as $property => $value) {
            if(method_exists($this,"set_$property")) {
                $aux = 'set_'.$property;
                $this->$aux($value);
            }
        }
    }

    
    public function get_format_array() {
        return $this->format;
    }
    
    public function set_size($size) {
        $this->format['font']['size'] = $size;
    }

    
    public function set_bold($weight = 1) {
        if ($weight == 1) {
            $weight = 700;
        }
        $this->format['font']['bold'] = ($weight > 400);
    }

    
    public function set_underline($underline) {
        if ($underline == 1) {
            $this->format['font']['underline'] = PHPExcel_Style_Font::UNDERLINE_SINGLE;
        } else if ($underline == 2) {
            $this->format['font']['underline'] = PHPExcel_Style_Font::UNDERLINE_DOUBLE;
        } else {
            $this->format['font']['underline'] = PHPExcel_Style_Font::UNDERLINE_NONE;
        }
    }

    
    public function set_italic() {
        $this->format['font']['italic'] = true;
    }

    
    public function set_strikeout() {
        $this->format['font']['strike'] = true;
    }

    
    public function set_outline() {
            }

    
    public function set_shadow() {
            }

    
    public function set_script($script) {
        if ($script == 1) {
            $this->format['font']['superScript'] = true;
        } else if ($script == 2) {
            $this->format['font']['subScript'] = true;
        } else {
            $this->format['font']['superScript'] = false;
            $this->format['font']['subScript'] = false;
        }
    }

    
    public function set_color($color) {
        $this->format['font']['color']['rgb'] = $this->parse_color($color);
    }

    
    protected function parse_color($color) {
        if (strpos($color, '#') === 0) {
                        return substr($color, 1);
        }

        if ($color > 7 and $color < 53) {
            $numbers = array(
                8  => 'black',
                12 => 'blue',
                16 => 'brown',
                15 => 'cyan',
                23 => 'gray',
                17 => 'green',
                11 => 'lime',
                14 => 'magenta',
                18 => 'navy',
                53 => 'orange',
                33 => 'pink',
                20 => 'purple',
                10 => 'red',
                22 => 'silver',
                9  => 'white',
                13 => 'yellow',
            );
            if (isset($numbers[$color])) {
                $color = $numbers[$color];
            } else {
                $color = 'black';
            }
        }

        $colors = array(
            'aqua'    => '00FFFF',
            'black'   => '000000',
            'blue'    => '0000FF',
            'brown'   => 'A52A2A',
            'cyan'    => '00FFFF',
            'fuchsia' => 'FF00FF',
            'gray'    => '808080',
            'grey'    => '808080',
            'green'   => '00FF00',
            'lime'    => '00FF00',
            'magenta' => 'FF00FF',
            'maroon'  => '800000',
            'navy'    => '000080',
            'orange'  => 'FFA500',
            'olive'   => '808000',
            'pink'    => 'FAAFBE',
            'purple'  => '800080',
            'red'     => 'FF0000',
            'silver'  => 'C0C0C0',
            'teal'    => '008080',
            'white'   => 'FFFFFF',
            'yellow'  => 'FFFF00',
        );

        if (isset($colors[$color])) {
            return($colors[$color]);
        }

        return($colors['black']);
    }

    
    public function set_fg_color($color) {
            }

    
    public function set_bg_color($color) {
        if (!isset($this->format['fill']['type'])) {
            $this->format['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
        }
        $this->format['fill']['color']['rgb'] = $this->parse_color($color);
    }

    
    public function set_pattern($pattern=1) {
        if ($pattern > 0) {
            if (!isset($this->format['fill']['color']['rgb'])) {
                $this->set_bg_color('black');
            }
        } else {
            unset($this->format['fill']['color']['rgb']);
            unset($this->format['fill']['type']);
        }
    }

    
    public function set_text_wrap() {
        $this->format['alignment']['wrap'] = true;
    }

    
    public function set_align($location) {
        if (in_array($location, array('left', 'centre', 'center', 'right', 'fill', 'merge', 'justify', 'equal_space'))) {
            $this->set_h_align($location);

        } else if (in_array($location, array('top', 'vcentre', 'vcenter', 'bottom', 'vjustify', 'vequal_space'))) {
            $this->set_v_align($location);
        }
    }

    
    public function set_h_align($location) {
        switch ($location) {
            case 'left':
                $this->format['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                break;
            case 'center':
            case 'centre':
                $this->format['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                break;
            case 'right':
                $this->format['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                break;
            case 'justify':
                $this->format['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY;
                break;
            default:
                $this->format['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_GENERAL;
        }
    }

    
    public function set_v_align($location) {
        switch ($location) {
            case 'top':
                $this->format['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_TOP;
                break;
            case 'vcentre':
            case 'vcenter':
            case 'centre':
            case 'center':
                $this->format['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_CENTER;
                break;
            case 'vjustify':
            case 'justify':
                $this->format['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_JUSTIFY;
                break;
            default:
                $this->format['alignment']['vertical'] = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
        }
    }

    
    public function set_top($style) {
        if ($style == 1) {
            $this->format['borders']['top']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        } else if ($style == 2) {
            $this->format['borders']['top']['style'] = PHPExcel_Style_Border::BORDER_THICK;
        } else {
            $this->format['borders']['top']['style'] = PHPExcel_Style_Border::BORDER_NONE;
        }
    }

    
    public function set_bottom($style) {
        if ($style == 1) {
            $this->format['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        } else if ($style == 2) {
            $this->format['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_THICK;
        } else {
            $this->format['borders']['bottom']['style'] = PHPExcel_Style_Border::BORDER_NONE;
        }
    }

    
    public function set_left($style) {
        if ($style == 1) {
            $this->format['borders']['left']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        } else if ($style == 2) {
            $this->format['borders']['left']['style'] = PHPExcel_Style_Border::BORDER_THICK;
        } else {
            $this->format['borders']['left']['style'] = PHPExcel_Style_Border::BORDER_NONE;
        }
    }

    
    public function set_right($style) {
        if ($style == 1) {
            $this->format['borders']['right']['style'] = PHPExcel_Style_Border::BORDER_THIN;
        } else if ($style == 2) {
            $this->format['borders']['right']['style'] = PHPExcel_Style_Border::BORDER_THICK;
        } else {
            $this->format['borders']['right']['style'] = PHPExcel_Style_Border::BORDER_NONE;
        }
    }

    
    public function set_border($style) {
        $this->set_top($style);
        $this->set_bottom($style);
        $this->set_left($style);
        $this->set_right($style);
    }

    
    public function set_num_format($num_format) {
        $numbers = array();

        $numbers[1] = '0';
        $numbers[2] = '0.00';
        $numbers[3] = '#,##0';
        $numbers[4] = '#,##0.00';
        $numbers[11] = '0.00E+00';
        $numbers[12] = '# ?/?';
        $numbers[13] = '# ??/??';
        $numbers[14] = 'mm-dd-yy';
        $numbers[15] = 'd-mmm-yy';
        $numbers[16] = 'd-mmm';
        $numbers[17] = 'mmm-yy';
        $numbers[22] = 'm/d/yy h:mm';
        $numbers[49] = '@';

        if ($num_format !== 0 and in_array($num_format, $numbers)) {
            $this->format['numberformat']['code'] = $num_format;
        }

        if (!isset($numbers[$num_format])) {
            return;
        }

        $this->format['numberformat']['code'] = $numbers[$num_format];
    }
}
