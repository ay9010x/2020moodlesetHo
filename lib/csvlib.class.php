<?php



defined('MOODLE_INTERNAL') || die();


class csv_import_reader {

    
    private $_iid;

    
    private $_type;

    
    private $_error;

    
    private $_columns;

    
    private $_fp;

    
    public function __construct($iid, $type) {
        $this->_iid  = $iid;
        $this->_type = $type;
    }

    
    public function __destruct() {
        $this->close();
    }

    
    public function load_csv_content($content, $encoding, $delimiter_name, $column_validation=null, $enclosure='"') {
        global $USER, $CFG;

        $this->close();
        $this->_error = null;

        $content = core_text::convert($content, $encoding, 'utf-8');
                $content = core_text::trim_utf8_bom($content);
                $content = preg_replace('!\r\n?!', "\n", $content);
                if ($delimiter_name == 'tab') {
                        $content = trim($content, chr(0x20) . chr(0x0A) . chr(0x0D) . chr(0x00) . chr(0x0B));
        } else {
            $content = trim($content);
        }

        $csv_delimiter = csv_import_reader::get_delimiter($delimiter_name);
        
                                $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');
        if (!$fp = fopen($tempfile, 'w+b')) {
            $this->_error = get_string('cannotsavedata', 'error');
            @unlink($tempfile);
            return false;
        }
        fwrite($fp, $content);
        fseek($fp, 0);
                $columns = array();
                        while ($fgetdata = fgetcsv($fp, 0, $csv_delimiter, $enclosure)) {
                        if (count($fgetdata) == 1) {
                if ($fgetdata[0] !== null) {
                                        $columns[] = $fgetdata;
                }
            } else {
                $columns[] = $fgetdata;
            }
        }
        $col_count = 0;

                if (!isset($columns[0])) {
            $this->_error = get_string('csvemptyfile', 'error');
            fclose($fp);
            unlink($tempfile);
            return false;
        } else {
            $col_count = count($columns[0]);
        }

                if ($column_validation) {
            $result = $column_validation($columns[0]);
            if ($result !== true) {
                $this->_error = $result;
                fclose($fp);
                unlink($tempfile);
                return false;
            }
        }

        $this->_columns = $columns[0];                 foreach ($columns as $rowdata) {
            if (count($rowdata) !== $col_count) {
                $this->_error = get_string('csvweirdcolumns', 'error');
                fclose($fp);
                unlink($tempfile);
                $this->cleanup();
                return false;
            }
        }

        $filename = $CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid;
        $filepointer = fopen($filename, "w");
                        $storedata = csv_export_writer::print_array($columns, ',', '"', true);
        fwrite($filepointer, $storedata);

        fclose($fp);
        unlink($tempfile);
        fclose($filepointer);

        $datacount = count($columns);
        return $datacount;
    }

    
    public function get_columns() {
        if (isset($this->_columns)) {
            return $this->_columns;
        }

        global $USER, $CFG;

        $filename = $CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid;
        if (!file_exists($filename)) {
            return false;
        }
        $fp = fopen($filename, "r");
        $line = fgetcsv($fp);
        fclose($fp);
        if ($line === false) {
            return false;
        }
        $this->_columns = $line;
        return $this->_columns;
    }

    
    public function init() {
        global $CFG, $USER;

        if (!empty($this->_fp)) {
            $this->close();
        }
        $filename = $CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid;
        if (!file_exists($filename)) {
            return false;
        }
        if (!$this->_fp = fopen($filename, "r")) {
            return false;
        }
                return (fgetcsv($this->_fp) !== false);
    }

    
    public function next() {
        if (empty($this->_fp) or feof($this->_fp)) {
            return false;
        }
        if ($ser = fgetcsv($this->_fp)) {
            return $ser;
        } else {
            return false;
        }
    }

    
    public function close() {
        if (!empty($this->_fp)) {
            fclose($this->_fp);
            $this->_fp = null;
        }
    }

    
    public function get_error() {
        return $this->_error;
    }

    
    public function cleanup($full=false) {
        global $USER, $CFG;

        if ($full) {
            @remove_dir($CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id);
        } else {
            @unlink($CFG->tempdir.'/csvimport/'.$this->_type.'/'.$USER->id.'/'.$this->_iid);
        }
    }

    
    public static function get_delimiter_list() {
        global $CFG;
        $delimiters = array('comma'=>',', 'semicolon'=>';', 'colon'=>':', 'tab'=>'\\t');
        if (isset($CFG->CSV_DELIMITER) and strlen($CFG->CSV_DELIMITER) === 1 and !in_array($CFG->CSV_DELIMITER, $delimiters)) {
            $delimiters['cfg'] = $CFG->CSV_DELIMITER;
        }
        return $delimiters;
    }

    
    public static function get_delimiter($delimiter_name) {
        global $CFG;
        switch ($delimiter_name) {
            case 'colon':     return ':';
            case 'semicolon': return ';';
            case 'tab':       return "\t";
            case 'cfg':       if (isset($CFG->CSV_DELIMITER)) { return $CFG->CSV_DELIMITER; }             case 'comma':     return ',';
            default :         return ',';          }
    }

    
    public static function get_encoded_delimiter($delimiter_name) {
        global $CFG;
        if ($delimiter_name == 'cfg' and isset($CFG->CSV_ENCODE)) {
            return $CFG->CSV_ENCODE;
        }
        $delimiter = csv_import_reader::get_delimiter($delimiter_name);
        return '&#'.ord($delimiter);
    }

    
    public static function get_new_iid($type) {
        global $USER;

        $filename = make_temp_directory('csvimport/'.$type.'/'.$USER->id);

                $iiid = time();
        while (file_exists($filename.'/'.$iiid)) {
            $iiid--;
        }

        return $iiid;
    }
}



