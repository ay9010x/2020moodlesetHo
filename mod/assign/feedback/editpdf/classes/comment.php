<?php



namespace assignfeedback_editpdf;


class comment {

    
    public $id = 0;

    
    public $gradeid = 0;

    
    public $pageno = 0;

    
    public $x = 0;

    
    public $y = 0;

    
    public $width = 120;

    
    public $rawtext = '';

    
    public $colour = 'yellow';

    
    public function __construct(\stdClass $record = null) {
        if ($record) {
            $intcols = array('width', 'x', 'y');
            foreach ($this as $key => $value) {
                if (isset($record->$key)) {
                    if (in_array($key, $intcols)) {
                        $this->$key = intval($record->$key);
                    } else {
                        $this->$key = $record->$key;
                    }
                }
            }
        }
    }
}
