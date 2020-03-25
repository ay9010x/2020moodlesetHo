<?php



defined('MOODLE_INTERNAL') || die();


class tool_filetypes_renderer extends plugin_renderer_base {

    
    public function edit_table(array $filetypes, array $deleted, $restricted) {
                $combined = array_merge($filetypes, $deleted);
        foreach ($deleted as $ext => $value) {
            $combined[$ext]['deleted'] = true;
        }
        ksort($combined);

        $out = $this->heading(get_string('pluginname', 'tool_filetypes'));
        if ($restricted) {
            $out .= html_writer::div(
                    html_writer::div(get_string('configoverride', 'admin'), 'form-overridden'),
                    '', array('id' => 'adminsettings'));
        }
        if (count($combined) > 1) {
                        $table = new html_table();
            $headings = new html_table_row();
            $headings->cells = array();
            $headings->cells[] = new html_table_cell(get_string('extension', 'tool_filetypes'));
            if (!$restricted) {
                $headings->cells[] =
                        new html_table_cell(html_writer::span(get_string('edit'), 'accesshide'));
            }
            $headings->cells[] = new html_table_cell(get_string('source', 'tool_filetypes'));
            $headings->cells[] = new html_table_cell(get_string('mimetype', 'tool_filetypes'));
            $headings->cells[] = new html_table_cell(get_string('groups', 'tool_filetypes'));
            $headings->cells[] = new html_table_cell(get_string('displaydescription', 'tool_filetypes'));
            foreach ($headings->cells as $cell) {
                $cell->header = true;
            }
            $table->data = array($headings);
            foreach ($combined as $extension => $filetype) {
                if ($extension === 'xxx') {
                    continue;
                }
                $row = new html_table_row();
                $row->cells = array();

                                $icon = $this->pix_icon('f/' . $filetype['icon'], '');
                $row->cells[] = new html_table_cell($icon . ' ' . html_writer::span(s($extension)));

                                $reverturl = new \moodle_url('/admin/tool/filetypes/revert.php',
                        array('extension' => $extension));
                $revertbutton = html_writer::link($reverturl, $this->pix_icon('t/restore',
                        get_string('revert', 'tool_filetypes', s($extension))));
                if ($restricted) {
                    $revertbutton = '';
                }

                                if (!empty($filetype['deleted'])) {
                                        if (!$restricted) {
                        $row->cells[] = new html_table_cell('');
                    }
                    $source = new html_table_cell(get_string('source_deleted', 'tool_filetypes') .
                            ' ' . $revertbutton);
                    $source->attributes = array('class' => 'nonstandard');
                    $row->cells[] = $source;

                                        $row->cells[] = new html_table_cell('');
                    $row->cells[] = new html_table_cell('');
                    $row->cells[] = new html_table_cell('');
                    $row->attributes = array('class' => 'deleted');
                } else {
                    if (!$restricted) {
                                                                        $editurl = new \moodle_url('/admin/tool/filetypes/edit.php',
                                array('oldextension' => $extension));
                        $editbutton = html_writer::link($editurl, $this->pix_icon('t/edit',
                                get_string('edita', '', s($extension))));
                        $deleteurl = new \moodle_url('/admin/tool/filetypes/delete.php',
                                array('extension' => $extension));
                        $deletebutton = html_writer::link($deleteurl, $this->pix_icon('t/delete',
                                get_string('deletea', 'tool_filetypes', s($extension))));
                        $row->cells[] = new html_table_cell($editbutton . '&nbsp;' . $deletebutton);
                    }

                                        $sourcestring = 'source_';
                    if (!empty($filetype['custom'])) {
                        $sourcestring .= 'custom';
                    } else if (!empty($filetype['modified'])) {
                        $sourcestring .= 'modified';
                    } else {
                        $sourcestring .= 'standard';
                    }
                    $source = new html_table_cell(get_string($sourcestring, 'tool_filetypes') .
                            ($sourcestring === 'source_modified' ? ' ' . $revertbutton : ''));
                    if ($sourcestring !== 'source_standard') {
                        $source->attributes = array('class' => 'nonstandard');
                    }
                    $row->cells[] = $source;

                                        $mimetype = html_writer::div(s($filetype['type']), 'mimetype');
                    if (!empty($filetype['defaulticon'])) {
                                                $mimetype .= html_writer::div(html_writer::tag('i',
                                get_string('defaulticon', 'tool_filetypes')));
                    }
                    $row->cells[] = new html_table_cell($mimetype);

                                        $groups = !empty($filetype['groups']) ? implode(', ', $filetype['groups']) : '';
                    $row->cells[] = new html_table_cell(s($groups));

                                        $description = get_mimetype_description(array('filename' => 'a.' . $extension));
                                                            if ($description === $filetype['type']) {
                        $description = '';
                    }
                    $row->cells[] = new html_table_cell($description);
                }

                $table->data[] = $row;
            }
            $out .= html_writer::table($table);
        } else {
            $out .= html_writer::tag('div', get_string('emptylist', 'tool_filetypes'));
        }
                if (!$restricted) {
            $out .= $this->single_button(new moodle_url('/admin/tool/filetypes/edit.php',
                    array('name' => 'add')), get_string('addfiletypes', 'tool_filetypes'), 'get');
        }
        return $out;
    }
}
