<?php





class EvalMath {

    
    private static $namepat = '[a-z][a-z0-9_]*';

    var $suppress_errors = false;
    var $last_error = null;

    var $v = array();     var $f = array();     var $vb = array();     var $fb = array(          'sin','sinh','arcsin','asin','arcsinh','asinh',
        'cos','cosh','arccos','acos','arccosh','acosh',
        'tan','tanh','arctan','atan','arctanh','atanh',
        'sqrt','abs','ln','log','exp','floor','ceil');

    var $fc = array(         'average'=>array(-1), 'max'=>array(-1),  'min'=>array(-1),
        'mod'=>array(2),      'pi'=>array(0),    'power'=>array(2),
        'round'=>array(1, 2), 'sum'=>array(-1), 'rand_int'=>array(2),
        'rand_float'=>array(0));

    var $allowimplicitmultiplication;

    public function __construct($allowconstants = false, $allowimplicitmultiplication = false) {
        if ($allowconstants){
            $this->v['pi'] = pi();
            $this->v['e'] = exp(1);
        }
        $this->allowimplicitmultiplication = $allowimplicitmultiplication;
    }

    
    public function EvalMath($allowconstants = false, $allowimplicitmultiplication = false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($allowconstants, $allowimplicitmultiplication);
    }

    function e($expr) {
        return $this->evaluate($expr);
    }

    function evaluate($expr) {
        $this->last_error = null;
        $expr = trim($expr);
        if (substr($expr, -1, 1) == ';') $expr = substr($expr, 0, strlen($expr)-1);                         if (preg_match('/^\s*('.self::$namepat.')\s*=\s*(.+)$/', $expr, $matches)) {
            if (in_array($matches[1], $this->vb)) {                 return $this->trigger(get_string('cannotassigntoconstant', 'mathslib', $matches[1]));
            }
            if (($tmp = $this->pfx($this->nfx($matches[2]))) === false) return false;             $this->v[$matches[1]] = $tmp;             return $this->v[$matches[1]];                         } elseif (preg_match('/^\s*('.self::$namepat.')\s*\(\s*('.self::$namepat.'(?:\s*,\s*'.self::$namepat.')*)\s*\)\s*=\s*(.+)$/', $expr, $matches)) {
            $fnn = $matches[1];             if (in_array($matches[1], $this->fb)) {                 return $this->trigger(get_string('cannotredefinebuiltinfunction', 'mathslib', $matches[1]));
            }
            $args = explode(",", preg_replace("/\s+/", "", $matches[2]));             if (($stack = $this->nfx($matches[3])) === false) return false;             for ($i = 0; $i<count($stack); $i++) {                 $token = $stack[$i];
                if (preg_match('/^'.self::$namepat.'$/', $token) and !in_array($token, $args)) {
                    if (array_key_exists($token, $this->v)) {
                        $stack[$i] = $this->v[$token];
                    } else {
                        return $this->trigger(get_string('undefinedvariableinfunctiondefinition', 'mathslib', $token));
                    }
                }
            }
            $this->f[$fnn] = array('args'=>$args, 'func'=>$stack);
            return true;
                } else {
            return $this->pfx($this->nfx($expr));         }
    }

    function vars() {
        return $this->v;
    }

