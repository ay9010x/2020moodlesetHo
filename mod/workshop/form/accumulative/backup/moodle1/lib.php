<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_workshopform_accumulative_handler extends moodle1_workshopform_handler {

    
    private $newscaleids = array();

    
    public function process_legacy_element(array $data, array $raw) {
                $fakerecord = (object)$data;
        $newscaleid = $this->get_new_scaleid($data['scale']);
        $converted = (array)workshopform_accumulative_upgrade_element($fakerecord, $newscaleid, 12345678);
        unset($converted['workshopid']);

        $converted['id'] = $data['id'];
        $this->write_xml('workshopform_accumulative_dimension', $converted, array('/workshopform_accumulative_dimension/id'));

        return $converted;
    }

    
    protected function get_new_scaleid($oldscaleid) {

        if ($oldscaleid >= 0 and $oldscaleid <= 6) {
                        if (!isset($this->newscaleids[$oldscaleid])) {
                                                $scale = $this->get_new_scale_definition($oldscaleid);
                                $currentscaleids = $this->converter->get_stash_itemids('scales');
                if (empty($currentscaleids)) {
                    $scale['id'] = 1;
                } else {
                    $scale['id'] = max($currentscaleids) + 1;
                }
                $this->converter->set_stash('scales', $scale, $scale['id']);
                $this->newscaleids[$oldscaleid] = $scale['id'];
                                $inforefman = $this->parenthandler->get_inforef_manager();
                $inforefman->add_ref('scale', $scale['id']);
            }
            return array($oldscaleid => $this->newscaleids[$oldscaleid]);

        } else {
                        return array();
        }
    }

    
    private function get_new_scale_definition($oldscaleid) {

        $data = array(
            'userid'            => 0,               'courseid'          => 0,               'description'       => '',
            'descriptionformat' => FORMAT_HTML,
        );

        switch ($oldscaleid) {
        case 0:
            $data['name']  = get_string('scalename0', 'workshopform_accumulative');
            $data['scale'] = implode(',', array(get_string('no'), get_string('yes')));
            break;
        case 1:
            $data['name']  = get_string('scalename1', 'workshopform_accumulative');
            $data['scale'] = implode(',', array(get_string('absent', 'workshopform_accumulative'),
                                                get_string('present', 'workshopform_accumulative')));
            break;
        case 2:
            $data['name']  = get_string('scalename2', 'workshopform_accumulative');
            $data['scale'] = implode(',', array(get_string('incorrect', 'workshopform_accumulative'),
                                                get_string('correct', 'workshopform_accumulative')));
            break;
        case 3:
            $data['name']  = get_string('scalename3', 'workshopform_accumulative');
            $data['scale'] = implode(',', array('* ' . get_string('poor', 'workshopform_accumulative'),
                                                '**',
                                                '*** ' . get_string('good', 'workshopform_accumulative')));
            break;
        case 4:
            $data['name']  = get_string('scalename4', 'workshopform_accumulative');
            $data['scale'] = implode(',', array('* ' . get_string('verypoor', 'workshopform_accumulative'),
                                                '**',
                                                '***',
                                                '**** ' . get_string('excellent', 'workshopform_accumulative')));
            break;
        case 5:
            $data['name']  = get_string('scalename5', 'workshopform_accumulative');
            $data['scale'] = implode(',', array('* ' . get_string('verypoor', 'workshopform_accumulative'),
                                                '**',
                                                '***',
                                                '****',
                                                '***** ' . get_string('excellent', 'workshopform_accumulative')));
            break;
        case 6:
            $data['name']  = get_string('scalename6', 'workshopform_accumulative');
            $data['scale'] = implode(',', array('* ' . get_string('verypoor', 'workshopform_accumulative'),
                                                '**',
                                                '***',
                                                '****',
                                                '*****',
                                                '******',
                                                '******* ' . get_string('excellent', 'workshopform_accumulative')));
            break;
        }

        return $data;
    }
}


function workshopform_accumulative_upgrade_element(stdclass $old, array $newscaleids, $newworkshopid) {
    $new = new stdclass();
    $new->workshopid = $newworkshopid;
    $new->sort = $old->elementno;
    $new->description = $old->description;
    $new->descriptionformat = FORMAT_HTML;
        if ($old->scale >= 0 and $old->scale <= 6 and isset($newscaleids[$old->scale])) {
        $new->grade = -$newscaleids[$old->scale];
    } elseif ($old->scale == 7) {
        $new->grade = 10;
    } elseif ($old->scale == 8) {
        $new->grade = 20;
    } elseif ($old->scale == 9) {
        $new->grade = 100;
    } else {
        $new->grade = 0;        }
                    switch ($old->weight) {
        case 8: $new->weight = 1; break;
        case 9: $new->weight = 2; break;
        case 10: $new->weight = 3; break;
        case 11: $new->weight = 4; break;
        case 12: $new->weight = 6; break;
        case 13: $new->weight = 8; break;
        case 14: $new->weight = 16; break;
        default: $new->weight = 0;
    }
    return $new;
}
