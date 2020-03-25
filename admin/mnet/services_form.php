<?php





if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    }

require_once($CFG->libdir . '/formslib.php');

class mnet_services_form extends moodleform {

    function definition() {
        $mform =& $this->_form;
        $mnet_peer =& $this->_customdata['peer'];
        $myservices = mnet_get_service_info($mnet_peer);

        $mform->addElement('hidden', 'hostid', $mnet_peer->id);
        $mform->setType('hostid', PARAM_INT);

        $count = 0;
        foreach ($myservices as $name => $versions) {
            $version = current($versions);
            $langmodule =
                ($version['plugintype'] == 'mod'
                    ? ''
                    : ($version['plugintype'] . '_'))
                . $version['pluginname']; 
            if ($count > 0) {
                $mform->addElement('html', '<hr />');
            }
            $mform->addElement('html', '<h3>' .  get_string($name.'_name', $langmodule , $mnet_peer->name) . '</h3>' . get_string($name.'_description', $langmodule, $mnet_peer->name));

            $mform->addElement('hidden', 'exists[' . $version['serviceid'] . ']', 1);
                        $mform->setType('exists', PARAM_BOOL);

            $pubstr = get_string('publish','mnet');
            if (!empty($version['hostsubscribes'])) {
                $pubstr .= ' <a class="notifysuccess" title="'.s(get_string('issubscribed','mnet', $mnet_peer->name)).'">&radic;</a> ';
            }
            $mform->addElement('advcheckbox', 'publish[' . $version['serviceid'] . ']', $pubstr);

            $substr = get_string('subscribe','mnet');
            if (!empty($version['hostpublishes'])) {
                $substr .= ' <a class="notifysuccess" title="'.s(get_string('ispublished','mnet', $mnet_peer->name)).'">&radic;</a> ';
            }
            $mform->addElement('advcheckbox', 'subscribe[' . $version['serviceid']. ']', $substr);
            $count++;
        }
        $this->add_action_buttons();
    }
}
