<?php




require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Behat\Context\Step\Given as Given,
    Behat\Gherkin\Node\TableNode as TableNode;

class behat_mod_lightboxgallery extends behat_base {

    
    public function i_view_the_lightboxgallery_with_idnumber($idnumber) {
        global $DB;

        $sql = "SELECT cm.id
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module
                WHERE m.name = 'lightboxgallery' AND cm.idnumber = ?";
        $cm = $DB->get_record_sql($sql, [$idnumber]);

        $href = new moodle_url('/mod/lightboxgallery/view.php', ['id' => $cm->id]);
        $this->getSession()->visit($href->out());
    }

}
