<?php


class HTMLPurifier_ErrorCollector
{

    
    const LINENO   = 0;
    const SEVERITY = 1;
    const MESSAGE  = 2;
    const CHILDREN = 3;

    
    protected $errors;

    
    protected $_current;

    
    protected $_stacks = array(array());

    
    protected $locale;

    
    protected $generator;

    
    protected $context;

    
    protected $lines = array();

    
    public function __construct($context)
    {
        $this->locale    =& $context->get('Locale');
        $this->context   = $context;
        $this->_current  =& $this->_stacks[0];
        $this->errors    =& $this->_stacks[0];
    }

    
    public function send($severity, $msg)
    {
        $args = array();
        if (func_num_args() > 2) {
            $args = func_get_args();
            array_shift($args);
            unset($args[0]);
        }

        $token = $this->context->get('CurrentToken', true);
        $line  = $token ? $token->line : $this->context->get('CurrentLine', true);
        $col   = $token ? $token->col  : $this->context->get('CurrentCol', true);
        $attr  = $this->context->get('CurrentAttr', true);

                $subst = array();
        if (!is_null($token)) {
            $args['CurrentToken'] = $token;
        }
        if (!is_null($attr)) {
            $subst['$CurrentAttr.Name'] = $attr;
            if (isset($token->attr[$attr])) {
                $subst['$CurrentAttr.Value'] = $token->attr[$attr];
            }
        }

        if (empty($args)) {
            $msg = $this->locale->getMessage($msg);
        } else {
            $msg = $this->locale->formatMessage($msg, $args);
        }

        if (!empty($subst)) {
            $msg = strtr($msg, $subst);
        }

                $error = array(
            self::LINENO   => $line,
            self::SEVERITY => $severity,
            self::MESSAGE  => $msg,
            self::CHILDREN => array()
        );
        $this->_current[] = $error;

                                        $new_struct = new HTMLPurifier_ErrorStruct();
        $new_struct->type = HTMLPurifier_ErrorStruct::TOKEN;
        if ($token) {
            $new_struct->value = clone $token;
        }
        if (is_int($line) && is_int($col)) {
            if (isset($this->lines[$line][$col])) {
                $struct = $this->lines[$line][$col];
            } else {
                $struct = $this->lines[$line][$col] = $new_struct;
            }
                        ksort($this->lines[$line], SORT_NUMERIC);
        } else {
            if (isset($this->lines[-1])) {
                $struct = $this->lines[-1];
            } else {
                $struct = $this->lines[-1] = $new_struct;
            }
        }
        ksort($this->lines, SORT_NUMERIC);

                if (!empty($attr)) {
            $struct = $struct->getChild(HTMLPurifier_ErrorStruct::ATTR, $attr);
            if (!$struct->value) {
                $struct->value = array($attr, 'PUT VALUE HERE');
            }
        }
        if (!empty($cssprop)) {
            $struct = $struct->getChild(HTMLPurifier_ErrorStruct::CSSPROP, $cssprop);
            if (!$struct->value) {
                                $struct->value = array($cssprop, 'PUT VALUE HERE');
            }
        }

                $struct->addError($severity, $msg);
    }

    
    public function getRaw()
    {
        return $this->errors;
    }

    
    public function getHTMLFormatted($config, $errors = null)
    {
        $ret = array();

        $this->generator = new HTMLPurifier_Generator($config, $this->context);
        if ($errors === null) {
            $errors = $this->errors;
        }

        
                foreach ($this->lines as $line => $col_array) {
            if ($line == -1) {
                continue;
            }
            foreach ($col_array as $col => $struct) {
                $this->_renderStruct($ret, $struct, $line, $col);
            }
        }
        if (isset($this->lines[-1])) {
            $this->_renderStruct($ret, $this->lines[-1]);
        }

        if (empty($errors)) {
            return '<p>' . $this->locale->getMessage('ErrorCollector: No errors') . '</p>';
        } else {
            return '<ul><li>' . implode('</li><li>', $ret) . '</li></ul>';
        }

    }

    private function _renderStruct(&$ret, $struct, $line = null, $col = null)
    {
        $stack = array($struct);
        $context_stack = array(array());
        while ($current = array_pop($stack)) {
            $context = array_pop($context_stack);
            foreach ($current->errors as $error) {
                list($severity, $msg) = $error;
                $string = '';
                $string .= '<div>';
                                $error = $this->locale->getErrorName($severity);
                $string .= "<span class=\"error e$severity\"><strong>$error</strong></span> ";
                if (!is_null($line) && !is_null($col)) {
                    $string .= "<em class=\"location\">Line $line, Column $col: </em> ";
                } else {
                    $string .= '<em class="location">End of Document: </em> ';
                }
                $string .= '<strong class="description">' . $this->generator->escape($msg) . '</strong> ';
                $string .= '</div>';
                                                                                                $ret[] = $string;
            }
            foreach ($current->children as $array) {
                $context[] = $current;
                $stack = array_merge($stack, array_reverse($array, true));
                for ($i = count($array); $i > 0; $i--) {
                    $context_stack[] = $context;
                }
            }
        }
    }
}

