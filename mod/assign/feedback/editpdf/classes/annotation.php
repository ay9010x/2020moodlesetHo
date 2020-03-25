<?php



namespace assignfeedback_editpdf;


class annotation {

    
    public $id = 0;

    
    public $gradeid = 0;

    
    public $pageno = 0;

    
    public $x = 0;

    
    public $endx = 0;

    
    public $y = 0;

    
    public $endy = 0;

    
    public $path = '';

    
    public $colour = 'yellow';

    
    public $type = 'line';

    
    public function __construct(\stdClass $record = null) {
        if ($record) {
            $intcols = array('endx', 'endy', 'x', 'y');
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