class csv_export_writer {
    
    var $delimiter;
    
    var $csvenclosure;
    
    var $mimetype;
    
    var $filename;
    
    var $path;
    
    protected $fp;

    
    public function __construct($delimiter = 'comma', $enclosure = '"', $mimetype = 'application/download') {
        $this->delimiter = $delimiter;
                if (strlen($enclosure) == 1) {
            $this->csvenclosure = $enclosure;
        } else {
            $this->csvenclosure = '"';
        }
        $this->filename = "Moodle-data-export.csv";
        $this->mimetype = $mimetype;
    }

    
    protected function set_temp_file_path() {
        global $USER, $CFG;
        make_temp_directory('csvimport/' . $USER->id);
        $path = $CFG->tempdir . '/csvimport/' . $USER->id. '/' . $this->filename;
                if (file_exists($path)) {
            unlink($path);
        }
        $this->path = $path;
    }

    
    public function add_data($row) {
        if(!isset($this->path)) {
            $this->set_temp_file_path();
            $this->fp = fopen($this->path, 'w+');
        }
        $delimiter = csv_import_reader::get_delimiter($this->delimiter);
        fputcsv($this->fp, $row, $delimiter, $this->csvenclosure);
    }

    
    public function print_csv_data($return = false) {
        fseek($this->fp, 0);
        $returnstring = '';
        while (($content = fgets($this->fp)) !== false) {
            if (!$return){
                echo $content;
            } else {
                $returnstring .= $content;
            }
        }
        if ($return) {
            return $returnstring;
        }
    }

    
    public function set_filename($dataname, $extension = '.csv') {
        $filename = clean_filename($dataname);
        $filename .= clean_filename('-' . gmdate("Ymd_Hi"));
        $filename .= clean_filename("-{$this->delimiter}_separated");
        $filename .= $extension;
        $this->filename = $filename;
    }

    
    protected function send_header() {
        global $CFG;

        if (defined('BEHAT_SITE_RUNNING')) {
                        return;
        }
        if (is_https()) {             header('Cache-Control: max-age=10');
            header('Pragma: ');
        } else {             header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
        }
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header("Content-Type: $this->mimetype\n");
        header("Content-Disposition: attachment; filename=\"$this->filename\"");
    }

    
    public function download_file() {
        $this->send_header();
        $this->print_csv_data();
        exit;
    }

    
    public static function download_array($filename, array &$records, $delimiter = 'comma', $enclosure='"') {
        $csvdata = new csv_export_writer($delimiter, $enclosure);
        $csvdata->set_filename($filename);
        foreach ($records as $row) {
            $csvdata->add_data($row);
        }
        $csvdata->download_file();
    }

    
    public static function print_array(array &$records, $delimiter = 'comma', $enclosure = '"', $return = false) {
        $csvdata = new csv_export_writer($delimiter, $enclosure);
        foreach ($records as $row) {
            $csvdata->add_data($row);
        }
        $data = $csvdata->print_csv_data($return);
        if ($return) {
            return $data;
        }
    }

    
    public function __destruct() {
        fclose($this->fp);
        unlink($this->path);
    }
}
