<?php

class edit_caption extends edit_base {

    public function __construct($gallery, $cm, $image, $tab) {
        parent::__construct($gallery, $cm, $image, $tab, true);
    }

    public function output($captiontext = '') {
        $result = '<textarea name="caption" cols="24" rows="4">'.$captiontext.'</textarea><br /><br />'.
                  '<input type="submit" value="'.get_string('update').'" />';
        return $this->enclose_in_form($result);
    }

    public function process_form() {
        $caption = required_param('caption', PARAM_NOTAGS);

        $fs = get_file_storage();
        $storedfile = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($storedfile, $this->gallery, $this->cm);

        $image->set_caption($caption);
    }

}
