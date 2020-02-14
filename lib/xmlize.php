<?php




class xml_format_exception extends moodle_exception {
    
    public $errorstring;
    public $line;
    public $char;
    function __construct($errorstring, $line, $char, $link = '') {
        $this->errorstring = $errorstring;
        $this->line = $line;
        $this->char = $char;

        $a = new stdClass();
        $a->errorstring = $errorstring;
        $a->errorline = $line;
        $a->errorchar = $char;
        parent::__construct('errorparsingxml', 'error', $link, $a);
    }
}


function xmlize($data, $whitespace = 1, $encoding = 'UTF-8', $reporterrors = false) {

    $data = trim($data);
    $vals = array();
    $parser = xml_parser_create($encoding);
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, $whitespace);
    xml_parse_into_struct($parser, $data, $vals);

        if ($reporterrors) {
        $errorcode = xml_get_error_code($parser);
        if ($errorcode) {
            $exception = new xml_format_exception(xml_error_string($errorcode),
                    xml_get_current_line_number($parser),
                    xml_get_current_column_number($parser));
            xml_parser_free($parser);
            throw $exception;
        }
    }
    xml_parser_free($parser);

    $i = 0;
    if (empty($vals)) {
                return false;
    }

    $array = array();
    $tagname = $vals[$i]['tag'];
    if (isset($vals[$i]['attributes'])) {
        $array[$tagname]['@'] = $vals[$i]['attributes'];
    } else {
        $array[$tagname]['@'] = array();
    }

    $array[$tagname]["#"] = xml_depth($vals, $i);

    return $array;
}


function xml_depth($vals, &$i) {
    $children = array();

    if ( isset($vals[$i]['value']) )
    {
        array_push($children, $vals[$i]['value']);
    }

    while (++$i < count($vals)) {

        switch ($vals[$i]['type']) {

           case 'open':

                if ( isset ( $vals[$i]['tag'] ) )
                {
                    $tagname = $vals[$i]['tag'];
                } else {
                    $tagname = '';
                }

                if ( isset ( $children[$tagname] ) )
                {
                    $size = sizeof($children[$tagname]);
                } else {
                    $size = 0;
                }

                if ( isset ( $vals[$i]['attributes'] ) ) {
                    $children[$tagname][$size]['@'] = $vals[$i]["attributes"];

                }

                $children[$tagname][$size]['#'] = xml_depth($vals, $i);

            break;


            case 'cdata':
                array_push($children, $vals[$i]['value']);
            break;

            case 'complete':
                $tagname = $vals[$i]['tag'];

                if( isset ($children[$tagname]) )
                {
                    $size = sizeof($children[$tagname]);
                } else {
                    $size = 0;
                }

                if( isset ( $vals[$i]['value'] ) )
                {
                    $children[$tagname][$size]["#"] = $vals[$i]['value'];
                } else {
                    $children[$tagname][$size]["#"] = '';
                }

                if ( isset ($vals[$i]['attributes']) ) {
                    $children[$tagname][$size]['@']
                                             = $vals[$i]['attributes'];
                }

            break;

            case 'close':
                return $children;
            break;
        }

    }

        return $children;


}



function traverse_xmlize($array, $arrName = 'array', $level = 0) {

    foreach($array as $key=>$val)
    {
        if ( is_array($val) )
        {
            traverse_xmlize($val, $arrName . '[' . $key . ']', $level + 1);
        } else {
            $GLOBALS['traverse_array'][] = '$' . $arrName . '[' . $key . '] = "' . $val . "\"\n";
        }
    }

    return 1;

}
