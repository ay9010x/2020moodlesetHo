<?php



namespace core\dataformat;


abstract class spout_base extends \core\dataformat\base {

    
    protected $spouttype = '';

    
    protected $writer;

    
    protected $sheettitle;

    
    public function send_http_headers() {
        $this->writer = \Box\Spout\Writer\WriterFactory::create($this->spouttype);
        if (method_exists($this->writer, 'setTempFolder')) {
            $this->writer->setTempFolder(make_request_directory());
        }
        $filename = $this->filename . $this->get_extension();
        $this->writer->openToBrowser($filename);
        if ($this->sheettitle && $this->writer instanceof \Box\Spout\Writer\AbstractMultiSheetsWriter) {
            $sheet = $this->writer->getCurrentSheet();
            $sheet->setName($this->sheettitle);
        }
    }

    
    public function set_sheettitle($title) {
        if (!$title) {
            return;
        }
        $this->sheettitle = $title;
    }

    
    public function write_header($columns) {
        $this->writer->addRow(array_values((array)$columns));
    }

    
    public function write_record($record, $rownum) {
        $this->writer->addRow(array_values((array)$record));
    }

    
    public function write_footer($columns) {
        $this->writer->close();
        $this->writer = null;
    }

}
