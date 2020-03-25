<?php



defined('MOODLE_INTERNAL') || die();


function tool_health_category_find_missing_parents($categories) {
    $missingparent = array();

    foreach ($categories as $category) {
        if ($category->parent != 0 && !array_key_exists($category->parent, $categories)) {
            $missingparent[$category->id] = $category;
        }
    }

    return $missingparent;
}


function tool_health_category_list_missing_parents($missingparent) {
    $description = '';

    if (!empty($missingparent)) {
        $description .= '<p>The following categories are missing their parents:</p><ul>';
        foreach ($missingparent as $cat) {
            $description .= "<li>Category $cat->id: " . s($cat->name) . "</li>\n";
        }
        $description .= "</ul>\n";
    }

    return $description;
}


function tool_health_category_find_loops($categories) {
    $loops = array();

    while (!empty($categories)) {

        $current = array_pop($categories);
        $thisloop = array($current->id => $current);

        while (true) {
            if (isset($thisloop[$current->parent])) {
                                $loops = $loops + $thisloop;
                break;
            } else if ($current->parent === 0) {
                                break;
            } else if (isset($loops[$current->parent])) {
                                $loops = $loops + $thisloop;
                break;
            } else if (!isset($categories[$current->parent])) {
                                break;
            } else {
                                $current = $categories[$current->parent];
                $thisloop[$current->id] = $current;
                unset($categories[$current->id]);
            }
        }
    }

    return $loops;
}


function tool_health_category_list_loops($loops) {
    $description = '';

    if (!empty($loops)) {
        $description .= '<p>The following categories form a loop of parents:</p><ul>';
        foreach ($loops as $loop) {
            $description .= "<li>\n";
            $description .= "Category $loop->id: " . s($loop->name) . " has parent $loop->parent\n";
            $description .= "</li>\n";
        }
        $description .= "</ul>\n";
    }

    return $description;
}
