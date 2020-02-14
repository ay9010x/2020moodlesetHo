<?php



namespace assignfeedback_editpdf;


class comments_quick_list {

    
    public static function get_comments() {
        global $DB, $USER;

        $comments = array();
        $records = $DB->get_records('assignfeedback_editpdf_quick', array('userid'=>$USER->id));

        return $records;
    }

    
    public static function add_comment($commenttext, $width, $colour) {
        global $DB, $USER;

        $comment = new \stdClass();
        $comment->userid = $USER->id;
        $comment->rawtext = $commenttext;
        $comment->width = $width;
        $comment->colour = $colour;

        $comment->id = $DB->insert_record('assignfeedback_editpdf_quick', $comment);
        return $comment;
    }

    
    public static function get_comment($commentid) {
        global $DB;

        $record = $DB->get_record('assignfeedback_editpdf_quick', array('id'=>$commentid), '*', IGNORE_MISSING);
        if ($record) {
            return $record;
        }
        return false;
    }

    
    public static function remove_comment($commentid) {
        global $DB, $USER;
        return $DB->delete_records('assignfeedback_editpdf_quick', array('id'=>$commentid, 'userid'=>$USER->id));
    }
}
