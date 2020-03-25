<?php



class core_register_renderer extends plugin_renderer_base {

    
    public function moodleorg_registration_message() {
        $moodleorgstatslink = html_writer::link('http://moodle.net/stats',
                                               get_string('statsmoodleorg', 'admin'),
                                               array('target' => '_blank'));

        $moodleorgregmsg = get_string('registermoodleorg', 'admin');
        $items = array(get_string('registermoodleorgli1', 'admin'),
                       get_string('registermoodleorgli2', 'admin', $moodleorgstatslink));
        $moodleorgregmsg .= html_writer::alist($items);
        return $moodleorgregmsg;
    }

    
    public function registration_confirmation($confirmationmessage) {
        $linktositelist = html_writer::tag('a', get_string('sitelist', 'hub'),
                        array('href' => new moodle_url('/local/hub/index.php')));
        $message = $confirmationmessage . html_writer::empty_tag('br') . $linktositelist;
        return $this->output->box($message);
    }

    
    public function registeredonhublisting($hubs) {
        global $CFG;
        $table = new html_table();
        $table->head = array(get_string('hub', 'hub'), get_string('operation', 'hub'));
        $table->size = array('80%', '20%');

        foreach ($hubs as $hub) {
            if ($hub->huburl == HUB_MOODLEORGHUBURL) {
                $hub->hubname = get_string('registeredmoodleorg', 'hub', $hub->hubname);
            }
            $hublink = html_writer::tag('a', $hub->hubname, array('href' => $hub->huburl));
            $hublinkcell = html_writer::tag('div', $hublink, array('class' => 'registeredhubrow'));

            $unregisterhuburl = new moodle_url("/" . $CFG->admin . "/registration/index.php",
                            array('sesskey' => sesskey(), 'huburl' => $hub->huburl,
                                'unregistration' => 1));
            $unregisterbutton = new single_button($unregisterhuburl,
                            get_string('unregister', 'hub'));
            $unregisterbutton->class = 'centeredbutton';
            $unregisterbuttonhtml = $this->output->render($unregisterbutton);

                        $cells = array($hublinkcell, $unregisterbuttonhtml);
            $row = new html_table_row($cells);
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }

}