    function funcs() {
        $output = array();
        foreach ($this->f as $fnn=>$dat)
            $output[] = $fnn . '(' . implode(',', $dat['args']) . ')';
        return $output;
    }

    
    public static function is_valid_var_or_func_name($name){
        return preg_match('/'.self::$namepat.'$/iA', $name);
    }

    
        function nfx($expr) {

        $index = 0;
        $stack = new EvalMathStack;
        $output = array();         $expr = trim(strtolower($expr));

        $ops   = array('+', '-', '*', '/', '^', '_');
        $ops_r = array('+'=>0,'-'=>0,'*'=>0,'/'=>0,'^'=>1);         $ops_p = array('+'=>0,'-'=>0,'*'=>1,'/'=>1,'_'=>1,'^'=>2); 
        $expecting_op = false;                                
        if (preg_match("/[^\w\s+*^\/()\.,-]/", $expr, $matches)) {             return $this->trigger(get_string('illegalcharactergeneral', 'mathslib', $matches[0]));
        }

        while(1) {             $op = substr($expr, $index, 1);                         $ex = preg_match('/^('.self::$namepat.'\(?|\d+(?:\.\d*)?(?:(e[+-]?)\d*)?|\.\d+|\()/', substr($expr, $index), $match);
                        if ($op == '-' and !$expecting_op) {                 $stack->push('_');                 $index++;
            } elseif ($op == '_') {                 return $this->trigger(get_string('illegalcharacterunderscore', 'mathslib'));                         } elseif ((in_array($op, $ops) or $ex) and $expecting_op) {                 if ($ex) {                     if (!$this->allowimplicitmultiplication){
                        return $this->trigger(get_string('implicitmultiplicationnotallowed', 'mathslib'));
                    } else {                        $op = '*';
                        $index--;
                    }
                }
                                while($stack->count > 0 and ($o2 = $stack->last()) and in_array($o2, $ops) and ($ops_r[$op] ? $ops_p[$op] < $ops_p[$o2] : $ops_p[$op] <= $ops_p[$o2])) {
                    $output[] = $stack->pop();                 }
                                $stack->push($op);                 $index++;
                $expecting_op = false;
                        } elseif ($op == ')' and $expecting_op) {                 while (($o2 = $stack->pop()) != '(') {                     if (is_null($o2)) return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                    else $output[] = $o2;
                }
                if (preg_match('/^('.self::$namepat.')\($/', $stack->last(2), $matches)) {                     $fnn = $matches[1];                     $arg_count = $stack->pop();                     $fn = $stack->pop();
                    $output[] = array('fn'=>$fn, 'fnn'=>$fnn, 'argcount'=>$arg_count);                     if (in_array($fnn, $this->fb)) {                         if($arg_count > 1) {
                            $a= new stdClass();
                            $a->expected = 1;
                            $a->given = $arg_count;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } elseif (array_key_exists($fnn, $this->fc)) {
                        $counts = $this->fc[$fnn];
                        if (in_array(-1, $counts) and $arg_count > 0) {}
                        elseif (!in_array($arg_count, $counts)) {
                            $a= new stdClass();
                            $a->expected = implode('/',$this->fc[$fnn]);
                            $a->given = $arg_count;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } elseif (array_key_exists($fnn, $this->f)) {
                        if ($arg_count != count($this->f[$fnn]['args'])) {
                            $a= new stdClass();
                            $a->expected = count($this->f[$fnn]['args']);
                            $a->given = $arg_count;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } else {                         return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                }
                $index++;
                        } elseif ($op == ',' and $expecting_op) {                 while (($o2 = $stack->pop()) != '(') {
                    if (is_null($o2)) return $this->trigger(get_string('unexpectedcomma', 'mathslib'));                     else $output[] = $o2;                 }
                                if (!preg_match('/^('.self::$namepat.')\($/', $stack->last(2), $matches))
                    return $this->trigger(get_string('unexpectedcomma', 'mathslib'));
                $stack->push($stack->pop()+1);                 $stack->push('(');                 $index++;
                $expecting_op = false;
                        } elseif ($op == '(' and !$expecting_op) {
                $stack->push('(');                 $index++;
                $allow_neg = true;
                        } elseif ($ex and !$expecting_op) {                 $expecting_op = true;
                $val = $match[1];
                if (preg_match('/^('.self::$namepat.')\($/', $val, $matches)) {                     if (in_array($matches[1], $this->fb) or array_key_exists($matches[1], $this->f) or array_key_exists($matches[1], $this->fc)) {                         $stack->push($val);
                        $stack->push(1);
                        $stack->push('(');
                        $expecting_op = false;
                    } else {                         $val = $matches[1];
                        $output[] = $val;
                    }
                } else {                     $output[] = $val;
                }
                $index += strlen($val);
                        } elseif ($op == ')') {
                                if ($stack->last() != '(' or $stack->last(2) != 1) return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                if (preg_match('/^('.self::$namepat.')\($/', $stack->last(3), $matches)) {                     $stack->pop();                    $stack->pop();                    $fn = $stack->pop();
                    $fnn = $matches[1];                     $counts = $this->fc[$fnn];
                    if (!in_array(0, $counts)){
                        $a= new stdClass();
                        $a->expected = $this->fc[$fnn];
                        $a->given = 0;
                        return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                    }
                    $output[] = array('fn'=>$fn, 'fnn'=>$fnn, 'argcount'=>0);                     $index++;
                    $expecting_op = true;
                } else {
                    return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                }
                        } elseif (in_array($op, $ops) and !$expecting_op) {                 return $this->trigger(get_string('unexpectedoperator', 'mathslib', $op));
            } else {                 return $this->trigger(get_string('anunexpectederroroccured', 'mathslib'));
            }
            if ($index == strlen($expr)) {
                if (in_array($op, $ops)) {                     return $this->trigger(get_string('operatorlacksoperand', 'mathslib', $op));
                } else {
                    break;
                }
            }
            while (substr($expr, $index, 1) == ' ') {                 $index++;                                         }

        }
        while (!is_null($op = $stack->pop())) {             if ($op == '(') return $this->trigger(get_string('expectingaclosingbracket', 'mathslib'));             $output[] = $op;
        }
        return $output;
    }

        function pfx($tokens, $vars = array()) {

        if ($tokens == false) return false;

        $stack = new EvalMathStack;

        foreach ($tokens as $token) { 
                        if (is_array($token)) {                 $fnn = $token['fnn'];
                $count = $token['argcount'];
                if (in_array($fnn, $this->fb)) {                     if (is_null($op1 = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                    $fnn = preg_replace("/^arc/", "a", $fnn);                     if ($fnn == 'ln') $fnn = 'log';
                    eval('$stack->push(' . $fnn . '($op1));');                 } elseif (array_key_exists($fnn, $this->fc)) {                                         $args = array();
                    for ($i = $count-1; $i >= 0; $i--) {
                        if (is_null($args[] = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $res = call_user_func_array(array('EvalMathFuncs', $fnn), array_reverse($args));
                    if ($res === FALSE) {
                        return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $stack->push($res);
                } elseif (array_key_exists($fnn, $this->f)) {                                         $args = array();
                    for ($i = count($this->f[$fnn]['args'])-1; $i >= 0; $i--) {
                        if (is_null($args[$this->f[$fnn]['args'][$i]] = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $stack->push($this->pfx($this->f[$fnn]['func'], $args));                 }
                        } elseif (in_array($token, array('+', '-', '*', '/', '^'), true)) {
                if (is_null($op2 = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                if (is_null($op1 = $stack->pop())) return $this->trigger(get_string('internalerror', 'mathslib'));
                switch ($token) {
                    case '+':
                        $stack->push($op1+$op2); break;
                    case '-':
                        $stack->push($op1-$op2); break;
                    case '*':
                        $stack->push($op1*$op2); break;
                    case '/':
                        if ($op2 == 0) return $this->trigger(get_string('divisionbyzero', 'mathslib'));
                        $stack->push($op1/$op2); break;
                    case '^':
                        $stack->push(pow($op1, $op2)); break;
                }
                        } elseif ($token == "_") {
                $stack->push(-1*$stack->pop());
                        } else {
                if (is_numeric($token)) {
                    $stack->push($token);
                } elseif (array_key_exists($token, $this->v)) {
                    $stack->push($this->v[$token]);
                } elseif (array_key_exists($token, $vars)) {
                    $stack->push($vars[$token]);
                } else {
                    return $this->trigger(get_string('undefinedvariable', 'mathslib', $token));
                }
            }
        }
                if ($stack->count != 1) return $this->trigger(get_string('internalerror', 'mathslib'));
        return $stack->pop();
    }

        function trigger($msg) {
        $this->last_error = $msg;
        if (!$this->suppress_errors) trigger_error($msg, E_USER_WARNING);
        return false;
    }

}

class EvalMathStack {

    var $stack = array();
    var $count = 0;

    function push($val) {
        $this->stack[$this->count] = $val;
        $this->count++;
    }

    function pop() {
        if ($this->count > 0) {
            $this->count--;
            return $this->stack[$this->count];
        }
        return null;
    }

    function last($n=1) {
        if ($this->count - $n >= 0) {
            return $this->stack[$this->count-$n];
        }
        return null;
    }
}


class EvalMathFuncs {

    static function average() {
        $args = func_get_args();
        return (call_user_func_array(array('self', 'sum'), $args) / count($args));
    }

    static function max() {
        $args = func_get_args();
        $res = array_pop($args);
        foreach($args as $a) {
            if ($res < $a) {
                $res = $a;
            }
        }
        return $res;
    }

    static function min() {
        $args = func_get_args();
        $res = array_pop($args);
        foreach($args as $a) {
            if ($res > $a) {
                $res = $a;
            }
        }
        return $res;
    }

    static function mod($op1, $op2) {
        return $op1 % $op2;
    }

    static function pi() {
        return pi();
    }

    static function power($op1, $op2) {
        return pow($op1, $op2);
    }

    static function round($val, $precision = 0) {
        return round($val, $precision);
    }

    static function sum() {
        $args = func_get_args();
        $res = 0;
        foreach($args as $a) {
           $res += $a;
        }
        return $res;
    }

    protected static $randomseed = null;

    static function set_random_seed($randomseed) {
        self::$randomseed = $randomseed;
    }

    static function get_random_seed() {
        if (is_null(self::$randomseed)){
            return microtime();
        } else {
            return self::$randomseed;
        }
    }

    static function rand_int($min, $max){
        if ($min >= $max) {
            return false;         }
        $noofchars = ceil(log($max + 1 - $min, '16'));
        $md5string = md5(self::get_random_seed());
        $stringoffset = 0;
        do {
            while (($stringoffset + $noofchars) > strlen($md5string)){
                $md5string .= md5($md5string);
            }
            $randomno = hexdec(substr($md5string, $stringoffset, $noofchars));
            $stringoffset += $noofchars;
        } while (($min + $randomno) > $max);
        return $min + $randomno;
    }

    static function rand_float() {
        $randomvalues = unpack('v', md5(self::get_random_seed(), true));
        return array_shift($randomvalues) / 65536;
    }
}
