<?php



class Horde_Imap_Client_Socket_Pop3 extends Horde_Imap_Client_Base
{
    
    protected $_defaultPorts = array(110, 995);

    
    protected $_deleted = array();

    
    protected $_fetchDataClass = 'Horde_Imap_Client_Data_Fetch_Pop3';

    
    protected function _initCache($current = false)
    {
        return parent::_initCache($current) &&
               $this->queryCapability('UIDL');
    }

    
    public function getIdsOb($ids = null, $sequence = false)
    {
        return new Horde_Imap_Client_Ids_Pop3($ids, $sequence);
    }

    
    protected function _capability()
    {
        $this->_connect();

        $capability = array();

        try {
            $res = $this->_sendLine('CAPA', array(
                'multiline' => 'array'
            ));

            foreach ($res['data'] as $val) {
                $prefix = explode(' ', $val);
                $capability[strtoupper($prefix[0])] = (count($prefix) > 1)
                    ? array_slice($prefix, 1)
                    : true;
            }
        } catch (Horde_Imap_Client_Exception $e) {
            $this->_temp['no_capa'] = true;

            
            $capability = array('USER' => true);

            
            if (!empty($this->_init['authmethod'])) {
                $this->_pop3Cache('uidl');
                if (empty($this->_temp['no_uidl'])) {
                    $capability['UIDL'] = true;
                }

                $this->_pop3Cache('top', 1);
                if (empty($this->_temp['no_top'])) {
                    $capability['TOP'] = true;
                }
            }
        }

        $this->_setInit('capability', $capability);
    }

    
    protected function _noop()
    {
        $this->_sendLine('NOOP');
    }

    
    protected function _getNamespaces()
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Namespaces');
    }

    
    public function alerts()
    {
        return array();
    }

    
    protected function _login()
    {
        
        if (is_null($this->getParam('password'))) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("No password provided."),
                Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED
            );
        }

        $this->_connect();

        $first_login = empty($this->_init['authmethod']);

                if (!$this->isSecureConnection()) {
            $secure = $this->getParam('secure');

            if (($secure === 'tls') || $secure === true) {
                                if ($first_login && !$this->queryCapability('STLS')) {
                    if ($secure === 'tls') {
                        throw new Horde_Imap_Client_Exception(
                            Horde_Imap_Client_Translation::r("Could not open secure connection to the POP3 server.") . ' ' . Horde_Imap_Client_Translation::r("Server does not support secure connections."),
                            Horde_Imap_Client_Exception::LOGIN_TLSFAILURE
                        );
                    } else {
                        $this->setParam('secure', false);
                    }
                } else {
                    $this->_sendLine('STLS');

                    $this->setParam('secure', 'tls');

                    if (!$this->_connection->startTls()) {
                        $this->logout();
                        throw new Horde_Imap_Client_Exception(
                            Horde_Imap_Client_Translation::r("Could not open secure connection to the POP3 server."),
                            Horde_Imap_Client_Exception::LOGIN_TLSFAILURE
                        );
                    }
                    $this->_debug->info('Successfully completed TLS negotiation.');
                }

                                $this->_setInit('capability');
            } else {
                $this->setParam('secure', false);
            }
        }

        if ($first_login) {
            
            $auth_mech = (($sasl = $this->queryCapability('SASL')) && is_array($sasl))
                ? $sasl
                : array();

            if (isset($this->_temp['pop3timestamp'])) {
                $auth_mech[] = 'APOP';
            }

            $auth_mech[] = 'USER';
        } else {
            $auth_mech = array($this->_init['authmethod']);
        }

        foreach ($auth_mech as $method) {
            try {
                $this->_tryLogin($method);
                $this->_setInit('authmethod', $method);

                if (!empty($this->_temp['no_capa']) ||
                    !$this->queryCapability('UIDL')) {
                    $this->_capability();
                }

                return true;
            } catch (Horde_Imap_Client_Exception $e) {
                if (!empty($this->_init['authmethod']) &&
                    ($e->getCode() != $e::LOGIN_UNAVAILABLE) &&
                    ($e->getCode() != $e::POP3_TEMP_ERROR)) {
                    $this->_setInit();
                    return $this->login();
                }
            }
        }

        throw new Horde_Imap_Client_Exception(
            Horde_Imap_Client_Translation::r("POP3 server denied authentication."),
            $e->getCode() ?: $e::LOGIN_AUTHENTICATIONFAILED
        );
    }

    
    protected function _connect()
    {
        if (!is_null($this->_connection)) {
            return;
        }

        try {
            $this->_connection = new Horde_Imap_Client_Socket_Connection_Pop3(
                $this->getParam('hostspec'),
                $this->getParam('port'),
                $this->getParam('timeout'),
                $this->getParam('secure'),
                array(
                    'debug' => $this->_debug
                )
            );
        } catch (Horde\Socket\Client\Exception $e) {
            $e2 = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Error connecting to mail server."),
                Horde_Imap_Client_Exception::SERVER_CONNECT
            );
            $e2->details = $e->details;
            throw $e2;
        }

        $line = $this->_getResponse();

                if (preg_match('/<.+@.+>/U', $line['resp'], $matches)) {
            $this->_temp['pop3timestamp'] = $matches[0];
        }
    }

    
    protected function _tryLogin($method)
    {
        $username = $this->getParam('username');
        $password = $this->getParam('password');

        switch ($method) {
        case 'CRAM-MD5':
        case 'CRAM-SHA1':
        case 'CRAM-SHA256':
                                    $challenge = $this->_sendLine('AUTH ' . $method);
            $response = base64_encode($username . ' ' . hash_hmac(strtolower(substr($method, 5)), base64_decode(substr($challenge['resp'], 2)), $password, true));
            $this->_sendLine($response, array(
                'debug' => sprintf('[%s Response - username: %s]', $method, $username)
            ));
            break;

        case 'DIGEST-MD5':
                        $challenge = $this->_sendLine('AUTH DIGEST-MD5');
            $response = base64_encode(new Horde_Imap_Client_Auth_DigestMD5(
                $username,
                $password,
                base64_decode(substr($challenge['resp'], 2)),
                $this->getParam('hostspec'),
                'pop3'
            ));
            $sresponse = $this->_sendLine($response, array(
                'debug' => sprintf('[%s Response - username: %s]', $method, $username)
            ));
            if (stripos(base64_decode(substr($sresponse['resp'], 2)), 'rspauth=') === false) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Unexpected response from server when authenticating."),
                    Horde_Imap_Client_Exception::SERVER_CONNECT
                );
            }

            
            $this->_sendLine('');
            break;

        case 'LOGIN':
                        $this->_sendLine('AUTH LOGIN');
            $this->_sendLine(base64_encode($username), array(
                'debug' => sprintf('[AUTH LOGIN Command - username: %s]', $username)
            ));
            $this->_sendLine(base64_encode($password), array(
                'debug' => '[AUTH LOGIN Command - password]'
            ));
            break;

        case 'PLAIN':
                        $this->_sendLine('AUTH PLAIN ' . base64_encode(implode("\0", array(
                $username,
                $username,
                $password
            ))), array(
                'debug' => sprintf('[AUTH PLAIN Command - username: %s]', $username)
            ));
            break;

        case 'APOP':
                        $this->_sendLine('APOP ' . $username . ' ' . hash('md5', $this->_temp['pop3timestamp'] . $password));
            break;

        case 'USER':
                        $this->_sendLine('USER ' . $username);
            $this->_sendLine('PASS ' . $password, array(
                'debug' => '[USER Command - password]'
            ));
            break;

        default:
            throw new Horde_Imap_Client_Exception(
                sprintf(Horde_Imap_Client_Translation::r("Unknown authentication method: %s"), $method),
                Horde_Imap_Client_Exception::SERVER_CONNECT
            );
        }
    }

    
    protected function _logout()
    {
        try {
            $this->_sendLine('QUIT');
        } catch (Horde_Imap_Client_Exception $e) {}
        $this->_deleted = array();
    }

    
    protected function _sendID($info)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('ID command');
    }

    
    protected function _getID()
    {
        $id = $this->queryCapability('IMPLEMENTATION');
        return empty($id)
            ? array()
            : array('implementation' => $id);
    }

    
    protected function _setLanguage($langs)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('LANGUAGE extension');
    }

    
    protected function _getLanguage($list)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('LANGUAGE extension');
    }

    
    protected function _openMailbox(Horde_Imap_Client_Mailbox $mailbox, $mode)
    {
        if ($mailbox != 'INBOX') {
            throw new Horde_Imap_Client_Exception_NoSupportPop3('Mailboxes other than INBOX');
        }
        $this->_changeSelected($mailbox, $mode);
    }

    
    protected function _createMailbox(Horde_Imap_Client_Mailbox $mailbox, $opts)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Creating mailboxes');
    }

    
    protected function _deleteMailbox(Horde_Imap_Client_Mailbox $mailbox)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Deleting mailboxes');
    }

    
    protected function _renameMailbox(Horde_Imap_Client_Mailbox $old,
                                      Horde_Imap_Client_Mailbox $new)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Renaming mailboxes');
    }

    
    protected function _subscribeMailbox(Horde_Imap_Client_Mailbox $mailbox,
                                         $subscribe)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Mailboxes other than INBOX');
    }

    
    protected function _listMailboxes($pattern, $mode, $options)
    {
        $tmp = array(
            'mailbox' => Horde_Imap_Client_Mailbox::get('INBOX')
        );

        if (!empty($options['attributes'])) {
            $tmp['attributes'] = array();
        }
        if (!empty($options['delimiter'])) {
            $tmp['delimiter'] = '';
        }

        return array('INBOX' => $tmp);
    }

    
    protected function _status($mboxes, $flags)
    {
        if ((count($mboxes) > 1) || (reset($mboxes) != 'INBOX')) {
            throw new Horde_Imap_Client_Exception_NoSupportPop3('Mailboxes other than INBOX');
        }

        $this->openMailbox('INBOX');

        $ret = array();

        if ($flags & Horde_Imap_Client::STATUS_MESSAGES) {
            $res = $this->_pop3Cache('stat');
            $ret['messages'] = $res['msgs'];
        }

        if ($flags & Horde_Imap_Client::STATUS_RECENT) {
            $res = $this->_pop3Cache('stat');
            $ret['recent'] = $res['msgs'];
        }

                        if ($flags & Horde_Imap_Client::STATUS_UIDNEXT) {
            $res = $this->_pop3Cache('stat');
            $ret['uidnext'] = $res['msgs'] + 1;
        }

        if ($flags & Horde_Imap_Client::STATUS_UIDVALIDITY) {
            $ret['uidvalidity'] = $this->queryCapability('UIDL')
                ? 1
                : microtime(true);
        }

        if ($flags & Horde_Imap_Client::STATUS_UNSEEN) {
            $ret['unseen'] = 0;
        }

        return array('INBOX' => $ret);
    }

    
    protected function _append(Horde_Imap_Client_Mailbox $mailbox, $data,
                               $options)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Appending messages');
    }

    
    protected function _check()
    {
        $this->noop();
    }

    
    protected function _close($options)
    {
        if (!empty($options['expunge'])) {
            $this->logout();
        }
    }

    
    protected function _expunge($options)
    {
        $msg_list = $this->_deleted;
        $this->logout();
        return empty($options['list'])
            ? null
            : $msg_list;
    }

    
    protected function _search($query, $options)
    {
        $sort = empty($options['sort'])
            ? null
            : reset($options['sort']);

                if ((strval($options['_query']['query']) != 'ALL') ||
            ($sort &&
             ((count($options['sort']) > 1) ||
              ($sort != Horde_Imap_Client::SORT_SEQUENCE)))) {
            throw new Horde_Imap_Client_Exception_NoSupportPop3('Server search');
        }

        $status = $this->status($this->_selected, Horde_Imap_Client::STATUS_MESSAGES);
        $res = range(1, $status['messages']);

        if (empty($options['sequence'])) {
            $tmp = array();
            $uidllist = $this->_pop3Cache('uidl');
            foreach ($res as $val) {
                $tmp[] = $uidllist[$val];
            }
            $res = $tmp;
        }

        if (!empty($options['partial'])) {
            $partial = $this->getIdsOb($options['partial'], true);
            $min = $partial->min - 1;
            $res = array_slice($res, $min, $partial->max - $min);
        }

        $ret = array();
        foreach ($options['results'] as $val) {
            switch ($val) {
            case Horde_Imap_Client::SEARCH_RESULTS_COUNT:
                $ret['count'] = count($res);
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_MATCH:
                $ret['match'] = $this->getIdsOb($res);
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_MAX:
                $ret['max'] = empty($res) ? null : max($res);
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_MIN:
                $ret['min'] = empty($res) ? null : min($res);
                break;
            }
        }

        return $ret;
    }

    
    protected function _setComparator($comparator)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Search comparators');
    }

    
    protected function _getComparator()
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Search comparators');
    }

    
    protected function _thread($options)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Server threading');
    }

    
    protected function _fetch(Horde_Imap_Client_Fetch_Results $results,
                              $queries)
    {
        foreach ($queries as $options) {
            $this->_fetchCmd($results, $options);
        }

        $this->_updateCache($results);
    }

     
    protected function _fetchCmd(Horde_Imap_Client_Fetch_Results $results,
                                 $options)
    {
                        $seq_ids = $this->_getSeqIds($options['ids']);
        if (empty($seq_ids)) {
            return;
        }

        $lookup = $options['ids']->sequence
            ? array_combine($seq_ids, $seq_ids)
            : $this->_pop3Cache('uidl');

        foreach ($options['_query'] as $type => $c_val) {
            switch ($type) {
            case Horde_Imap_Client::FETCH_FULLMSG:
                foreach ($seq_ids as $id) {
                    $tmp = $this->_pop3Cache('msg', $id);

                    if (empty($c_val['start']) && empty($c_val['length'])) {
                        $tmp2 = fopen('php://temp', 'r+');
                        stream_copy_to_stream($tmp, $tmp2, empty($c_val['length']) ? -1 : $c_val['length'], empty($c_val['start']) ? 0 : $c_val['start']);
                        $results->get($lookup[$id])->setFullMsg($tmp2);
                    } else {
                        $results->get($lookup[$id])->setFullMsg($tmp);
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_HEADERTEXT:
                                foreach ($c_val as $key => $val) {
                    foreach ($seq_ids as $id) {
                        
                        try {
                            $tmp = ($key == 0)
                                ? $this->_pop3Cache('hdr', $id)
                                : Horde_Mime_Part::getRawPartText(stream_get_contents($this->_pop3Cache('msg', $id)), 'header', $key);
                            $results->get($lookup[$id])->setHeaderText($key, $this->_processString($tmp, $c_val));
                        } catch (Horde_Mime_Exception $e) {}
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_BODYTEXT:
                                foreach ($c_val as $key => $val) {
                    foreach ($seq_ids as $id) {
                        try {
                            $results->get($lookup[$id])->setBodyText($key, $this->_processString(Horde_Mime_Part::getRawPartText(stream_get_contents($this->_pop3Cache('msg', $id)), 'body', $key), $val));
                        } catch (Horde_Mime_Exception $e) {}
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_MIMEHEADER:
                                foreach ($c_val as $key => $val) {
                    foreach ($seq_ids as $id) {
                        try {
                            $results->get($lookup[$id])->setMimeHeader($key, $this->_processString(Horde_Mime_Part::getRawPartText(stream_get_contents($this->_pop3Cache('msg', $id)), 'header', $key), $val));
                        } catch (Horde_Mime_Exception $e) {}
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_BODYPART:
                                foreach ($c_val as $key => $val) {
                    foreach ($seq_ids as $id) {
                        try {
                            $results->get($lookup[$id])->setBodyPart($key, $this->_processString(Horde_Mime_Part::getRawPartText(stream_get_contents($this->_pop3Cache('msg', $id)), 'body', $key), $val));
                        } catch (Horde_Mime_Exception $e) {}
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_HEADERS:
                                foreach ($seq_ids as $id) {
                    $ob = $this->_pop3Cache('hdrob', $id);
                    foreach ($c_val as $key => $val) {
                        $tmp = $ob;

                        if (empty($val['notsearch'])) {
                            $tmp2 = $tmp->toArray(array('nowrap' => true));
                            foreach (array_keys($tmp2) as $hdr) {
                                if (!in_array($hdr, $val['headers'])) {
                                    $tmp->removeHeader($hdr);
                                }
                            }
                        } else {
                            foreach ($val['headers'] as $hdr) {
                                $tmp->removeHeader($hdr);
                            }
                        }

                        $results->get($lookup[$id])->setHeaders($key, $tmp);
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_STRUCTURE:
                foreach ($seq_ids as $id) {
                    if ($ptr = $this->_pop3Cache('msg', $id)) {
                        try {
                            $results->get($lookup[$id])->setStructure(Horde_Mime_Part::parseMessage(stream_get_contents($ptr), array('no_body' => true)));
                        } catch (Horde_Exception $e) {}
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_ENVELOPE:
                foreach ($seq_ids as $id) {
                    $tmp = $this->_pop3Cache('hdrob', $id);
                    $results->get($lookup[$id])->setEnvelope(array(
                        'date' => $tmp->getValue('date'),
                        'subject' => $tmp->getValue('subject'),
                        'from' => $tmp->getOb('from'),
                        'sender' => $tmp->getOb('sender'),
                        'reply_to' => $tmp->getOb('reply-to'),
                        'to' => $tmp->getOb('to'),
                        'cc' => $tmp->getOb('cc'),
                        'bcc' => $tmp->getOb('bcc'),
                        'in_reply_to' => $tmp->getValue('in-reply-to'),
                        'message_id' => $tmp->getValue('message-id')
                    ));
                }
                break;

            case Horde_Imap_Client::FETCH_IMAPDATE:
                foreach ($seq_ids as $id) {
                    $tmp = $this->_pop3Cache('hdrob', $id);
                    $results->get($lookup[$id])->setImapDate($tmp->getValue('date'));
                }
                break;

            case Horde_Imap_Client::FETCH_SIZE:
                $sizelist = $this->_pop3Cache('size');
                foreach ($seq_ids as $id) {
                    $results->get($lookup[$id])->setSize($sizelist[$id]);
                }
                break;

            case Horde_Imap_Client::FETCH_SEQ:
                foreach ($seq_ids as $id) {
                    $results->get($lookup[$id])->setSeq($id);
                }
                break;

            case Horde_Imap_Client::FETCH_UID:
                $uidllist = $this->_pop3Cache('uidl');
                foreach ($seq_ids as $id) {
                    if (isset($uidllist[$id])) {
                        $results->get($lookup[$id])->setUid($uidllist[$id]);
                    }
                }
                break;
            }
        }
    }

    
    protected function _pop3Cache($type, $index = null, $data = null)
    {
        if (isset($this->_temp['pop3cache'][$index][$type])) {
            if ($type == 'msg') {
                rewind($this->_temp['pop3cache'][$index][$type]);
            }
            return $this->_temp['pop3cache'][$index][$type];
        }

        switch ($type) {
        case 'hdr':
        case 'top':
            $data = null;
            if ($this->queryCapability('TOP') || ($type == 'top')) {
                try {
                    $res = $this->_sendLine('TOP ' . $index . ' 0', array(
                        'multiline' => 'stream'
                    ));
                    rewind($res['data']);
                    $data = stream_get_contents($res['data']);
                    fclose($res['data']);
                } catch (Horde_Imap_Client_Exception $e) {
                    $this->_temp['no_top'] = true;
                    if ($type == 'top') {
                        return null;
                    }
                }
            }

            if (is_null($data)) {
                $data = Horde_Mime_Part::getRawPartText(stream_get_contents($this->_pop3Cache('msg', $index)), 'header', 0);
            }
            break;

        case 'hdrob':
            $data = Horde_Mime_Headers::parseHeaders($this->_pop3Cache('hdr', $index));
            break;

        case 'msg':
            $res = $this->_sendLine('RETR ' . $index, array(
                'multiline' => 'stream'
            ));
            $data = $res['data'];
            rewind($data);
            break;

        case 'size':
        case 'uidl':
            $data = array();
            try {
                $res = $this->_sendLine(($type == 'size') ? 'LIST' : 'UIDL', array(
                    'multiline' => 'array'
                ));
                foreach ($res['data'] as $val) {
                    $resp_data = explode(' ', $val, 2);
                    $data[$resp_data[0]] = $resp_data[1];
                }
            } catch (Horde_Imap_Client_Exception $e) {
                if ($type == 'uidl') {
                    $this->_temp['no_uidl'] = true;
                }
            }
            break;

        case 'stat':
            $resp = $this->_sendLine('STAT');
            $resp_data = explode(' ', $resp['resp'], 2);
            $data = array('msgs' => $resp_data[0], 'size' => $resp_data[1]);
            break;
        }

        $this->_temp['pop3cache'][$index][$type] = $data;

        return $data;
    }

    
    protected function _processString($str, $opts)
    {
        if (!empty($opts['length'])) {
            return substr($str, empty($opts['start']) ? 0 : $opts['start'], $opts['length']);
        } elseif (!empty($opts['start'])) {
            return substr($str, $opts['start']);
        }

        return $str;
    }

    
    protected function _vanished($modseq, Horde_Imap_Client_Ids $ids)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('QRESYNC commands');
    }

    
    protected function _store($options)
    {
        $delete = $reset = false;

        
        if (isset($options['replace'])) {
            $delete = (bool)(count(array_intersect($options['replace'], array(
                Horde_Imap_Client::FLAG_DELETED
            ))));
            $reset = !$delete;
        } else {
            if (!empty($options['add'])) {
                $delete = (bool)(count(array_intersect($options['add'], array(
                    Horde_Imap_Client::FLAG_DELETED
                ))));
            }

            if (!empty($options['remove'])) {
                $reset = !(bool)(count(array_intersect($options['remove'], array(
                    Horde_Imap_Client::FLAG_DELETED
                ))));
            }
        }

        if ($reset) {
            $this->_sendLine('RSET');
        } elseif ($delete) {
            foreach ($this->_getSeqIds($options['ids']) as $id) {
                try {
                    $this->_sendLine('DELE ' . $id);
                    $this->_deleted[] = $id;
                } catch (Horde_Imap_Client_Exception $e) {}
            }
        }

        return $this->getIdsOb();
    }

    
    protected function _copy(Horde_Imap_Client_Mailbox $dest, $options)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Copying messages');
    }

    
    protected function _setQuota(Horde_Imap_Client_Mailbox $root, $options)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Quotas');
    }

    
    protected function _getQuota(Horde_Imap_Client_Mailbox $root)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Quotas');
    }

    
    protected function _getQuotaRoot(Horde_Imap_Client_Mailbox $mailbox)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Quotas');
    }

    
    protected function _setACL(Horde_Imap_Client_Mailbox $mailbox, $identifier,
                               $options)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('ACLs');
    }

    
    protected function _deleteACL(Horde_Imap_Client_Mailbox $mailbox, $identifier)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('ACLs');
    }

    
    protected function _getACL(Horde_Imap_Client_Mailbox $mailbox)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('ACLs');
    }

    
    protected function _listACLRights(Horde_Imap_Client_Mailbox $mailbox,
                                      $identifier)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('ACLs');
    }

    
    protected function _getMyACLRights(Horde_Imap_Client_Mailbox $mailbox)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('ACLs');
    }

    
    protected function _getMetadata(Horde_Imap_Client_Mailbox $mailbox,
                                    $entries, $options)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Metadata');
    }

    
    protected function _setMetadata(Horde_Imap_Client_Mailbox $mailbox, $data)
    {
        throw new Horde_Imap_Client_Exception_NoSupportPop3('Metadata');
    }

    
    protected function _getSearchCache($type, $options)
    {
        
        return null;
    }

    
    public function resolveIds(Horde_Imap_Client_Mailbox $mailbox,
                               Horde_Imap_Client_Ids $ids, $convert = 0)
    {
        if (!$ids->special &&
            (!$convert ||
             (!$ids->sequence && ($convert == 1)) ||
             $ids->isEmpty())) {
            return clone $ids;
        }

        $uids = $this->_pop3Cache('uidl');

        return $this->getIdsOb(
            $ids->all ? array_values($uids) : array_intersect_keys($uids, $ids->ids)
        );
    }

    

    
    protected function _sendLine($cmd, $options = array())
    {
        $old_debug = $this->_debug->debug;
        if (!empty($options['debug'])) {
            $this->_debug->raw($options['debug'] . "\n");
            $this->_debug->debug = false;
        }

        if ($old_debug) {
            $timer = new Horde_Support_Timer();
            $timer->push();
        }

        try {
            $this->_connection->write($cmd);
        } catch (Horde_Imap_Client_Exception $e) {
            $this->_debug->debug = $old_debug;
            throw $e;
        }

        $this->_debug->debug = $old_debug;

        $resp = $this->_getResponse(
            empty($options['multiline']) ? false : $options['multiline']
        );

        if ($old_debug) {
            $this->_debug->info(sprintf(
                'Command took %s seconds.',
                round($timer->pop(), 4)
            ));
        }

        return $resp;
    }

    
    protected function _getResponse($multiline = false)
    {
        $ob = array('resp' => '');

        $read = explode(' ', rtrim($this->_connection->read(), "\r\n"), 2);
        if (!in_array($read[0], array('+OK', '-ERR', '+'))) {
            $this->_debug->info('ERROR: IMAP read/timeout error.');
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Error when communicating with the mail server."),
                Horde_Imap_Client_Exception::SERVER_READERROR
            );
        }

        $respcode = null;
        if (isset($read[1]) &&
            isset($this->_init['capability']) &&
            $this->queryCapability('RESP-CODES')) {
            $respcode = $this->_parseResponseCode($read[1]);
        }

        switch ($read[0]) {
        case '+OK':
        case '+':
            if ($respcode) {
                $ob['resp'] = $respcode->text;
            } elseif (isset($read[1])) {
                $ob['resp'] = $read[1];
            }
            break;

        case '-ERR':
            $errcode = 0;
            if ($respcode) {
                $errtext = $respcode->text;

                if (isset($respcode->code)) {
                    switch ($respcode->code) {
                                        case 'IN-USE':
                                        case 'LOGIN-DELAY':
                        $errcode = Horde_Imap_Client_Exception::LOGIN_UNAVAILABLE;
                        break;

                                        case 'SYS/TEMP':
                        $errcode = Horde_Imap_Client_Exception::POP3_TEMP_ERROR;
                        break;

                                        case 'SYS/PERM':
                        $errcode = Horde_Imap_Client_Exception::POP3_PERM_ERROR;
                        break;

                                        case 'AUTH':
                        $errcode = Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED;
                        break;
                    }
                }
            } elseif (isset($read[1])) {
                $errtext = $read[1];
            } else {
                $errtext = '[No error message provided by server]';
            }

            $e = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("POP3 error reported by server."),
                $errcode
            );
            $e->details = $errtext;
            throw $e;
        }

        switch ($multiline) {
        case 'array':
            $ob['data'] = array();
            break;

        case 'none':
            $ob['data'] = null;
            break;

        case 'stream':
            $ob['data'] = fopen('php://temp', 'r+');
            break;

        default:
            return $ob;
        }

        do {
            $orig_read = $this->_connection->read();
            $read = rtrim($orig_read, "\r\n");

            if ($read === '.') {
                break;
            } elseif (substr($read, 0, 2) === '..') {
                $read = substr($read, 1);
            }

            if (is_array($ob['data'])) {
                $ob['data'][] = $read;
            } elseif (!is_null($ob['data'])) {
                fwrite($ob['data'], $orig_read);
            }
        } while (true);

        return $ob;
    }

    
    protected function _getSeqIds(Horde_Imap_Client_Ids $ids)
    {
        if (!count($ids)) {
            $status = $this->status($this->_selected, Horde_Imap_Client::STATUS_MESSAGES);
            return range(1, $status['messages']);
        } elseif ($ids->sequence) {
            return $ids->ids;
        }

        return array_keys(array_intersect($this->_pop3Cache('uidl'), $ids->ids));
    }

    
    protected function _parseResponseCode($text)
    {
        $ret = new stdClass;

        $text = trim($text);
        if ($text[0] === '[') {
            $pos = strpos($text, ' ', 2);
            $end_pos = strpos($text, ']', 2);
            if ($pos > $end_pos) {
                $ret->code = strtoupper(substr($text, 1, $end_pos - 1));
            } else {
                $ret->code = strtoupper(substr($text, 1, $pos - 1));
                $ret->data = substr($text, $pos + 1, $end_pos - $pos - 1);
            }
            $ret->text = trim(substr($text, $end_pos + 1));
        } else {
            $ret->text = $text;
        }

        return $ret;
    }

}
