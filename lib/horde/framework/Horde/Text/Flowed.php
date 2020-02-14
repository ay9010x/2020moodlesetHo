<?php

class Horde_Text_Flowed
{
    
    protected $_maxlength = 78;

    
    protected $_optlength = 72;

    
    protected $_text;

    
    protected $_output = array();

    
    protected $_formattype = null;

    
    protected $_charset;

    
    protected $_delsp = false;

    
    public function __construct($text, $charset = 'UTF-8')
    {
        $this->_text = $text;
        $this->_charset = $charset;
    }

    
    public function setMaxLength($max)
    {
        $this->_maxlength = $max;
    }

    
    public function setOptLength($opt)
    {
        $this->_optlength = $opt;
    }

    
    public function setDelSp($delsp)
    {
        $this->_delsp = (bool)$delsp;
    }

    
    public function toFixed($quote = false)
    {
        $txt = '';

        $this->_reformat(false, $quote);
        reset($this->_output);
        $lines = count($this->_output) - 1;
        while (list($no, $line) = each($this->_output)) {
            $txt .= $line['text'] . (($lines == $no) ? '' : "\n");
        }

        return $txt;
    }

    
    public function toFixedArray($quote = false)
    {
        $this->_reformat(false, $quote);
        return $this->_output;
    }

    
    public function toFlowed($quote = false, array $opts = array())
    {
        $txt = '';

        $this->_reformat(true, $quote, empty($opts['nowrap']));
        reset($this->_output);
        while (list(,$line) = each($this->_output)) {
            $txt .= $line['text'] . "\n";
        }

        return $txt;
    }

    
    protected function _reformat($toflowed, $quote, $wrap = true)
    {
        $format_type = implode('|', array($toflowed, $quote));
        if ($format_type == $this->_formattype) {
            return;
        }

        $this->_output = array();
        $this->_formattype = $format_type;

        
        $delsp = ($toflowed && $this->_delsp) ? 1 : 0;
        $opt = $this->_optlength - 1 - $delsp;

        
        $text = preg_split("/\r?\n/", $this->_text);
        $text_count = count($text) - 1;
        $skip = 0;
        reset($text);

        while (list($no, $line) = each($text)) {
            if ($skip) {
                --$skip;
                continue;
            }

            

            
            
            if (($num_quotes = $this->_numquotes($line))) {
                $line = substr($line, $num_quotes);
            }

            
            if (!$toflowed || $num_quotes) {
                
                $line = $this->_unstuff($line);

                
                if ($line != '-- ') {
                    while (!empty($line) &&
                           (substr($line, -1) == ' ') &&
                           ($text_count != $no) &&
                           ($this->_numquotes($text[$no + 1]) == $num_quotes)) {
                        
                        if (!$toflowed && $this->_delsp) {
                            $line = substr($line, 0, -1);
                        }
                        $line .= $this->_unstuff(substr($text[++$no], $num_quotes));
                        ++$skip;
                    }
                }
            }

            
            if ($line != '-- ') {
                $line = rtrim($line);
            }

            
            if ($quote) {
                $num_quotes++;
            }

            
            $quotestr = str_repeat('>', $num_quotes);

            if (empty($line)) {
                
                $this->_output[] = array('text' => $quotestr, 'level' => $num_quotes);
            } elseif ((!$wrap && !$num_quotes) ||
                      empty($this->_maxlength) ||
                      ((Horde_String::length($line, $this->_charset) + $num_quotes) <= $this->_maxlength)) {
                
                $this->_output[] = array('text' => $quotestr . $this->_stuff($line, $num_quotes, $toflowed), 'level' => $num_quotes);
            } else {
                $min = $num_quotes + 1;

                
                while ($line) {
                    
                    $line = $quotestr . $this->_stuff($line, $num_quotes, $toflowed);
                    $line_length = Horde_String::length($line, $this->_charset);
                    if ($line_length <= $this->_optlength) {
                        
                        $this->_output[] = array('text' => $line, 'level' => $num_quotes);
                        break;
                    } else {
                        $regex = array();
                        if ($min <= $opt) {
                            $regex[] = '^(.{' . $min . ',' . $opt . '}) (.*)';
                        }
                        if ($min <= $this->_maxlength) {
                            $regex[] = '^(.{' . $min . ',' . $this->_maxlength . '}) (.*)';
                        }
                        $regex[] = '^(.{' . $min . ',})? (.*)';

                        if ($m = Horde_String::regexMatch($line, $regex, $this->_charset)) {
                            
                            if (empty($m[1])) {
                                $m[1] = $m[2];
                                $m[2] = '';
                            }
                            $this->_output[] = array('text' => $m[1] . ' ' . (($delsp) ? ' ' : ''), 'level' => $num_quotes);
                            $line = $m[2];
                        } elseif ($line_length > 998) {
                            
                            $this->_output[] = array('text' => Horde_String::substr($line, 0, 998, $this->_charset), 'level' => $num_quotes);
                            $line = Horde_String::substr($line, 998, null, $this->_charset);
                        } else {
                            $this->_output[] = array('text' => $line, 'level' => $num_quotes);
                            break;
                        }
                    }
                }
            }
        }
    }

    
    protected function _numquotes($text)
    {
        return strspn($text, '>');
    }

    
    protected function _stuff($text, $num_quotes, $toflowed)
    {
        return ($toflowed && ($num_quotes || preg_match("/^(?: |>|From |From$)/", $text)))
            ? ' ' . $text
            : $text;
    }

    
    protected function _unstuff($text)
    {
        return (!empty($text) && ($text[0] == ' '))
            ? substr($text, 1)
            : $text;
    }

}
