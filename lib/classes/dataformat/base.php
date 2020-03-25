<?php



namespace core\dataformat;


abstract class base {

    
    protected $mimetype = "text/plain";

    
    protected $extension = ".txt";

    
    protected $filename = '';

    
    public function get_extension() {
        return $this->extension;
    }

    
    public function set_filename($filename) {
        $this->filename = $filename;
    }

    
    public function set_sheettitle($title) {
    }

    
    public function send_http_headers() {
        global $CFG;

        if (defined('BEHAT_SITE_RUNNING')) {
                        return;
        }
        if (is_https()) {
                        header('Cache-Control: max-age=10');
            header('Pragma: ');
        } else {
                        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
            header('Pragma: no-cache');
        }
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header("Content-Type: $this->mimetype\n");
        $filename = $this->filename . $this->get_extension();
        header("Content-Disposition: attachment; filename=\"$filename\"");
    }

    
    public function write_header($columns) {
            }

    
    abstract public function write_record($record, $rownum);

    
    public function write_footer($columns) {
            }

}
