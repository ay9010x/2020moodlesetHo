<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_workshopform_comments_handler extends moodle1_workshopform_handler {

    
    public function process_legacy_element(array $data, array $raw) {
                $fakerecord = (object)$data;
        $converted = (array)workshopform_comments_upgrade_element($fakerecord, 12345678);
        unset($converted['workshopid']);

        $converted['id'] = $data['id'];
        $this->write_xml('workshopform_comments_dimension', $converted, array('/workshopform_comments_dimension/id'));

        return $converted;
    }
}


function workshopform_comments_upgrade_element(stdclass $old, $newworkshopid) {
    $new                    = new stdclass();
    $new->workshopid        = $newworkshopid;
    $new->sort              = $old->elementno;
    $new->description       = $old->description;
    $new->descriptionformat = FORMAT_HTML;
    return $new;
}
