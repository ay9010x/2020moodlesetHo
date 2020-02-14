<?php

class edit_delete extends edit_base {

    public function __construct($gallery, $cm, $image, $tab) {
        parent::__construct($gallery, $cm, $image, $tab, true);
    }

    public function output() {
        global $page;
        $result = get_string('deletecheck', '', $this->image).'<br /><br />';
        $result .= '<input type="hidden" name="page" value="'.$page.'" />';
        $result .= '<input type="submit" value="'.get_string('yes').'" />';
        return $this->enclose_in_form($result);
    }

    public function process_form() {
        global $CFG, $DB, $page;
        $fs = get_file_storage();
        $storedfile = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($storedfile, $this->gallery, $this->cm);
        $image->delete_file();
        redirect($CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$this->cm->id.'&page='.$page.'&editing=1');
    }

}
