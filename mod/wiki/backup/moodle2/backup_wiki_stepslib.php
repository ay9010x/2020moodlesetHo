<?php







class backup_wiki_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $wiki = new backup_nested_element('wiki', array('id'), array('name', 'intro', 'introformat', 'timecreated', 'timemodified', 'firstpagetitle', 'wikimode', 'defaultformat', 'forceformat', 'editbegin', 'editend'));

        $subwikis = new backup_nested_element('subwikis');

        $subwiki = new backup_nested_element('subwiki', array('id'), array('groupid', 'userid'));

        $pages = new backup_nested_element('pages');

        $page = new backup_nested_element('page', array('id'), array('title', 'cachedcontent', 'timecreated', 'timemodified', 'timerendered', 'userid', 'pageviews', 'readonly'));

        $synonyms = new backup_nested_element('synonyms');

        $synonym = new backup_nested_element('synonym', array('id'), array('pageid', 'pagesynonym'));

        $links = new backup_nested_element('links');

        $link = new backup_nested_element('link', array('id'), array('frompageid', 'topageid', 'tomissingpage'));

        $versions = new backup_nested_element('versions');

        $version = new backup_nested_element('version', array('id'), array('content', 'contentformat', 'version', 'timecreated', 'userid'));

        $tags = new backup_nested_element('tags');

        $tag = new backup_nested_element('tag', array('id'), array('name', 'rawname'));

                $wiki->add_child($subwikis);
        $subwikis->add_child($subwiki);

        $subwiki->add_child($pages);
        $pages->add_child($page);

        $subwiki->add_child($synonyms);
        $synonyms->add_child($synonym);

        $subwiki->add_child($links);
        $links->add_child($link);

        $page->add_child($versions);
        $versions->add_child($version);

        $page->add_child($tags);
        $tags->add_child($tag);

                $wiki->set_source_table('wiki', array('id' => backup::VAR_ACTIVITYID));

                if ($userinfo) {
            $subwiki->set_source_sql('
                SELECT *
                  FROM {wiki_subwikis}
                 WHERE wikiid = ?', array(backup::VAR_PARENTID));

            $page->set_source_table('wiki_pages', array('subwikiid' => backup::VAR_PARENTID));

            $synonym->set_source_table('wiki_synonyms', array('subwikiid' => backup::VAR_PARENTID));

            $link->set_source_table('wiki_links', array('subwikiid' => backup::VAR_PARENTID));

            $version->set_source_table('wiki_versions', array('pageid' => backup::VAR_PARENTID));

            $tag->set_source_sql('SELECT t.id, t.name, t.rawname
                                    FROM {tag} t
                                    JOIN {tag_instance} ti ON ti.tagid = t.id
                                   WHERE ti.itemtype = ?
                                     AND ti.component = ?
                                     AND ti.itemid = ?', array(
                                         backup_helper::is_sqlparam('wiki_pages'),
                                         backup_helper::is_sqlparam('mod_wiki'),
                                         backup::VAR_PARENTID));
        }

                $subwiki->annotate_ids('group', 'groupid');

        $subwiki->annotate_ids('user', 'userid');

        $page->annotate_ids('user', 'userid');

        $version->annotate_ids('user', 'userid');

                $wiki->annotate_files('mod_wiki', 'intro', null);         $subwiki->annotate_files('mod_wiki', 'attachments', 'id'); 
                return $this->prepare_activity_structure($wiki);
    }

}
