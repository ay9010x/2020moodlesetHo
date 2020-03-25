<?php



defined('MOODLE_INTERNAL') || die();


function block_comments_comment_validate($comment_param) {
    if ($comment_param->commentarea != 'page_comments') {
        throw new comment_exception('invalidcommentarea');
    }
    if ($comment_param->itemid != 0) {
        throw new comment_exception('invalidcommentitemid');
    }
    return true;
}


function block_comments_comment_permissions($args) {
    return array('post'=>true, 'view'=>true);
}


function block_comments_comment_display($comments, $args) {
    if ($args->commentarea != 'page_comments') {
        throw new comment_exception('invalidcommentarea');
    }
    if ($args->itemid != 0) {
        throw new comment_exception('invalidcommentitemid');
    }
    return $comments;
}
