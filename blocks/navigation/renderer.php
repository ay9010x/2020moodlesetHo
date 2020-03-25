<?php




class block_navigation_renderer extends plugin_renderer_base {

    
    public function navigation_tree(global_navigation $navigation, $expansionlimit, array $options = array()) {
        $navigation->add_class('navigation_node');
        $navigationattrs = array(
            'class' => 'block_tree list',
            'role' => 'tree',
            'data-ajax-loader' => 'block_navigation/nav_loader');
        $content = $this->navigation_node(array($navigation), $navigationattrs, $expansionlimit, $options);
        if (isset($navigation->id) && !is_numeric($navigation->id) && !empty($content)) {
            $content = $this->output->box($content, 'block_tree_box', $navigation->id);
        }
        return $content;
    }
    
    protected function navigation_node($items, $attrs=array(), $expansionlimit=null, array $options = array(), $depth=1) {
                if (count($items) === 0) {
            return '';
        }

                $lis = array();
                static $number = 0;
        foreach ($items as $item) {
            $number++;
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }

            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));

                        if (!$isexpandable && empty($item->action)) {
                continue;
            }

            $id = $item->id ? $item->id : html_writer::random_id();
            $content = $item->get_content();
            $title = $item->get_title();
            $ulattr = ['id' => $id . '_group', 'role' => 'group'];
            $liattr = ['class' => [$item->get_css_type(), 'depth_'.$depth]];
            $pattr = ['class' => ['tree_item'], 'role' => 'treeitem'];
            $pattr += !empty($item->id) ? ['id' => $item->id] : [];
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() || $item->type <= navigation_node::TYPE_CATEGORY)));
            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY || $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);
            $icon = '';

            if ($hasicon) {
                $liattr['class'][] = 'item_with_icon';
                $pattr['class'][] = 'hasicon';
                $icon = $this->output->render($item->icon);
                                                $content = $icon . html_writer::span($content, 'item-content-wrap');
            }
            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton'));
            }
            if (empty($content)) {
                continue;
            }

            $nodetextid = 'label_' . $depth . '_' . $number;
            $attributes = array('tabindex' => '-1', 'id' => $nodetextid);
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if (is_string($item->action) || empty($item->action) ||
                    (($item->type === navigation_node::TYPE_CATEGORY || $item->type === navigation_node::TYPE_MY_CATEGORY) &&
                    empty($options['linkcategories']))) {
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                                $link = $item->action;
                $link->text = $icon.html_writer::span($link->text, 'item-content-wrap');
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }

            if ($isbranch) {
                $pattr['class'][] = 'branch';
                $liattr['class'][] = 'contains_branch';
                $pattr += ['aria-expanded' => ($item->has_children() && (!$item->forceopen || $item->collapse)) ? "false" : "true"];
                if ($item->requiresajaxloading) {
                    $pattr += [
                        'data-requires-ajax' => 'true',
                        'data-loaded' => 'false',
                        'data-node-id' => $item->id,
                        'data-node-key' => $item->key,
                        'data-node-type' => $item->type
                    ];
                } else {
                    $pattr += ['aria-owns' => $id . '_group'];
                }
            }

            if ($item->isactive === true) {
                $liattr['class'][] = 'current_branch';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $pattr['class'] = array_merge($pattr['class'], $item->classes);
            }

            $liattr['class'] = join(' ', $liattr['class']);
            $pattr['class'] = join(' ', $pattr['class']);

            $pattr += $depth == 1 ? ['data-collapsible' => 'false'] : [];
            if (isset($pattr['aria-expanded']) && $pattr['aria-expanded'] === 'false') {
                $ulattr += ['aria-hidden' => 'true'];
            }

                        $content = html_writer::tag('p', $content, $pattr);
            if ($isexpandable) {
                $content .= $this->navigation_node($item->children, $ulattr, $expansionlimit, $options, $depth + 1);
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }

            $liattr['aria-labelledby'] = $nodetextid;
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis) === 0) {
                        return '';
        }

                        return html_writer::tag('ul', implode('', $lis), $attrs);
    }
}
