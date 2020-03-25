<?php



namespace core\message\inbound;

defined('MOODLE_INTERNAL') || die();


class address_manager {

    
    const HASHSIZE = 24;

    
    const VALIDATION_SUCCESS = 0;

    
    const VALIDATION_INVALID_ADDRESS_FORMAT = 1;

    
    const VALIDATION_UNKNOWN_HANDLER = 2;

    
    const VALIDATION_UNKNOWN_USER = 4;

    
    const VALIDATION_UNKNOWN_DATAKEY = 8;

    
    const VALIDATION_DISABLED_HANDLER = 16;

    
    const VALIDATION_DISABLED_USER = 32;

    
    const VALIDATION_EXPIRED_DATAKEY = 64;

    
    const VALIDATION_INVALID_HASH = 128;

    
    const VALIDATION_ADDRESS_MISMATCH = 256;

    
    private $handler;

    
    private $datavalue;

    
    private $datakey;

    
    private $record;

    
    private $user;

    
    public function set_handler($classname) {
        $this->handler = manager::get_handler($classname);
    }

    
    public function get_handler() {
        return $this->handler;
    }

    
    public function set_data($datavalue, $datakey = null) {
        $this->datavalue = $datavalue;

                $this->set_data_key($datakey);
    }

    
    public function set_data_key($datakey = null) {
        $this->datakey = $datakey;
    }

    
    public function fetch_data_key() {
        global $CFG, $DB;

                if (!isset($CFG->messageinbound_enabled) || !$this->handler || !$this->handler->enabled) {
            return null;
        }

        if (!isset($this->datakey)) {
                        $datakey = $DB->get_field('messageinbound_datakeys', 'datakey', array(
                    'handler' => $this->handler->id,
                    'datavalue' => $this->datavalue,
                ));
            if (!$datakey) {
                $datakey = $this->generate_data_key();
            }
            $this->datakey = $datakey;
        }

        return $this->datakey;
    }

    
    protected function generate_data_key() {
        global $DB;

        $key = new \stdClass();
        $key->handler = $this->handler->id;
        $key->datavalue = $this->datavalue;
        $key->datakey = md5($this->datavalue . '_' . time() . random_string(40));
        $key->timecreated = time();

        if ($this->handler->defaultexpiration) {
                        $key->expires = $key->timecreated + $this->handler->defaultexpiration;
        }
        $DB->insert_record('messageinbound_datakeys', $key);

        return $key->datakey;
    }

    
    public function generate($userid, $userkey = null) {
        global $CFG;

                if (!manager::is_enabled()) {
            return null;
        }

        if ($userkey == null) {
            $userkey = get_user_key('messageinbound_handler', $userid);
        }

                if (!isset($this->handler) || !$this->handler) {
            throw new \coding_exception('Inbound Message handler not specified.');
        }

                if (!$this->handler->enabled) {
            return null;
        }

        if (!isset($this->datavalue)) {
            throw new \coding_exception('Inbound Message data item has not been specified.');
        }

        $data = array(
            self::pack_int($this->handler->id),
            self::pack_int($userid),
            self::pack_int($this->datavalue),
            pack('H*', substr(md5($this->fetch_data_key() . $userkey), 0, self::HASHSIZE)),
        );
        $subaddress = base64_encode(implode($data));

        return $CFG->messageinbound_mailbox . '+' . $subaddress . '@' . $CFG->messageinbound_domain;
    }

    
    public static function is_correct_format($address) {
        global $CFG;
                return preg_match('/' . $CFG->messageinbound_mailbox . '\+[^@]*@' . $CFG->messageinbound_domain . '/', $address);
    }

    
    protected function process($address) {
        global $DB;

        if (!self::is_correct_format($address)) {
                        return;
        }

                $this->record = null;

        $record = new \stdClass();
        $record->address = $address;

        list($localpart) = explode('@', $address, 2);
        list($record->mailbox, $encodeddata) = explode('+', $localpart, 2);
        $data = base64_decode($encodeddata, true);
        if (!$data) {
                        return;
        }

        $content = @unpack('N2handlerid/N2userid/N2datavalue/H*datakey', $data);

        if (!$content) {
                        return;
        }

        if (PHP_INT_SIZE === 8) {
                        $content['handlerid'] = $content['handlerid1'] << 32 | $content['handlerid2'];
            $content['userid']    = $content['userid1'] << 32    | $content['userid2'];
            $content['datavalue'] = $content['datavalue1'] << 32 | $content['datavalue2'];
        } else {
            if ($content['handlerid1'] > 0 || $content['userid1'] > 0 || $content['datavalue1'] > 0) {
                                                throw new \moodle_exception('Mixed environment.' .
                    ' Key generated with a 64-bit machine but received into a 32-bit machine.');
            }
            $content['handlerid'] = $content['handlerid2'];
            $content['userid']    = $content['userid2'];
            $content['datavalue'] = $content['datavalue2'];
        }

                unset($content['handlerid1']);
        unset($content['handlerid2']);
        unset($content['userid1']);
        unset($content['userid2']);
        unset($content['datavalue1']);
        unset($content['datavalue2']);

        $record = (object) array_merge((array) $record, $content);

                $record->user = $DB->get_record('user', array('id' => $record->userid));

                if ($handler = manager::get_handler_from_id($record->handlerid)) {
            $this->handler = $handler;

                        $record->data = $DB->get_record('messageinbound_datakeys',
                    array('handler' => $handler->id, 'datavalue' => $record->datavalue));
        }

        $this->record = $record;
    }

    
    public function get_data() {
        return $this->record;
    }

    
    protected function validate($address) {
        if (!$this->record) {
                        return self::VALIDATION_INVALID_ADDRESS_FORMAT;
        }

                $returnvalue = 0;

        if (!$this->handler) {
            $returnvalue += self::VALIDATION_UNKNOWN_HANDLER;
        } else if (!$this->handler->enabled) {
            $returnvalue += self::VALIDATION_DISABLED_HANDLER;
        }

        if (!isset($this->record->data) || !$this->record->data) {
            $returnvalue += self::VALIDATION_UNKNOWN_DATAKEY;
        } else if ($this->record->data->expires != 0 && $this->record->data->expires < time()) {
            $returnvalue += self::VALIDATION_EXPIRED_DATAKEY;
        } else {

            if (!$this->record->user) {
                $returnvalue += self::VALIDATION_UNKNOWN_USER;
            } else {
                if ($this->record->user->deleted || !$this->record->user->confirmed) {
                    $returnvalue += self::VALIDATION_DISABLED_USER;
                }

                $userkey = get_user_key('messageinbound_handler', $this->record->user->id);
                $hashvalidation = substr(md5($this->record->data->datakey . $userkey), 0, self::HASHSIZE) == $this->record->datakey;
                if (!$hashvalidation) {
                                        $returnvalue += self::VALIDATION_INVALID_HASH;
                }

                if ($this->handler->validateaddress) {
                                        if ($address !== $this->record->user->email) {
                                                                        $returnvalue += self::VALIDATION_ADDRESS_MISMATCH;
                    }
                }
            }
        }

        return $returnvalue;
    }

    
    public function process_envelope($recipient, $sender) {
                $this->process($recipient);

                $this->status = $this->validate($sender);

        return $this->status;
    }

    
    public function handle_message(\stdClass $messagedata) {
        $this->record = $this->get_data();
        return $this->handler->process_message($this->record, $messagedata);
    }

    
    protected function pack_int($int) {
        if (PHP_INT_SIZE === 8) {
            $left = 0xffffffff00000000;
            $right = 0x00000000ffffffff;
            $l = ($int & $left) >>32;
            $r = $int & $right;

            return pack('NN', $l, $r);
        } else {
            return pack('NN', 0, $int);
        }
    }
}
