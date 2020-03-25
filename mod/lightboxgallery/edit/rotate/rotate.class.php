<?php

class edit_rotate extends edit_base {

    public function __construct($gallery, $cm, $image, $tab) {
        parent::__construct($gallery, $cm, $image, $tab, true);
    }

    public function output() {
        $result = get_string('selectrotation', 'lightboxgallery').'<br /><br />'.
                  '<label><input type="radio" name="angle" value="-90" />-90&#176;</label>'.
                  '<label><input type="radio" name="angle" value="180" />180&#176;</label>'.
                  '<label><input type="radio" name="angle" value="90" />90&#176;</label>'.
                  '<br /><br /><input type="submit" value="'.get_string('edit_rotate', 'lightboxgallery').'" />';

        return $this->enclose_in_form($result);
    }

    public function process_form() {
        $angle = required_param('angle', PARAM_INT);

        $fs = get_file_storage();
        $storedfile = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($storedfile, $this->gallery, $this->cm);

        $this->image = $image->rotate_image($angle);
    }

}
