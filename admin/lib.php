<?php





function admin_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        'admin-*' => get_string('page-admin-x', 'pagetype'),
        $pagetype => get_string('page-admin-current', 'pagetype')
    );
    return $array;
}
