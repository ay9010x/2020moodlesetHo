<?php




require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');


class behat_assignfeedback_editpdf extends behat_base {

    
    public function ghostscript_is_installed() {
        $testpath = assignfeedback_editpdf\pdf::test_gs_path();
        if (!extension_loaded('zlib') or
            $testpath->status !== assignfeedback_editpdf\pdf::GSPATH_OK) {
            throw new \Moodle\BehatExtension\Exception\SkippedException;
        }
    }

    
    public function i_draw_on_the_pdf() {
        $js = ' (function() {
    var instance = M.assignfeedback_editpdf.instance;
    var event = { clientX: 100, clientY: 250, preventDefault: function() {} };
    instance.edit_start(event);
}()); ';
        $this->getSession()->executeScript($js);
        sleep(1);
        $js = ' (function() {
    var instance = M.assignfeedback_editpdf.instance;
    var event = { clientX: 150, clientY: 275, preventDefault: function() {} };
    instance.edit_move(event);
}()); ';
        $this->getSession()->executeScript($js);
        sleep(1);
        $js = ' (function() {
    var instance = M.assignfeedback_editpdf.instance;
    var event = { clientX: 200, clientY: 300, preventDefault: function() {} };
    instance.edit_end(event);
}()); ';
        $this->getSession()->executeScript($js);
        sleep(1);
    }
}
