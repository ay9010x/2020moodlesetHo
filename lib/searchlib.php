<?php




defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir.'/lexer.php');



define("TOKEN_USER","0");
define("TOKEN_META","1");
define("TOKEN_EXACT","2");
define("TOKEN_NEGATE","3");
define("TOKEN_STRING","4");
define("TOKEN_USERID","5");
define("TOKEN_DATEFROM","6");
define("TOKEN_DATETO","7");
define("TOKEN_INSTANCE","8");


class search_token {
  private $value;
  private $type;

  public function __construct($type,$value){
    $this->type = $type;
    $this->value = $this->sanitize($value);

  }

  
  public function search_token($type, $value) {
    debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
    self::__construct($type, $value);
  }

    
  function sanitize($userstring){
    return htmlspecialchars($userstring);
  }
  function getValue(){
    return $this->value;
  }
  function getType(){
    return $this->type;
  }
}



class search_lexer extends Lexer{

  public function __construct(&$parser){

        parent::__construct($parser);

    
    
            $this->addEntryPattern("datefrom:\S+","accept","indatefrom");

            $this->addExitPattern("\s","indatefrom");


    
            $this->addEntryPattern("dateto:\S+","accept","indateto");

            $this->addExitPattern("\s","indateto");


    
            $this->addEntryPattern("instance:\S+","accept","ininstance");

            $this->addExitPattern("\s","ininstance");


    
            $this->addEntryPattern("userid:\S+","accept","inuserid");

            $this->addExitPattern("\s","inuserid");


    
            $this->addEntryPattern("user:\S+","accept","inusername");

            $this->addExitPattern("\s","inusername");


    
           $this->addEntryPattern("subject:\S+","accept","inmeta");

            $this->addExitPattern("\s","inmeta");


    
            $this->addEntryPattern("\+\S+","accept","inrequired");
        $this->addExitPattern("\s","inrequired");

    
           $this->addEntryPattern("\-\S+","accept","inexcluded");
        $this->addExitPattern("\s","inexcluded");


    
            
    $this->addEntryPattern("\"[^\"]+","accept","inquotedstring");

        $this->addExitPattern("\"","inquotedstring");

    
            $this->addEntryPattern("\S+","accept","plainstring");

        $this->addExitPattern("\s","plainstring");

  }

  
  public function search_lexer(&$parser) {
    debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
    self::__construct($parser);
  }

}




class search_parser {
    private $tokens;

        function get_parsed_array(){
        return $this->tokens;
    }

    

        function accept() {
        return true;
    }

        function indatefrom($content){
        if (strlen($content) < 10) {             return true;
        }
                $param = trim(substr($content,9));
        $this->tokens[] = new search_token(TOKEN_DATEFROM,$param);
        return true;
    }

        function indateto($content){
        if (strlen($content) < 8) {             return true;
        }
                $param = trim(substr($content,7));
        $this->tokens[] = new search_token(TOKEN_DATETO,$param);
        return true;
    }

        function ininstance($content){
        if (strlen($content) < 10) {             return true;
        }
                $param = trim(substr($content,9));
        $this->tokens[] = new search_token(TOKEN_INSTANCE,$param);
        return true;
    }


        function inuserid($content){
        if (strlen($content) < 8) {             return true;
        }
                $param = trim(substr($content,7));
        $this->tokens[] = new search_token(TOKEN_USERID,$param);
        return true;
    }


        function inusername($content){
        if (strlen($content) < 6) {             return true;
        }
                $param = trim(substr($content,5));
        $this->tokens[] = new search_token(TOKEN_USER,$param);
        return true;
    }


        function inmeta($content){
        if (strlen($content) < 9) {             return true;
        }
                $param = trim(substr($content,8));
        $this->tokens[] = new search_token(TOKEN_META,$param);
        return true;
    }


            function inrequired($content){
        if (strlen($content) < 2) {             return true;
        }
                $this->tokens[] = new search_token(TOKEN_EXACT,substr($content,1));
        return true;
    }

            function inexcluded($content){
        if (strlen($content) < 2) {             return true;
        }
                $this->tokens[] = new search_token(TOKEN_NEGATE,substr($content,1));
        return true;
    }


