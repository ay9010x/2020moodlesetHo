<?php

define('FLIP_VERTICAL', 1);
define('FLIP_HORIZONTAL', 2);

class edit_flip extends edit_base {

    public function __construct($gallery, $cm, $image, $tab) {
        parent::__construct($gallery, $cm, $image, $tab, true);
    }

    public function output() {
        $result = get_string('selectflipmode', 'lightboxgallery').'<br /><br />'.
                  '<label><input type="radio" name="mode" value="'.FLIP_VERTICAL.'" /> Vertical</label><br />'.
                  '<label><input type="radio" name="mode" value="'.FLIP_HORIZONTAL.'" /> Horizontal</label>'.
                  '<br /><br /><input type="submit" value="'.get_string('edit_flip', 'lightboxgallery').'" />';

        return $this->enclose_in_form($result);
    }

    public function process_form() {
        $mode = required_param('mode', PARAM_INT);

        $fs = get_file_storage();
        $storedfile = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($storedfile, $this->gallery, $this->cm);

        if ($mode & FLIP_HORIZONTAL) {
            $image->flip_image('horizontal');
        } else {
            $image->flip_image('vertical');
        }
    }

}
