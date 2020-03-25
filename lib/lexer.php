<?php



    
    define("LEXER_ENTER", 1);
    
    define("LEXER_MATCHED", 2);
    
    define("LEXER_UNMATCHED", 3);
    
    define("LEXER_EXIT", 4);
    
    define("LEXER_SPECIAL", 5);
    
    
    class ParallelRegex {
        var $_patterns;
        var $_labels;
        var $_regex;
        var $_case;
        
        
        public function __construct($case) {
            $this->_case = $case;
            $this->_patterns = array();
            $this->_labels = array();
            $this->_regex = null;
        }

        
        public function ParallelRegex($case) {
            debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
            self::__construct($case);
        }

        
        function addPattern($pattern, $label = true) {
            $count = count($this->_patterns);
            $this->_patterns[$count] = $pattern;
            $this->_labels[$count] = $label;
            $this->_regex = null;
        }
        
        
        function match($subject, &$match) {
            if (count($this->_patterns) == 0) {
                return false;
            }
            if (!preg_match($this->_getCompoundedRegex(), $subject, $matches)) {
                $match = "";
                return false;
            }
            $match = $matches[0];
            for ($i = 1; $i < count($matches); $i++) {
                if ($matches[$i]) {
                    return $this->_labels[$i - 1];
                }
            }
            return true;
        }
        
        
        function _getCompoundedRegex() {
            if ($this->_regex == null) {
                for ($i = 0; $i < count($this->_patterns); $i++) {
                    $this->_patterns[$i] = '(' . str_replace(
                            array('/', '(', ')'),
                            array('\/', '\(', '\)'),
                            $this->_patterns[$i]) . ')';
                }
                $this->_regex = "/" . implode("|", $this->_patterns) . "/" . $this->_getPerlMatchingFlags();
            }
            return $this->_regex;
        }
        
        
        function _getPerlMatchingFlags() {
            return ($this->_case ? "msS" : "msSi");
        }
    }
    
    
    class StateStack {
        var $_stack;
        
        
        public function __construct($start) {
            $this->_stack = array($start);
        }

        
        public function StateStack($start) {
            debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
            self::__construct($start);
        }

        
        function getCurrent() {
            return $this->_stack[count($this->_stack) - 1];
        }
        
        
        function enter($state) {
            array_push($this->_stack, $state);
        }
        
        
        function leave() {
            if (count($this->_stack) == 1) {
                return false;
            }
            array_pop($this->_stack);
            return true;
        }
    }
    
    
    class Lexer {
        var $_regexes;
        var $_parser;
        var $_mode;
        var $_mode_handlers;
        var $_case;
        
        
        public function __construct(&$parser, $start = "accept", $case = false) {
            $this->_case = $case;
            $this->_regexes = array();
            $this->_parser = &$parser;
            $this->_mode = new StateStack($start);
            $this->_mode_handlers = array();
        }

        
        public function Lexer(&$parser, $start = "accept", $case = false) {
            debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
            self::__construct($parser, $start, $case);
        }
        
        
        function addPattern($pattern, $mode = "accept") {
            if (!isset($this->_regexes[$mode])) {
                $this->_regexes[$mode] = new ParallelRegex($this->_case);
            }
            $this->_regexes[$mode]->addPattern($pattern);
        }
        
        
        function addEntryPattern($pattern, $mode, $new_mode) {
            if (!isset($this->_regexes[$mode])) {
                $this->_regexes[$mode] = new ParallelRegex($this->_case);
            }
            $this->_regexes[$mode]->addPattern($pattern, $new_mode);
        }
        
        
        function addExitPattern($pattern, $mode) {
            if (!isset($this->_regexes[$mode])) {
                $this->_regexes[$mode] = new ParallelRegex($this->_case);
            }
            $this->_regexes[$mode]->addPattern($pattern, "__exit");
        }
        
        
        function addSpecialPattern($pattern, $mode, $special) {
            if (!isset($this->_regexes[$mode])) {
                $this->_regexes[$mode] = new ParallelRegex($this->_case);
            }
            $this->_regexes[$mode]->addPattern($pattern, "_$special");
        }
        
        
        function mapHandler($mode, $handler) {
            $this->_mode_handlers[$mode] = $handler;
        }
        
        
        function parse($raw) {
            if (!isset($this->_parser)) {
                return false;
            }
            $length = strlen($raw);
            while (is_array($parsed = $this->_reduce($raw))) {
                list($unmatched, $matched, $mode) = $parsed;
                if (!$this->_dispatchTokens($unmatched, $matched, $mode)) {
                    return false;
                }
                if (strlen($raw) == $length) {
                    return false;
                }
                $length = strlen($raw);
            }
            if (!$parsed) {
                return false;
            }
            return $this->_invokeParser($raw, LEXER_UNMATCHED);
        }
        
        
        function _dispatchTokens($unmatched, $matched, $mode = false) {
            if (!$this->_invokeParser($unmatched, LEXER_UNMATCHED)) {
                return false;
            }
            if ($mode === "__exit") {
                if (!$this->_invokeParser($matched, LEXER_EXIT)) {
                    return false;
                }
                return $this->_mode->leave();
            }
            if (strncmp($mode, "_", 1) == 0) {
                $mode = substr($mode, 1);
                $this->_mode->enter($mode);
                if (!$this->_invokeParser($matched, LEXER_SPECIAL)) {
                    return false;
                }
                return $this->_mode->leave();
            }
            if (is_string($mode)) {
                $this->_mode->enter($mode);
                return $this->_invokeParser($matched, LEXER_ENTER);
            }
            return $this->_invokeParser($matched, LEXER_MATCHED);
        }
        
        
        function _invokeParser($content, $is_match) {
            if (($content === "") || ($content === false)) {
                return true;
            }
            $handler = $this->_mode->getCurrent();
            if (isset($this->_mode_handlers[$handler])) {
                $handler = $this->_mode_handlers[$handler];
            }
            return $this->_parser->$handler($content, $is_match);
        }
        
        
        function _reduce(&$raw) {
            if (!isset($this->_regexes[$this->_mode->getCurrent()])) {
                return false;
            }
            if ($raw === "") {
                return true;
            }
            if ($action = $this->_regexes[$this->_mode->getCurrent()]->match($raw, $match)) {
                $count = strpos($raw, $match);
                $unparsed = substr($raw, 0, $count);
                $raw = substr($raw, $count + strlen($match));
                return array($unparsed, $match, $action);
            }
            return true;
        }
    }
?>