        function inquotedstring($content){
        if (strlen($content) < 2) {             return true;
        }
                $this->tokens[] = new search_token(TOKEN_STRING,substr($content,1));
        return true;
    }

            function plainstring($content){
        if (trim($content) === '') {             return true;
        }
                $this->tokens[] = new search_token(TOKEN_STRING,$content);
        return true;
    }
}


function search_generate_text_SQL($parsetree, $datafield, $metafield, $mainidfield, $useridfield,
                             $userfirstnamefield, $userlastnamefield, $timefield, $instancefield) {
    debugging('search_generate_text_SQL() is deprecated, please use search_generate_SQL() instead.', DEBUG_DEVELOPER);

    return search_generate_SQL($parsetree, $datafield, $metafield, $mainidfield, $useridfield,
                               $userfirstnamefield, $userlastnamefield, $timefield, $instancefield);
}


function search_generate_SQL($parsetree, $datafield, $metafield, $mainidfield, $useridfield,
                             $userfirstnamefield, $userlastnamefield, $timefield, $instancefield) {
    global $CFG, $DB;
    static $p = 0;

    if ($DB->sql_regex_supported()) {
        $REGEXP    = $DB->sql_regex(true);
        $NOTREGEXP = $DB->sql_regex(false);
    }

    $params = array();

    $ntokens = count($parsetree);
    if ($ntokens == 0) {
        return "";
    }

    $SQLString = '';

    for ($i=0; $i<$ntokens; $i++){
        if ($i > 0) {            $SQLString .= ' AND ';
        }

        $type = $parsetree[$i]->getType();
        $value = $parsetree[$i]->getValue();

            if (!$DB->sql_regex_supported()) {
            $value = trim($value, '+-');
            if ($type == TOKEN_EXACT) {
                $type = TOKEN_STRING;
            }
        }

        $name1 = 'sq'.$p++;
        $name2 = 'sq'.$p++;

        switch($type){
            case TOKEN_STRING:
                $SQLString .= "((".$DB->sql_like($datafield, ":$name1", false).") OR (".$DB->sql_like($metafield, ":$name2", false)."))";
                $params[$name1] =  "%$value%";
                $params[$name2] =  "%$value%";
                break;
            case TOKEN_EXACT:
                $SQLString .= "(($datafield $REGEXP :$name1) OR ($metafield $REGEXP :$name2))";
                $params[$name1] =  "[[:<:]]".$value."[[:>:]]";
                $params[$name2] =  "[[:<:]]".$value."[[:>:]]";
                break;
            case TOKEN_META:
                if ($metafield != '') {
                    $SQLString .= "(".$DB->sql_like($metafield, ":$name1", false).")";
                    $params[$name1] =  "%$value%";
                }
                break;
            case TOKEN_USER:
                $SQLString .= "(($mainidfield = $useridfield) AND ((".$DB->sql_like($userfirstnamefield, ":$name1", false).") OR (".$DB->sql_like($userlastnamefield, ":$name2", false).")))";
                $params[$name1] =  "%$value%";
                $params[$name2] =  "%$value%";
                break;
            case TOKEN_USERID:
                $SQLString .= "($useridfield = :$name1)";
                $params[$name1] =  $value;
                break;
            case TOKEN_INSTANCE:
                $SQLString .= "($instancefield = :$name1)";
                $params[$name1] =  $value;
                break;
            case TOKEN_DATETO:
                $SQLString .= "($timefield <= :$name1)";
                $params[$name1] =  $value;
                break;
            case TOKEN_DATEFROM:
                $SQLString .= "($timefield >= :$name1)";
                $params[$name1] =  $value;
                break;
            case TOKEN_NEGATE:
                $SQLString .= "(NOT ((".$DB->sql_like($datafield, ":$name1", false).") OR (".$DB->sql_like($metafield, ":$name2", false).")))";
                $params[$name1] =  "%$value%";
                $params[$name2] =  "%$value%";
                break;
            default:
                return '';

        }
    }
    return array($SQLString, $params);
}
