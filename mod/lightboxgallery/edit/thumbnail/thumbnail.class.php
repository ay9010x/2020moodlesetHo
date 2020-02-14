<?php

class edit_thumbnail extends edit_base {

    public function __construct($gallery, $cm, $image, $tab) {
        parent::__construct($gallery, $cm, $image, $tab, true);
    }

    public function output() {

        $fs = get_file_storage();
        $storedfile = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($storedfile, $this->gallery, $this->cm);

        $result = '<input type="submit" name="index" value="' . get_string('setasindex', 'lightboxgallery')  . '" /><br /><br />' .
                   get_string('selectthumbpos', 'lightboxgallery') . '<br /><br />';

        if ($image->width < $image->height) {
            $result .= '<label><input type="radio" name="move" value="1" />'.
                       get_string('dirup', 'lightboxgallery') . '</label>&nbsp;'.
                       '<label><input type="radio" name="move" value="2" />'.
                       get_string('dirdown', 'lightboxgallery') . '</label>';
        } else {
            $result .= '<label><input type="radio" name="move" value="3" />'.
                       get_string('dirleft', 'lightboxgallery') . '</label>&nbsp;'.
                       '<label><input type="radio" name="move" value="4" />'.
                       get_string('dirright', 'lightboxgallery') . '</label>';
        }
        $result .= '<br /><br />' . get_string('thumbnailoffset', 'lightboxgallery').
                   ': <input type="text" name="offset" value="20" size="4" /><br /><br />'.
                   '<input type="submit" value="' . get_string('move').
                   '" />&nbsp;<input type="submit" name="reset" value="' . get_string('reset') . '" />';

        return $this->enclose_in_form($result);
    }

    public function process_form() {

        $fs = get_file_storage();
        $storedfile = $fs->get_file($this->context->id, 'mod_lightboxgallery', 'gallery_images', '0', '/', $this->image);
        $image = new lightboxgallery_image($storedfile, $this->gallery, $this->cm);
        $domove = true;

        if (optional_param('index', '', PARAM_TEXT)) {
            return lightboxgallery_index_thumbnail($this->gallery->course, $this->gallery, $image);
        } else if (optional_param('reset', '', PARAM_TEXT)) {
            $offsetx = 0;
            $offsety = 0;
        } else {
            $move = optional_param('move', -1, PARAM_INT);
            $offset = optional_param('offset', 20, PARAM_INT);
            switch ($move) {
                case 1:
                    $offsetx = 0;
                    $offsety = -$offset;
                    break;
                case 2:
                    $offsetx = 0;
                    $offsety = $offset;
                    break;
                case 3:
                    $offsetx = -$offset;
                    $offsety = 0;
                    break;
                case 4:
                    $offsetx = $offset;
                    $offsety = 0;
                    break;
                default:
                    $domove = false;
            }
        }

        if ($domove) {
            $image->create_thumbnail($offsetx, $offsety);
        }
    }

}
