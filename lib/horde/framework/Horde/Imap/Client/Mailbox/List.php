<?php



class Horde_Imap_Client_Mailbox_List implements Countable, IteratorAggregate
{
    
    protected $_delimiter;

    
    protected $_mboxes = array();

    
    protected $_sortinbox;

    
    public function __construct($mboxes)
    {
        $this->_mboxes = is_array($mboxes)
            ? $mboxes
            : array($mboxes);
    }

    
    public function sort(array $opts = array())
    {
        $this->_delimiter = isset($opts['delimiter'])
            ? $opts['delimiter']
            : '.';
        $this->_sortinbox = (!isset($opts['inbox']) || !empty($opts['inbox']));

        if (empty($opts['noupdate'])) {
            $mboxes = &$this->_mboxes;
        } else {
            $mboxes = $this->_mboxes;
        }

        uasort($mboxes, array($this, '_mboxCompare'));

        return $mboxes;
    }

    
    protected final function _mboxCompare($a, $b)
    {
        
        if ($this->_sortinbox) {
            if (strcasecmp($a, 'INBOX') === 0) {
                return -1;
            } elseif (strcasecmp($b, 'INBOX') === 0) {
                return 1;
            }
        }

        $a_parts = explode($this->_delimiter, $a);
        $b_parts = explode($this->_delimiter, $b);

        $a_count = count($a_parts);
        $b_count = count($b_parts);

        for ($i = 0, $iMax = min($a_count, $b_count); $i < $iMax; ++$i) {
            if ($a_parts[$i] != $b_parts[$i]) {
                
                if ($this->_sortinbox && ($i === 0)) {
                    $a_base = (strcasecmp($a_parts[0], 'INBOX') === 0);
                    $b_base = (strcasecmp($b_parts[0], 'INBOX') === 0);
                    if ($a_base && !$b_base) {
                        return -1;
                    } elseif (!$a_base && $b_base) {
                        return 1;
                    }
                }

                $cmp = strnatcasecmp($a_parts[$i], $b_parts[$i]);
                return ($cmp === 0)
                    ? strcmp($a_parts[$i], $b_parts[$i])
                    : $cmp;
            } elseif ($a_parts[$i] !== $b_parts[$i]) {
                return strlen($a_parts[$i]) - strlen($b_parts[$i]);
            }
        }

        return ($a_count - $b_count);
    }

    

    
    public function count()
    {
        return count($this->_mboxes);
    }

    

    
    public function getIterator()
    {
        return new ArrayIterator($this->_mboxes);
    }

}
