<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');


class mnet_simple_host_form extends moodleform {
    function definition() {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('text', 'wwwroot', get_string('hostname', 'mnet'), array('maxlength' => 255, 'size' => 50));
        $mform->setType('wwwroot', PARAM_URL);
        $mform->addRule('wwwroot', null, 'required', null, 'client');
        $mform->addRule('wwwroot', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('select', 'applicationid', get_string('applicationtype', 'mnet'),
                           $DB->get_records_menu('mnet_application', array(), 'id,display_name'));
        $mform->addRule('applicationid', null, 'required', null, 'client');

        $this->add_action_buttons(false, get_string('addhost', 'mnet'));
    }

    function validation($data, $files) {
        global $DB;

        $wwwroot = $data['wwwroot'];
                if (strtolower(substr($wwwroot, 0, 4)) != 'http') {
            $wwwroot = 'http://'.$wwwroot;
        }
        if ($host = $DB->get_record('mnet_host', array('wwwroot' => $wwwroot))) {
            global $CFG;
            return array('wwwroot' => get_string('hostexists', 'mnet',
                new moodle_url('/admin/mnet/peers.php', array('hostid' => $host->id))));
        }
        return array();
    }
}


class mnet_review_host_form extends moodleform {
    function definition() {
        global $OUTPUT;

        $mform = $this->_form;
        $mnet_peer = $this->_customdata['peer'];

        $mform->addElement('hidden', 'last_connect_time');
        $mform->setType('last_connect_time', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'applicationid');
        $mform->setType('applicationid', PARAM_INT);
        $mform->addElement('hidden', 'oldpublickey');
        $mform->setType('oldpublickey', PARAM_PEM);

        $mform->addElement('text', 'name', get_string('site'), array('maxlength' => 80, 'size' => 50));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->addRule('name', get_string('maximumchars', '', 80), 'maxlength', 80, 'client');

        $mform->addElement('text', 'wwwroot', get_string('hostname', 'mnet'), array('maxlength' => 255, 'size' => 50));
        $mform->setType('wwwroot', PARAM_URL);
        $mform->addRule('wwwroot', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $options = array(
            mnet_peer::SSL_NONE => get_string('none'),
            mnet_peer::SSL_HOST => get_string('verifyhostonly', 'core_mnet'),
            mnet_peer::SSL_HOST_AND_PEER => get_string('verifyhostandpeer', 'core_mnet')
        );
        $mform->addElement('select', 'sslverification', get_string('sslverification', 'core_mnet'), $options);
        $mform->setDefault('sslverification', mnet_peer::SSL_HOST_AND_PEER);
        $mform->addHelpButton('sslverification', 'sslverification', 'core_mnet');

        $themes = array('' => get_string('forceno'));
        foreach (array_keys(core_component::get_plugin_list('theme')) as $themename) {
            $themes[$themename] = get_string('pluginname', 'theme_'.$themename);
        }
        $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);

        $mform->addElement('textarea', 'public_key', get_string('publickey', 'mnet'), array('rows' => 17, 'cols' => 100, 'class' => 'smalltext'));
        $mform->setType('public_key', PARAM_PEM);
        $mform->addRule('public_key', get_string('required'), 'required');

                if ($mnet_peer && !empty($mnet_peer->bootstrapped)) {
            $expires = '';
            if ($mnet_peer->public_key_expires < time()) {
                $expires = get_string('expired', 'mnet')  . ' ';
            }
            $expires .= userdate($mnet_peer->public_key_expires);
            $mform->addElement('static', 'validuntil', get_string('expires', 'mnet'), $expires);

            $lastconnect = '';
            if ($mnet_peer->last_connect_time == 0) {
                $lastconnect = get_string('never', 'mnet');
            } else {
                $lastconnect = date('H:i:s d/m/Y',$mnet_peer->last_connect_time);
            }

            $mform->addElement('static', 'lastconnect', get_string('last_connect_time', 'mnet'), $lastconnect);
            $mform->addElement('static', 'ipaddress', get_string('ipaddress', 'mnet'), $mnet_peer->ip_address);

            if (isset($mnet_peer->currentkey)) {                 $currentkeystr = '<b>' . get_string('keymismatch', 'mnet') . '</b><br /><br /> ' . $OUTPUT->box('<pre>' . $mnet_peer->currentkey . '</pre>');
                $mform->addElement('static', 'keymismatch', get_string('currentkey', 'mnet'), $currentkeystr);
            }

            $credstr = '';
            if ($credentials = $mnet_peer->check_credentials($mnet_peer->public_key)) {
                foreach($credentials['subject'] as $key => $credential) {
                    if (is_scalar($credential)) {
                        $credstr .= str_pad($key, 16, " ", STR_PAD_LEFT).': '.$credential."\n";
                    } else {
                        $credstr .= str_pad($key, 16, " ", STR_PAD_LEFT).': '.var_export($credential,1)."\n";
                    }
                }
            }

            $mform->addElement('static', 'certdetails', get_string('certdetails', 'mnet'),
                $OUTPUT->box('<pre>' . $credstr . '</pre>', 'generalbox certdetails'));
        }

        if ($mnet_peer && !empty($mnet_peer->deleted)) {
            $radioarray = array();
            $radioarray[] = $mform->createElement('static', 'deletedinfo', '',
                $OUTPUT->container(get_string('deletedhostinfo', 'mnet'), 'deletedhostinfo'));
            $radioarray[] = $mform->createElement('radio', 'deleted', '', get_string('yes'), 1);
            $radioarray[] = $mform->createElement('radio', 'deleted', '', get_string('no'), 0);
            $mform->addGroup($radioarray, 'radioar', get_string('deleted'), array(' ', ' '), false);
        } else {
            $mform->addElement('hidden', 'deleted');
            $mform->setType('deleted', PARAM_BOOL);
        }

                $this->add_action_buttons(false);
    }

    function validation($data, $files) {
        $errors = array();
        if ($data['oldpublickey'] == $data['public_key']) {
            return;
        }
        $mnet_peer = new mnet_peer();         $mnet_peer->wwwroot = $data['wwwroot'];         if (empty($data['public_key'])) {
            $errors['public_key'] = get_string('publickeyrequired', 'mnet');
        } else if (!$credentials = $mnet_peer->check_credentials($data['public_key'])) {
            $errmsg = '';
            foreach ($mnet_peer->error as $err) {
                $errmsg .= $err['code'] . ': ' . $err['text'].'<br />';
            }
            $errors['public_key'] = get_string('invalidpubkey', 'mnet', $errmsg);
        }
        unset($mnet_peer);
        return $errors;
    }
}
