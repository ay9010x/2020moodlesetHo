<?php



abstract class Horde_Imap_Client_Base implements Serializable
{
    
    const VERSION = 2;

    
    const CACHE_MODSEQ = '_m';
    const CACHE_SEARCH = '_s';
    
    const CACHE_SEARCHID = '_i';

    
    const CACHE_DOWNGRADED = 'HICdg';

    
    public $cacheFields = array(
        Horde_Imap_Client::FETCH_ENVELOPE => 'HICenv',
        Horde_Imap_Client::FETCH_FLAGS => 'HICflags',
        Horde_Imap_Client::FETCH_HEADERS => 'HIChdrs',
        Horde_Imap_Client::FETCH_IMAPDATE => 'HICdate',
        Horde_Imap_Client::FETCH_SIZE => 'HICsize',
        Horde_Imap_Client::FETCH_STRUCTURE => 'HICstruct'
    );

    
    public $changed = false;

    
    public $statuscache = true;

    
    protected $_cache = null;

    
    protected $_connection = null;

    
    protected $_debug = null;

    
    protected $_defaultPorts = array();

    
    protected $_fetchDataClass = 'Horde_Imap_Client_Data_Fetch';

    
    protected $_init;

    
    protected $_isAuthenticated = false;

    
    protected $_mode = 0;

    
    protected $_params = array();

    
    protected $_selected = null;

    
    protected $_temp = array(
        'enabled' => array()
    );

    
    public function __construct(array $params = array())
    {
        if (!isset($params['username'])) {
            throw new InvalidArgumentException('Horde_Imap_Client requires a username.');
        }

        $this->_setInit();

                $params = array_merge(array(
            'hostspec' => 'localhost',
            'secure' => false,
            'timeout' => 30
        ), array_filter($params));

        if (!isset($params['port'])) {
            $params['port'] = (!empty($params['secure']) && in_array($params['secure'], array('ssl', 'sslv2', 'sslv3'), true))
                ? $this->_defaultPorts[1]
                : $this->_defaultPorts[0];
        }

        if (empty($params['cache'])) {
            $params['cache'] = array('fields' => array());
        } elseif (empty($params['cache']['fields'])) {
            $params['cache']['fields'] = $this->cacheFields;
        } else {
            $params['cache']['fields'] = array_flip($params['cache']['fields']);
        }

        if (empty($params['cache']['fetch_ignore'])) {
            $params['cache']['fetch_ignore'] = array();
        }

        $this->_params = $params;
        if (isset($params['password'])) {
            $this->setParam('password', $params['password']);
        }

        $this->changed = true;
        $this->_initOb();
    }

    
    protected function _getEncryptKey()
    {
        if (is_callable($ekey = $this->getParam('encryptKey'))) {
            return call_user_func($ekey);
        }

        throw new InvalidArgumentException('encryptKey parameter is not a valid callback.');
    }

    
    protected function _initOb()
    {
        register_shutdown_function(array($this, 'shutdown'));
        $this->_debug = ($debug = $this->getParam('debug'))
            ? new Horde_Imap_Client_Base_Debug($debug)
            : new Horde_Support_Stub();
    }

    
    public function shutdown()
    {
        $this->logout();
    }

    
    public function __clone()
    {
        throw new LogicException('Object cannot be cloned.');
    }

    
    public function serialize()
    {
        return serialize(array(
            'i' => $this->_init,
            'p' => $this->_params,
            'v' => self::VERSION
        ));
    }

    
    public function unserialize($data)
    {
        $data = @unserialize($data);
        if (!is_array($data) ||
            !isset($data['v']) ||
            ($data['v'] != self::VERSION)) {
            throw new Exception('Cache version change');
        }

        $this->_init = $data['i'];
        $this->_params = $data['p'];

        $this->_initOb();
    }

    
    public function _setInit($key = null, $val = null)
    {
        if (is_null($key)) {
            $this->_init = array(
                'namespace' => array(),
                's_charset' => array()
            );
        } elseif (is_null($val)) {
            unset($this->_init[$key]);

            switch ($key) {
            case 'capability':
                unset($this->_init['cmdlength']);
                break;
            }
        } else {
            switch ($key) {
            case 'capability':
                if ($ci = $this->getParam('capability_ignore')) {
                    if ($this->_debug->debug &&
                        ($ignored = array_intersect_key($val, array_flip($ci)))) {
                        $this->_debug->info(sprintf(
                            'CONFIG: IGNORING these IMAP capabilities: %s',
                            implode(', ', array_keys($ignored))
                        ));
                    }

                    $val = array_diff_key($val, array_flip($ci));
                }

                
                if (!empty($val['QRESYNC'])) {
                    $val['CONDSTORE'] = true;
                    $val['ENABLE'] = true;
                }

                
                $this->_init['cmdlength'] = (isset($val['CONDSTORE']) || isset($val['QRESYNC']))
                    ? 8000
                    : 2000;
                break;
            }

            
            if (isset($this->_init[$key]) && ($this->_init[$key] == $val)) {
                return;
            }

            $this->_init[$key] = $val;
        }

        $this->changed = true;
    }

    
    protected function _enabled($exts, $status)
    {
        
        if (in_array('QRESYNC', $exts)) {
            $exts[] = 'CONDSTORE';
        }

        switch ($status) {
        case 2:
            $enabled_list = array_intersect(array(2), $this->_temp['enabled']);
            break;

        case 1:
        default:
            $enabled_list = $this->_temp['enabled'];
            $status = 1;
            break;
        }

        $this->_temp['enabled'] = array_merge(
            $enabled_list,
            array_fill_keys($exts, $status)
        );
    }

    
    protected function _initCache($current = false)
    {
        $c = $this->getParam('cache');

        if (empty($c['fields'])) {
            return false;
        }

        if (is_null($this->_cache)) {
            if (isset($c['backend'])) {
                $backend = $c['backend'];
            } elseif (isset($c['cacheob'])) {
                
                $backend = new Horde_Imap_Client_Cache_Backend_Cache($c);
            } else {
                return false;
            }

            $this->_cache = new Horde_Imap_Client_Cache(array(
                'backend' => $backend,
                'baseob' => $this,
                'debug' => $this->_debug
            ));
        }

        return $current
            
            ? !($this->_mailboxOb()->getStatus(Horde_Imap_Client::STATUS_UIDNOTSTICKY))
            : true;
    }

    
    public function getParam($key)
    {
        
        switch ($key) {
        case 'password':
            if (isset($this->_params[$key]) &&
                ($this->_params[$key] instanceof Horde_Imap_Client_Base_Password)) {
                return $this->_params[$key]->getPassword();
            }

                        if (!empty($this->_params['_passencrypt'])) {
                try {
                    $secret = new Horde_Secret();
                    return $secret->read($this->_getEncryptKey(), $this->_params['password']);
                } catch (Exception $e) {
                    return null;
                }
            }
            break;
        }

        return isset($this->_params[$key])
            ? $this->_params[$key]
            : null;
    }

    
    public function setParam($key, $val)
    {
        switch ($key) {
        case 'password':
            if ($val instanceof Horde_Imap_Client_Base_Password) {
                break;
            }

                        try {
                $encrypt_key = $this->_getEncryptKey();
                if (strlen($encrypt_key)) {
                    $secret = new Horde_Secret();
                    $val = $secret->write($encrypt_key, $val);
                    $this->_params['_passencrypt'] = true;
                }
            } catch (Exception $e) {}
            break;
        }

        $this->_params[$key] = $val;
        $this->changed = true;
    }

    
    public function getCache()
    {
        $this->_initCache();
        return $this->_cache;
    }

    
    public function getIdsOb($ids = null, $sequence = false)
    {
        return new Horde_Imap_Client_Ids($ids, $sequence);
    }

    
    public function queryCapability($capability)
    {
                        try {
            $this->capability();
        } catch (Horde_Imap_Client_Exception $e) {
            return false;
        }

        $capability = strtoupper($capability);

        if (!isset($this->_init['capability'][$capability])) {
            return false;
        }

        
        if (isset(Horde_Imap_Client::$capability_deps[$capability])) {
            foreach (Horde_Imap_Client::$capability_deps[$capability] as $val) {
                if (!$this->queryCapability($val)) {
                    return false;
                }
            }
        }

        return $this->_init['capability'][$capability];
    }

    
    public function capability()
    {
        if (!isset($this->_init['capability'])) {
            $this->_capability();
        }

        return $this->_init['capability'];
    }

    
    abstract protected function _capability();

    
    public function noop()
    {
        if (!$this->_connection) {
                        $this->_connect();
        }
        $this->_noop();
    }

    
    abstract protected function _noop();

    
    public function getNamespaces(
        array $additional = array(), array $opts = array()
    )
    {
        $additional = array_map('strval', $additional);
        $sig = hash(
            (PHP_MINOR_VERSION >= 4) ? 'fnv132' : 'sha1',
            json_encode($additional) . intval(empty($opts['ob_return']))
        );

        if (isset($this->_init['namespace'][$sig])) {
            $ns = $this->_init['namespace'][$sig];
        } else {
            $this->login();

            $ns = $this->_getNamespaces();

            
            $to_process = array_diff(array_filter($additional, 'strlen'), array_map('strlen', iterator_to_array($ns)));
            if (!empty($to_process)) {
                foreach ($this->listMailboxes($to_process, Horde_Imap_Client::MBOX_ALL, array('delimiter' => true)) as $val) {
                    $ob = new Horde_Imap_Client_Data_Namespace();
                    $ob->delimiter = $val['delimiter'];
                    $ob->hidden = true;
                    $ob->name = $val;
                    $ob->type = $ob::NS_SHARED;
                    $ns[$val] = $ob;
                }
            }

            if (!count($ns)) {
                
                $mbox = $this->listMailboxes('', Horde_Imap_Client::MBOX_ALL, array('delimiter' => true));
                $first = reset($mbox);

                $ob = new Horde_Imap_Client_Data_Namespace();
                $ob->delimiter = $first['delimiter'];
                $ns[''] = $ob;
            }

            $this->_init['namespace'][$sig] = $ns;
            $this->_setInit('namespace', $this->_init['namespace']);
        }

        if (!empty($opts['ob_return'])) {
            return $ns;
        }

        
        $out = array();
        foreach ($ns as $key => $val) {
            $out[$key] = array(
                'delimiter' => $val->delimiter,
                'hidden' => $val->hidden,
                'name' => $val->name,
                'translation' => $val->translation,
                'type' => $val->type
            );
        }

        return $out;
    }

    
    abstract protected function _getNamespaces();

    
    public function isSecureConnection()
    {
        return ($this->_connection && $this->_connection->secure);
    }

    
    abstract protected function _connect();

    
    abstract public function alerts();

    
    public function login()
    {
        if (!$this->_isAuthenticated && $this->_login()) {
            if ($this->getParam('id')) {
                try {
                    $this->sendID();
                } catch (Horde_Imap_Client_Exception_NoSupportExtension $e) {
                                    }
            }

            if ($this->getParam('comparator')) {
                try {
                    $this->setComparator();
                } catch (Horde_Imap_Client_Exception_NoSupportExtension $e) {
                                    }
            }
        }

        $this->_isAuthenticated = true;
    }

    
    abstract protected function _login();

    
    public function logout()
    {
        if ($this->_isAuthenticated && $this->_connection->connected) {
            $this->_logout();
            $this->_connection->close();
        }

        $this->_connection = $this->_selected = null;
        $this->_isAuthenticated = false;
        $this->_mode = 0;
    }

    
    abstract protected function _logout();

    
    public function sendID($info = null)
    {
        if (!$this->queryCapability('ID')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('ID');
        }

        $this->_sendID(is_null($info) ? ($this->getParam('id') ?: array()) : $info);
    }

    
    abstract protected function _sendID($info);

    
    public function getID()
    {
        if (!$this->queryCapability('ID')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('ID');
        }

        return $this->_getID();
    }

    
    abstract protected function _getID();

    
    public function setLanguage($langs = null)
    {
        $lang = null;

        if ($this->queryCapability('LANGUAGE')) {
            $lang = is_null($langs)
                ? $this->getParam('lang')
                : $langs;
        }

        return is_null($lang)
            ? null
            : $this->_setLanguage($lang);
    }

    
    abstract protected function _setLanguage($langs);

    
    public function getLanguage($list = false)
    {
        if (!$this->queryCapability('LANGUAGE')) {
            return $list ? array() : null;
        }

        return $this->_getLanguage($list);
    }

    
    abstract protected function _getLanguage($list);

    
    public function openMailbox($mailbox, $mode = Horde_Imap_Client::OPEN_AUTO)
    {
        $this->login();

        $change = false;
        $mailbox = Horde_Imap_Client_Mailbox::get($mailbox);

        if ($mode == Horde_Imap_Client::OPEN_AUTO) {
            if (is_null($this->_selected) ||
                !$mailbox->equals($this->_selected)) {
                $mode = Horde_Imap_Client::OPEN_READONLY;
                $change = true;
            }
        } else {
            $change = (is_null($this->_selected) ||
                       !$mailbox->equals($this->_selected) ||
                       ($mode != $this->_mode));
        }

        if ($change) {
            $this->_openMailbox($mailbox, $mode);
            $this->_mailboxOb()->open = true;
            if ($this->_initCache(true)) {
                $this->_condstoreSync();
            }
        }
    }

    
    abstract protected function _openMailbox(Horde_Imap_Client_Mailbox $mailbox,
                                             $mode);

    
    protected function _changeSelected($mailbox = null, $mode = null)
    {
        $this->_mode = $mode;
        if (is_null($mailbox)) {
            $this->_selected = null;
        } else {
            $this->_selected = clone $mailbox;
            $this->_mailboxOb()->reset();
        }
    }

    
    protected function _mailboxOb($mailbox = null)
    {
        $name = is_null($mailbox)
            ? strval($this->_selected)
            : strval($mailbox);

        if (!isset($this->_temp['mailbox_ob'][$name])) {
            $this->_temp['mailbox_ob'][$name] = new Horde_Imap_Client_Base_Mailbox();
        }

        return $this->_temp['mailbox_ob'][$name];
    }

    
    public function currentMailbox()
    {
        return is_null($this->_selected)
            ? null
            : array(
                'mailbox' => clone $this->_selected,
                'mode' => $this->_mode
            );
    }

    
    public function createMailbox($mailbox, array $opts = array())
    {
        $this->login();

        if (!$this->queryCapability('CREATE-SPECIAL-USE')) {
            unset($opts['special_use']);
        }

        $this->_createMailbox(Horde_Imap_Client_Mailbox::get($mailbox), $opts);
    }

    
    abstract protected function _createMailbox(Horde_Imap_Client_Mailbox $mailbox,
                                               $opts);

    
    public function deleteMailbox($mailbox)
    {
        $this->login();

        $mailbox = Horde_Imap_Client_Mailbox::get($mailbox);

        $this->_deleteMailbox($mailbox);
        $this->_deleteMailboxPost($mailbox);
    }

    
    abstract protected function _deleteMailbox(Horde_Imap_Client_Mailbox $mailbox);

    
    protected function _deleteMailboxPost(Horde_Imap_Client_Mailbox $mailbox)
    {
        
        if ($this->_initCache()) {
            $this->_cache->deleteMailbox($mailbox);
        }
        unset($this->_temp['mailbox_ob'][strval($mailbox)]);

        
        try {
            $this->subscribeMailbox($mailbox, false);
        } catch (Horde_Imap_Client_Exception $e) {
                    }
    }

    
    public function renameMailbox($old, $new)
    {
        
        $old = Horde_Imap_Client_Mailbox::get($old);
        $new = Horde_Imap_Client_Mailbox::get($new);

        
        $base = $this->listMailboxes($old, Horde_Imap_Client::MBOX_SUBSCRIBED, array('delimiter' => true));
        if (empty($base)) {
            $base = $this->listMailboxes($old, Horde_Imap_Client::MBOX_ALL, array('delimiter' => true));
            $base = reset($base);
            $subscribed = array();
        } else {
            $base = reset($base);
            $subscribed = array($base['mailbox']);
        }

        $all_mboxes = array($base['mailbox']);
        if (strlen($base['delimiter'])) {
            $search = $old->list_escape . $base['delimiter'] . '*';
            $all_mboxes = array_merge($all_mboxes, $this->listMailboxes($search, Horde_Imap_Client::MBOX_ALL, array('flat' => true)));
            $subscribed = array_merge($subscribed, $this->listMailboxes($search, Horde_Imap_Client::MBOX_SUBSCRIBED, array('flat' => true)));
        }

        $this->_renameMailbox($old, $new);

        
        foreach ($all_mboxes as $val) {
            $this->_deleteMailboxPost($val);
        }

        foreach ($subscribed as $val) {
            try {
                $this->subscribeMailbox(new Horde_Imap_Client_Mailbox(substr_replace($val, $new, 0, strlen($old))));
            } catch (Horde_Imap_Client_Exception $e) {
                            }
        }
    }

    
    abstract protected function _renameMailbox(Horde_Imap_Client_Mailbox $old,
                                               Horde_Imap_Client_Mailbox $new);

    
    public function subscribeMailbox($mailbox, $subscribe = true)
    {
        $this->login();
        $this->_subscribeMailbox(Horde_Imap_Client_Mailbox::get($mailbox), (bool)$subscribe);
    }

    
    abstract protected function _subscribeMailbox(Horde_Imap_Client_Mailbox $mailbox,
                                                  $subscribe);

    
    public function listMailboxes($pattern,
                                  $mode = Horde_Imap_Client::MBOX_ALL,
                                  array $options = array())
    {
        $this->login();

        $pattern = is_array($pattern)
            ? array_unique($pattern)
            : array($pattern);

        
        $plist = array();
        foreach ($pattern as $val) {
            if ($val instanceof Horde_Imap_Client_Mailbox) {
                $val = $val->list_escape;
            }
            $plist[] = Horde_Imap_Client_Mailbox::get(preg_replace(
                array("/\*{2,}/", "/\%{2,}/"),
                array('*', '%'),
                Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($val)
            ), true);
        }

        if (isset($options['special_use']) &&
            !$this->queryCapability('SPECIAL-USE')) {
            unset($options['special_use']);
        }

        $ret = $this->_listMailboxes($plist, $mode, $options);

        if (!empty($options['status']) &&
            !$this->queryCapability('LIST-STATUS')) {
            foreach ($this->status(array_keys($ret), $options['status']) as $key => $val) {
                $ret[$key]['status'] = $val;
            }
        }

        if (empty($options['sort'])) {
            return $ret;
        }

        $list_ob = new Horde_Imap_Client_Mailbox_List(empty($options['flat']) ? array_keys($ret) : $ret);
        $sorted = $list_ob->sort(array(
            'delimiter' => empty($options['sort_delimiter']) ? '.' : $options['sort_delimiter']
        ));

        if (!empty($options['flat'])) {
            return $sorted;
        }

        $out = array();
        foreach ($sorted as $val) {
            $out[$val] = $ret[$val];
        }

        return $out;
    }

    
    abstract protected function _listMailboxes($pattern, $mode, $options);

    
    public function status($mailbox, $flags = Horde_Imap_Client::STATUS_ALL,
                           array $opts = array())
    {
        $opts = array_merge(array(
            'sort' => false,
            'sort_delimiter' => '.'
        ), $opts);

        $this->login();

        if (is_array($mailbox)) {
            if (empty($mailbox)) {
                return array();
            }
            $ret_array = true;
        } else {
            $mailbox = array($mailbox);
            $ret_array = false;
        }

        $mlist = array_map(array('Horde_Imap_Client_Mailbox', 'get'), $mailbox);

        $unselected_flags = array(
            'messages' => Horde_Imap_Client::STATUS_MESSAGES,
            'recent' => Horde_Imap_Client::STATUS_RECENT,
            'uidnext' => Horde_Imap_Client::STATUS_UIDNEXT,
            'uidvalidity' => Horde_Imap_Client::STATUS_UIDVALIDITY,
            'unseen' => Horde_Imap_Client::STATUS_UNSEEN
        );

        if (!$this->statuscache) {
            $flags |= Horde_Imap_Client::STATUS_FORCE_REFRESH;
        }

        if ($flags & Horde_Imap_Client::STATUS_ALL) {
            foreach ($unselected_flags as $val) {
                $flags |= $val;
            }
        }

        $master = $ret = array();

        
        if (($flags & Horde_Imap_Client::STATUS_HIGHESTMODSEQ) &&
            !isset($this->_temp['enabled']['CONDSTORE'])) {
            $master['highestmodseq'] = 0;
            $flags &= ~Horde_Imap_Client::STATUS_HIGHESTMODSEQ;
        }

        if (($flags & Horde_Imap_Client::STATUS_UIDNOTSTICKY) &&
            !$this->queryCapability('UIDPLUS')) {
            $master['uidnotsticky'] = false;
            $flags &= ~Horde_Imap_Client::STATUS_UIDNOTSTICKY;
        }

        
        if ($flags & Horde_Imap_Client::STATUS_UIDNEXT_FORCE) {
            $flags |= Horde_Imap_Client::STATUS_UIDNEXT;
        }

        foreach ($mlist as $val) {
            $name = strval($val);
            $tmp_flags = $flags;

            if ($val->equals($this->_selected)) {
                
                $opened = true;

                if ($flags & Horde_Imap_Client::STATUS_FORCE_REFRESH) {
                    $this->noop();
                }
            } else {
                
                $opened = ($flags & Horde_Imap_Client::STATUS_FIRSTUNSEEN) ||
                    ($flags & Horde_Imap_Client::STATUS_FLAGS) ||
                    ($flags & Horde_Imap_Client::STATUS_PERMFLAGS) ||
                    ($flags & Horde_Imap_Client::STATUS_UIDNOTSTICKY) ||
                    
                    (strpbrk($name, '*%') !== false);
            }

            $ret[$name] = $master;
            $ptr = &$ret[$name];

            
            if ($flags & Horde_Imap_Client::STATUS_PERMFLAGS) {
                $this->openMailbox($val, Horde_Imap_Client::OPEN_READWRITE);
                $opened = true;
            }

            
            if ($flags & Horde_Imap_Client::STATUS_SYNCMODSEQ) {
                $this->openMailbox($val);
                $ptr['syncmodseq'] = $this->_mailboxOb($val)->getStatus(Horde_Imap_Client::STATUS_SYNCMODSEQ);
                $tmp_flags &= ~Horde_Imap_Client::STATUS_SYNCMODSEQ;
                $opened = true;
            }

            if ($flags & Horde_Imap_Client::STATUS_SYNCFLAGUIDS) {
                $this->openMailbox($val);
                $ptr['syncflaguids'] = $this->getIdsOb($this->_mailboxOb($val)->getStatus(Horde_Imap_Client::STATUS_SYNCFLAGUIDS));
                $tmp_flags &= ~Horde_Imap_Client::STATUS_SYNCFLAGUIDS;
                $opened = true;
            }

            if ($flags & Horde_Imap_Client::STATUS_SYNCVANISHED) {
                $this->openMailbox($val);
                $ptr['syncvanished'] = $this->getIdsOb($this->_mailboxOb($val)->getStatus(Horde_Imap_Client::STATUS_SYNCVANISHED));
                $tmp_flags &= ~Horde_Imap_Client::STATUS_SYNCVANISHED;
                $opened = true;
            }

            
            if ($flags & Horde_Imap_Client::STATUS_RECENT_TOTAL) {
                $this->openMailbox($val);
                $ptr['recent_total'] = $this->_mailboxOb($val)->getStatus(Horde_Imap_Client::STATUS_RECENT_TOTAL);
                $tmp_flags &= ~Horde_Imap_Client::STATUS_RECENT_TOTAL;
                $opened = true;
            }

            if ($opened) {
                if ($tmp_flags) {
                    $tmp = $this->_status(array($val), $tmp_flags);
                    $ptr += reset($tmp);
                }
            } else {
                $to_process[] = $val;
            }
        }

        if ($flags && !empty($to_process)) {
            if ((count($to_process) > 1) &&
                $this->queryCapability('LIST-STATUS')) {
                foreach ($this->listMailboxes($to_process, Horde_Imap_Client::MBOX_ALL, array('status' => $flags)) as $key => $val) {
                    if (isset($val['status'])) {
                        $ret[$key] += $val['status'];
                    }
                }
            } else {
                foreach ($this->_status($to_process, $flags) as $key => $val) {
                    $ret[$key] += $val;
                }
            }
        }

        if (!$opts['sort'] || (count($ret) === 1)) {
            return $ret_array
                ? $ret
                : reset($ret);
        }

        $list_ob = new Horde_Imap_Client_Mailbox_List(array_keys($ret));
        $sorted = $list_ob->sort(array(
            'delimiter' => $opts['sort_delimiter']
        ));

        $out = array();
        foreach ($sorted as $val) {
            $out[$val] = $ret[$val];
        }

        return $out;
    }

    
    abstract protected function _status($mboxes, $flags);

    
    public function statusMultiple($mailboxes,
                                   $flags = Horde_Imap_Client::STATUS_ALL,
                                   array $opts = array())
    {
        return $this->status($mailboxes, $flags, $opts);
    }

    
    public function append($mailbox, $data, array $options = array())
    {
        $this->login();

        $mailbox = Horde_Imap_Client_Mailbox::get($mailbox);

        $ret = $this->_append($mailbox, $data, $options);

        if ($ret instanceof Horde_Imap_Client_Ids) {
            return $ret;
        }

        $uids = $this->getIdsOb();

        while (list(,$val) = each($data)) {
            if (is_resource($val['data'])) {
                rewind($val['data']);
            }

            $uids->add($this->_getUidByMessageId(
                $mailbox,
                Horde_Mime_Headers::parseHeaders($val['data'])->getValue('message-id')
            ));
        }

        return $uids;
    }

    
    abstract protected function _append(Horde_Imap_Client_Mailbox $mailbox,
                                        $data, $options);

    
    public function check()
    {
                if ($this->_isAuthenticated) {
            $this->_check();
        }
    }

    
    abstract protected function _check();

    
    public function close(array $options = array())
    {
                if (is_null($this->_selected)) {
            return;
        }

        
        if (!empty($options['expunge']) && $this->_initCache(true)) {
            
            $this->openMailbox($this->_selected, Horde_Imap_Client::OPEN_READWRITE);
            if ($this->_mode == Horde_Imap_Client::OPEN_READONLY) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Cannot expunge read-only mailbox."),
                    Horde_Imap_Client_Exception::MAILBOX_READONLY
                );
            }

