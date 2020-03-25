<?php







class backup_forum_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

        
        $forum = new backup_nested_element('forum', array('id'), array(
            'type', 'name', 'intro', 'introformat',
            'assessed', 'assesstimestart', 'assesstimefinish', 'scale',
            'maxbytes', 'maxattachments', 'forcesubscribe', 'trackingtype',
            'rsstype', 'rssarticles', 'timemodified', 'warnafter',
            'blockafter', 'blockperiod', 'completiondiscussions', 'completionreplies',
            'completionposts', 'displaywordcount'));

        $discussions = new backup_nested_element('discussions');

        $discussion = new backup_nested_element('discussion', array('id'), array(
            'name', 'firstpost', 'userid', 'groupid',
            'assessed', 'timemodified', 'usermodified', 'timestart',
            'timeend', 'pinned'));

        $posts = new backup_nested_element('posts');

        $post = new backup_nested_element('post', array('id'), array(
            'parent', 'userid', 'created', 'modified',
            'mailed', 'subject', 'message', 'messageformat',
            'messagetrust', 'attachment', 'totalscore', 'mailnow'));

        $ratings = new backup_nested_element('ratings');

        $rating = new backup_nested_element('rating', array('id'), array(
            'component', 'ratingarea', 'scaleid', 'value', 'userid', 'timecreated', 'timemodified'));

        $discussionsubs = new backup_nested_element('discussion_subs');

        $discussionsub = new backup_nested_element('discussion_sub', array('id'), array(
            'userid',
            'preference',
        ));

        $subscriptions = new backup_nested_element('subscriptions');

        $subscription = new backup_nested_element('subscription', array('id'), array(
            'userid'));

        $digests = new backup_nested_element('digests');

        $digest = new backup_nested_element('digest', array('id'), array(
            'userid', 'maildigest'));

        $readposts = new backup_nested_element('readposts');

        $read = new backup_nested_element('read', array('id'), array(
            'userid', 'discussionid', 'postid', 'firstread',
            'lastread'));

        $trackedprefs = new backup_nested_element('trackedprefs');

        $track = new backup_nested_element('track', array('id'), array(
            'userid'));

        
        $forum->add_child($discussions);
        $discussions->add_child($discussion);

        $forum->add_child($subscriptions);
        $subscriptions->add_child($subscription);

        $forum->add_child($digests);
        $digests->add_child($digest);

        $forum->add_child($readposts);
        $readposts->add_child($read);

        $forum->add_child($trackedprefs);
        $trackedprefs->add_child($track);

        $discussion->add_child($posts);
        $posts->add_child($post);

        $post->add_child($ratings);
        $ratings->add_child($rating);

        $discussion->add_child($discussionsubs);
        $discussionsubs->add_child($discussionsub);

        
        $forum->set_source_table('forum', array('id' => backup::VAR_ACTIVITYID));

                if ($userinfo) {
            $discussion->set_source_sql('
                SELECT *
                  FROM {forum_discussions}
                 WHERE forum = ?',
                array(backup::VAR_PARENTID));

                        $post->set_source_table('forum_posts', array('discussion' => backup::VAR_PARENTID), 'id ASC');
            $discussionsub->set_source_table('forum_discussion_subs', array('discussion' => backup::VAR_PARENTID));

            $subscription->set_source_table('forum_subscriptions', array('forum' => backup::VAR_PARENTID));
            $digest->set_source_table('forum_digests', array('forum' => backup::VAR_PARENTID));

            $read->set_source_table('forum_read', array('forumid' => backup::VAR_PARENTID));

            $track->set_source_table('forum_track_prefs', array('forumid' => backup::VAR_PARENTID));

            $rating->set_source_table('rating', array('contextid'  => backup::VAR_CONTEXTID,
                                                      'component'  => backup_helper::is_sqlparam('mod_forum'),
                                                      'ratingarea' => backup_helper::is_sqlparam('post'),
                                                      'itemid'     => backup::VAR_PARENTID));
            $rating->set_source_alias('rating', 'value');
        }

        
        $forum->annotate_ids('scale', 'scale');

        $discussion->annotate_ids('group', 'groupid');

        $post->annotate_ids('user', 'userid');

        $discussionsub->annotate_ids('user', 'userid');

        $rating->annotate_ids('scale', 'scaleid');

        $rating->annotate_ids('user', 'userid');

        $subscription->annotate_ids('user', 'userid');

        $digest->annotate_ids('user', 'userid');

        $read->annotate_ids('user', 'userid');

        $track->annotate_ids('user', 'userid');

        
        $forum->annotate_files('mod_forum', 'intro', null); 
        $post->annotate_files('mod_forum', 'post', 'id');
        $post->annotate_files('mod_forum', 'attachment', 'id');

                return $this->prepare_activity_structure($forum);
    }

}
