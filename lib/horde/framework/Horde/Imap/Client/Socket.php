<?php



class Horde_Imap_Client_Socket extends Horde_Imap_Client_Base
{
    
    const CACHE_FLAGS = 'HICflags';

    
    protected $_cmdQueue = array();

    
    protected $_defaultPorts = array(143, 993);

    
    protected $_statusFields = array(
        'messages' => Horde_Imap_Client::STATUS_MESSAGES,
        'recent' => Horde_Imap_Client::STATUS_RECENT,
        'uidnext' => Horde_Imap_Client::STATUS_UIDNEXT,
        'uidvalidity' => Horde_Imap_Client::STATUS_UIDVALIDITY,
        'unseen' => Horde_Imap_Client::STATUS_UNSEEN,
        'firstunseen' => Horde_Imap_Client::STATUS_FIRSTUNSEEN,
        'flags' => Horde_Imap_Client::STATUS_FLAGS,
        'permflags' => Horde_Imap_Client::STATUS_PERMFLAGS,
        'uidnotsticky' => Horde_Imap_Client::STATUS_UIDNOTSTICKY,
        'highestmodseq' => Horde_Imap_Client::STATUS_HIGHESTMODSEQ
    );

    
    protected $_tag = 0;

    
    public function __construct(array $params = array())
    {
        parent::__construct(array_merge(array(
            'debug_literal' => false,
            'envelope_addrs' => 1000,
            'envelope_string' => 2048
        ), $params));
    }

    
    public function getParam($key)
    {
        switch ($key) {
        case 'xoauth2_token':
            if (isset($this->_params[$key]) &&
                ($this->_params[$key] instanceof Horde_Imap_Client_Base_Password)) {
                return $this->_params[$key]->getPassword();
            }
            break;
        }

        return parent::getParam($key);
    }

    
    protected function _capability()
    {
                        $this->_connect();

                        if (!isset($this->_init['capability'])) {
            $this->_sendCmd($this->_command('CAPABILITY'));
        }
    }

    
    protected function _parseCapability(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        $data
    )
    {
        if (!empty($this->_temp['no_cap'])) {
            return;
        }

        $pipeline->data['capability_set'] = true;

        $c = array();

        foreach ($data as $val) {
            $cap_list = explode('=', $val);
            $cap_list[0] = strtoupper($cap_list[0]);
            if (isset($cap_list[1])) {
                if (!isset($c[$cap_list[0]]) || !is_array($c[$cap_list[0]])) {
                    $c[$cap_list[0]] = array();
                }
                $c[$cap_list[0]][] = $cap_list[1];
            } elseif (!isset($c[$cap_list[0]])) {
                $c[$cap_list[0]] = true;
            }
        }

        $this->_setInit('capability', $c);
    }

    
    protected function _unsetCapability($cap)
    {
        $cap_list = $this->capability();
        unset($cap_list[$cap]);
        $this->_setInit('capability', $cap_list);
    }

    
    protected function _noop()
    {
                $this->_sendCmd($this->_command('NOOP'));
    }

    
    protected function _getNamespaces()
    {
        $data = $this->queryCapability('NAMESPACE')
            ? $this->_sendCmd($this->_command('NAMESPACE'))->data
            : array();

        return isset($data['namespace'])
            ? $data['namespace']
            : array();
    }

    
    protected function _parseNamespace(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        $namespace_array = array(
            Horde_Imap_Client_Data_Namespace::NS_PERSONAL,
            Horde_Imap_Client_Data_Namespace::NS_OTHER,
            Horde_Imap_Client_Data_Namespace::NS_SHARED
        );

        $c = array();

                        foreach ($namespace_array as $val) {
            $entry = $data->next();

            if (is_null($entry)) {
                continue;
            }

            while ($data->next() !== false) {
                $ob = Horde_Imap_Client_Mailbox::get($data->next(), true);

                $ns = new Horde_Imap_Client_Data_Namespace();
                $ns->delimiter = $data->next();
                $ns->name = strval($ob);
                $ns->type = $val;
                $c[strval($ob)] = $ns;

                                while (($ext = $data->next()) !== false) {
                    switch (strtoupper($ext)) {
                    case 'TRANSLATION':
                                                $data->next();
                        $ns->translation = $data->next();
                        $data->next();
                        break;
                    }
                }
            }
        }

        $pipeline->data['namespace'] = new Horde_Imap_Client_Namespace_List($c);
    }

    
    public function alerts()
    {
        $alerts = empty($this->_temp['alerts'])
            ? array()
            : $this->_temp['alerts'];
        $this->_temp['alerts'] = array();
        return $alerts;
    }

    
    protected function _login()
    {
        $secure = $this->getParam('secure');

        if (!empty($this->_temp['preauth'])) {
            unset($this->_temp['preauth']);

            
            if (!$this->isSecureConnection() && ($secure !== false)) {
                $this->logout();
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Could not open secure TLS connection to the IMAP server."),
                    Horde_Imap_Client_Exception::LOGIN_TLSFAILURE
                );
            }

            return $this->_loginTasks();
        }

        
        if (is_null($this->getParam('password'))) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("No password provided."),
                Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED
            );
        }

        $this->_connect();

        $first_login = empty($this->_init['authmethod']);

                if (!$this->isSecureConnection() &&
            (($secure === 'tls') ||
             (($secure === true) && $this->queryCapability('LOGINDISABLED')))) {
            if ($first_login && !$this->queryCapability('STARTTLS')) {
                
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Server does not support TLS connections."),
                    Horde_Imap_Client_Exception::LOGIN_TLSFAILURE
                );
            }

                                    $this->_sendCmd($this->_command('STARTTLS'));

            if (!$this->_connection->startTls()) {
                $this->logout();
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Could not open secure TLS connection to the IMAP server."),
                    Horde_Imap_Client_Exception::LOGIN_TLSFAILURE
                );
            }

            $this->_debug->info('Successfully completed TLS negotiation.');

            $this->setParam('secure', 'tls');
            $secure = 'tls';

            if ($first_login) {
                                $this->_setInit('capability');

                                $this->_setInit('lang');
            }

                        if (!empty($this->_init['imapproxy'])) {
                $this->setLanguage();
            }
        }

        
        if (($secure === true) && !$this->isSecureConnection()) {
            $this->setParam('secure', false);
            $secure = false;
        }

        if ($first_login) {
                        $auth_mech = array();

            $auth = ($auth = $this->queryCapability('AUTH'))
                ? array_flip($auth)
                : array();

                        if (isset($auth['XOAUTH2']) && $this->getParam('xoauth2_token')) {
                $auth_mech[] = 'XOAUTH2';
            }
            unset($auth['XOAUTH2']);

            
            if ($secure) {
                if (isset($auth['PLAIN'])) {
                    $auth_mech[] = 'PLAIN';
                    unset($auth['PLAIN']);
                } else {
                    $auth_mech[] = 'LOGIN';
                }
            }

                                    if (isset($auth['CRAM-MD5'])) {
                $auth_mech[] = 'CRAM-MD5';
            } elseif (isset($auth['DIGEST-MD5'])) {
                $auth_mech[] = 'DIGEST-MD5';
            }
            unset($auth['CRAM-MD5'], $auth['DIGEST-MD5']);

                        $auth_mech = array_merge($auth_mech, array_keys($auth));
            if (!$secure && !$this->queryCapability('LOGINDISABLED')) {
                $auth_mech[] = 'LOGIN';
            }

            if (empty($auth_mech)) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("No supported IMAP authentication method could be found."),
                    Horde_Imap_Client_Exception::LOGIN_NOAUTHMETHOD
                );
            }
        } else {
            $auth_mech = array($this->_init['authmethod']);
        }

        $login_err = null;

        foreach ($auth_mech as $method) {
            try {
                $resp = $this->_tryLogin($method);
                $data = $resp->data;
                $this->_setInit('authmethod', $method);
                unset($this->_temp['referralcount']);
            } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
                $data = $e->resp_data;
                if (isset($data['loginerr'])) {
                    $login_err = $data['loginerr'];
                }
                $resp = false;
            } catch (Horde_Imap_Client_Exception $e) {
                $resp = false;
            }

                                    if (isset($data['referral'])) {
                foreach (array('hostspec', 'port', 'username') as $val) {
                    if (!is_null($data['referral']->$val)) {
                        $this->setParam($val, $data['referral']->$val);
                    }
                }

                if (!is_null($data['referral']->auth)) {
                    $this->_setInit('authmethod', $data['referral']->auth);
                }

                if (!isset($this->_temp['referralcount'])) {
                    $this->_temp['referralcount'] = 0;
                }

                                                if (++$this->_temp['referralcount'] < 10) {
                    $this->logout();
                    $this->_setInit('capability');
                    $this->_setInit('namespace', array());
                    return $this->login();
                }

                unset($this->_temp['referralcount']);
            }

            if ($resp) {
                return $this->_loginTasks($first_login, $resp->data);
            }
        }

        
        if (!empty($this->_init['authmethod'])) {
            $this->_setInit();
            unset($this->_temp['no_cap']);
            try {
                return $this->_login();
            } catch (Horde_Imap_Client_Exception $e) {}
        }

        
        if (is_null($login_err)) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Mail server denied authentication."),
                Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED
            );
        }

        throw $login_err;
    }

    
    protected function _connect()
    {
        if (!is_null($this->_connection)) {
            return;
        }

        try {
            $this->_connection = new Horde_Imap_Client_Socket_Connection_Socket(
                $this->getParam('hostspec'),
                $this->getParam('port'),
                $this->getParam('timeout'),
                $this->getParam('secure'),
                array(
                    'debug' => $this->_debug,
                    'debugliteral' => $this->getParam('debug_literal')
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

                        if (isset($this->_init['capability'])) {
            $this->_temp['no_cap'] = true;
        }

        
        try {
            $this->_getLine($this->_pipeline());
        } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
            if ($e->status === Horde_Imap_Client_Interaction_Server::BYE) {
                
                $e->setMessage(Horde_Imap_Client_Translation::r("Server rejected connection."));
                $e->setCode(Horde_Imap_Client_Exception::SERVER_CONNECT);
            }
            throw $e;
        }

                if (!$this->queryCapability('IMAP4REV1')) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("The mail server does not support IMAP4rev1 (RFC 3501)."),
                Horde_Imap_Client_Exception::SERVER_CONNECT
            );
        }

                if (empty($this->_init['imapproxy'])) {
            if ($this->queryCapability('XIMAPPROXY')) {
                $this->_setInit('imapproxy', true);
            } else {
                $this->setLanguage();
            }
        }

                if (!empty($this->_temp['preauth'])) {
            $this->login();
        }
    }

    
    protected function _tryLogin($method)
    {
        $username = $this->getParam('username');
        $password = $this->getParam('password');

        $authenticate_cmd = false;

        switch ($method) {
        case 'CRAM-MD5':
        case 'CRAM-SHA1':
        case 'CRAM-SHA256':
                        
                                    $args = array(
                $username,
                strtolower(substr($method, 5)),
                $password
            );

            $cmd = $this->_command('AUTHENTICATE')->add(array(
                $method,
                new Horde_Imap_Client_Interaction_Command_Continuation(function($ob) use ($args) {
                    return new Horde_Imap_Client_Data_Format_List(
                        base64_encode($args[0] . ' ' . hash_hmac($args[1], base64_decode($ob->token->current()), $args[2], false))
                    );
                })
            ));
            $cmd->debug = sprintf('[AUTHENTICATE %s Command - username: %s]', $method, $username);
            break;

        case 'DIGEST-MD5':
            
                                    $args = array(
                $username,
                $password,
                $this->getParam('hostspec')
            );

            $cmd = $this->_command('AUTHENTICATE')->add(array(
                $method,
                new Horde_Imap_Client_Interaction_Command_Continuation(function($ob) use ($args) {
                    return new Horde_Imap_Client_Data_Format_List(
                        base64_encode(new Horde_Imap_Client_Auth_DigestMD5(
                            $args[0],
                            $args[1],
                            base64_decode($ob->token->current()),
                            $args[2],
                            'imap'
                        ))
                    );
                }),
                new Horde_Imap_Client_Interaction_Command_Continuation(function($ob) {
                    if (strpos(base64_decode($ob->token->current()), 'rspauth=') === false) {
                        throw new Horde_Imap_Client_Exception(
                            Horde_Imap_Client_Translation::r("Unexpected response from server when authenticating."),
                            Horde_Imap_Client_Exception::SERVER_CONNECT
                        );
                    }

                    return new Horde_Imap_Client_Data_Format_List();
                })
            ));
            $cmd->debug = sprintf('[AUTHENTICATE DIGEST-MD5 Command - username: %s]', $username);
            break;

        case 'LOGIN':
            $cmd = $this->_command('LOGIN')->add(array(
                new Horde_Imap_Client_Data_Format_Astring($username),
                new Horde_Imap_Client_Data_Format_Astring($password)
            ));
            $cmd->debug = sprintf('[LOGIN Command - username: %s]', $username);
            break;

        case 'PLAIN':
                        $auth = base64_encode(implode("\0", array(
                $username,
                $username,
                $password
            )));
            $authenticate_cmd = true;
            break;

        case 'XOAUTH2':
                        $auth = $this->getParam('xoauth2_token');
            $authenticate_cmd = true;
            break;

        default:
            throw new Horde_Imap_Client_Exception(
                sprintf(Horde_Imap_Client_Translation::r("Unknown authentication method: %s"), $method),
                Horde_Imap_Client_Exception::SERVER_CONNECT
            );
        }

        if ($authenticate_cmd) {
            $cmd = $this->_command('AUTHENTICATE')->add($method);

            if ($this->queryCapability('SASL-IR')) {
                                $cmd->add($auth);
                $cmd->debug = sprintf('[SASL-IR AUTHENTICATE Command - method: %s, username: %s]', $method, $username);
            } else {
                $cmd->add(new Horde_Imap_Client_Interaction_Command_Continuation(function($ob) use ($auth) {
                    return new Horde_Imap_Client_Data_Format_List($auth);
                }));
                $cmd->debug = sprintf('[AUTHENTICATE Command - method: %s, username: %s]', $method, $username);
            }

            
            $error_continuation = new Horde_Imap_Client_Interaction_Command_Continuation(function($ob) {
                return new Horde_Imap_Client_Data_Format_List();
            });
            $error_continuation->optional = true;
            $cmd->add($error_continuation);
        }

        return $this->_sendCmd($this->_pipeline($cmd));
    }

    
    protected function _loginTasks($firstlogin = true, array $resp = array())
    {
        
        if (!$firstlogin && !empty($resp['proxyreuse'])) {
            if (isset($this->_init['enabled'])) {
                $this->_temp['enabled'] = $this->_init['enabled'];
            }

                        if (!isset($this->_init['lang'])) {
                $this->_temp['lang_queue'] = true;
                $this->setLanguage();
                unset($this->_temp['lang_queue']);
            }
            return false;
        }

        
        if ($firstlogin && empty($resp['capability_set'])) {
            $this->_setInit('capability');
        }

        $this->_temp['lang_queue'] = true;
        $this->setLanguage();
        unset($this->_temp['lang_queue']);

        
        if ($this->_initCache()) {
            if ($this->queryCapability('QRESYNC')) {
                $this->_enable(array('QRESYNC'));
            } elseif ($this->queryCapability('CONDSTORE')) {
                $this->_enable(array('CONDSTORE'));
            }
        }

        return true;
    }

    
    protected function _logout()
    {
        if (empty($this->_temp['logout'])) {
            
            if (!empty($this->_cmdQueue) &&
                !empty($this->_init['imapproxy'])) {
                $this->_sendCmd($this->_pipeline());
            }

            $this->_temp['logout'] = true;
            try {
                $this->_sendCmd($this->_command('LOGOUT'));
            } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
                            }
            unset($this->_temp['logout']);
        }
    }

    
    protected function _sendID($info)
    {
        $cmd = $this->_command('ID');

        if (empty($info)) {
            $cmd->add(new Horde_Imap_Client_Data_Format_Nil());
        } else {
            $tmp = new Horde_Imap_Client_Data_Format_List();
            foreach ($info as $key => $val) {
                $tmp->add(array(
                    new Horde_Imap_Client_Data_Format_String(strtolower($key)),
                    new Horde_Imap_Client_Data_Format_Nstring($val)
                ));
            }
            $cmd->add($tmp);
        }

        $this->_temp['id'] = $this->_sendCmd($cmd)->data['id'];
    }

    
    protected function _parseID(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        $ids = array();

        if (!is_null($data->next())) {
            while (($curr = $data->next()) !== false) {
                if (!is_null($id = $data->next())) {
                    $ids[$curr] = $id;
                }
            }
        }

        $pipeline->data['id'] = $ids;
    }

    
    protected function _getID()
    {
        if (!isset($this->_temp['id'])) {
            $this->sendID();
        }

        return $this->_temp['id'];
    }

    
    protected function _setLanguage($langs)
    {
        $cmd = $this->_command('LANGUAGE');
        foreach ($langs as $lang) {
            $cmd->add(new Horde_Imap_Client_Data_Format_Astring($lang));
        }

        if (!empty($this->_temp['lang_queue'])) {
            $this->_cmdQueue[] = $cmd;
            return array();
        }

        try {
            $this->_sendCmd($cmd);
        } catch (Horde_Imap_Client_Exception $e) {
            $this->_setInit('lang', false);
            return null;
        }

        return $this->_init['lang'];
    }

    
    protected function _getLanguage($list)
    {
        if (!$list) {
            return empty($this->_init['lang'])
                ? null
                : $this->_init['lang'];
        }

        if (!isset($this->_init['langavail'])) {
            try {
                $this->_sendCmd($this->_command('LANGUAGE'));
            } catch (Horde_Imap_Client_Exception $e) {
                $this->_setInit('langavail', array());
            }
        }

        return $this->_init['langavail'];
    }

    
    protected function _parseLanguage(Horde_Imap_Client_Tokenize $data)
    {
        $lang_list = $data->flushIterator();

        if (count($lang_list) === 1) {
                        $this->_setInit('lang', reset($lang_list));
        } else {
                        $this->_setInit('langavail', $lang_list);
        }
    }

    
    protected function _enable($exts)
    {
        if ($this->queryCapability('ENABLE')) {
                        $exts = array_diff($exts, array_keys($this->_temp['enabled']));
            if (!empty($exts)) {
                $this->_cmdQueue[] = $this->_command('ENABLE')->add($exts);
                $this->_enabled($exts, 1);
            }
        }
    }

    
    protected function _parseEnabled(Horde_Imap_Client_Tokenize $data)
    {
        $this->_enabled($data->flushIterator(), 2);
    }

    
    protected function _enabled($exts, $status)
    {
        parent::_enabled($exts, $status);

        if (($status == 2) && !empty($this->_init['imapproxy'])) {
            $this->_setInit('enabled', $this->_temp['enabled']);
        }
    }

    
    protected function _openMailbox(Horde_Imap_Client_Mailbox $mailbox, $mode)
    {
        $qresync = isset($this->_temp['enabled']['QRESYNC']);

        $cmd = $this->_command(
            ($mode == Horde_Imap_Client::OPEN_READONLY) ? 'EXAMINE' : 'SELECT'
        )->add(
            new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
        );
        $pipeline = $this->_pipeline($cmd);

        
        if ($qresync) {
            $this->_initCache();
            $md = $this->_cache->getMetaData($mailbox, null, array(self::CACHE_MODSEQ, 'uidvalid'));

            if (isset($md[self::CACHE_MODSEQ])) {
                if ($uids = $this->_cache->get($mailbox)) {
                    $uids = $this->getIdsOb($uids);

                    
                    if (strlen($uid_str = $uids->tostring_sort) > 7000) {
                        $uid_str = $uids->range_string;
                    }
                } else {
                    $uid_str = null;
                }

                
                $cmd->add(new Horde_Imap_Client_Data_Format_List(array(
                    'QRESYNC',
                    new Horde_Imap_Client_Data_Format_List(array_filter(array(
                        $md['uidvalid'],
                        $md[self::CACHE_MODSEQ],
                        $uid_str
                    )))
                )));
            }

            
            if ($this->_selected) {
                $pipeline->data['qresyncmbox'] = array($mailbox, $mode);
            } else {
                $this->_changeSelected($mailbox, $mode);
            }
        } else {
            if (!isset($this->_temp['enabled']['CONDSTORE']) &&
                $this->_initCache() &&
                $this->queryCapability('CONDSTORE')) {
                
                $cmd->add(new Horde_Imap_Client_Data_Format_List('CONDSTORE'));
                $this->_enabled(array('CONDSTORE'), 2);
            }

            $this->_changeSelected($mailbox, $mode);
        }

        try {
            $this->_sendCmd($pipeline);
        } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
                                    if ($e->status === Horde_Imap_Client_Interaction_Server::NO) {
                $this->_changeSelected(null);
                $this->_mode = 0;
                if (!$e->getCode()) {
                    throw new Horde_Imap_Client_Exception(
                        sprintf(Horde_Imap_Client_Translation::r("Could not open mailbox \"%s\"."), $mailbox),
                        Horde_Imap_Client_Exception::MAILBOX_NOOPEN
                    );
                }
            }
            throw $e;
        }

        if ($qresync) {
            
            $this->_mailboxOb()->sync = true;
        }
    }

    
    protected function _createMailbox(Horde_Imap_Client_Mailbox $mailbox, $opts)
    {
        $cmd = $this->_command('CREATE')->add(
            new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
        );

        if (!empty($opts['special_use'])) {
            $cmd->add(array(
                'USE',
                new Horde_Imap_Client_Data_Format_List($opts['special_use'])
            ));
        }

                $this->_sendCmd($cmd);
    }

    
    protected function _deleteMailbox(Horde_Imap_Client_Mailbox $mailbox)
    {
                        if ($mailbox->equals($this->_selected)) {
            $this->close();
        }

        $cmd = $this->_command('DELETE')->add(
            new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
        );

        try {
                        $this->_sendCmd($cmd);
        } catch (Horde_Imap_Client_Exception $e) {
                                    $this->expunge($mailbox, array(
                'delete' => true
            ));
            $this->_sendCmd($cmd);
        }
    }

    
    protected function _renameMailbox(Horde_Imap_Client_Mailbox $old,
                                      Horde_Imap_Client_Mailbox $new)
    {
                        if ($old->equals($this->_selected)) {
            $this->close();
        }

                $this->_sendCmd(
            $this->_command('RENAME')->add(array(
                new Horde_Imap_Client_Data_Format_Mailbox($old),
                new Horde_Imap_Client_Data_Format_Mailbox($new)
            ))
        );
    }

    
    protected function _subscribeMailbox(Horde_Imap_Client_Mailbox $mailbox,
                                         $subscribe)
    {
                        $this->_sendCmd(
            $this->_command(
                $subscribe ? 'SUBSCRIBE' : 'UNSUBSCRIBE'
            )->add(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
            )
        );
    }

    
    protected function _listMailboxes($pattern, $mode, $options)
    {
                        if (($mode == Horde_Imap_Client::MBOX_SUBSCRIBED) &&
            empty($options['attributes']) &&
            empty($options['children']) &&
            empty($options['recursivematch']) &&
            empty($options['remote']) &&
            empty($options['special_use']) &&
            empty($options['status'])) {
            return $this->_getMailboxList(
                $pattern,
                Horde_Imap_Client::MBOX_SUBSCRIBED,
                array(
                    'delimiter' => !empty($options['delimiter']),
                    'flat' => !empty($options['flat']),
                    'no_listext' => true
                )
            );
        }

                                if (($mode != Horde_Imap_Client::MBOX_ALL) &&
            !$this->queryCapability('LIST-EXTENDED')) {
            $subscribed = $this->_getMailboxList($pattern, Horde_Imap_Client::MBOX_SUBSCRIBED, array('flat' => true));

                                    if (($mode == Horde_Imap_Client::MBOX_SUBSCRIBED) &&
                !empty($options['flat'])) {
                return $subscribed;
            }
        } else {
            $subscribed = null;
        }

        return $this->_getMailboxList($pattern, $mode, $options, $subscribed);
    }

    
    protected function _getMailboxList($pattern, $mode, $options,
                                       $subscribed = null)
    {
        $check = (($mode != Horde_Imap_Client::MBOX_ALL) && !is_null($subscribed));

                $pipeline = $this->_pipeline();
        $pipeline->data['mailboxlist'] = array(
            'check' => $check,
            'ext' => false,
            'options' => $options,
            'subexist' => ($mode == Horde_Imap_Client::MBOX_SUBSCRIBED_EXISTS),
            
            'subscribed' => ($check ? (array_flip(array_map('strval', $subscribed)) + array('INBOX' => true)) : null)
        );
        $pipeline->data['listresponse'] = array();

        $cmds = array();
        $return_opts = new Horde_Imap_Client_Data_Format_List();

        if ($this->queryCapability('LIST-EXTENDED') &&
            empty($options['no_listext'])) {
            $cmd = $this->_command('LIST');
            $pipeline->data['mailboxlist']['ext'] = true;

            $select_opts = new Horde_Imap_Client_Data_Format_List();
            $subscribed = false;

            if (($mode == Horde_Imap_Client::MBOX_SUBSCRIBED) ||
                ($mode == Horde_Imap_Client::MBOX_SUBSCRIBED_EXISTS)) {
                $select_opts->add('SUBSCRIBED');
                $return_opts->add('SUBSCRIBED');
                $subscribed = true;
            }

            if (!empty($options['remote'])) {
                $select_opts->add('REMOTE');
            }

            if (!empty($options['recursivematch'])) {
                $select_opts->add('RECURSIVEMATCH');
            }

            $cmd->add(array(
                $select_opts,
                ''
            ));

            $tmp = new Horde_Imap_Client_Data_Format_List();
            foreach ($pattern as $val) {
                if ($subscribed && (strcasecmp($val, 'INBOX') === 0)) {
                    $cmds[] = $this->_command('LIST')->add(array(
                        '',
                        'INBOX'
                    ));
                } else {
                    $tmp->add(new Horde_Imap_Client_Data_Format_ListMailbox($val));
                }
            }

            if (count($tmp)) {
                $cmd->add($tmp);
                $cmds[] = $cmd;
            }

            if (!empty($options['children'])) {
                $return_opts->add('CHILDREN');
            }

            if (!empty($options['special_use'])) {
                $return_opts->add('SPECIAL-USE');
            }
        } else {
            foreach ($pattern as $val) {
                $cmds[] = $this->_command(
                    ($mode == Horde_Imap_Client::MBOX_SUBSCRIBED) ? 'LSUB' : 'LIST'
                )->add(array(
                    '',
                    new Horde_Imap_Client_Data_Format_ListMailbox($val)
                ));
            }
        }

        
        if (!empty($options['status']) &&
            $this->queryCapability('LIST-STATUS')) {
            $available_status = array(
                Horde_Imap_Client::STATUS_MESSAGES,
                Horde_Imap_Client::STATUS_RECENT,
                Horde_Imap_Client::STATUS_UIDNEXT,
                Horde_Imap_Client::STATUS_UIDVALIDITY,
                Horde_Imap_Client::STATUS_UNSEEN,
                Horde_Imap_Client::STATUS_HIGHESTMODSEQ
            );

            $status_opts = array();
            foreach (array_intersect($this->_statusFields, $available_status) as $key => $val) {
                if ($options['status'] & $val) {
                    $status_opts[] = $key;
                }
            }

            if (count($status_opts)) {
                $return_opts->add(array(
                    'STATUS',
                    new Horde_Imap_Client_Data_Format_List(
                        array_map('strtoupper', $status_opts)
                    )
                ));
            }
        }

        foreach ($cmds as $val) {
            if (count($return_opts)) {
                $val->add(array(
                    'RETURN',
                    $return_opts
                ));
            }

            $pipeline->add($val);
        }

        try {
            $lr = $this->_sendCmd($pipeline)->data['listresponse'];
        } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
            
            if (($e->status === Horde_Imap_Client_Interaction_Server::BAD) &&
                $this->queryCapability('LIST-EXTENDED')) {
                $this->_unsetCapability('LIST-EXTENDED');
                return $this->_listMailboxes($pattern, $mode, $options);
            }

            throw $e;
        }

        if (!empty($options['flat'])) {
            return array_values($lr);
        }

        
        if (!empty($options['status'])) {
            foreach ($pattern as $val) {
                $val_utf8 = Horde_Imap_Client_Utf7imap::Utf7ImapToUtf8($val);
                if (isset($lr[$val_utf8])) {
                    $lr[$val_utf8]['status'] = $this->_prepareStatusResponse($status_opts, $val_utf8);
                }
            }
        }

        return $lr;
    }

    
    protected function _parseList(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        $data->next();
        $attr = $data->flushIterator();
        $delimiter = $data->next();
        $mbox = Horde_Imap_Client_Mailbox::get($data->next(), true);
        $ml = $pipeline->data['mailboxlist'];

        if ($ml['check'] &&
            $ml['subexist'] &&
                        !isset($ml['subscribed'][strval($mbox)])) {
            return;
        } elseif ((!$ml['check'] && $ml['subexist']) ||
                   (empty($ml['options']['flat']) &&
                    !empty($ml['options']['attributes']))) {
            $attr = array_flip(array_map('strtolower', $attr));
            if ($ml['subexist'] &&
                !$ml['check'] &&
                isset($attr['\\nonexistent'])) {
                return;
            }
        }

        if (empty($ml['options']['flat'])) {
            $tmp = array(
                'mailbox' => $mbox
            );

            if (!empty($ml['options']['attributes'])) {
                
                if ($ml['ext']) {
                    if (isset($attr['\\noinferiors'])) {
                        $attr['\\hasnochildren'] = 1;
                    }
                    if (isset($attr['\\nonexistent'])) {
                        $attr['\\noselect'] = 1;
                    }
                }
                $tmp['attributes'] = array_keys($attr);
            }
            if (!empty($ml['options']['delimiter'])) {
                $tmp['delimiter'] = $delimiter;
            }
            if ($data->next() !== false) {
                $tmp['extended'] = $data->flushIterator();
            }
            $pipeline->data['listresponse'][strval($mbox)] = $tmp;
        } else {
            $pipeline->data['listresponse'][] = $mbox;
        }
    }

    
    protected function _status($mboxes, $flags)
    {
        $out = $to_process = array();
        $pipeline = $this->_pipeline();
        $unseen_flags = array(
            Horde_Imap_Client::STATUS_FIRSTUNSEEN,
            Horde_Imap_Client::STATUS_UNSEEN
        );

        foreach ($mboxes as $mailbox) {
            
            if (($flags & Horde_Imap_Client::STATUS_FIRSTUNSEEN) ||
                ($flags & Horde_Imap_Client::STATUS_FLAGS) ||
                ($flags & Horde_Imap_Client::STATUS_PERMFLAGS) ||
                ($flags & Horde_Imap_Client::STATUS_UIDNOTSTICKY)) {
                $this->openMailbox($mailbox);
            }

            $mbox_ob = $this->_mailboxOb($mailbox);
            $data = $query = array();

            foreach ($this->_statusFields as $key => $val) {
                if (!($val & $flags)) {
                    continue;
                }

                if ($val == Horde_Imap_Client::STATUS_HIGHESTMODSEQ) {
                    
                    if (!$this->queryCapability('CONDSTORE')) {
                        continue;
                    }

                    
                    if (!isset($this->_temp['enabled']['CONDSTORE'])) {
                        $this->_enabled(array('CONDSTORE'), 2);
                    }
                }

                if ($mailbox->equals($this->_selected)) {
                    if (!is_null($tmp = $mbox_ob->getStatus($val))) {
                        $data[$key] = $tmp;
                    } elseif (($val == Horde_Imap_Client::STATUS_UIDNEXT) &&
                              ($flags & Horde_Imap_Client::STATUS_UIDNEXT_FORCE)) {
                        
                        if ($mbox_ob->getStatus(Horde_Imap_Client::STATUS_MESSAGES) == 0) {
                            $data[$key] = 0;
                        } else {
                            $fquery = new Horde_Imap_Client_Fetch_Query();
                            $fquery->uid();
                            $fetch_res = $this->fetch($this->_selected, $fquery, array(
                                'ids' => $this->getIdsOb(Horde_Imap_Client_Ids::LARGEST)
                            ));
                            $data[$key] = $fetch_res->first()->getUid() + 1;
                        }
                    } elseif (in_array($val, $unseen_flags)) {
                        
                        $squery = new Horde_Imap_Client_Search_Query();
                        $squery->flag(Horde_Imap_Client::FLAG_SEEN, false);
                        $search = $this->search($mailbox, $squery, array(
                            'results' => array(
                                Horde_Imap_Client::SEARCH_RESULTS_MIN,
                                Horde_Imap_Client::SEARCH_RESULTS_COUNT
                            ),
                            'sequence' => true
                        ));

                        $mbox_ob->setStatus(Horde_Imap_Client::STATUS_FIRSTUNSEEN, $search['min']);
                        $mbox_ob->setStatus(Horde_Imap_Client::STATUS_UNSEEN, $search['count']);

                        $data[$key] = $mbox_ob->getStatus($val);
                    }
                } else {
                    $query[] = $key;
                }
            }

            $out[strval($mailbox)] = $data;

            if (count($query)) {
                $pipeline->add(
                    $this->_command('STATUS')->add(array(
                        new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                        new Horde_Imap_Client_Data_Format_List(
                            array_map('strtoupper', $query)
                        )
                    ))
                );
                $to_process[] = array($query, $mailbox);
            }
        }

        if (count($pipeline)) {
            $this->_sendCmd($pipeline);

            foreach ($to_process as $val) {
                $out[strval($val[1])] += $this->_prepareStatusResponse($val[0], $val[1]);
            }
        }

        return $out;
    }

    
    protected function _parseStatus(Horde_Imap_Client_Tokenize $data)
    {
                $mbox_ob = $this->_mailboxOb(
            Horde_Imap_Client_Mailbox::get($data->next(), true)
        );

        $data->next();

        while (($k = $data->next()) !== false) {
            $mbox_ob->setStatus(
                $this->_statusFields[strtolower($k)],
                $data->next()
            );
        }
    }

    
    protected function _prepareStatusResponse($request, $mailbox)
    {
        $mbox_ob = $this->_mailboxOb($mailbox);
        $out = array();

        foreach ($request as $val) {
            $out[$val] = $mbox_ob->getStatus($this->_statusFields[$val]);
        }

        return $out;
    }

    
    protected function _append(Horde_Imap_Client_Mailbox $mailbox, $data,
                               $options)
    {
                if ((count($data) > 1) && !$this->queryCapability('MULTIAPPEND')) {
            $result = $this->getIdsOb();
            foreach (array_keys($data) as $key) {
                $res = $this->_append($mailbox, array($data[$key]), $options);
                if (($res === true) || ($result === true)) {
                    $result = true;
                } else {
                    $result->add($res);
                }
            }
            return $result;
        }

                $catenate = $this->queryCapability('CATENATE');

        $asize = 0;

        $cmd = $this->_command('APPEND')->add(
            new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
        );
        $cmd->literal8 = true;

        foreach (array_keys($data) as $key) {
            if (!empty($data[$key]['flags'])) {
                $tmp = new Horde_Imap_Client_Data_Format_List();
                foreach ($data[$key]['flags'] as $val) {
                    
                    if (strcasecmp($val, Horde_Imap_Client::FLAG_RECENT) !== 0) {
                        $tmp->add($val);
                    }
                }
                $cmd->add($tmp);
            }

            if (!empty($data[$key]['internaldate'])) {
                $cmd->add(new Horde_Imap_Client_Data_Format_DateTime($data[$key]['internaldate']));
            }

            if (is_array($data[$key]['data'])) {
                if ($catenate) {
                    $cmd->add('CATENATE');
                    $tmp = new Horde_Imap_Client_Data_Format_List();
                } else {
                    $data_stream = new Horde_Stream_Temp();
                }

                reset($data[$key]['data']);
                while (list(,$v) = each($data[$key]['data'])) {
                    switch ($v['t']) {
                    case 'text':
                        if ($catenate) {
                            $tmp->add(array(
                                'TEXT',
                                $this->_appendData($v['v'], $asize)
                            ));
                        } else {
                            if (is_resource($v['v'])) {
                                rewind($v['v']);
                            }
                            $data_stream->add($v['v']);
                        }
                        break;

                    case 'url':
                        if ($catenate) {
                            $tmp->add(array(
                                'URL',
                                new Horde_Imap_Client_Data_Format_Astring($v['v'])
                            ));
                        } else {
                            $data_stream->add($this->_convertCatenateUrl($v['v']));
                        }
                        break;
                    }
                }

                if ($catenate) {
                    $cmd->add($tmp);
                } else {
                    $cmd->add($this->_appendData($data_stream->stream, $asize));
                }
            } else {
                $cmd->add($this->_appendData($data[$key]['data'], $asize));
            }
        }

        
        $cmd->literalplus = (($asize < 524288) && !$this->queryCapability('BINARY'));

                                        $this->close();

        try {
            $resp = $this->_sendCmd($cmd);
        } catch (Horde_Imap_Client_Exception $e) {
            switch ($e->getCode()) {
            case $e::CATENATE_BADURL:
            case $e::CATENATE_TOOBIG:
                
                $this->_unsetCapability('CATENATE');
                return $this->_append($mailbox, $data, $options);

            case $e::DISCONNECT:
                
                if ($this->queryCapability('BINARY')) {
                                        $this->login();
                    $this->_unsetCapability('BINARY');
                    return $this->_append($mailbox, $data, $options);
                }
                break;
            }

            if (!empty($options['create']) &&
                !empty($e->resp_data['trycreate'])) {
                $this->createMailbox($mailbox);
                unset($options['create']);
                return $this->_append($mailbox, $data, $options);
            }

            
            if ($this->queryCapability('BINARY') &&
                ($e instanceof Horde_Imap_Client_Exception_ServerResponse)) {
                switch ($e->status) {
                case Horde_Imap_Client_Interaction_Server::BAD:
                case Horde_Imap_Client_Interaction_Server::NO:
                    $this->_unsetCapability('BINARY');
                    return $this->_append($mailbox, $data, $options);
                }
            }

            throw $e;
        }

        
        return isset($resp->data['appenduid'])
            ? $resp->data['appenduid']
            : true;
    }

    
    protected function _appendData($data, &$asize)
    {
        if (is_resource($data)) {
            rewind($data);
        }

        $ob = new Horde_Imap_Client_Data_Format_String($data, array(
            'eol' => true,
            'skipscan' => true
        ));

                $ob->forceLiteral();

        $asize += $ob->length();

        return $ob;
    }

    
    protected function _convertCatenateUrl($url)
    {
        $e = $part = null;
        $url = new Horde_Imap_Client_Url($url);

        if (!is_null($url->mailbox) && !is_null($url->uid)) {
            try {
                $status_res = is_null($url->uidvalidity)
                    ? null
                    : $this->status($url->mailbox, Horde_Imap_Client::STATUS_UIDVALIDITY);

                if (is_null($status_res) ||
                    ($status_res['uidvalidity'] == $url->uidvalidity)) {
                    if (!isset($this->_temp['catenate_ob'])) {
                        $this->_temp['catenate_ob'] = new Horde_Imap_Client_Socket_Catenate($this);
                    }
                    $part = $this->_temp['catenate_ob']->fetchFromUrl($url);
                }
            } catch (Horde_Imap_Client_Exception $e) {}
        }

        if (is_null($part)) {
            $message = 'Bad IMAP URL given in CATENATE data: ' . strval($url);
            if ($e) {
                $message .= ' ' . $e->getMessage();
            }

            throw new InvalidArgumentException($message);
        }

        return $part;
    }

    
    protected function _check()
    {
                $this->_sendCmd($this->_command('CHECK'));
    }

    
    protected function _close($options)
    {
        if (empty($options['expunge'])) {
            if ($this->queryCapability('UNSELECT')) {
                                $this->_sendCmd($this->_command('UNSELECT'));
            } else {
                
                try {
                    $this->_sendCmd($this->_command('EXAMINE')->add(
                        new Horde_Imap_Client_Data_Format_Mailbox("\24nonexist\24")
                    ));

                    
                    $this->_sendCmd($this->_command('CLOSE'));
                } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
                                    }
            }
        } else {
                                    if ($this->_initCache(true)) {
                $this->expunge($this->_selected);
            }

                        $this->_sendCmd($this->_command('CLOSE'));
        }
    }

    
    protected function _expunge($options)
    {
        $expunged_ob = $modseq = null;
        $ids = $options['ids'];
        $list_msgs = !empty($options['list']);
        $uidplus = $this->queryCapability('UIDPLUS');
        $unflag = array();
        $use_cache = $this->_initCache(true);

        if ($ids->all) {
            if (!$uidplus || $list_msgs || $use_cache) {
                $ids = $this->resolveIds($this->_selected, $ids, 2);
            }
        } elseif ($uidplus) {
            
            unset($this->_temp['search_save']);
            if (isset($this->_temp['enabled']['QRESYNC'])) {
                $ids = $this->resolveIds($this->_selected, $ids, 1);
                if ($list_msgs) {
                    $modseq = $this->_mailboxOb()->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ);
                }
            } else {
                $ids = $this->resolveIds($this->_selected, $ids, ($list_msgs || $use_cache) ? 2 : 1);
            }
            if (!empty($this->_temp['search_save'])) {
                $ids = $this->getIdsOb(Horde_Imap_Client_Ids::SEARCH_RES);
            }
        } else {
            
            $squery = new Horde_Imap_Client_Search_Query();
            $squery->flag(Horde_Imap_Client::FLAG_DELETED, true);
            $squery->ids($ids, true);

            $s_res = $this->search($this->_selected, $squery, array(
                'results' => array(
                    Horde_Imap_Client::SEARCH_RESULTS_MATCH,
                    Horde_Imap_Client::SEARCH_RESULTS_SAVE
                )
            ));

            $this->store($this->_selected, array(
                'ids' => empty($s_res['save']) ? $s_res['match'] : $this->getIdsOb(Horde_Imap_Client_Ids::SEARCH_RES),
                'remove' => array(Horde_Imap_Client::FLAG_DELETED)
            ));

            $unflag = $s_res['match'];
        }

        if ($list_msgs) {
            $expunged_ob = $this->getIdsOb();
            $this->_temp['expunged'] = $expunged_ob;
        }

        
        if ($uidplus) {
            
            if (empty($options['delete'])) {
                $pipeline = $this->_pipeline();
            } else {
                $pipeline = $this->_storeCmd(array(
                    'add' => array(
                        Horde_Imap_Client::FLAG_DELETED
                    ),
                    'ids' => $ids
                ));
            }

            foreach ($ids->split(2000) as $val) {
                $pipeline->add(
                    $this->_command('UID EXPUNGE')->add($val)
                );
            }

            $resp = $this->_sendCmd($pipeline);
        } else {
            if (!empty($options['delete'])) {
                $this->store($this->_selected, array(
                    'add' => array(Horde_Imap_Client::FLAG_DELETED),
                    'ids' => $ids
                ));
            }

            if ($use_cache || $list_msgs) {
                $this->_sendCmd($this->_command('EXPUNGE'));
            } else {
                
                $this->close(array('expunge' => true));
            }
        }

        unset($this->_temp['expunged']);

        if (!empty($unflag)) {
            $this->store($this->_selected, array(
                'add' => array(Horde_Imap_Client::FLAG_DELETED),
                'ids' => $unflag
            ));
        }

        if (!is_null($modseq) && !empty($resp->data['expunge_seen'])) {
            
            $expunged_ob = $this->vanished($this->_selected, $modseq, array(
                'ids' => $ids
            ));
            $this->_deleteMsgs($this->_selected, $expunged_ob, array(
                'pipeline' => $resp
            ));
        }

        return $expunged_ob;
    }

    
    protected function _parseVanished(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        
        if (($curr = $data->next()) === true) {
            if (strtoupper($data->next()) === 'EARLIER') {
                
                $data->next();
                $vanished = $this->getIdsOb($data->next());
                if (isset($pipeline->data['vanished'])) {
                    $pipeline->data['vanished']->add($vanished);
                } else {
                    $this->_deleteMsgs($this->_selected, $vanished, array(
                        'pipeline' => $pipeline
                    ));
                }
            }
        } else {
            
            $this->_deleteMsgs($this->_selected, $this->getIdsOb($curr), array(
                'decrement' => true,
                'pipeline' => $pipeline
            ));
        }
    }

    
    protected function _search($query, $options)
    {
        $sort_criteria = array(
            Horde_Imap_Client::SORT_ARRIVAL => 'ARRIVAL',
            Horde_Imap_Client::SORT_CC => 'CC',
            Horde_Imap_Client::SORT_DATE => 'DATE',
            Horde_Imap_Client::SORT_DISPLAYFROM => 'DISPLAYFROM',
            Horde_Imap_Client::SORT_DISPLAYTO => 'DISPLAYTO',
            Horde_Imap_Client::SORT_FROM => 'FROM',
            Horde_Imap_Client::SORT_REVERSE => 'REVERSE',
            Horde_Imap_Client::SORT_RELEVANCY => 'RELEVANCY',
                                    Horde_Imap_Client::SORT_SEQUENCE => 'SEQUENCE',
            Horde_Imap_Client::SORT_SIZE => 'SIZE',
            Horde_Imap_Client::SORT_SUBJECT => 'SUBJECT',
            Horde_Imap_Client::SORT_TO => 'TO'
        );

        $results_criteria = array(
            Horde_Imap_Client::SEARCH_RESULTS_COUNT => 'COUNT',
            Horde_Imap_Client::SEARCH_RESULTS_MATCH => 'ALL',
            Horde_Imap_Client::SEARCH_RESULTS_MAX => 'MAX',
            Horde_Imap_Client::SEARCH_RESULTS_MIN => 'MIN',
            Horde_Imap_Client::SEARCH_RESULTS_RELEVANCY => 'RELEVANCY',
            Horde_Imap_Client::SEARCH_RESULTS_SAVE => 'SAVE'
        );

                $esearch = $return_sort = $server_seq_sort = $server_sort = false;
        if (!empty($options['sort'])) {
            
            if (count(array_intersect($options['sort'], array_keys($sort_criteria))) === 0) {
                unset($options['sort']);
            } else {
                $return_sort = true;

                if ($server_sort = $this->queryCapability('SORT')) {
                    
                    $server_sort =
                        !array_intersect($options['sort'], array(Horde_Imap_Client::SORT_DISPLAYFROM, Horde_Imap_Client::SORT_DISPLAYTO)) ||
                        (is_array($server_sort) &&
                         in_array('DISPLAY', $server_sort));
                }

                
                if ($server_sort &&
                    in_array(Horde_Imap_Client::SORT_SEQUENCE, $options['sort'])) {
                    $server_sort = false;

                    
                    switch (count($options['sort'])) {
                    case 1:
                        $server_seq_sort = true;
                        break;

                    case 2:
                        $server_seq_sort = (reset($options['sort']) == Horde_Imap_Client::SORT_REVERSE);
                        break;
                    }
                }
            }
        }

        $charset = is_null($options['_query']['charset'])
            ? 'US-ASCII'
            : $options['_query']['charset'];
        $partial = false;

        if ($server_sort) {
            $cmd = $this->_command(
                empty($options['sequence']) ? 'UID SORT' : 'SORT'
            );
            $results = array();

                        $esearch = false;

                        if ($this->queryCapability('ESORT')) {
                foreach ($options['results'] as $val) {
                    if (isset($results_criteria[$val]) &&
                        ($val != Horde_Imap_Client::SEARCH_RESULTS_SAVE)) {
                        $results[] = $results_criteria[$val];
                    }
                }
                $esearch = true;
            }

                        if ((!$esearch || !empty($options['partial'])) &&
                ($cap = $this->queryCapability('CONTEXT')) &&
                in_array('SORT', $cap)) {
                
                $esearch = true;

                if (!empty($options['partial'])) {
                    
                    $results = array_diff($results, array('ALL'));

                    $results[] = 'PARTIAL';
                    $results[] = $options['partial'];
                    $partial = true;
                }
            }

            if ($esearch && empty($this->_init['noesearch'])) {
                $cmd->add(array(
                    'RETURN',
                    new Horde_Imap_Client_Data_Format_List($results)
                ));
            }

            $tmp = new Horde_Imap_Client_Data_Format_List();
            foreach ($options['sort'] as $val) {
                if (isset($sort_criteria[$val])) {
                    $tmp->add($sort_criteria[$val]);
                }
            }
            $cmd->add($tmp);

                        $cmd->add($charset);
        } else {
            $cmd = $this->_command(
                empty($options['sequence']) ? 'UID SEARCH' : 'SEARCH'
            );
            $esearch = false;
            $results = array();

                        if ($this->queryCapability('ESEARCH')) {
                foreach ($options['results'] as $val) {
                    if (isset($results_criteria[$val])) {
                        $results[] = $results_criteria[$val];
                    }
                }
                $esearch = true;
            }

                        if ((!$esearch || !empty($options['partial'])) &&
                ($cap = $this->queryCapability('CONTEXT')) &&
                in_array('SEARCH', $cap)) {
                
                $esearch = true;

                if (!empty($options['partial'])) {
                                        $results = array_diff($results, array('ALL'));

                    $results[] = 'PARTIAL';
                    $results[] = $options['partial'];
                    $partial = true;
                }
            }

            if ($esearch && empty($this->_init['noesearch'])) {
                                                $cmd->add(array(
                    'RETURN',
                    new Horde_Imap_Client_Data_Format_List($results)
                ));
            }

                        if ($charset != 'US-ASCII') {
                $cmd->add(array(
                    'CHARSET',
                    $options['_query']['charset']
                ));
            }
        }

        $cmd->add($options['_query']['query'], true);

        $pipeline = $this->_pipeline($cmd);
        $pipeline->data['esearchresp'] = array();
        $er = &$pipeline->data['esearchresp'];
        $pipeline->data['searchresp'] = $this->getIdsOb(array(), !empty($options['sequence']));
        $sr = &$pipeline->data['searchresp'];

        try {
            $resp = $this->_sendCmd($pipeline);
        } catch (Horde_Imap_Client_Exception $e) {
            if (($e instanceof Horde_Imap_Client_Exception_ServerResponse) &&
                ($e->status === Horde_Imap_Client_Interaction_Server::NO) &&
                ($charset != 'US-ASCII')) {
                
                $s_charset = $this->_init['s_charset'];
                $s_charset[$charset] = false;
                $this->_setInit('s_charset', $s_charset);
                $e->setCode(Horde_Imap_Client_Exception::BADCHARSET);
            }

            if (empty($this->_temp['search_retry'])) {
                $this->_temp['search_retry'] = true;

                
                if ($esearch && ($charset != 'US-ASCII')) {
                    $this->_unsetCapability('ESEARCH');
                    $this->_setInit('noesearch', true);

                    try {
                        return $this->_search($query, $options);
                    } catch (Horde_Imap_Client_Exception $e) {}
                }

                
                if (($e->getCode() === Horde_Imap_Client_Exception::BADCHARSET) &&
                    ($charset != 'US-ASCII')) {
                    foreach (array_merge(array_keys(array_filter($this->_init['s_charset'])), array('US-ASCII')) as $val) {
                        $this->_temp['search_retry'] = 1;
                        $new_query = clone($query);
                        try {
                            $new_query->charset($val);
                            $options['_query'] = $new_query->build($this->capability());
                            return $this->_search($new_query, $options);
                        } catch (Horde_Imap_Client_Exception $e) {}
                    }
                }

                unset($this->_temp['search_retry']);
            }

            throw $e;
        }

        if ($return_sort && !$server_sort) {
            if ($server_seq_sort) {
                $sr->sort();
                if (reset($options['sort']) == Horde_Imap_Client::SORT_REVERSE) {
                    $sr->reverse();
                }
            } else {
                if (!isset($this->_temp['clientsort'])) {
                    $this->_temp['clientsort'] = new Horde_Imap_Client_Socket_ClientSort($this);
                }
                $sr = $this->getIdsOb($this->_temp['clientsort']->clientSort($sr, $options), !empty($options['sequence']));
            }
        }

        if (!$partial && !empty($options['partial'])) {
            $partial = $this->getIdsOb($options['partial'], true);
            $min = $partial->min - 1;

            $sr->sort();
            $sr = $this->getIdsOb(
                array_slice($sr->ids(), $min, $partial->max - $min),
                !empty($options['sequence'])
            );
        }

        $ret = array();
        foreach ($options['results'] as $val) {
            switch ($val) {
            case Horde_Imap_Client::SEARCH_RESULTS_COUNT:
                $ret['count'] = ($esearch && !$partial)
                    ? $er['count']
                    : count($sr);
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_MATCH:
                $ret['match'] = $sr;
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_MAX:
                $ret['max'] = $esearch
                    ? (!$partial && isset($er['max']) ? $er['max'] : null)
                    : (count($sr) ? max($sr->ids) : null);
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_MIN:
                $ret['min'] = $esearch
                    ? (!$partial && isset($er['min']) ? $er['min'] : null)
                    : (count($sr) ? min($sr->ids) : null);
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_RELEVANCY:
                $ret['relevancy'] = ($esearch && isset($er['relevancy'])) ? $er['relevancy'] : array();
                break;

            case Horde_Imap_Client::SEARCH_RESULTS_SAVE:
                $this->_temp['search_save'] = $ret['save'] = $esearch ? empty($resp->data['searchnotsaved']) : false;
                break;
            }
        }

                if (!empty($er['modseq'])) {
            $ret['modseq'] = $er['modseq'];
        }

        unset($this->_temp['search_retry']);

        
        if (!empty($resp->data['expungeissued'])) {
            $this->noop();
        }

        return $ret;
    }

    
    protected function _parseSearch(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        $data
    )
    {
        
        $pipeline->data['searchresp']->add($data);
    }

    
    protected function _parseEsearch(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
                if ($data->next() === true) {
            $data->flushIterator(false);
        }

                $current = $data->next();
        if (strtoupper($current) === 'UID') {
            $current = $data->next();
        }

        do {
            $val = $data->next();
            $tag = strtoupper($current);

            switch ($tag) {
            case 'ALL':
                $this->_parseSearch($pipeline, $val);
                break;

            case 'COUNT':
            case 'MAX':
            case 'MIN':
            case 'MODSEQ':
            case 'RELEVANCY':
                $pipeline->data['esearchresp'][strtolower($tag)] = $val;
                break;

            case 'PARTIAL':
                                $partial = $val->flushIterator();
                $this->_parseSearch($pipeline, end($partial));
                break;
            }
        } while (($current = $data->next()) !== false);
    }

    
    protected function _setComparator($comparator)
    {
        $cmd = $this->_command('COMPARATOR');
        foreach ($comparator as $val) {
            $cmd->add(new Horde_Imap_Client_Data_Format_Astring($val));
        }
        $this->_sendCmd($cmd);
    }

    
    protected function _getComparator()
    {
        $resp = $this->_sendCmd($this->_command('COMPARATOR'));

        return isset($resp->data['comparator'])
            ? $resp->data['comparator']
            : null;
    }

    
    protected function _parseComparator(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        $data
    )
    {
        $pipeline->data['comparator'] = $data->next();
            }

    
    protected function _thread($options)
    {
        $thread_criteria = array(
            Horde_Imap_Client::THREAD_ORDEREDSUBJECT => 'ORDEREDSUBJECT',
            Horde_Imap_Client::THREAD_REFERENCES => 'REFERENCES',
            Horde_Imap_Client::THREAD_REFS => 'REFS'
        );

        $tsort = (isset($options['criteria']))
            ? (is_string($options['criteria']) ? strtoupper($options['criteria']) : $thread_criteria[$options['criteria']])
            : 'ORDEREDSUBJECT';

        $cap = $this->queryCapability('THREAD');
        if (!$cap || !in_array($tsort, $cap)) {
            switch ($tsort) {
            case 'ORDEREDSUBJECT':
                if (empty($options['search'])) {
                    $ids = $this->getIdsOb(Horde_Imap_Client_Ids::ALL, !empty($options['sequence']));
                } else {
                    $search_res = $this->search($this->_selected, $options['search'], array('sequence' => !empty($options['sequence'])));
                    $ids = $search_res['match'];
                }

                
                $query = new Horde_Imap_Client_Fetch_Query();
                $query->envelope();
                $query->imapDate();

                $fetch_res = $this->fetch($this->_selected, $query, array(
                    'ids' => $ids
                ));

                if (!isset($this->_temp['clientsort'])) {
                    $this->_temp['clientsort'] = new Horde_Imap_Client_Socket_ClientSort($this);
                }
                return $this->_temp['clientsort']->threadOrderedSubject($fetch_res, empty($options['sequence']));

            case 'REFERENCES':
            case 'REFS':
                throw new Horde_Imap_Client_Exception_NoSupportExtension(
                    'THREAD',
                    sprintf('Server does not support "%s" thread sort.', $tsort)
                );
            }
        }

        $cmd = $this->_command(
            empty($options['sequence']) ? 'UID THREAD' : 'THREAD'
        )->add($tsort);

        if (empty($options['search'])) {
            $cmd->add(array(
                'US-ASCII',
                'ALL'
            ));
        } else {
            $search_query = $options['search']->build();
            $cmd->add(is_null($search_query['charset']) ? 'US-ASCII' : $search_query['charset']);
            $cmd->add($search_query['query'], true);
        }

        return new Horde_Imap_Client_Data_Thread(
            $this->_sendCmd($cmd)->data['threadparse'],
            empty($options['sequence']) ? 'uid' : 'sequence'
        );
    }

    
    protected function _parseThread(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        $out = array();

        while ($data->next() !== false) {
            $thread = array();
            $this->_parseThreadLevel($thread, $data);
            $out[] = $thread;
        }

        $pipeline->data['threadparse'] = $out;
    }

    
    protected function _parseThreadLevel(&$thread,
                                         Horde_Imap_Client_Tokenize $data,
                                         $level = 0)
    {
        while (($curr = $data->next()) !== false) {
            if ($curr === true) {
                $this->_parseThreadLevel($thread, $data, $level);
            } elseif (!is_bool($curr)) {
                $thread[$curr] = $level++;
            }
        }
    }

    
    protected function _fetch(Horde_Imap_Client_Fetch_Results $results,
                              $queries)
    {
        $pipeline = $this->_pipeline();
        $pipeline->data['fetch_lookup'] = array();
        $pipeline->data['fetch_followup'] = array();

        foreach ($queries as $options) {
            $this->_fetchCmd($pipeline, $options);
            $sequence = $options['ids']->sequence;
        }

        try {
            $resp = $this->_sendCmd($pipeline);

            
            if (!empty($resp->data['expungeissued'])) {
                $this->noop();
            }

            foreach ($resp->fetch as $k => $v) {
                $results->get($sequence ? $k : $v->getUid())->merge($v);
            }
        } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
            if ($e->status === Horde_Imap_Client_Interaction_Server::NO) {
                if ($e->getCode() === $e::UNKNOWNCTE) {
                    
                    $bq = $pipeline->data['binaryquery'];

                    foreach ($queries as $val) {
                        foreach ($bq as $key2 => $val2) {
                            unset($val2['decode']);
                            $val['_query']->bodyPart($key2, $val2);
                            $val['_query']->remove(Horde_Imap_Client::FETCH_BODYPARTSIZE, $key2);
                        }
                        $pipeline->data['fetch_followup'][] = $val;
                    }
                } elseif ($sequence) {
                    
                    $this->noop();
                }
            }
        } catch (Exception $e) {
                                            }

        if (!empty($pipeline->data['fetch_followup'])) {
            $this->_fetch($results, $pipeline->data['fetch_followup']);
        }
    }

    
    protected function _fetchCmd(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        $options
    )
    {
        $fetch = new Horde_Imap_Client_Data_Format_List();
        $sequence = $options['ids']->sequence;

        

        foreach ($options['_query'] as $type => $c_val) {
            switch ($type) {
            case Horde_Imap_Client::FETCH_STRUCTURE:
                $fetch->add('BODYSTRUCTURE');
                break;

            case Horde_Imap_Client::FETCH_FULLMSG:
                if (empty($c_val['peek'])) {
                    $this->openMailbox($this->_selected, Horde_Imap_Client::OPEN_READWRITE);
                }
                $fetch->add(
                    'BODY' .
                    (!empty($c_val['peek']) ? '.PEEK' : '') .
                    '[]' .
                    $this->_partialAtom($c_val)
                );
                break;

            case Horde_Imap_Client::FETCH_HEADERTEXT:
            case Horde_Imap_Client::FETCH_BODYTEXT:
            case Horde_Imap_Client::FETCH_MIMEHEADER:
            case Horde_Imap_Client::FETCH_BODYPART:
            case Horde_Imap_Client::FETCH_HEADERS:
                foreach ($c_val as $key => $val) {
                    $cmd = ($key == 0)
                        ? ''
                        : $key . '.';
                    $main_cmd = 'BODY';

                    switch ($type) {
                    case Horde_Imap_Client::FETCH_HEADERTEXT:
                        $cmd .= 'HEADER';
                        break;

                    case Horde_Imap_Client::FETCH_BODYTEXT:
                        $cmd .= 'TEXT';
                        break;

                    case Horde_Imap_Client::FETCH_MIMEHEADER:
                        $cmd .= 'MIME';
                        break;

                    case Horde_Imap_Client::FETCH_BODYPART:
                                                $cmd = substr($cmd, 0, -1);

                        if (!empty($val['decode']) &&
                            $this->queryCapability('BINARY')) {
                            $main_cmd = 'BINARY';
                            $pipeline->data['binaryquery'][$key] = $val;
                        }
                        break;

                    case Horde_Imap_Client::FETCH_HEADERS:
                        $cmd .= 'HEADER.FIELDS';
                        if (!empty($val['notsearch'])) {
                            $cmd .= '.NOT';
                        }
                        $cmd .= ' (' . implode(' ', array_map('strtoupper', $val['headers'])) . ')';

                                                                        $pipeline->data['fetch_lookup'][$cmd] = $key;
                    }

                    if (empty($val['peek'])) {
                        $this->openMailbox($this->_selected, Horde_Imap_Client::OPEN_READWRITE);
                    }

                    $fetch->add(
                        $main_cmd .
                        (!empty($val['peek']) ? '.PEEK' : '') .
                        '[' . $cmd . ']' .
                        $this->_partialAtom($val)
                    );
                }
                break;

            case Horde_Imap_Client::FETCH_BODYPARTSIZE:
                if ($this->queryCapability('BINARY')) {
                    foreach ($c_val as $val) {
                        $fetch->add('BINARY.SIZE[' . $val . ']');
                    }
                }
                break;

            case Horde_Imap_Client::FETCH_ENVELOPE:
                $fetch->add('ENVELOPE');
                break;

            case Horde_Imap_Client::FETCH_FLAGS:
                $fetch->add('FLAGS');
                break;

            case Horde_Imap_Client::FETCH_IMAPDATE:
                $fetch->add('INTERNALDATE');
                break;

            case Horde_Imap_Client::FETCH_SIZE:
                $fetch->add('RFC822.SIZE');
                break;

            case Horde_Imap_Client::FETCH_UID:
                
                if ($sequence || (count($options['_query']) === 1)) {
                    $fetch->add('UID');
                }
                break;

            case Horde_Imap_Client::FETCH_SEQ:
                                                if (count($options['_query']) === 1) {
                    $fetch->add('UID');
                }
                break;

            case Horde_Imap_Client::FETCH_MODSEQ:
                
                if (empty($options['changedsince'])) {
                    $fetch->add('MODSEQ');
                }
                break;
            }
        }

        
        if (empty($options['changedsince'])) {
            $fetch_cmd = $fetch;
        } else {
            
            $fetch_cmd = array(
                count($fetch)
                    ? $fetch
                    : new Horde_Imap_Client_Data_Format_List('UID'),
                new Horde_Imap_Client_Data_Format_List(array(
                    'CHANGEDSINCE',
                    new Horde_Imap_Client_Data_Format_Number($options['changedsince'])
                ))
            );
        }

        
        foreach ($options['ids']->split($this->_init['cmdlength']) as $val) {
            $cmd = $this->_command(
                $sequence ? 'FETCH' : 'UID FETCH'
            )->add(array(
                $val,
                $fetch_cmd
            ));
            $pipeline->add($cmd);
        }
    }

    
    protected function _partialAtom($opts)
    {
        if (!empty($opts['length'])) {
            return '<' . (empty($opts['start']) ? 0 : intval($opts['start'])) . '.' . intval($opts['length']) . '>';
        }

        return empty($opts['start'])
            ? ''
            : ('<' . intval($opts['start']) . '>');
    }

    
    protected function _parseFetch(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        $id,
        Horde_Imap_Client_Tokenize $data
    )
    {
        if ($data->next() !== true) {
            return;
        }

        $ob = $pipeline->fetch->get($id);
        $ob->setSeq($id);

        $flags = $modseq = $uid = false;

        while (($tag = $data->next()) !== false) {
            $tag = strtoupper($tag);

            switch ($tag) {
            case 'BODYSTRUCTURE':
                $data->next();
                $structure = $this->_parseBodystructure($data);
                $structure->buildMimeIds();
                $ob->setStructure($structure);
                break;

            case 'ENVELOPE':
                $data->next();
                $ob->setEnvelope($this->_parseEnvelope($data));
                break;

            case 'FLAGS':
                $data->next();
                $ob->setFlags($data->flushIterator());
                $flags = true;
                break;

            case 'INTERNALDATE':
                $ob->setImapDate($data->next());
                break;

            case 'RFC822.SIZE':
                $ob->setSize($data->next());
                break;

            case 'UID':
                $ob->setUid($data->next());
                $uid = true;
                break;

            case 'MODSEQ':
                $data->next();
                $modseq = $data->next();
                $data->next();

                
                if ($modseq > 0) {
                    $ob->setModSeq($modseq);

                    
                    $pipeline->data['modseqs'][] = $modseq;
                }
                break;

            default:
                                if (strpos($tag, 'BODY[') === 0) {
                                        $tag = substr($tag, 5);

                                        if (!empty($pipeline->data['fetch_lookup']) &&
                        (strpos($tag, 'HEADER.FIELDS') !== false)) {
                        $data->next();
                        $sig = $tag . ' (' . implode(' ', array_map('strtoupper', $data->flushIterator())) . ')';

                                                $data->next();

                        $ob->setHeaders($pipeline->data['fetch_lookup'][$sig], $data->next());
                    } else {
                                                $tag = substr($tag, 0, strrpos($tag, ']'));

                        if (!strlen($tag)) {
                                                        if (!is_null($tmp = $data->next())) {
                                $ob->setFullMsg($tmp);
                            }
                        } elseif (is_numeric(substr($tag, -1))) {
                                                        if (!is_null($tmp = $data->next())) {
                                $ob->setBodyPart($tag, $tmp);
                            }
                        } else {
                                                        if (($last_dot = strrpos($tag, '.')) === false) {
                                $mime_id = 0;
                            } else {
                                $mime_id = substr($tag, 0, $last_dot);
                                $tag = substr($tag, $last_dot + 1);
                            }

                            if (!is_null($tmp = $data->next())) {
                                switch ($tag) {
                                case 'HEADER':
                                    $ob->setHeaderText($mime_id, $tmp);
                                    break;

                                case 'TEXT':
                                    $ob->setBodyText($mime_id, $tmp);
                                    break;

                                case 'MIME':
                                    $ob->setMimeHeader($mime_id, $tmp);
                                    break;
                                }
                            }
                        }
                    }
                } elseif (strpos($tag, 'BINARY[') === 0) {
                                                                                $tag = substr($tag, 7, strrpos($tag, ']') - 7);
                    $body = $data->next();

                    if (is_null($body)) {
                        
                        $bq = $pipeline->data['binaryquery'][$tag];
                        unset($bq['decode']);

                        $query = new Horde_Imap_Client_Fetch_Query();
                        $query->bodyPart($tag, $bq);

                        $qids = ($quid = $ob->getUid())
                            ? new Horde_Imap_Client_Ids($quid)
                            : new Horde_Imap_Client_Ids($id, true);

                        $pipeline->data['fetch_followup'][] = array(
                            '_query' => $query,
                            'ids' => $qids
                        );
                    } else {
                        $ob->setBodyPart(
                            $tag,
                            $body,
                            empty($this->_temp['literal8']) ? '8bit' : 'binary'
                        );
                    }
                } elseif (strpos($tag, 'BINARY.SIZE[') === 0) {
                                                                                $tag = substr($tag, 12, strrpos($tag, ']') - 12);
                    $ob->setBodyPartSize($tag, $data->next());
                }
                break;
            }
        }

        
        if ($flags && $modseq && !$uid) {
            $pipeline->data['modseqs_nouid'][] = $id;
        }
    }

    
    protected function _parseBodystructure(Horde_Imap_Client_Tokenize $data)
    {
        $ob = new Horde_Mime_Part();

                if (($entry = $data->next()) === true) {
            do {
                $ob->addPart($this->_parseBodystructure($data));
            } while (($entry = $data->next()) === true);

                        $ob->setType('multipart/' . $entry);

                        
                        if (($tmp = $data->next()) === false) {
                return $ob;
            } elseif ($tmp === true) {
                foreach ($this->_parseStructureParams($data, 'content-type') as $key => $val) {
                    $ob->setContentTypeParameter($key, $val);
                }
            }
        } else {
            $ob->setType($entry . '/' . $data->next());

            if ($data->next() === true) {
                foreach ($this->_parseStructureParams($data, 'content-type') as $key => $val) {
                    $ob->setContentTypeParameter($key, $val);
                }
            }

            if (!is_null($tmp = $data->next())) {
                $ob->setContentId($tmp);
            }

            if (!is_null($tmp = $data->next())) {
                $ob->setDescription(Horde_Mime::decode($tmp));
            }

            if (!is_null($tmp = $data->next())) {
                $ob->setTransferEncoding($tmp);
            }

            $ob->setBytes($data->next());

                                    switch ($ob->getPrimaryType()) {
            case 'message':
                if ($ob->getSubType() == 'rfc822') {
                    if ($data->next() === true) {
                                                $data->flushIterator(false);
                    }
                    if ($data->next() === true) {
                        $ob->addPart($this->_parseBodystructure($data));
                    }
                    $data->next();                 }
                break;

            case 'text':
                $data->next();                 break;
            }

                        
                        if ($data->next() === false) {
                return $ob;
            }
        }

                if (($tmp = $data->next()) === false) {
            return $ob;
        } elseif ($tmp === true) {
            $ob->setDisposition($data->next());

            if ($data->next() === true) {
                foreach ($this->_parseStructureParams($data, 'content-disposition') as $key => $val) {
                    $ob->setDispositionParameter($key, $val);
                }
            }
            $data->next();
        }

                        if (($tmp = $data->next()) === false) {
            return $ob;
        } elseif (!is_null($tmp)) {
            $ob->setLanguage(($tmp === true) ? $data->flushIterator() : $tmp);
        }

                $data->flushIterator(false);

        return $ob;
    }

    
    protected function _parseStructureParams($data, $type)
    {
        $params = array();

        if (is_null($data)) {
            return $params;
        }

        while (($name = $data->next()) !== false) {
            $params[strtolower($name)] = $data->next();
        }

        $ret = Horde_Mime::decodeParam($type, $params);

        return $ret['params'];
    }

    
    protected function _parseEnvelope(Horde_Imap_Client_Tokenize $data)
    {
                $addr_structure = array(
            0 => 'personal',
            2 => 'mailbox',
            3 => 'host'
        );
        $env_data = array(
            0 => 'date',
            1 => 'subject',
            2 => 'from',
            3 => 'sender',
            4 => 'reply_to',
            5 => 'to',
            6 => 'cc',
            7 => 'bcc',
            8 => 'in_reply_to',
            9 => 'message_id'
        );

        $addr_ob = new Horde_Mail_Rfc822_Address();
        $env_addrs = $this->getParam('envelope_addrs');
        $env_str = $this->getParam('envelope_string');
        $key = 0;
        $ret = new Horde_Imap_Client_Data_Envelope();

        while (($val = $data->next()) !== false) {
            if (!isset($env_data[$key]) || is_null($val)) {
                ++$key;
                continue;
            }

            if (is_string($val)) {
                                $ret->{$env_data[$key]} = substr($val, 0, $env_str);
            } else {
                                $group = null;
                $key2 = 0;
                $tmp = new Horde_Mail_Rfc822_List();

                while ($data->next() !== false) {
                    $a_val = $data->flushIterator();

                                                                                if (is_null($a_val[3])) {
                        if (is_null($a_val[2])) {
                            $group = null;
                        } else {
                            $group = new Horde_Mail_Rfc822_Group($a_val[2]);
                            $tmp->add($group);
                        }
                    } else {
                        $addr = clone $addr_ob;

                        foreach ($addr_structure as $add_key => $add_val) {
                            if (!is_null($a_val[$add_key])) {
                                $addr->$add_val = $a_val[$add_key];
                            }
                        }

                        if ($group) {
                            $group->addresses->add($addr);
                        } else {
                            $tmp->add($addr);
                        }
                    }

                    if (++$key2 >= $env_addrs) {
                        $data->flushIterator(false);
                        break;
                    }
                }

                $ret->{$env_data[$key]} = $tmp;
            }

            ++$key;
        }

        return $ret;
    }

    
    protected function _vanished($modseq, Horde_Imap_Client_Ids $ids)
    {
        $pipeline = $this->_pipeline(
            $this->_command('UID FETCH')->add(array(
                strval($ids),
                'UID',
                new Horde_Imap_Client_Data_Format_List(array(
                    'VANISHED',
                    'CHANGEDSINCE',
                    new Horde_Imap_Client_Data_Format_Number($modseq)
                ))
            ))
        );
        $pipeline->data['vanished'] = $this->getIdsOb();

        return $this->_sendCmd($pipeline)->data['vanished'];
    }

    
    protected function _store($options)
    {
        $pipeline = $this->_storeCmd($options);
        $pipeline->data['modified'] = $this->getIdsOb();

        try {
            $resp = $this->_sendCmd($pipeline);

            
            if (!empty($resp->data['expungeissued'])) {
                $this->noop();
            }

            return $resp->data['modified'];
        } catch (Horde_Imap_Client_Exception_ServerResponse $e) {
            
            if (empty($pipeline->data['store_silent']) &&
                !empty($options['sequence']) &&
                ($e->status === Horde_Imap_Client_Interaction_Server::NO)) {
                $this->noop();
            }

            return $pipeline->data['modified'];
        }
    }

    
    protected function _storeCmd($options)
    {
        $cmds = array();
        $silent = empty($options['unchangedsince'])
             ? !($this->_debug->debug || $this->_initCache(true))
             : false;

        if (!empty($options['replace'])) {
            $cmds[] = array(
                'FLAGS' . ($silent ? '.SILENT' : ''),
                $options['replace']
            );
        } else {
            foreach (array('add' => '+', 'remove' => '-') as $k => $v) {
                if (!empty($options[$k])) {
                    $cmds[] = array(
                        $v . 'FLAGS' . ($silent ? '.SILENT' : ''),
                        $options[$k]
                    );
                }
            }
        }

        $pipeline = $this->_pipeline();
        $pipeline->data['store_silent'] = $silent;

        foreach ($cmds as $val) {
            $cmd = $this->_command(
                empty($options['sequence']) ? 'UID STORE' : 'STORE'
            )->add(strval($options['ids']));
            if (!empty($options['unchangedsince'])) {
                $cmd->add(new Horde_Imap_Client_Data_Format_List(array(
                    'UNCHANGEDSINCE',
                    new Horde_Imap_Client_Data_Format_Number(intval($options['unchangedsince']))
                )));
            }
            $cmd->add($val);

            $pipeline->add($cmd);
        }

        return $pipeline;
    }

    
    protected function _copy(Horde_Imap_Client_Mailbox $dest, $options)
    {
        
        $move_cmd = (!empty($options['move']) &&
                     $this->queryCapability('MOVE'));

        $cmd = $this->_pipeline(
            $this->_command(
                ($options['ids']->sequence ? '' : 'UID ') . ($move_cmd ? 'MOVE' : 'COPY')
            )->add(array(
                strval($options['ids']),
                new Horde_Imap_Client_Data_Format_Mailbox($dest)
            ))
        );
        $cmd->data['copydest'] = $dest;

                try {
            $resp = $this->_sendCmd($cmd);
        } catch (Horde_Imap_Client_Exception $e) {
            if (!empty($options['create']) &&
                !empty($e->resp_data['trycreate'])) {
                $this->createMailbox($dest);
                unset($options['create']);
                return $this->_copy($dest, $options);
            }
            throw $e;
        }

                        if (!$move_cmd &&
            !empty($options['move']) &&
            (isset($resp->data['copyuid']) ||
             !$this->queryCapability('UIDPLUS'))) {
            $this->expunge($this->_selected, array(
                'delete' => true,
                'ids' => $options['ids']
            ));
        }

        return isset($resp->data['copyuid'])
            ? $resp->data['copyuid']
            : true;
    }

    
    protected function _setQuota(Horde_Imap_Client_Mailbox $root, $resources)
    {
        $limits = new Horde_Imap_Client_Data_Format_List();

        foreach ($resources as $key => $val) {
            $limits->add(array(
                strtoupper($key),
                new Horde_Imap_Client_Data_Format_Number($val)
            ));
        }

        $this->_sendCmd(
            $this->_command('SETQUOTA')->add(array(
                new Horde_Imap_Client_Data_Format_Mailbox($root),
                $limits
            ))
        );
    }

    
    protected function _getQuota(Horde_Imap_Client_Mailbox $root)
    {
        $pipeline = $this->_pipeline(
            $this->_command('GETQUOTA')->add(
                new Horde_Imap_Client_Data_Format_Mailbox($root)
            )
        );
        $pipeline->data['quotaresp'] = array();

        return reset($this->_sendCmd($pipeline)->data['quotaresp']);
    }

    
    protected function _parseQuota(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        $c = &$pipeline->data['quotaresp'];

        $root = $data->next();
        $c[$root] = array();

        $data->next();

        while (($curr = $data->next()) !== false) {
            $c[$root][strtolower($curr)] = array(
                'usage' => $data->next(),
                'limit' => $data->next()
            );
        }
    }

    
    protected function _getQuotaRoot(Horde_Imap_Client_Mailbox $mailbox)
    {
        $pipeline = $this->_pipeline(
            $this->_command('GETQUOTAROOT')->add(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
            )
        );
        $pipeline->data['quotaresp'] = array();

        return $this->_sendCmd($pipeline)->data['quotaresp'];
    }

    
    protected function _setACL(Horde_Imap_Client_Mailbox $mailbox, $identifier,
                               $options)
    {
                $this->_sendCmd(
            $this->_command('SETACL')->add(array(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                new Horde_Imap_Client_Data_Format_Astring($identifier),
                new Horde_Imap_Client_Data_Format_Astring($options['rights'])
            ))
        );
    }

    
    protected function _deleteACL(Horde_Imap_Client_Mailbox $mailbox, $identifier)
    {
                $this->_sendCmd(
            $this->_command('DELETEACL')->add(array(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                new Horde_Imap_Client_Data_Format_Astring($identifier)
            ))
        );
    }

    
    protected function _getACL(Horde_Imap_Client_Mailbox $mailbox)
    {
        return $this->_sendCmd(
            $this->_command('GETACL')->add(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
            )
        )->data['getacl'];
    }

    
    protected function _parseACL(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
        $acl = array();

                $data->next();

        while (($curr = $data->next()) !== false) {
            $acl[$curr] = ($curr[0] === '-')
                ? new Horde_Imap_Client_Data_AclNegative($data->next())
                : new Horde_Imap_Client_Data_Acl($data->next());
        }

        $pipeline->data['getacl'] = $acl;
    }

    
    protected function _listACLRights(Horde_Imap_Client_Mailbox $mailbox,
                                      $identifier)
    {
        $resp = $this->_sendCmd(
            $this->_command('LISTRIGHTS')->add(array(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                new Horde_Imap_Client_Data_Format_Astring($identifier)
            ))
        );

        return isset($resp->data['listaclrights'])
            ? $resp->data['listaclrights']
            : new Horde_Imap_Client_Data_AclRights();
    }

    
    protected function _parseListRights(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
                $data->next();
        $data->next();

        $pipeline->data['listaclrights'] = new Horde_Imap_Client_Data_AclRights(
            str_split($data->next()),
            $data->flushIterator()
        );
    }

    
    protected function _getMyACLRights(Horde_Imap_Client_Mailbox $mailbox)
    {
        $resp = $this->_sendCmd(
            $this->_command('MYRIGHTS')->add(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
            )
        );

        return isset($resp->data['myrights'])
            ? $resp->data['myrights']
            : new Horde_Imap_Client_Data_Acl();
    }

    
    protected function _parseMyRights(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
                $data->next();

        $pipeline->data['myrights'] = new Horde_Imap_Client_Data_Acl($data->next());
    }

    
    protected function _getMetadata(Horde_Imap_Client_Mailbox $mailbox,
                                    $entries, $options)
    {
        $pipeline = $this->_pipeline();
        $pipeline->data['metadata'] = array();

        if ($this->queryCapability('METADATA') ||
            (strlen($mailbox) && $this->queryCapability('METADATA-SERVER'))) {
            $cmd_options = new Horde_Imap_Client_Data_Format_List();

            if (!empty($options['maxsize'])) {
                $cmd_options->add(array(
                    'MAXSIZE',
                    new Horde_Imap_Client_Data_Format_Number($options['maxsize'])
                ));
            }
            if (!empty($options['depth'])) {
                $cmd_options->add(array(
                    'DEPTH',
                    new Horde_Imap_Client_Data_Format_Number($options['depth'])
                ));
            }

            $queries = new Horde_Imap_Client_Data_Format_List();
            foreach ($entries as $md_entry) {
                $queries->add(new Horde_Imap_Client_Data_Format_Astring($md_entry));
            }

            $cmd = $this->_command('GETMETADATA')->add(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox)
            );
            if (count($cmd_options)) {
                $cmd->add($cmd_options);
            }
            $cmd->add($queries);

            $pipeline->add($cmd);
        } else {
            if (!$this->queryCapability('ANNOTATEMORE') &&
                !$this->queryCapability('ANNOTATEMORE2')) {
                throw new Horde_Imap_Client_Exception_NoSupportExtension('METADATA');
            }

            $queries = array();
            foreach ($entries as $md_entry) {
                list($entry, $type) = $this->_getAnnotateMoreEntry($md_entry);

                if (!isset($queries[$type])) {
                    $queries[$type] = new Horde_Imap_Client_Data_Format_List();
                }
                $queries[$type]->add(new Horde_Imap_Client_Data_Format_String($entry));
            }

            foreach ($queries as $key => $val) {
                                $pipeline->add(
                    $this->_command('GETANNOTATION')->add(array(
                        new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                        $val,
                        new Horde_Imap_Client_Data_Format_String($key)
                    ))
                );
            }
        }

        return $this->_sendCmd($pipeline)->data['metadata'];
    }

    
    protected function _getAnnotateMoreEntry($name)
    {
        if (substr($name, 0, 7) === '/shared') {
            return array(substr($name, 7), 'value.shared');
        } else if (substr($name, 0, 8) === '/private') {
            return array(substr($name, 8), 'value.priv');
        }

        throw new Horde_Imap_Client_Exception(
            sprintf(Horde_Imap_Client_Translation::r("Invalid METADATA entry: \"%s\"."), $name),
            Horde_Imap_Client_Exception::METADATA_INVALID
        );
    }

    
    protected function _setMetadata(Horde_Imap_Client_Mailbox $mailbox, $data)
    {
        if ($this->queryCapability('METADATA') ||
            (strlen($mailbox) && $this->queryCapability('METADATA-SERVER'))) {
            $data_elts = new Horde_Imap_Client_Data_Format_List();

            foreach ($data as $key => $value) {
                $data_elts->add(array(
                    new Horde_Imap_Client_Data_Format_Astring($key),
                    new Horde_Imap_Client_Data_Format_Nstring($value)
                ));
            }

            $cmd = $this->_command('SETMETADATA')->add(array(
                new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                $data_elts
            ));
        } else {
            if (!$this->queryCapability('ANNOTATEMORE') &&
                !$this->queryCapability('ANNOTATEMORE2')) {
                throw new Horde_Imap_Client_Exception_NoSupportExtension('METADATA');
            }

            $cmd = $this->_pipeline();

            foreach ($data as $md_entry => $value) {
                list($entry, $type) = $this->_getAnnotateMoreEntry($md_entry);

                $cmd->add(
                    $this->_command('SETANNOTATION')->add(array(
                        new Horde_Imap_Client_Data_Format_Mailbox($mailbox),
                        new Horde_Imap_Client_Data_Format_String($entry),
                        new Horde_Imap_Client_Data_Format_List(array(
                            new Horde_Imap_Client_Data_Format_String($type),
                            new Horde_Imap_Client_Data_Format_Nstring($value)
                        ))
                    ))
                );
            }
        }

        $this->_sendCmd($cmd);
    }

    
    protected function _parseAnnotation(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
                $mbox = Horde_Imap_Client_Mailbox::get($data->next(), true);
        $entry = $data->next();

                if ($data->next() !== true) {
            return;
        }

        while (($type = $data->next()) !== false) {
            switch ($type) {
            case 'value.priv':
                $pipeline->data['metadata'][strval($mbox)]['/private' . $entry] = $data->next();
                break;

            case 'value.shared':
                $pipeline->data['metadata'][strval($mbox)]['/shared' . $entry] = $data->next();
                break;

            default:
                throw new Horde_Imap_Client_Exception(
                    sprintf(Horde_Imap_Client_Translation::r("Invalid METADATA value type \"%s\"."), $type),
                    Horde_Imap_Client_Exception::METADATA_INVALID
                );
            }
        }
    }

    
    protected function _parseMetadata(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Tokenize $data
    )
    {
                $mbox = Horde_Imap_Client_Mailbox::get($data->next(), true);

                if ($data->next() === true) {
            while (($entry = $data->next()) !== false) {
                $pipeline->data['metadata'][strval($mbox)][$entry] = $data->next();
            }
        }
    }

    

    
    protected function _deleteMsgs(Horde_Imap_Client_Mailbox $mailbox,
                                   Horde_Imap_Client_Ids $ids,
                                   array $opts = array())
    {
        
        if (isset($opts['pipeline'])) {
            $this->_updateCache($opts['pipeline']->fetch);
        }

        $res = parent::_deleteMsgs($mailbox, $ids);

        if (isset($this->_temp['expunged'])) {
            $this->_temp['expunged']->add($res);
        }

        if (!empty($opts['decrement'])) {
            $mbox_ob = $this->_mailboxOb();
            $mbox_ob->setStatus(
                Horde_Imap_Client::STATUS_MESSAGES,
                $mbox_ob->getStatus(Horde_Imap_Client::STATUS_MESSAGES) - count($ids)
            );
        }
    }

    

    
    protected function _sendCmd($cmd)
    {
        $pipeline = ($cmd instanceof Horde_Imap_Client_Interaction_Command)
            ? $this->_pipeline($cmd)
            : $cmd;

        if (!empty($this->_cmdQueue)) {
            
            foreach (array_reverse($this->_cmdQueue) as $val) {
                $pipeline->add($val, true);
            }

            $this->_cmdQueue = array();
        }

        $cmd_list = array();

        foreach ($pipeline as $val) {
            if ($val->continuation) {
                $this->_sendCmdChunk($pipeline, $cmd_list);
                $this->_sendCmdChunk($pipeline, array($val));
                $cmd_list = array();
            } else {
                $cmd_list[] = $val;
            }
        }

        $this->_sendCmdChunk($pipeline, $cmd_list);

        
        foreach ($pipeline->data['modseqs_nouid'] as $val) {
            if (!$pipeline->fetch[$val]->getUid()) {
                $this->_debug->info(
                    'Server provided FLAGS MODSEQ without providing UID.'
                );
                $this->close();
                return $pipeline;
            }
        }

        
        if (!empty($pipeline->data['modseqs'])) {
            $modseq = max($pipeline->data['modseqs']);
            $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ, $modseq);
            
            if (!empty($this->_temp['enabled']['QRESYNC'])) {
                $this->_updateModSeq($modseq);
            }
        }

        
        $this->_updateCache($pipeline->fetch);

        return $pipeline;
    }

    
    protected function _sendCmdChunk($pipeline, $chunk)
    {
        if (empty($chunk)) {
            return;
        }

        $cmd_count = count($chunk);
        $exception = null;

        foreach ($chunk as $val) {
            try {
                $old_debug = $this->_debug->debug;
                if (!is_null($val->debug)) {
                    $this->_debug->raw($val->tag . ' ' . $val->debug . "\n");
                    $this->_debug->debug = false;
                }
                if ($this->_processCmd($pipeline, $val, $val)) {
                    $this->_connection->write('', true);
                } else {
                    $cmd_count = 0;
                }
                $this->_debug->debug = $old_debug;
            } catch (Horde_Imap_Client_Exception $e) {
                $this->_debug->debug = $old_debug;

                switch ($e->getCode()) {
                case Horde_Imap_Client_Exception::SERVER_WRITEERROR:
                    $this->_temp['logout'] = true;
                    $this->logout();
                    break;
                }

                throw $e;
            }
        }

        while ($cmd_count) {
            try {
                if ($this->_getLine($pipeline) instanceof Horde_Imap_Client_Interaction_Server_Tagged) {
                    --$cmd_count;
                }
            } catch (Horde_Imap_Client_Exception $e) {
                switch ($e->getCode()) {
                case $e::DISCONNECT:
                    
                    $this->_temp['logout'] = true;
                    $this->logout();
                    throw $e;
                }

                
                if (is_null($exception)) {
                    $exception = $e;
                }

                if (($e instanceof Horde_Imap_Client_Exception_ServerResponse) &&
                    $e->command) {
                    --$cmd_count;
                }
            }
        }

        if (!is_null($exception)) {
            throw $exception;
        }
    }

    
    protected function _processCmd($pipeline, $cmd, $data)
    {
        if ($this->_debug->debug &&
            ($data instanceof Horde_Imap_Client_Interaction_Command)) {
            $data->startTimer();
        }

        foreach ($data as $key => $val) {
            if ($val instanceof Horde_Imap_Client_Interaction_Command_Continuation) {
                $this->_connection->write('', true);

                
                if (!$cmd_continuation = $this->_processCmdContinuation($pipeline, $val->optional)) {
                    return false;
                }

                $this->_processCmd(
                    $pipeline,
                    $cmd,
                    $val->getCommands($cmd_continuation)
                );
                continue;
            }

            if ($key) {
                $this->_connection->write(' ');
            }

            if ($val instanceof Horde_Imap_Client_Data_Format_List) {
                $this->_connection->write('(');
                $this->_processCmd($pipeline, $cmd, $val);
                $this->_connection->write(')');
            } elseif (($val instanceof Horde_Imap_Client_Data_Format_String) &&
                      $val->literal()) {
                
                if ($cmd->literal8 &&
                    $val->binary() &&
                    $this->queryCapability('BINARY')) {
                    $binary = true;
                    $this->_connection->write('~');
                } else {
                    $binary = false;
                }

                $literal_len = $val->length();
                $this->_connection->write('{' . $literal_len);

                
                if ($cmd->literalplus && $this->queryCapability('LITERAL+')) {
                    $this->_connection->write('+}', true);
                } else {
                    $this->_connection->write('}', true);
                    $this->_processCmdContinuation($pipeline);
                }

                $this->_connection->writeLiteral($val->getStream(), $literal_len, $binary);
            } else {
                $this->_connection->write($val->escape());
            }
        }

        return true;
    }

    
    protected function _processCmdContinuation($pipeline, $noexception = false)
    {
        do {
            $ob = $this->_getLine($pipeline);
        } while ($ob instanceof Horde_Imap_Client_Interaction_Server_Untagged);

        if ($ob instanceof Horde_Imap_Client_Interaction_Server_Continuation) {
            return $ob;
        } elseif ($noexception) {
            return false;
        }

        $this->_debug->info(
            'ERROR: Unexpected response from server while waiting for a continuation request.'
        );
        $e = new Horde_Imap_Client_Exception(
            Horde_Imap_Client_Translation::r("Error when communicating with the mail server."),
            Horde_Imap_Client_Exception::SERVER_READERROR
        );
        $e->details = strval($ob);

        throw $e;
    }

    
    protected function _command($cmd)
    {
        return new Horde_Imap_Client_Interaction_Command($cmd, ++$this->_tag);
    }

    
    protected function _pipeline($cmd = null)
    {
        if (!isset($this->_temp['fetchob'])) {
            $this->_temp['fetchob'] = new Horde_Imap_Client_Fetch_Results(
                $this->_fetchDataClass,
                Horde_Imap_Client_Fetch_Results::SEQUENCE
            );
        }

        $ob = new Horde_Imap_Client_Interaction_Pipeline(
            clone $this->_temp['fetchob']
        );

        if (!is_null($cmd)) {
            $ob->add($cmd);
        }

        return $ob;
    }

    
    protected function _getLine(
        Horde_Imap_Client_Interaction_Pipeline $pipeline
    )
    {
        $server = Horde_Imap_Client_Interaction_Server::create(
            $this->_connection->read()
        );

        switch (get_class($server)) {
        case 'Horde_Imap_Client_Interaction_Server_Continuation':
            $this->_responseCode($pipeline, $server);
            break;

        case 'Horde_Imap_Client_Interaction_Server_Tagged':
            $cmd = $pipeline->complete($server);
            if ($timer = $cmd->getTimer()) {
                $this->_debug->info(sprintf(
                    'Command %s took %s seconds.',
                    $cmd->tag,
                    $timer
                ));
            }
            $this->_responseCode($pipeline, $server);
            break;

        case 'Horde_Imap_Client_Interaction_Server_Untagged':
            if (is_null($server->status)) {
                $this->_serverResponse($pipeline, $server);
            } else {
                $this->_responseCode($pipeline, $server);
            }
            break;
        }

        switch ($server->status) {
        case $server::BAD:
        case $server::NO:
            
            if ($server instanceof Horde_Imap_Client_Interaction_Server_Tagged) {
                throw new Horde_Imap_Client_Exception_ServerResponse(
                    Horde_Imap_Client_Translation::r("IMAP error reported by server."),
                    0,
                    $server,
                    $pipeline
                );
            }
            break;

        case $server::BYE:
            
            if (empty($this->_temp['logout'])) {
                $e = new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("IMAP Server closed the connection."),
                    Horde_Imap_Client_Exception::DISCONNECT
                );
                $e->details = strval($server);
                throw $e;
            }
            break;

        case $server::PREAUTH:
            
            $this->_temp['preauth'] = true;
            break;
        }

        return $server;
    }

    
    protected function _serverResponse(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Interaction_Server $ob
    )
    {
        $token = $ob->token;

        
        switch ($first = strtoupper($token->current())) {
        case 'CAPABILITY':
            $this->_parseCapability($pipeline, $token->flushIterator());
            break;

        case 'LIST':
        case 'LSUB':
            $this->_parseList($pipeline, $token);
            break;

        case 'STATUS':
                        $this->_parseStatus($token);
            break;

        case 'SEARCH':
        case 'SORT':
                        $this->_parseSearch($pipeline, $token->flushIterator());
            break;

        case 'ESEARCH':
                        $this->_parseEsearch($pipeline, $token);
            break;

        case 'FLAGS':
            $token->next();
            $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_FLAGS, array_map('strtolower', $token->flushIterator()));
            break;

        case 'QUOTA':
            $this->_parseQuota($pipeline, $token);
            break;

        case 'QUOTAROOT':
                                    break;

        case 'NAMESPACE':
            $this->_parseNamespace($pipeline, $token);
            break;

        case 'THREAD':
            $this->_parseThread($pipeline, $token);
            break;

        case 'ACL':
            $this->_parseACL($pipeline, $token);
            break;

        case 'LISTRIGHTS':
            $this->_parseListRights($pipeline, $token);
            break;

        case 'MYRIGHTS':
            $this->_parseMyRights($pipeline, $token);
            break;

        case 'ID':
                        $this->_parseID($pipeline, $token);
            break;

        case 'ENABLED':
                        $this->_parseEnabled($token);
            break;

        case 'LANGUAGE':
                        $this->_parseLanguage($token);
            break;

        case 'COMPARATOR':
                        $this->_parseComparator($pipeline, $token);
            break;

        case 'VANISHED':
                        $this->_parseVanished($pipeline, $token);
            break;

        case 'ANNOTATION':
                        $this->_parseAnnotation($pipeline, $token);
            break;

        case 'METADATA':
                        $this->_parseMetadata($pipeline, $token);
            break;

        default:
                        switch (strtoupper($token->next())) {
            case 'EXISTS':
                                $mbox_ob = $this->_mailboxOb();

                                if ($mbox_ob->open &&
                    ($uidnext = $mbox_ob->getStatus(Horde_Imap_Client::STATUS_UIDNEXT))) {
                    $mbox_ob->setStatus(Horde_Imap_Client::STATUS_UIDNEXT, $uidnext + $first - $mbox_ob->getStatus(Horde_Imap_Client::STATUS_MESSAGES));
                }

                $mbox_ob->setStatus(Horde_Imap_Client::STATUS_MESSAGES, $first);
                break;

            case 'RECENT':
                                $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_RECENT, $first);
                break;

            case 'EXPUNGE':
                                $this->_deleteMsgs($this->_selected, $this->getIdsOb($first, true), array(
                    'decrement' => true,
                    'pipeline' => $pipeline
                ));
                $pipeline->data['expunge_seen'] = true;
                break;

            case 'FETCH':
                                $this->_parseFetch($pipeline, $first, $token);
                break;
            }
            break;
        }
    }

    
    protected function _responseCode(
        Horde_Imap_Client_Interaction_Pipeline $pipeline,
        Horde_Imap_Client_Interaction_Server $ob
    )
    {
        if (is_null($ob->responseCode)) {
            return;
        }

        $rc = $ob->responseCode;

        switch ($rc->code) {
        case 'ALERT':
                case 'CONTACTADMIN':
            if (!isset($this->_temp['alerts'])) {
                $this->_temp['alerts'] = array();
            }
            $this->_temp['alerts'][] = strval($ob->token);
            break;

        case 'BADCHARSET':
            
            $s_charset = array();
            foreach ($rc->data[0] as $val) {
                $s_charset[$val] = true;
            }

            if (!empty($s_charset)) {
                $this->_setInit('s_charset', array_merge(
                    $this->_init['s_charset'],
                    $s_charset
                ));
            }

            throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("Charset used in search query is not supported on the mail server."),
                Horde_Imap_Client_Exception::BADCHARSET,
                $ob,
                $pipeline
            );

        case 'CAPABILITY':
            $this->_parseCapability($pipeline, $rc->data);
            break;

        case 'PARSE':
            
            switch ($ob->status) {
            case Horde_Imap_Client_Interaction_Server::BAD:
            case Horde_Imap_Client_Interaction_Server::NO:
                throw new Horde_Imap_Client_Exception_ServerResponse(
                    sprintf(Horde_Imap_Client_Translation::r("The mail server was unable to parse the contents of the mail message: %s"), strval($ob->token)),
                    Horde_Imap_Client_Exception::PARSEERROR,
                    $ob,
                    $pipeline
                );
            }
            break;

        case 'READ-ONLY':
            $this->_mode = Horde_Imap_Client::OPEN_READONLY;
            break;

        case 'READ-WRITE':
            $this->_mode = Horde_Imap_Client::OPEN_READWRITE;
            break;

        case 'TRYCREATE':
                        $pipeline->data['trycreate'] = true;
            break;

        case 'PERMANENTFLAGS':
            $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_PERMFLAGS, array_map('strtolower', $rc->data[0]));
            break;

        case 'UIDNEXT':
            $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_UIDNEXT, $rc->data[0]);
            break;

        case 'UIDVALIDITY':
            $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_UIDVALIDITY, $rc->data[0]);
            break;

        case 'UNSEEN':
            
            $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_FIRSTUNSEEN, $rc->data[0]);
            break;

        case 'REFERRAL':
                        $pipeline->data['referral'] = new Horde_Imap_Client_Url($rc->data[0]);
            break;

        case 'UNKNOWN-CTE':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The mail server was unable to parse the contents of the mail message."),
                Horde_Imap_Client_Exception::UNKNOWNCTE,
                $ob,
                $pipeline
            );

        case 'APPENDUID':
                                    $pipeline->data['appenduid'] = $this->getIdsOb($rc->data[1]);
            break;

        case 'COPYUID':
                                    $pipeline->data['copyuid'] = array_combine(
                $this->getIdsOb($rc->data[1])->ids,
                $this->getIdsOb($rc->data[2])->ids
            );

            
            $this->_moveCache($pipeline->data['copydest'], $pipeline->data['copyuid'], $rc->data[0]);
            break;

        case 'UIDNOTSTICKY':
                        $this->_mailboxOb()->setStatus(Horde_Imap_Client::STATUS_UIDNOTSTICKY, true);
            break;

        case 'BADURL':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("Could not save message on server."),
                Horde_Imap_Client_Exception::CATENATE_BADURL,
                $ob,
                $pipeline
            );

        case 'TOOBIG':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("Could not save message data because it is too large."),
                Horde_Imap_Client_Exception::CATENATE_TOOBIG,
                $ob,
                $pipeline
            );

        case 'HIGHESTMODSEQ':
                        $pipeline->data['modseqs'][] = $rc->data[0];
            break;

        case 'NOMODSEQ':
                        $pipeline->data['modseqs'][] = 0;
            break;

        case 'MODIFIED':
                        $pipeline->data['modified']->add($rc->data[0]);
            break;

        case 'CLOSED':
                        if (isset($pipeline->data['qresyncmbox'])) {
                
                $this->_updateCache($pipeline->fetch);
                $pipeline->fetch->clear();

                $this->_changeSelected(
                    $pipeline->data['qresyncmbox'][0],
                    $pipeline->data['qresyncmbox'][1]
                );
                unset($pipeline->data['qresyncmbox']);
            }
            break;

        case 'NOTSAVED':
                        $pipeline->data['searchnotsaved'] = true;
            break;

        case 'BADCOMPARATOR':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The comparison algorithm was not recognized by the server."),
                Horde_Imap_Client_Exception::BADCOMPARATOR,
                $ob,
                $pipeline
            );

        case 'METADATA':
            $md = $rc->data[0];

            switch ($md[0]) {
            case 'LONGENTRIES':
                                $pipeline->data['metadata']['*longentries'] = intval($md[1]);
                break;

            case 'MAXSIZE':
                                throw new Horde_Imap_Client_Exception_ServerResponse(
                    Horde_Imap_Client_Translation::r("The metadata item could not be saved because it is too large."),
                    Horde_Imap_Client_Exception::METADATA_MAXSIZE,
                    $ob,
                    $pipeline
                );

            case 'NOPRIVATE':
                                throw new Horde_Imap_Client_Exception_ServerResponse(
                    Horde_Imap_Client_Translation::r("The metadata item could not be saved because the server does not support private annotations."),
                    Horde_Imap_Client_Exception::METADATA_NOPRIVATE,
                    $ob,
                    $pipeline
                );

            case 'TOOMANY':
                                throw new Horde_Imap_Client_Exception_ServerResponse(
                    Horde_Imap_Client_Translation::r("The metadata item could not be saved because the maximum number of annotations has been exceeded."),
                    Horde_Imap_Client_Exception::METADATA_TOOMANY,
                    $ob,
                    $pipeline
                );
            }
            break;

        case 'UNAVAILABLE':
                        $pipeline->data['loginerr'] = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Remote server is temporarily unavailable."),
                Horde_Imap_Client_Exception::LOGIN_UNAVAILABLE
            );
            break;

        case 'AUTHENTICATIONFAILED':
                        $pipeline->data['loginerr'] = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Authentication failed."),
                Horde_Imap_Client_Exception::LOGIN_AUTHENTICATIONFAILED
            );
            break;

        case 'AUTHORIZATIONFAILED':
                        $pipeline->data['loginerr'] = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Authentication was successful, but authorization failed."),
                Horde_Imap_Client_Exception::LOGIN_AUTHORIZATIONFAILED
            );
            break;

        case 'EXPIRED':
                        $pipeline->data['loginerr'] = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Authentication credentials have expired."),
                Horde_Imap_Client_Exception::LOGIN_EXPIRED
            );
            break;

        case 'PRIVACYREQUIRED':
                        $pipeline->data['loginerr'] = new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Operation failed due to a lack of a secure connection."),
                Horde_Imap_Client_Exception::LOGIN_PRIVACYREQUIRED
            );
            break;

        case 'NOPERM':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("You do not have adequate permissions to carry out this operation."),
                Horde_Imap_Client_Exception::NOPERM,
                $ob,
                $pipeline
            );

        case 'INUSE':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("There was a temporary issue when attempting this operation. Please try again later."),
                Horde_Imap_Client_Exception::INUSE,
                $ob,
                $pipeline
            );

        case 'EXPUNGEISSUED':
                        $pipeline->data['expungeissued'] = true;
            break;

        case 'CORRUPTION':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The mail server is reporting corrupt data in your mailbox."),
                Horde_Imap_Client_Exception::CORRUPTION,
                $ob,
                $pipeline
            );

        case 'SERVERBUG':
        case 'CLIENTBUG':
        case 'CANNOT':
                        $this->_debug->info(
                'ERROR: mail server explicitly reporting an error.'
            );
            break;

        case 'LIMIT':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The mail server has denied the request."),
                Horde_Imap_Client_Exception::LIMIT,
                $ob,
                $pipeline
            );

        case 'OVERQUOTA':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The operation failed because the quota has been exceeded on the mail server."),
                Horde_Imap_Client_Exception::OVERQUOTA,
                $ob,
                $pipeline
            );

        case 'ALREADYEXISTS':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The object could not be created because it already exists."),
                Horde_Imap_Client_Exception::ALREADYEXISTS,
                $ob,
                $pipeline
            );

        case 'NONEXISTENT':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The object could not be deleted because it does not exist."),
                Horde_Imap_Client_Exception::NONEXISTENT,
                $ob,
                $pipeline
            );

        case 'USEATTR':
                        throw new Horde_Imap_Client_Exception_ServerResponse(
                Horde_Imap_Client_Translation::r("The special-use attribute requested for the mailbox is not supported."),
                Horde_Imap_Client_Exception::USEATTR,
                $ob,
                $pipeline
            );

        case 'DOWNGRADED':
                        $downgraded = $this->getIdsOb($rc->data[0]);
            foreach ($pipeline->fetch as $val) {
                if (in_array($val->getUid(), $downgraded)) {
                    $val->setDowngraded(true);
                }
            }
            break;

        case 'XPROXYREUSE':
                        $pipeline->data['proxyreuse'] = true;
            break;

        default:
                        break;
        }
    }

}
