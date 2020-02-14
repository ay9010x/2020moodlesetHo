<?php



class Horde_Imap_Client_Socket_ClientSort
{
    
    protected $_socket;

    
    public function __construct(Horde_Imap_Client_Socket $socket)
    {
        $this->_socket = $socket;
    }

    
    public function clientSort($res, $opts)
    {
        if (!count($res)) {
            return $res;
        }

        
        $query = new Horde_Imap_Client_Fetch_Query();

        foreach ($opts['sort'] as $val) {
            switch ($val) {
            case Horde_Imap_Client::SORT_ARRIVAL:
                $query->imapDate();
                break;

            case Horde_Imap_Client::SORT_DATE:
                $query->imapDate();
                $query->envelope();
                break;

            case Horde_Imap_Client::SORT_CC:
            case Horde_Imap_Client::SORT_DISPLAYFROM:
            case Horde_Imap_Client::SORT_DISPLAYTO:
            case Horde_Imap_Client::SORT_FROM:
            case Horde_Imap_Client::SORT_SUBJECT:
            case Horde_Imap_Client::SORT_TO:
                $query->envelope();
                break;

            case Horde_Imap_Client::SORT_SIZE:
                $query->size();
                break;
            }
        }

        if (!count($query)) {
            return $res;
        }

        $mbox = $this->_socket->currentMailbox();
        $fetch_res = $this->_socket->fetch($mbox['mailbox'], $query, array(
            'ids' => $res
        ));

        return $this->_clientSortProcess($res->ids, $fetch_res, $opts['sort']);
    }

    
    public function threadOrderedSubject(Horde_Imap_Client_Fetch_Results $data,
                                         $uids)
    {
        $dates = $this->_getSentDates($data, $data->ids());
        $out = $sorted = $tsort = array();

        foreach ($data as $k => $v) {
            $subject = strval(new Horde_Imap_Client_Data_BaseSubject($v->getEnvelope()->subject));
            $sorted[$subject][$k] = $dates[$k];
        }

        
        foreach (array_keys($sorted) as $key) {
            asort($sorted[$key], SORT_NUMERIC);
            $tsort[$key] = reset($sorted[$key]);
        }

        
        asort($tsort, SORT_NUMERIC);

        
        foreach (array_keys($tsort) as $key) {
            $keys = array_keys($sorted[$key]);
            $out[$keys[0]] = array(
                $keys[0] => 0
            ) + array_fill_keys(array_slice($keys, 1) , 1);
        }

        return new Horde_Imap_Client_Data_Thread($out, $uids ? 'uid' : 'sequence');
    }

    
    protected function _clientSortProcess($res, $fetch_res, $sort)
    {
        
        $slices = array(0 => $res);
        $reverse = false;

        foreach ($sort as $val) {
            if ($val == Horde_Imap_Client::SORT_REVERSE) {
                $reverse = true;
                continue;
            }

            $slices_list = $slices;
            $slices = array();

            foreach ($slices_list as $slice_start => $slice) {
                $sorted = array();

                if ($reverse) {
                    $slice = array_reverse($slice);
                }

                switch ($val) {
                case Horde_Imap_Client::SORT_SEQUENCE:
                    
                    $sorted = array_flip($slice);
                    ksort($sorted, SORT_NUMERIC);
                    break;

                case Horde_Imap_Client::SORT_SIZE:
                    foreach ($slice as $num) {
                        $sorted[$num] = $fetch_res[$num]->getSize();
                    }
                    asort($sorted, SORT_NUMERIC);
                    break;

                case Horde_Imap_Client::SORT_DISPLAYFROM:
                case Horde_Imap_Client::SORT_DISPLAYTO:
                    $field = ($val == Horde_Imap_Client::SORT_DISPLAYFROM)
                        ? 'from'
                        : 'to';

                    foreach ($slice as $num) {
                        $env = $fetch_res[$num]->getEnvelope();

                        if (empty($env->$field)) {
                            $sorted[$num] = null;
                        } else {
                            $addr_ob = reset($env->$field);
                            if (is_null($sorted[$num] = $addr_ob->personal)) {
                                $sorted[$num] = $addr_ob->mailbox;
                            }
                        }
                    }

                    asort($sorted, SORT_LOCALE_STRING);
                    break;

                case Horde_Imap_Client::SORT_CC:
                case Horde_Imap_Client::SORT_FROM:
                case Horde_Imap_Client::SORT_TO:
                    if ($val == Horde_Imap_Client::SORT_CC) {
                        $field = 'cc';
                    } elseif ($val == Horde_Imap_Client::SORT_FROM) {
                        $field = 'from';
                    } else {
                        $field = 'to';
                    }

                    foreach ($slice as $num) {
                        $tmp = $fetch_res[$num]->getEnvelope()->$field;
                        $sorted[$num] = count($tmp)
                            ? $tmp[0]->mailbox
                            : null;
                    }
                    asort($sorted, SORT_LOCALE_STRING);
                    break;

                case Horde_Imap_Client::SORT_ARRIVAL:
                    $sorted = $this->_getSentDates($fetch_res, $slice, true);
                    asort($sorted, SORT_NUMERIC);
                    break;

                case Horde_Imap_Client::SORT_DATE:
                                        $sorted = $this->_getSentDates($fetch_res, $slice);
                    asort($sorted, SORT_NUMERIC);
                    break;

                case Horde_Imap_Client::SORT_SUBJECT:
                                        foreach ($slice as $num) {
                        $sorted[$num] = strval(new Horde_Imap_Client_Data_BaseSubject($fetch_res[$num]->getEnvelope()->subject));
                    }
                    asort($sorted, SORT_LOCALE_STRING);
                    break;
                }

                                                if (!empty($sorted)) {
                    if (count($sorted) === count($res)) {
                        $res = array_keys($sorted);
                    } else {
                        array_splice($res, $slice_start, count($slice), array_keys($sorted));
                    }

                                        $last = $start = null;
                    $i = 0;
                    reset($sorted);
                    while (list($k, $v) = each($sorted)) {
                        if (is_null($last) || ($last != $v)) {
                            if ($i) {
                                $slices[array_search($start, $res)] = array_slice($sorted, array_search($start, $sorted), $i + 1);
                                $i = 0;
                            }
                            $last = $v;
                            $start = $k;
                        } else {
                            ++$i;
                        }
                    }
                    if ($i) {
                        $slices[array_search($start, $res)] = array_slice($sorted, array_search($start, $sorted), $i + 1);
                    }
                }
            }

            $reverse = false;
        }

        return $res;
    }

    
    protected function _getSentDates(Horde_Imap_Client_Fetch_Results $data,
                                     $ids, $internal = false)
    {
        $dates = array();

        foreach ($ids as $num) {
            $dt = ($internal || !isset($data[$num]->getEnvelope()->date))
                                                ? $data[$num]->getImapDate()
                : $data[$num]->getEnvelope()->date;
            $dates[$num] = $dt->format('U');
        }

        return $dates;
    }

}
