<?php



defined('MOODLE_INTERNAL') || die();


class tool_messageinbound_renderer extends plugin_renderer_base {

    
    public function messageinbound_handlers_table(array $handlers) {
        global $CFG;

        $table = new html_table();
        $handlername = new html_table_cell(get_string('name', 'tool_messageinbound') . "\n" .
                html_writer::tag('span', get_string('classname', 'tool_messageinbound'), array('class' => 'handler-function')));

                $enabled = new html_table_cell(get_string('enabled', 'tool_messageinbound'));
        $enabled->attributes['class'] = 'state';
        $edit = new html_table_cell(get_string('edit', 'tool_messageinbound'));
        $edit->attributes['class'] = 'edit';
        $table->head  = array(
                $handlername,
                get_string('description', 'tool_messageinbound'),
                $enabled,
                $edit,
            );
        $table->attributes['class'] = 'admintable generaltable messageinboundhandlers';

        $yes = get_string('yes');
        $no = get_string('no');

        $data = array();

                $descriptionoptions = new stdClass();
        $descriptionoptions->trusted = false;
        $descriptionoptions->noclean = false;
        $descriptionoptions->smiley = false;
        $descriptionoptions->filter = false;
        $descriptionoptions->para = true;
        $descriptionoptions->newlines = false;
        $descriptionoptions->overflowdiv = true;

        $editurlbase = new moodle_url('/admin/tool/messageinbound/index.php');
        foreach ($handlers as $handler) {
            $handlername = new html_table_cell($handler->name . "\n" .
                    html_writer::tag('span', $handler->classname, array('class' => 'handler-function')));
            $handlername->header = true;

            $editurl = new moodle_url($editurlbase, array('classname' => $handler->classname));
            $editlink = $this->action_icon($editurl, new pix_icon('t/edit',
                    get_string('edithandler', 'tool_messageinbound', $handler->classname)));

                        $enabled = new html_table_cell($handler->enabled ? $yes : $no);
            $enabled->attributes['class'] = 'state';
            $edit = new html_table_cell($editlink);
            $edit->attributes['class'] = 'edit';

                        $row = new html_table_row(array(
                        $handlername,
                        format_text($handler->description, FORMAT_MARKDOWN, $descriptionoptions),
                        $enabled,
                        $edit,
                    ));

            if (!$handler->enabled) {
                $row->attributes['class'] = 'disabled';
            }
            $data[] = $row;
        }
        $table->data = $data;
        return html_writer::table($table);
    }

}
