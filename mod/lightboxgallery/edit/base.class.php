<?php




class edit_base {

    public $imageobj;

    public $gallery;
    public $image;
    public $tab;
    public $showthumb;
    public $context;

    public function __construct($gallery, $cm, $image, $tab, $showthumb = true) {
        global $CFG;

        $this->gallery = $gallery;
        $this->cm = $cm;
        $this->image = $image;
        $this->tab = $tab;
        $this->showthumb = $showthumb;
        $this->context = context_module::instance($this->cm->id);
    }

    public function processing() {
        return optional_param('process', false, PARAM_BOOL);
    }

    public function enclose_in_form($text) {
        global $CFG, $USER;

        return '<form action="'.$CFG->wwwroot.'/mod/lightboxgallery/imageedit.php" method="post">'.
               '<fieldset class="invisiblefieldset">'.
               '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />'.
               '<input type="hidden" name="id" value="'.$this->cm->id.'" />'.
               '<input type="hidden" name="image" value="'.$this->image.'" />'.
               '<input type="hidden" name="tab" value="'.$this->tab.'" />'.
               '<input type="hidden" name="process" value="1" />'.$text.'</fieldset></form>';
    }

    public function output() {

    }

    public function process_form() {

    }

}