            $search_query = new Horde_Imap_Client_Search_Query();
            $search_query->flag(Horde_Imap_Client::FLAG_DELETED, true);
            $search_res = $this->search($this->_selected, $search_query);
            $mbox = $this->_selected;
        } else {
            $search_res = null;
        }

        $this->_close($options);
        $this->_selected = null;
        $this->_mode = 0;

        if (!is_null($search_res)) {
            $this->_deleteMsgs($mbox, $search_res['match']);
        }
    }

    
    abstract protected function _close($options);

    
    public function expunge($mailbox, array $options = array())
    {
                $this->openMailbox($mailbox, Horde_Imap_Client::OPEN_READWRITE);

        
        if ($this->_mode == Horde_Imap_Client::OPEN_READONLY) {
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Cannot expunge read-only mailbox."),
                Horde_Imap_Client_Exception::MAILBOX_READONLY
            );
        }

        if (empty($options['ids'])) {
            $options['ids'] = $this->getIdsOb(Horde_Imap_Client_Ids::ALL);
        } elseif ($options['ids']->isEmpty()) {
            return $this->getIdsOb();
        }

        return $this->_expunge($options);
    }

    
    abstract protected function _expunge($options);

    
    public function search($mailbox, $query = null, array $options = array())
    {
        $this->login();

        if (empty($options['results'])) {
            $options['results'] = array(
                Horde_Imap_Client::SEARCH_RESULTS_MATCH,
                Horde_Imap_Client::SEARCH_RESULTS_COUNT
            );
        } elseif (!in_array(Horde_Imap_Client::SEARCH_RESULTS_COUNT, $options['results'])) {
            $options['results'][] = Horde_Imap_Client::SEARCH_RESULTS_COUNT;
        }

                if (is_null($query)) {
            $query = new Horde_Imap_Client_Search_Query();
        }

                if ((($pos = array_search(Horde_Imap_Client::SEARCH_RESULTS_SAVE, $options['results'])) !== false) &&
            !$this->queryCapability('SEARCHRES')) {
            unset($options['results'][$pos]);
        }

                if (!empty($options['sort'])) {
            $sort = $this->queryCapability('SORT');
            foreach ($options['sort'] as $key => $val) {
                switch ($val) {
                case Horde_Imap_Client::SORT_DISPLAYFROM_FALLBACK:
                    $options['sort'][$key] = (!is_array($sort) || !in_array('DISPLAY', $sort))
                        ? Horde_Imap_Client::SORT_FROM
                        : Horde_Imap_Client::SORT_DISPLAYFROM;
                    break;

                case Horde_Imap_Client::SORT_DISPLAYTO_FALLBACK:
                    $options['sort'][$key] = (!is_array($sort) || !in_array('DISPLAY', $sort))
                        ? Horde_Imap_Client::SORT_TO
                        : Horde_Imap_Client::SORT_DISPLAYTO;
                    break;
                }
            }
        }

                $options['_query'] = $query->build($this->capability());
        if (!is_null($options['_query']['charset']) &&
            array_key_exists($options['_query']['charset'], $this->_init['s_charset']) &&
            !$this->_init['s_charset'][$options['_query']['charset']]) {
            foreach (array_merge(array_keys(array_filter($this->_init['s_charset'])), array('US-ASCII')) as $val) {
                try {
                    $new_query = clone $query;
                    $new_query->charset($val);
                    break;
                } catch (Horde_Imap_Client_Exception_SearchCharset $e) {
                    unset($new_query);
                }
            }

            if (!isset($new_query)) {
                throw $e;
            }

            $query = $new_query;
            $options['_query'] = $query->build($this->capability());
        }

        
        if (in_array(Horde_Imap_Client::SEARCH_RESULTS_RELEVANCY, $options['results']) &&
            !in_array('SEARCH=FUZZY', $options['_query']['exts_used'])) {
            throw new InvalidArgumentException('Cannot specify RELEVANCY results if not doing a FUZZY search.');
        }

        
        if (!empty($options['partial'])) {
            $pids = $this->getIdsOb($options['partial'], true)->range_string;
            if (!strlen($pids)) {
                throw new InvalidArgumentException('Cannot specify empty sequence range for a PARTIAL search.');
            }

            if (strpos($pids, ':') === false) {
                $pids .= ':' . $pids;
            }

            $options['partial'] = $pids;
        }

        
        if ((count($options['results']) === 1) &&
            (reset($options['results']) == Horde_Imap_Client::SEARCH_RESULTS_COUNT)) {
            switch ($options['_query']['query']) {
            case 'ALL':
                $ret = $this->status($this->_selected, Horde_Imap_Client::STATUS_MESSAGES);
                return array('count' => $ret['messages']);

            case 'RECENT':
                $ret = $this->status($this->_selected, Horde_Imap_Client::STATUS_RECENT);
                return array('count' => $ret['recent']);
            }
        }

        $this->openMailbox($mailbox, Horde_Imap_Client::OPEN_AUTO);

        
        $cache = null;
        if (empty($options['nocache']) &&
            $this->_initCache(true) &&
            (isset($this->_temp['enabled']['CONDSTORE']) ||
             !$query->flagSearch())) {
            $cache = $this->_getSearchCache('search', $options);
            if (isset($cache['data'])) {
                if (isset($cache['data']['match'])) {
                    $cache['data']['match'] = $this->getIdsOb($cache['data']['match']);
                }
                return $cache['data'];
            }
        }

        
        $status_res = $this->status($this->_selected, Horde_Imap_Client::STATUS_MESSAGES | Horde_Imap_Client::STATUS_HIGHESTMODSEQ);
        if ($status_res['messages'] ||
            in_array(Horde_Imap_Client::SEARCH_RESULTS_SAVE, $options['results'])) {
            
            if (in_array('CONDSTORE', $options['_query']['exts']) &&
                !$this->_mailboxOb()->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ)) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Mailbox does not support mod-sequences."),
                    Horde_Imap_Client_Exception::MBOXNOMODSEQ
                );
            }

            $ret = $this->_search($query, $options);
        } else {
            $ret = array(
                'count' => 0,
                'match' => $this->getIdsOb(),
                'max' => null,
                'min' => null,
                'relevancy' => array()
            );
            if (isset($status_res['highestmodseq'])) {
                $ret['modseq'] = $status_res['highestmodseq'];
            }
        }

        if ($cache) {
            $save = $ret;
            if (isset($save['match'])) {
                $save['match'] = strval($ret['match']);
            }
            $this->_setSearchCache($save, $cache);
        }

        return $ret;
    }

    
    abstract protected function _search($query, $options);

    
    public function setComparator($comparator = null)
    {
        $comp = is_null($comparator)
            ? $this->getParam('comparator')
            : $comparator;
        if (is_null($comp)) {
            return;
        }

        $this->login();

        $i18n = $this->queryCapability('I18NLEVEL');
        if (empty($i18n) || (max($i18n) < 2)) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension(
                'I18NLEVEL',
                'The IMAP server does not support changing SEARCH/SORT comparators.'
            );
        }

        $this->_setComparator($comp);
    }

    
    abstract protected function _setComparator($comparator);

    
    public function getComparator()
    {
        $this->login();

        $i18n = $this->queryCapability('I18NLEVEL');
        if (empty($i18n) || (max($i18n) < 2)) {
            return null;
        }

        return $this->_getComparator();
    }

    
    abstract protected function _getComparator();

    
    public function thread($mailbox, array $options = array())
    {
                $this->openMailbox($mailbox, Horde_Imap_Client::OPEN_AUTO);

        
        $cache = null;
        if ($this->_initCache(true) &&
            (isset($this->_temp['enabled']['CONDSTORE']) ||
             empty($options['search']) ||
             !$options['search']->flagSearch())) {
            $cache = $this->_getSearchCache('thread', $options);
            if (isset($cache['data']) &&
                ($cache['data'] instanceof Horde_Imap_Client_Data_Thread)) {
                return $cache['data'];
            }
        }

        $status_res = $this->status($this->_selected, Horde_Imap_Client::STATUS_MESSAGES);

        $ob = $status_res['messages']
            ? $this->_thread($options)
            : new Horde_Imap_Client_Data_Thread(array(), empty($options['sequence']) ? 'uid' : 'sequence');

        if ($cache) {
            $this->_setSearchCache($ob, $cache);
        }

        return $ob;
    }

    
    abstract protected function _thread($options);

    
    public function fetch($mailbox, $query, array $options = array())
    {
        try {
            $ret = $this->_fetchWrapper($mailbox, $query, $options);
            unset($this->_temp['fetch_nocache']);
            return $ret;
        } catch (Exception $e) {
            unset($this->_temp['fetch_nocache']);
            throw $e;
        }
    }

    
    private function _fetchWrapper($mailbox, $query, $options)
    {
        $this->login();

        $query = clone $query;

        $cache_array = $header_cache = $new_query = array();

        if (empty($options['ids'])) {
            $options['ids'] = $this->getIdsOb(Horde_Imap_Client_Ids::ALL);
        } elseif ($options['ids']->isEmpty()) {
            return new Horde_Imap_Client_Fetch_Results($this->_fetchDataClass);
        } elseif ($options['ids']->search_res &&
                  !$this->queryCapability('SEARCHRES')) {
            
            throw new Horde_Imap_Client_Exception_NoSupportExtension('SEARCHRES');
        }

        $this->openMailbox($mailbox, Horde_Imap_Client::OPEN_AUTO);
        $mbox_ob = $this->_mailboxOb();

        if (!empty($options['nocache'])) {
            $this->_temp['fetch_nocache'] = true;
        }

        $cf = $this->_initCache(true)
            ? $this->_cacheFields()
            : array();

        if (!empty($cf)) {
            
            $query->uid();
        }

        $modseq_check = !empty($options['changedsince']);
        if ($query->contains(Horde_Imap_Client::FETCH_MODSEQ)) {
            if (!isset($this->_temp['enabled']['CONDSTORE'])) {
                unset($query[Horde_Imap_Client::FETCH_MODSEQ]);
            } elseif (empty($options['changedsince'])) {
                $modseq_check = true;
            }
        }

        if ($modseq_check &&
            !$mbox_ob->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ)) {
            
            throw new Horde_Imap_Client_Exception(
                Horde_Imap_Client_Translation::r("Mailbox does not support mod-sequences."),
                Horde_Imap_Client_Exception::MBOXNOMODSEQ
            );
        }

        
        foreach ($cf as $k => $v) {
            if (isset($query[$k])) {
                switch ($k) {
                case Horde_Imap_Client::FETCH_ENVELOPE:
                case Horde_Imap_Client::FETCH_FLAGS:
                case Horde_Imap_Client::FETCH_IMAPDATE:
                case Horde_Imap_Client::FETCH_SIZE:
                case Horde_Imap_Client::FETCH_STRUCTURE:
                    $cache_array[$k] = $v;
                    break;

                case Horde_Imap_Client::FETCH_HEADERS:
                    $this->_temp['headers_caching'] = array();

                    foreach ($query[$k] as $key => $val) {
                        
                        if (!empty($val['cache']) && !empty($val['peek'])) {
                            $cache_array[$k] = $v;
                            ksort($val);
                            $header_cache[$key] = hash(
                                (PHP_MINOR_VERSION >= 4) ? 'fnv132' : 'sha1',
                                serialize($val)
                            );
                        }
                    }
                    break;
                }
            }
        }

        $ret = new Horde_Imap_Client_Fetch_Results(
            $this->_fetchDataClass,
            $options['ids']->sequence ? Horde_Imap_Client_Fetch_Results::SEQUENCE : Horde_Imap_Client_Fetch_Results::UID
        );

        
        if (empty($cache_array)) {
            $options['_query'] = $query;
            $this->_fetch($ret, array($options));
            return $ret;
        }

        $cs_ret = empty($options['changedsince'])
            ? null
            : clone $ret;

        
        $ids = $this->resolveIds($this->_selected, $options['ids'], empty($options['exists']) ? 1 : 2);

        
        $cache_array[Horde_Imap_Client::FETCH_DOWNGRADED] = self::CACHE_DOWNGRADED;

        
        $data = $this->_cache->get($this->_selected, $ids->ids, array_values($cache_array), $mbox_ob->getStatus(Horde_Imap_Client::STATUS_UIDVALIDITY));

        
        $map = array_flip($mbox_ob->map->map);
        $sequence = $options['ids']->sequence;
        foreach ($ids as $uid) {
            $crit = clone $query;

            if ($sequence) {
                if (!isset($map[$uid])) {
                    continue;
                }
                $entry_idx = $map[$uid];
            } else {
                $entry_idx = $uid;
                unset($crit[Horde_Imap_Client::FETCH_UID]);
            }

            $entry = $ret->get($entry_idx);

            if (isset($map[$uid])) {
                $entry->setSeq($map[$uid]);
                unset($crit[Horde_Imap_Client::FETCH_SEQ]);
            }

            $entry->setUid($uid);

            foreach ($cache_array as $key => $cid) {
                switch ($key) {
                case Horde_Imap_Client::FETCH_DOWNGRADED:
                    if (!empty($data[$uid][$cid])) {
                        $entry->setDowngraded(true);
                    }
                    break;

                case Horde_Imap_Client::FETCH_ENVELOPE:
                    if (isset($data[$uid][$cid]) &&
                        ($data[$uid][$cid] instanceof Horde_Imap_Client_Data_Envelope)) {
                        $entry->setEnvelope($data[$uid][$cid]);
                        unset($crit[$key]);
                    }
                    break;

                case Horde_Imap_Client::FETCH_FLAGS:
                    if (isset($data[$uid][$cid]) &&
                        is_array($data[$uid][$cid])) {
                        $entry->setFlags($data[$uid][$cid]);
                        unset($crit[$key]);
                    }
                    break;

                case Horde_Imap_Client::FETCH_HEADERS:
                    foreach ($header_cache as $hkey => $hval) {
                        if (isset($data[$uid][$cid][$hval])) {
                            
                            $entry->setHeaders($hkey, $data[$uid][$cid][$hval]);
                            $crit->remove($key, $hkey);
                        } else {
                            $this->_temp['headers_caching'][$hkey] = $hval;
                        }
                    }
                    break;

                case Horde_Imap_Client::FETCH_IMAPDATE:
                    if (isset($data[$uid][$cid]) &&
                        ($data[$uid][$cid] instanceof Horde_Imap_Client_DateTime)) {
                        $entry->setImapDate($data[$uid][$cid]);
                        unset($crit[$key]);
                    }
                    break;

                case Horde_Imap_Client::FETCH_SIZE:
                    if (isset($data[$uid][$cid])) {
                        $entry->setSize($data[$uid][$cid]);
                        unset($crit[$key]);
                    }
                    break;

                case Horde_Imap_Client::FETCH_STRUCTURE:
                    if (isset($data[$uid][$cid]) &&
                        ($data[$uid][$cid] instanceof Horde_Mime_Part)) {
                        $entry->setStructure($data[$uid][$cid]);
                        unset($crit[$key]);
                    }
                    break;
                }
            }

            if (count($crit)) {
                $sig = $crit->hash();
                if (isset($new_query[$sig])) {
                    $new_query[$sig]['i'][] = $entry_idx;
                } else {
                    $new_query[$sig] = array(
                        'c' => $crit,
                        'i' => array($entry_idx)
                    );
                }
            }
        }

        $to_fetch = array();
        foreach ($new_query as $val) {
            $ids_ob = $this->getIdsOb(null, $sequence);
            $ids_ob->duplicates = true;
            $ids_ob->add($val['i']);
            $to_fetch[] = array_merge($options, array(
                '_query' => $val['c'],
                'ids' => $ids_ob
            ));
        }

        if (!empty($to_fetch)) {
            $this->_fetch(is_null($cs_ret) ? $ret : $cs_ret, $to_fetch);
        }

        if (is_null($cs_ret)) {
            return $ret;
        }

        
        if (empty($new_query)) {
            $squery = new Horde_Imap_Client_Search_Query();
            $squery->modseq($options['changedsince'] + 1);
            $squery->ids($options['ids']);

            $cs = $this->search($this->_selected, $squery, array(
                'sequence' => $sequence
            ));

            foreach ($cs['match'] as $val) {
                $entry = $ret->get($val);
                if ($sequence) {
                    $entry->setSeq($val);
                } else {
                    $entry->setUid($val);
                }
                $cs_ret[$val] = $entry;
            }
        } else {
            foreach ($cs_ret as $key => $val) {
                $val->merge($ret->get($key));
            }
        }

        return $cs_ret;
    }

    
    abstract protected function _fetch(Horde_Imap_Client_Fetch_Results $results,
                                       $queries);

    
    public function vanished($mailbox, $modseq, array $opts = array())
    {
        $this->login();

        $qresync = $this->queryCapability('QRESYNC');

        if (empty($opts['ids'])) {
            if (!$qresync) {
                return $this->getIdsOb();
            }
            $opts['ids'] = $this->getIdsOb(Horde_Imap_Client_Ids::ALL);
        } elseif ($opts['ids']->isEmpty()) {
            return $this->getIdsOb();
        } elseif ($opts['ids']->sequence) {
            throw new InvalidArgumentException('Vanished requires UIDs.');
        }

        $this->openMailbox($mailbox, Horde_Imap_Client::OPEN_AUTO);

        if ($qresync) {
            if (!$this->_mailboxOb()->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ)) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Mailbox does not support mod-sequences."),
                    Horde_Imap_Client_Exception::MBOXNOMODSEQ
                );
            }

            return $this->_vanished(max(1, $modseq), $opts['ids']);
        }

        $ids = $this->resolveIds($mailbox, $opts['ids']);

        $squery = new Horde_Imap_Client_Search_Query();
        $squery->ids($this->getIdsOb($ids->range_string));
        $search = $this->search($mailbox, $squery, array(
            'nocache' => true
        ));

        return $this->getIdsOb(array_diff($ids->ids, $search['match']->ids));
    }

    
    abstract protected function _vanished($modseq, Horde_Imap_Client_Ids $ids);

    
    public function store($mailbox, array $options = array())
    {
                $this->openMailbox($mailbox, Horde_Imap_Client::OPEN_READWRITE);

        
        if (empty($options['ids'])) {
            $options['ids'] = $this->getIdsOb(Horde_Imap_Client_Ids::ALL);
        } elseif ($options['ids']->isEmpty()) {
            return $this->getIdsOb();
        } elseif ($options['ids']->search_res &&
                  !$this->queryCapability('SEARCHRES')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('SEARCHRES');
        }

        if (!empty($options['unchangedsince'])) {
            if (!isset($this->_temp['enabled']['CONDSTORE'])) {
                throw new Horde_Imap_Client_Exception_NoSupportExtension('CONDSTORE');
            }

            
            if (!$this->_mailboxOb()->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ)) {
                throw new Horde_Imap_Client_Exception(
                    Horde_Imap_Client_Translation::r("Mailbox does not support mod-sequences."),
                    Horde_Imap_Client_Exception::MBOXNOMODSEQ
                );
            }
        }

        return $this->_store($options);
    }

    
    abstract protected function _store($options);

    
    public function copy($source, $dest, array $options = array())
    {
                $this->openMailbox($source, empty($options['move']) ? Horde_Imap_Client::OPEN_AUTO : Horde_Imap_Client::OPEN_READWRITE);

        
        if (empty($options['ids'])) {
            $options['ids'] = $this->getIdsOb(Horde_Imap_Client_Ids::ALL);
        } elseif ($options['ids']->isEmpty()) {
            return array();
        } elseif ($options['ids']->search_res &&
                  !$this->queryCapability('SEARCHRES')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('SEARCHRES');
        }

        $dest = Horde_Imap_Client_Mailbox::get($dest);
        $res = $this->_copy($dest, $options);

        if (($res === true) && !empty($options['force_map'])) {
            
            $query = new Horde_Imap_Client_Fetch_Query();
            $query->envelope();
            $fetch = $this->fetch($source, $query, array(
                'ids' => $options['ids']
            ));

            $res = array();
            foreach ($fetch as $val) {
                if ($uid = $this->_getUidByMessageId($dest, $val->getEnvelope()->message_id)) {
                    $res[$val->getUid()] = $uid;
                }
            }
        }

        return $res;
    }

    
    abstract protected function _copy(Horde_Imap_Client_Mailbox $dest,
                                      $options);

    
    public function setQuota($root, array $resources = array())
    {
        $this->login();

        if (!$this->queryCapability('QUOTA')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('QUOTA');
        }

        if (!empty($resources)) {
            $this->_setQuota(Horde_Imap_Client_Mailbox::get($root), $resources);
        }
    }

    
    abstract protected function _setQuota(Horde_Imap_Client_Mailbox $root,
                                          $resources);

    
    public function getQuota($root)
    {
        $this->login();

        if (!$this->queryCapability('QUOTA')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('QUOTA');
        }

        return $this->_getQuota(Horde_Imap_Client_Mailbox::get($root));
    }

    
    abstract protected function _getQuota(Horde_Imap_Client_Mailbox $root);

    
    public function getQuotaRoot($mailbox)
    {
        $this->login();

        if (!$this->queryCapability('QUOTA')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('QUOTA');
        }

        return $this->_getQuotaRoot(Horde_Imap_Client_Mailbox::get($mailbox));
    }

    
    abstract protected function _getQuotaRoot(Horde_Imap_Client_Mailbox $mailbox);

    
    public function getACL($mailbox)
    {
        $this->login();
        return $this->_getACL(Horde_Imap_Client_Mailbox::get($mailbox));
    }

    
    abstract protected function _getACL(Horde_Imap_Client_Mailbox $mailbox);

    
    public function setACL($mailbox, $identifier, $options)
    {
        $this->login();

        if (!$this->queryCapability('ACL')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('ACL');
        }

        if (empty($options['rights'])) {
            if (!isset($options['action']) ||
                (($options['action'] != 'add') &&
                 $options['action'] != 'remove')) {
                $this->_deleteACL(
                    Horde_Imap_Client_Mailbox::get($mailbox),
                    Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($identifier)
                );
            }
            return;
        }

        $acl = ($options['rights'] instanceof Horde_Imap_Client_Data_Acl)
            ? $options['rights']
            : new Horde_Imap_Client_Data_Acl(strval($options['rights']));

        $options['rights'] = $acl->getString(
            $this->queryCapability('RIGHTS')
                ? Horde_Imap_Client_Data_AclCommon::RFC_4314
                : Horde_Imap_Client_Data_AclCommon::RFC_2086
        );
        if (isset($options['action'])) {
            switch ($options['action']) {
            case 'add':
                $options['rights'] = '+' . $options['rights'];
                break;
            case 'remove':
                $options['rights'] = '-' . $options['rights'];
                break;
            }
        }

        $this->_setACL(
            Horde_Imap_Client_Mailbox::get($mailbox),
            Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($identifier),
            $options
        );
    }

    
    abstract protected function _setACL(Horde_Imap_Client_Mailbox $mailbox,
                                        $identifier, $options);

    
    public function deleteACL($mailbox, $identifier)
    {
        $this->login();

        if (!$this->queryCapability('ACL')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('ACL');
        }

        $this->_deleteACL(
            Horde_Imap_Client_Mailbox::get($mailbox),
            Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($identifier)
        );
    }

    
    abstract protected function _deleteACL(Horde_Imap_Client_Mailbox $mailbox,
                                           $identifier);

    
    public function listACLRights($mailbox, $identifier)
    {
        $this->login();

        if (!$this->queryCapability('ACL')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('ACL');
        }

        return $this->_listACLRights(
            Horde_Imap_Client_Mailbox::get($mailbox),
            Horde_Imap_Client_Utf7imap::Utf8ToUtf7Imap($identifier)
        );
    }

    
    abstract protected function _listACLRights(Horde_Imap_Client_Mailbox $mailbox,
                                               $identifier);

    
    public function getMyACLRights($mailbox)
    {
        $this->login();

        if (!$this->queryCapability('ACL')) {
            throw new Horde_Imap_Client_Exception_NoSupportExtension('ACL');
        }

        return $this->_getMyACLRights(Horde_Imap_Client_Mailbox::get($mailbox));
    }

    
    abstract protected function _getMyACLRights(Horde_Imap_Client_Mailbox $mailbox);

    
    public function allAclRights()
    {
        $this->login();

        $rights = array(
            Horde_Imap_Client::ACL_LOOKUP,
            Horde_Imap_Client::ACL_READ,
            Horde_Imap_Client::ACL_SEEN,
            Horde_Imap_Client::ACL_WRITE,
            Horde_Imap_Client::ACL_INSERT,
            Horde_Imap_Client::ACL_POST,
            Horde_Imap_Client::ACL_ADMINISTER
        );

        if ($capability = $this->queryCapability('RIGHTS')) {
                        return array_merge($rights, str_split(reset($capability)));
        }

                        return array_merge($rights, array(
            Horde_Imap_Client::ACL_CREATE,
            Horde_Imap_Client::ACL_DELETE
        ));
    }

    
    public function getMetadata($mailbox, $entries, array $options = array())
    {
        $this->login();

        if (!is_array($entries)) {
            $entries = array($entries);
        }

        return $this->_getMetadata(Horde_Imap_Client_Mailbox::get($mailbox), array_map(array('Horde_Imap_Client_Utf7imap', 'Utf8ToUtf7Imap'), $entries), $options);
    }

    
    abstract protected function _getMetadata(Horde_Imap_Client_Mailbox $mailbox,
                                             $entries, $options);

    
    public function setMetadata($mailbox, $data)
    {
        $this->login();
        $this->_setMetadata(Horde_Imap_Client_Mailbox::get($mailbox), $data);
    }

    
    abstract protected function _setMetadata(Horde_Imap_Client_Mailbox $mailbox,
                                             $data);

    

    
    public function getCacheId($mailbox, array $addl = array())
    {
        return Horde_Imap_Client_Base_Deprecated::getCacheId($this, $mailbox, isset($this->_temp['enabled']['CONDSTORE']), $addl);
    }

    
    public function parseCacheId($id)
    {
        return Horde_Imap_Client_Base_Deprecated::parseCacheId($id);
    }

    
    public function resolveIds(Horde_Imap_Client_Mailbox $mailbox,
                               Horde_Imap_Client_Ids $ids, $convert = 0)
    {
        $map = $this->_mailboxOb($mailbox)->map;

        if ($ids->special) {
            
            if (!$convert && $ids->all && $ids->sequence) {
                $res = $this->status($mailbox, Horde_Imap_Client::STATUS_MESSAGES);
                return $this->getIdsOb($res['messages'] ? ('1:' . $res['messages']) : array(), true);
            }

            $convert = 2;
        } elseif (!$convert ||
                  (!$ids->sequence && ($convert == 1)) ||
                  $ids->isEmpty()) {
            return clone $ids;
        } else {
            
            $lookup = $map->lookup($ids);
            if (count($lookup) === count($ids)) {
                return $this->getIdsOb(array_values($lookup));
            }
        }

        $query = new Horde_Imap_Client_Search_Query();
        $query->ids($ids);

        $res = $this->search($mailbox, $query, array(
            'results' => array(
                Horde_Imap_Client::SEARCH_RESULTS_MATCH,
                Horde_Imap_Client::SEARCH_RESULTS_SAVE
            ),
            'sequence' => (!$convert && $ids->sequence),
            'sort' => array(Horde_Imap_Client::SORT_SEQUENCE)
        ));

        
        if ($convert) {
            if ($ids->all) {
                $ids = $this->getIdsOb('1:' . count($res['match']));
            } elseif ($ids->special) {
                return $res['match'];
            }

            
            $list1 = array_slice($ids->ids, 0, count($res['match']));
            $list2 = $res['match']->ids;
            if (!empty($list1) &&
                !empty($list2) &&
                (count($list1) === count($list2))) {
                $map->update(array_combine($list1, $list2));
            }
        }

        return $res['match'];
    }

    
    public function validSearchCharset($charset)
    {
        $charset = strtoupper($charset);

        if ($charset == 'US-ASCII') {
            return true;
        }

        if (!isset($this->_init['s_charset'][$charset])) {
            $s_charset = $this->_init['s_charset'];

            
            $query = new Horde_Imap_Client_Search_Query();
            $query->charset($charset, false);
            $query->ids($this->getIdsOb(1, true));
            $query->text('a');
            try {
                $this->search('INBOX', $query, array(
                    'nocache' => true,
                    'sequence' => true
                ));
                $s_charset[$charset] = true;
            } catch (Horde_Imap_Client_Exception $e) {
                $s_charset[$charset] = ($e->getCode() !== Horde_Imap_Client_Exception::BADCHARSET);
            }

            $this->_setInit('s_charset', $s_charset);
        }

        return $this->_init['s_charset'][$charset];
    }

    

    
    public function getSyncToken($mailbox)
    {
        $out = array();

        foreach ($this->_syncStatus($mailbox) as $key => $val) {
            $out[] = $key . $val;
        }

        return base64_encode(implode(',', $out));
    }

    
    public function sync($mailbox, $token, array $opts = array())
    {
        if (($token = base64_decode($token, true)) === false) {
            throw new Horde_Imap_Client_Exception_Sync('Bad token.', Horde_Imap_Client_Exception_Sync::BAD_TOKEN);
        }

        $sync = array();
        foreach (explode(',', $token) as $val) {
            $sync[substr($val, 0, 1)] = substr($val, 1);
        }

        return new Horde_Imap_Client_Data_Sync(
            $this,
            $mailbox,
            $sync,
            $this->_syncStatus($mailbox),
            (isset($opts['criteria']) ? $opts['criteria'] : Horde_Imap_Client::SYNC_ALL),
            (isset($opts['ids']) ? $opts['ids'] : null)
        );
    }

    

    
    protected function _updateCache(Horde_Imap_Client_Fetch_Results $data)
    {
        if (!empty($this->_temp['fetch_nocache']) ||
            empty($this->_selected) ||
            !count($data) ||
            !$this->_initCache(true)) {
            return;
        }

        $c = $this->getParam('cache');
        if (in_array(strval($this->_selected), $c['fetch_ignore'])) {
            $this->_debug->info(sprintf(
                'CACHE: Ignoring FETCH data [%s]',
                $this->_selected
            ));
            return;
        }

        
        $mbox_ob = $this->_mailboxOb();
        $highestmodseq = $mbox_ob->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ);
        $uidvalidity = $mbox_ob->getStatus(Horde_Imap_Client::STATUS_UIDVALIDITY);

        $mapping = $modseq = $tocache = array();
        if (count($data)) {
            $cf = $this->_cacheFields();
        }

        foreach ($data as $v) {
            
            if (!($uid = $v->getUid())) {
                return;
            }

            $tmp = array();

            if ($v->isDowngraded()) {
                $tmp[self::CACHE_DOWNGRADED] = true;
            }

            foreach ($cf as $key => $val) {
                if ($v->exists($key)) {
                    switch ($key) {
                    case Horde_Imap_Client::FETCH_ENVELOPE:
                        $tmp[$val] = $v->getEnvelope();
                        break;

                    case Horde_Imap_Client::FETCH_FLAGS:
                        if ($highestmodseq) {
                            $modseq[$uid] = $v->getModSeq();
                            $tmp[$val] = $v->getFlags();
                        }
                        break;

                    case Horde_Imap_Client::FETCH_HEADERS:
                        foreach ($this->_temp['headers_caching'] as $label => $hash) {
                            if ($hdr = $v->getHeaders($label)) {
                                $tmp[$val][$hash] = $hdr;
                            }
                        }
                        break;

                    case Horde_Imap_Client::FETCH_IMAPDATE:
                        $tmp[$val] = $v->getImapDate();
                        break;

                    case Horde_Imap_Client::FETCH_SIZE:
                        $tmp[$val] = $v->getSize();
                        break;

                    case Horde_Imap_Client::FETCH_STRUCTURE:
                        $tmp[$val] = clone $v->getStructure();
                        break;
                    }
                }
            }

            if (!empty($tmp)) {
                $tocache[$uid] = $tmp;
            }

            $mapping[$v->getSeq()] = $uid;
        }

        if (!empty($mapping)) {
            if (!empty($tocache)) {
                $this->_cache->set($this->_selected, $tocache, $uidvalidity);
            }

            $this->_mailboxOb()->map->update($mapping);
        }

        if (!empty($modseq)) {
            $this->_updateModSeq(max(array_merge($modseq, array($highestmodseq))));
            $mbox_ob->setStatus(Horde_Imap_Client::STATUS_SYNCFLAGUIDS, array_keys($modseq));
        }
    }

    
    protected function _moveCache(Horde_Imap_Client_Mailbox $to, $map,
                                  $uidvalid)
    {
        if (!$this->_initCache()) {
            return;
        }

        $c = $this->getParam('cache');
        if (in_array(strval($to), $c['fetch_ignore'])) {
            $this->_debug->info(sprintf(
                'CACHE: Ignoring moving FETCH data (%s => %s)',
                $this->_selected,
                $to
            ));
            return;
        }

        $old = $this->_cache->get($this->_selected, array_keys($map), null);
        $new = array();

        foreach ($map as $key => $val) {
            if (!empty($old[$key])) {
                $new[$val] = $old[$key];
            }
        }

        if (!empty($new)) {
            $this->_cache->set($to, $new, $uidvalid);
        }
    }

    
    protected function _deleteMsgs(Horde_Imap_Client_Mailbox $mailbox,
                                   Horde_Imap_Client_Ids $ids,
                                   array $opts = array())
    {
        if (!$this->_initCache()) {
            return $ids;
        }

        $mbox_ob = $this->_mailboxOb();
        $ids_ob = $ids->sequence
            ? $this->getIdsOb($mbox_ob->map->lookup($ids))
            : $ids;

        $this->_cache->deleteMsgs($mailbox, $ids_ob->ids);
        $mbox_ob->setStatus(Horde_Imap_Client::STATUS_SYNCVANISHED, $ids_ob->ids);
        $mbox_ob->map->remove($ids);

        return $ids_ob;
    }

    
    protected function _getSearchCache($type, $options)
    {
        $status = $this->status($this->_selected, Horde_Imap_Client::STATUS_HIGHESTMODSEQ | Horde_Imap_Client::STATUS_UIDVALIDITY);

        
        if (empty($status['highestmodseq'])) {
            return null;
        }

        ksort($options);
        $cache = hash(
            (PHP_MINOR_VERSION >= 4) ? 'fnv132' : 'sha1',
            $type . serialize($options)
        );
        $cacheid = $this->getSyncToken($this->_selected);
        $ret = array();

        $md = $this->_cache->getMetaData(
            $this->_selected,
            $status['uidvalidity'],
            array(self::CACHE_SEARCH, self::CACHE_SEARCHID)
        );

        if (!isset($md[self::CACHE_SEARCHID]) ||
            ($md[self::CACHE_SEARCHID] != $cacheid)) {
            $md[self::CACHE_SEARCH] = array();
            $md[self::CACHE_SEARCHID] = $cacheid;
            if ($this->_debug->debug &&
                !isset($this->_temp['searchcacheexpire'][strval($this->_selected)])) {
                $this->_debug->info(sprintf(
                    'SEARCH: Expired from cache [%s]',
                    $this->_selected
                ));
                $this->_temp['searchcacheexpire'][strval($this->_selected)] = true;
            }
        } elseif (isset($md[self::CACHE_SEARCH][$cache])) {
            $this->_debug->info(sprintf(
                'SEARCH: Retrieved %s from cache (%s [%s])',
                $type,
                $cache,
                $this->_selected
            ));
            $ret['data'] = $md[self::CACHE_SEARCH][$cache];
            unset($md[self::CACHE_SEARCHID]);
        }

        return array_merge($ret, array(
            'id' => $cache,
            'metadata' => $md,
            'type' => $type
        ));
    }

    
    protected function _setSearchCache($data, $sdata)
    {
        $sdata['metadata'][self::CACHE_SEARCH][$sdata['id']] = $data;

        $this->_cache->setMetaData($this->_selected, null, $sdata['metadata']);

        if ($this->_debug->debug) {
            $this->_debug->info(sprintf(
                'SEARCH: Saved %s to cache (%s [%s])',
                $sdata['type'],
                $sdata['id'],
                $this->_selected
            ));
            unset($this->_temp['searchcacheexpire'][strval($this->_selected)]);
        }
    }

    
    protected function _updateModSeq($modseq)
    {
        if (!$this->_initCache(true)) {
            return false;
        }

        $mbox_ob = $this->_mailboxOb();
        $uidvalid = $mbox_ob->getStatus(Horde_Imap_Client::STATUS_UIDVALIDITY);
        $md = $this->_cache->getMetaData($this->_selected, $uidvalid, array(self::CACHE_MODSEQ));

        if (isset($md[self::CACHE_MODSEQ])) {
            if ($md[self::CACHE_MODSEQ] < $modseq) {
                $set = true;
                $sync = $md[self::CACHE_MODSEQ];
            } else {
                $set = false;
                $sync = 0;
            }
            $mbox_ob->setStatus(Horde_Imap_Client::STATUS_SYNCMODSEQ, $md[self::CACHE_MODSEQ]);
        } else {
            $set = true;
            $sync = 0;
        }

        if ($set) {
            $this->_cache->setMetaData($this->_selected, $uidvalid, array(
                self::CACHE_MODSEQ => $modseq
            ));
        }

        return $sync;
    }

    
    protected function _condstoreSync()
    {
        $mbox_ob = $this->_mailboxOb();

        
        if (!($highestmodseq = $mbox_ob->getStatus(Horde_Imap_Client::STATUS_HIGHESTMODSEQ)) ||
            !($modseq = $this->_updateModSeq($highestmodseq))) {
            $mbox_ob->sync = true;
        }

        if ($mbox_ob->sync) {
            return;
        }

        $uids_ob = $this->getIdsOb($this->_cache->get($this->_selected, array(), array(), $mbox_ob->getStatus(Horde_Imap_Client::STATUS_UIDVALIDITY)));

        
        if (array_key_exists(Horde_Imap_Client::FETCH_FLAGS, $this->_cacheFields())) {
            $fquery = new Horde_Imap_Client_Fetch_Query();
            $fquery->flags();

            
            $this->_fetch(new Horde_Imap_Client_Fetch_Results(), array(
                array(
                    '_query' => $fquery,
                    'changedsince' => $modseq,
                    'ids' => $uids_ob
                )
            ));
        }

        
        $vanished = $this->vanished($this->_selected, $modseq, array(
            'ids' => $uids_ob
        ));
        $disappear = array_diff($uids_ob->ids, $vanished->ids);
        if (!empty($disappear)) {
            $this->_deleteMsgs($this->_selected, $this->getIdsOb($disappear));
        }

        $mbox_ob->sync = true;
    }

    
    protected function _cacheFields()
    {
        $c = $this->getParam('cache');
        $out = $c['fields'];

        if (!isset($this->_temp['enabled']['CONDSTORE'])) {
            unset($out[Horde_Imap_Client::FETCH_FLAGS]);
        }

        return $out;
    }

    
    protected function _syncStatus($mailbox)
    {
        $status = $this->status(
            $mailbox,
            Horde_Imap_Client::STATUS_HIGHESTMODSEQ |
            Horde_Imap_Client::STATUS_MESSAGES |
            Horde_Imap_Client::STATUS_UIDNEXT_FORCE |
            Horde_Imap_Client::STATUS_UIDVALIDITY
        );

        $fields = array('uidnext', 'uidvalidity');
        if (empty($status['highestmodseq'])) {
            $fields[] = 'messages';
        } else {
            $fields[] = 'highestmodseq';
        }

        $out = array();
        $sync_map = array_flip(Horde_Imap_Client_Data_Sync::$map);

        foreach ($fields as $val) {
            $out[$sync_map[$val]] = $status[$val];
        }

        return array_filter($out);
    }

    
    protected function _getUidByMessageId($mailbox, $msgid)
    {
        if (!$msgid) {
            return null;
        }

        $query = new Horde_Imap_Client_Search_Query();
        $query->headerText('Message-ID', $msgid);
        $res = $this->search($mailbox, $query, array(
            'results' => array(Horde_Imap_Client::SEARCH_RESULTS_MAX)
        ));

        return $res['max'];
    }

}
