<?php



defined('MOODLE_INTERNAL') || die();


class assignfeedback_editpdf_renderer extends plugin_renderer_base {

    
    private function get_shortcut($name) {

        $shortcuts = array('navigate-previous-button' => 'j',
            'navigate-page-select' => 'k',
            'navigate-next-button' => 'l',
            'searchcomments' => 'h',
            'comment' => 'z',
            'commentcolour' => 'x',
            'select' => 'c',
            'drag' => 'd',
            'pen' => 'y',
            'line' => 'u',
            'rectangle' => 'i',
            'oval' => 'o',
            'highlight' => 'p',
            'annotationcolour' => 'r',
            'stamp' => 'n',
            'currentstamp' => 'm');


                return $shortcuts[$name];
    }

    
    private function render_toolbar_button($icon, $tool, $accesskey = null, $disabled=false) {

                $alttext = new stdClass();
        $alttext->tool = get_string($tool, 'assignfeedback_editpdf');
        if (!empty($accesskey)) {
            $alttext->shortcut = '(Alt/Shift-Alt/Ctrl-Option + ' . $accesskey . ')';
        } else {
            $alttext->shortcut = '';
        }
        $iconalt = get_string('toolbarbutton', 'assignfeedback_editpdf', $alttext);

        $iconhtml = $this->pix_icon($icon, $iconalt, 'assignfeedback_editpdf');
        $iconparams = array('data-tool'=>$tool, 'class'=>$tool . 'button');
        if ($disabled) {
            $iconparams['disabled'] = 'true';
        }
        if (!empty($accesskey)) {
            $iconparams['accesskey'] = $accesskey;
        }

        return html_writer::tag('button', $iconhtml, $iconparams);
    }

    
    public function render_assignfeedback_editpdf_widget(assignfeedback_editpdf_widget $widget) {
        global $CFG;

        $html = '';

        $html .= html_writer::div(get_string('jsrequired', 'assignfeedback_editpdf'), 'hiddenifjs');
        $linkid = html_writer::random_id();
        if ($widget->readonly) {
            $launcheditorlink = html_writer::tag('a',
                                              get_string('viewfeedbackonline', 'assignfeedback_editpdf'),
                                              array('id'=>$linkid, 'class'=>'btn', 'href'=>'#'));
        } else {
            $launcheditorlink = html_writer::tag('a',
                                              get_string('launcheditor', 'assignfeedback_editpdf'),
                                              array('id'=>$linkid, 'class'=>'btn', 'href'=>'#'));
        }
        $links = $launcheditorlink;
        $html .= '<input type="hidden" name="assignfeedback_editpdf_haschanges" value="false"/>';

        $html .= html_writer::div($links, 'visibleifjs');
        $header = get_string('pluginname', 'assignfeedback_editpdf');
        $body = '';
                $navigation1 = '';
        $navigation2 = '';
        $navigation3 = '';

                if (right_to_left()) {
            $nav_prev = 'nav_next';
            $nav_next = 'nav_prev';
        } else {
            $nav_prev = 'nav_prev';
            $nav_next = 'nav_next';
        }

        $iconalt = get_string('navigateprevious', 'assignfeedback_editpdf');
        $iconhtml = $this->pix_icon($nav_prev, $iconalt, 'assignfeedback_editpdf');
        $navigation1 .= html_writer::tag('button', $iconhtml, array('disabled'=>'true',
            'class'=>'navigate-previous-button', 'accesskey' => $this->get_shortcut('navigate-previous-button')));
        $navigation1 .= html_writer::tag('select', null, array('disabled'=>'true',
            'aria-label' => get_string('gotopage', 'assignfeedback_editpdf'), 'class'=>'navigate-page-select',
            'accesskey' => $this->get_shortcut('navigate-page-select')));
        $iconalt = get_string('navigatenext', 'assignfeedback_editpdf');
        $iconhtml = $this->pix_icon($nav_next, $iconalt, 'assignfeedback_editpdf');
        $navigation1 .= html_writer::tag('button', $iconhtml, array('disabled'=>'true',
            'class'=>'navigate-next-button', 'accesskey' => $this->get_shortcut('navigate-next-button')));

        $navigation1 = html_writer::div($navigation1, 'navigation', array('role'=>'navigation'));

        $navigation2 .= $this->render_toolbar_button('comment_search', 'searchcomments', $this->get_shortcut('searchcomments'));
        $navigation2 = html_writer::div($navigation2, 'navigation-search', array('role'=>'navigation'));


        $toolbar1 = '';
        $toolbar2 = '';
        $toolbar3 = '';
        $toolbar4 = '';
        $clearfix = html_writer::div('', 'clearfix');
        if (!$widget->readonly) {

                        $toolbar1 .= $this->render_toolbar_button('comment', 'comment', $this->get_shortcut('comment'));
            $toolbar1 .= $this->render_toolbar_button('background_colour_clear', 'commentcolour', $this->get_shortcut('commentcolour'));
            $toolbar1 = html_writer::div($toolbar1, 'toolbar', array('role'=>'toolbar'));

                        $toolbar2 .= $this->render_toolbar_button('drag', 'drag', $this->get_shortcut('drag'));
            $toolbar2 .= $this->render_toolbar_button('select', 'select', $this->get_shortcut('select'));
            $toolbar2 = html_writer::div($toolbar2, 'toolbar', array('role'=>'toolbar'));

                        $toolbar3 = $this->render_toolbar_button('pen', 'pen', $this->get_shortcut('pen'));
            $toolbar3 .= $this->render_toolbar_button('line', 'line', $this->get_shortcut('line'));
            $toolbar3 .= $this->render_toolbar_button('rectangle', 'rectangle', $this->get_shortcut('rectangle'));
            $toolbar3 .= $this->render_toolbar_button('oval', 'oval', $this->get_shortcut('oval'));
            $toolbar3 .= $this->render_toolbar_button('highlight', 'highlight', $this->get_shortcut('highlight'));
            $toolbar3 .= $this->render_toolbar_button('background_colour_clear', 'annotationcolour', $this->get_shortcut('annotationcolour'));
            $toolbar3 = html_writer::div($toolbar3, 'toolbar', array('role'=>'toolbar'));

                        $toolbar4 .= $this->render_toolbar_button('stamp', 'stamp', 'n');
            $toolbar4 .= $this->render_toolbar_button('background_colour_clear', 'currentstamp', $this->get_shortcut('currentstamp'));
            $toolbar4 = html_writer::div($toolbar4, 'toolbar', array('role'=>'toolbar'));
        }

                $pageheader = html_writer::div($navigation1 .
                                       $navigation2 .
                                       $toolbar4 .
                                       $toolbar3 .
                                       $toolbar2 .
                                       $toolbar1 .
                                       $clearfix,
                                       'pageheader');
        $body = $pageheader;

                $progressbar = html_writer::div('', 'bar', array('style' => 'width: 0%'));
        $progressbar = html_writer::div($progressbar, 'progress progress-info progress-striped active',
            array('title' => get_string('loadingeditor', 'assignfeedback_editpdf'),
                  'role'=> 'progressbar', 'aria-valuenow' => 0, 'aria-valuemin' => 0,
                  'aria-valuemax' => 100));
        $progressbarlabel = html_writer::div(get_string('generatingpdf', 'assignfeedback_editpdf'),
            'progressbarlabel');
        $loading = html_writer::div($progressbar . $progressbarlabel, 'loading');

        $canvas = html_writer::div($loading, 'drawingcanvas');
        $canvas = html_writer::div($canvas, 'drawingregion');
        $changesmessage = html_writer::tag('div',
                                           get_string('draftchangessaved', 'assignfeedback_editpdf'),
                                           array(
                                               'class' => 'assignfeedback_editpdf_unsavedchanges warning label label-info'
                                           ));

        $changesmessage = html_writer::div($changesmessage, 'unsaved-changes');
        $canvas .= $changesmessage;

        $body .= $canvas;

        $footer = '';

        $editorparams = array(array('header'=>$header,
                                    'body'=>$body,
                                    'footer'=>$footer,
                                    'linkid'=>$linkid,
                                    'assignmentid'=>$widget->assignment,
                                    'userid'=>$widget->userid,
                                    'attemptnumber'=>$widget->attemptnumber,
                                    'stampfiles'=>$widget->stampfiles,
                                    'readonly'=>$widget->readonly,
                                    'pagetotal'=>$widget->pagetotal));

        $this->page->requires->yui_module('moodle-assignfeedback_editpdf-editor',
                                          'M.assignfeedback_editpdf.editor.init',
                                          $editorparams);

        $this->page->requires->strings_for_js(array(
            'yellow',
            'white',
            'red',
            'blue',
            'green',
            'black',
            'clear',
            'colourpicker',
            'loadingeditor',
            'pagexofy',
            'deletecomment',
            'addtoquicklist',
            'filter',
            'searchcomments',
            'commentcontextmenu',
            'deleteannotation',
            'stamp',
            'stamppicker',
            'cannotopenpdf',
            'pagenumber'
        ), 'assignfeedback_editpdf');

        return $html;
    }
}
