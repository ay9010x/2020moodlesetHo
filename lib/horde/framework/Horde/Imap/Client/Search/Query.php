<?php



class Horde_Imap_Client_Search_Query implements Serializable
{
    
    const VERSION = 3;

    
    const DATE_BEFORE = 'BEFORE';
    const DATE_ON = 'ON';
    const DATE_SINCE = 'SINCE';

    
    const INTERVAL_OLDER = 'OLDER';
    const INTERVAL_YOUNGER = 'YOUNGER';

    
    protected $_charset = null;

    
    protected $_search = array();

    
    public function __toString()
    {
        try {
            $res = $this->build(null);
            return $res['query']->escape();
        } catch (Exception $e) {
            return '';
        }
    }

    
    public function charset($charset, $convert = true)
    {
        $oldcharset = $this->_charset;
        $this->_charset = strtoupper($charset);

        if (!$convert || ($oldcharset == $this->_charset)) {
            return;
        }

        foreach (array('header', 'text') as $item) {
            if (isset($this->_search[$item])) {
                foreach ($this->_search[$item] as $key => $val) {
                    $new_val = Horde_String::convertCharset($val['text'], $oldcharset, $this->_charset);
                    if (Horde_String::convertCharset($new_val, $this->_charset, $oldcharset) != $val['text']) {
                        throw new Horde_Imap_Client_Exception_SearchCharset($this->_charset);
                    }
                    $this->_search[$item][$key]['text'] = $new_val;
                }
            }
        }
    }

    
    public function build($exts = array())
    {
        $temp = array(
            'cmds' => new Horde_Imap_Client_Data_Format_List(),
            'exts' => $exts,
            'exts_used' => array()
        );
        $cmds = &$temp['cmds'];
        $charset = null;
        $exts_used = &$temp['exts_used'];
        $ptr = &$this->_search;

        if (isset($ptr['new'])) {
            $this->_addFuzzy(!empty($ptr['newfuzzy']), $temp);
            if ($ptr['new']) {
                $cmds->add('NEW');
                unset($ptr['flag']['UNSEEN']);
            } else {
                $cmds->add('OLD');
            }
            unset($ptr['flag']['RECENT']);
        }

        if (!empty($ptr['flag'])) {
            foreach ($ptr['flag'] as $key => $val) {
                $this->_addFuzzy(!empty($val['fuzzy']), $temp);

                $tmp = '';
                if (empty($val['set'])) {
                                                            if ($key == 'RECENT') {
                        $cmds->add('NOT');
                    } else {
                        $tmp = 'UN';
                    }
                }

                if ($val['type'] == 'keyword') {
                    $cmds->add(array(
                        $tmp . 'KEYWORD',
                        $key
                    ));
                } else {
                    $cmds->add($tmp . $key);
                }
            }
        }

        if (!empty($ptr['header'])) {
            
            $systemheaders = array(
                'BCC', 'CC', 'FROM', 'SUBJECT', 'TO'
            );

            foreach ($ptr['header'] as $val) {
                $this->_addFuzzy(!empty($val['fuzzy']), $temp);

                if (!empty($val['not'])) {
                    $cmds->add('NOT');
                }

                if (in_array($val['header'], $systemheaders)) {
                    $cmds->add($val['header']);
                } else {
                    $cmds->add(array(
                        'HEADER',
                        new Horde_Imap_Client_Data_Format_Astring($val['header'])
                    ));
                }
                $cmds->add(new Horde_Imap_Client_Data_Format_Astring(isset($val['text']) ? $val['text'] : ''));
                $charset = is_null($this->_charset)
                    ? 'US-ASCII'
                    : $this->_charset;
            }
        }

        if (!empty($ptr['text'])) {
            foreach ($ptr['text'] as $val) {
                $this->_addFuzzy(!empty($val['fuzzy']), $temp);

                if (!empty($val['not'])) {
                    $cmds->add('NOT');
                }
                $cmds->add(array(
                    $val['type'],
                    new Horde_Imap_Client_Data_Format_Astring($val['text'])
                ));
                if (is_null($charset)) {
                    $charset = is_null($this->_charset)
                        ? 'US-ASCII'
                        : $this->_charset;
                }
            }
        }

        if (!empty($ptr['size'])) {
            foreach ($ptr['size'] as $key => $val) {
                $this->_addFuzzy(!empty($val['fuzzy']), $temp);
                if (!empty($val['not'])) {
                    $cmds->add('NOT');
                }
                $cmds->add(array(
                    $key,
                    new Horde_Imap_Client_Data_Format_Number($val['size'])
                ));
            }
        }

        if (isset($ptr['ids']) &&
            (count($ptr['ids']['ids']) || $ptr['ids']['ids']->special)) {
            $this->_addFuzzy(!empty($ptr['ids']['fuzzy']), $temp);
            if (!empty($ptr['ids']['not'])) {
                $cmds->add('NOT');
            }
            if (!$ptr['ids']['ids']->sequence) {
                $cmds->add('UID');
            }
            $cmds->add(strval($ptr['ids']['ids']));
        }

        if (!empty($ptr['date'])) {
            foreach ($ptr['date'] as $val) {
                $this->_addFuzzy(!empty($val['fuzzy']), $temp);

                if (!empty($val['not'])) {
                    $cmds->add('NOT');
                }

                if (empty($val['header'])) {
                    $cmds->add($val['range']);
                } else {
                    $cmds->add('SENT' . $val['range']);
                }
                $cmds->add($val['date']);
            }
        }

        if (!empty($ptr['within'])) {
            if (is_null($exts) || isset($exts['WITHIN'])) {
                $exts_used[] = 'WITHIN';
            }

            foreach ($ptr['within'] as $key => $val) {
                $this->_addFuzzy(!empty($val['fuzzy']), $temp);
                if (!empty($val['not'])) {
                    $cmds->add('NOT');
                }

                if (is_null($exts) || isset($exts['WITHIN'])) {
                    $cmds->add(array(
                        $key,
                        new Horde_Imap_Client_Data_Format_Number($val['interval'])
                    ));
                } else {
                                                            $cmds->add(array(
                        ($key == self::INTERVAL_OLDER) ? self::DATE_BEFORE : self::DATE_SINCE,
                        new Horde_Imap_Client_Data_Format_Date('now -' . $val['interval'] . ' seconds')
                    ));
                }
            }
        }

        if (!empty($ptr['modseq'])) {
            if (!is_null($exts) && !isset($exts['CONDSTORE'])) {
                throw new Horde_Imap_Client_Exception_NoSupportExtension('CONDSTORE');
            }

            $exts_used[] = 'CONDSTORE';

            $this->_addFuzzy(!empty($ptr['modseq']['fuzzy']), $temp);

            if (!empty($ptr['modseq']['not'])) {
                $cmds->add('NOT');
            }
            $cmds->add('MODSEQ');
            if (isset($ptr['modseq']['name'])) {
                $cmds->add(array(
                    new Horde_Imap_Client_Data_Format_String($ptr['modseq']['name']),
                    $ptr['modseq']['type']
                ));
            }
            $cmds->add(new Horde_Imap_Client_Data_Format_Number($ptr['modseq']['value']));
        }

        if (isset($ptr['prevsearch'])) {
            if (!is_null($exts) && !isset($exts['SEARCHRES'])) {
                throw new Horde_Imap_Client_Exception_NoSupportExtension('SEARCHRES');
            }

            $exts_used[] = 'SEARCHRES';

            $this->_addFuzzy(!empty($ptr['prevsearchfuzzy']), $temp);

            if (!$ptr['prevsearch']) {
                $cmds->add('NOT');
            }
            $cmds->add('$');
        }

                if (!empty($ptr['and'])) {
            foreach ($ptr['and'] as $val) {
                $ret = $val->build();
                if ($ret['charset'] != 'US-ASCII') {
                    $charset = $ret['charset'];
                }
                $exts_used = array_merge($exts_used, $ret['exts']);
                $cmds->add($ret['query'], true);
            }
        }

                if (!empty($ptr['or'])) {
            foreach ($ptr['or'] as $val) {
                $ret = $val->build();

                if ($ret['charset'] != 'US-ASCII') {
                    $charset = $ret['charset'];
                }
                $exts_used = array_merge($exts_used, $ret['exts']);

                                if (count($cmds)) {
                    $new_cmds = new Horde_Imap_Client_Data_Format_List();
                    $new_cmds->add(array(
                        'OR',
                        $ret['query'],
                        $cmds
                    ));
                    $cmds = $new_cmds;
                } else {
                    $cmds = $ret['query'];
                }
            }
        }

                if (!count($cmds)) {
            $cmds->add('ALL');
        }

        return array(
            'charset' => $charset,
            'exts' => array_keys(array_flip($exts_used)),
            'query' => $cmds
        );
    }

    
    protected function _addFuzzy($add, &$temp)
    {
        if ($add) {
            if (!isset($temp['exts']['SEARCH']) ||
                !in_array('FUZZY', $temp['exts']['SEARCH'])) {
                throw new Horde_Imap_Client_Exception_NoSupportExtension('SEARCH=FUZZY');
            }
            $temp['cmds']->add('FUZZY');
            $temp['exts_used'][] = 'SEARCH=FUZZY';
        }
    }

    
    public function flag($name, $set = true, array $opts = array())
    {
        $name = strtoupper(ltrim($name, '\\'));
        if (!isset($this->_search['flag'])) {
            $this->_search['flag'] = array();
        }

        
        $systemflags = array(
            'ANSWERED', 'DELETED', 'DRAFT', 'FLAGGED', 'RECENT', 'SEEN'
        );

        $this->_search['flag'][$name] = array_filter(array(
            'fuzzy' => !empty($opts['fuzzy']),
            'set' => $set,
            'type' => in_array($name, $systemflags) ? 'flag' : 'keyword'
        ));
    }

    
    public function flagSearch()
    {
        return !empty($this->_search['flag']);
    }

    
    public function newMsgs($newmsgs = true, array $opts = array())
    {
        $this->_search['new'] = $newmsgs;
        if (!empty($opts['fuzzy'])) {
            $this->_search['newfuzzy'] = true;
        }
    }

    
    public function headerText($header, $text, $not = false,
                                array $opts = array())
    {
        if (!isset($this->_search['header'])) {
            $this->_search['header'] = array();
        }
        $this->_search['header'][] = array_filter(array(
            'fuzzy' => !empty($opts['fuzzy']),
            'header' => strtoupper($header),
            'text' => $text,
            'not' => $not
        ));
    }

    
    public function text($text, $bodyonly = true, $not = false,
                         array $opts = array())
    {
        if (!isset($this->_search['text'])) {
            $this->_search['text'] = array();
        }

        $this->_search['text'][] = array_filter(array(
            'fuzzy' => !empty($opts['fuzzy']),
            'not' => $not,
            'text' => $text,
            'type' => $bodyonly ? 'BODY' : 'TEXT'
        ));
    }

    
    public function size($size, $larger = false, $not = false,
                         array $opts = array())
    {
        if (!isset($this->_search['size'])) {
            $this->_search['size'] = array();
        }
        $this->_search['size'][$larger ? 'LARGER' : 'SMALLER'] = array_filter(array(
            'fuzzy' => !empty($opts['fuzzy']),
            'not' => $not,
            'size' => (float)$size
        ));
    }

    
    public function ids(Horde_Imap_Client_Ids $ids, $not = false,
                        array $opts = array())
    {
        if (!$ids->isEmpty()) {
            $this->_search['ids'] = array_filter(array(
                'fuzzy' => !empty($opts['fuzzy']),
                'ids' => $ids,
                'not' => $not
            ));
        }
    }

    
    public function dateSearch($date, $range, $header = true, $not = false,
                               array $opts = array())
    {
        if (!isset($this->_search['date'])) {
            $this->_search['date'] = array();
        }

                        $ob = new Horde_Imap_Client_Data_Format_Date($date);

        $this->_search['date'][] = array_filter(array(
            'date' => $ob->escape(),
            'fuzzy' => !empty($opts['fuzzy']),
            'header' => $header,
            'range' => $range,
            'not' => $not
        ));
    }

    
    public function intervalSearch($interval, $range, $not = false,
                                   array $opts = array())
    {
        if (!isset($this->_search['within'])) {
            $this->_search['within'] = array();
        }
        $this->_search['within'][$range] = array(
            'fuzzy' => !empty($opts['fuzzy']),
            'interval' => $interval,
            'not' => $not
        );
    }

    
    public function andSearch($queries)
    {
        if (!isset($this->_search['and'])) {
            $this->_search['and'] = array();
        }

        if ($queries instanceof Horde_Imap_Client_Search_Query) {
            $queries = array($queries);
        }

        $this->_search['and'] = array_merge($this->_search['and'], $queries);
    }

    
    public function orSearch($queries)
    {
        if (!isset($this->_search['or'])) {
            $this->_search['or'] = array();
        }

        if ($queries instanceof Horde_Imap_Client_Search_Query) {
            $queries = array($queries);
        }

        $this->_search['or'] = array_merge($this->_search['or'], $queries);
    }

    
    public function modseq($value, $name = null, $type = null, $not = false,
                           array $opts = array())
    {
        if (!is_null($type)) {
            $type = strtolower($type);
            if (!in_array($type, array('shared', 'priv', 'all'))) {
                $type = 'all';
            }
        }

        $this->_search['modseq'] = array_filter(array(
            'fuzzy' => !empty($opts['fuzzy']),
            'name' => $name,
            'not' => $not,
            'type' => (!is_null($name) && is_null($type)) ? 'all' : $type,
            'value' => $value
        ));
    }

    
    public function previousSearch($not = false, array $opts = array())
    {
        $this->_search['prevsearch'] = $not;
        if (!empty($opts['fuzzy'])) {
            $this->_search['prevsearchfuzzy'] = true;
        }
    }

    

    
    public function serialize()
    {
        $data = array(
                        self::VERSION,
            $this->_search
        );

        if (!is_null($this->_charset)) {
            $data[] = $this->_charset;
        }

        return serialize($data);
    }

    
    public function unserialize($data)
    {
        $data = @unserialize($data);
        if (!is_array($data) ||
            !isset($data[0]) ||
            ($data[0] != self::VERSION)) {
            throw new Exception('Cache version change');
        }

        $this->_search = $data[1];
        if (isset($data[2])) {
            $this->_charset = $data[2];
        }
    }

}
