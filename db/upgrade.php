<?php
defined('MOODLE_INTERNAL') || die();

/**
 *
 * @param int $oldversion
 * @return boolean
 */
function xmldb_videoassessment_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012110200) {

        // Define field allowstudentpeerselection to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('allowstudentpeerselection', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'allowstudentupload');

        // Conditionally launch add field allowstudentpeerselection
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2012110200, 'videoassessment');
    }

    if ($oldversion < 2013080900) {
    	require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';

    	$mods = $DB->get_records('videoassessment');
    	foreach ($mods as $mod) {
    		videoassess\va::cleanup_old_peer_grades($mod->id);
    	}

    	upgrade_mod_savepoint(true, 2013080900, 'videoassessment');
    }

    if ($oldversion < 2015032010) {
        require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';

        $DB->delete_records('grade_items', array('itemnumber' => 4));
        $DB->delete_records('grade_items', array('itemnumber' => 5));
        $DB->delete_records('grade_items', array('itemnumber' => 6));

        $courses = videoassess\va::get_courses();
        foreach ($courses as $course)
        {
            $users = videoassess\va::get_users($course->id);

            foreach ($users as $user)
            {
                $grade = videoassess\va::get_grade($course->id, $user->id);

                if ($grade->count > 0)
                {
                    $course_item = $DB->get_record('grade_items', array(
                        'itemtype' => 'course',
                        'courseid' => $course->id,
                    ));

                    $item_grade = $DB->get_record('grade_grades', array(
                        'itemid' => $course_item->id,
                        'userid' => $user->id
                    ));

                    if (!empty($item_grade))
                    {
                        $item_grade->finalgrade = $grade->total / $grade->count;
                        $DB->update_record('grade_grades', $item_grade);
                    }
                }
            }
        }

        upgrade_mod_savepoint(true, 2015032010, 'videoassessment');
    }
    
    if ($oldversion < 2015051901) {
        // Define field ratingclass to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('ratingclass', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'ratingpeer');
        
        // Conditionally launch add field ratingclass
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('class', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'ratingclass');
        // Conditionally launch add field ratingclass
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2015051901, 'videoassessment');
    }

    if ($oldversion < 2015051902) {
        // Define field gradebeforeclass to be added to videoassessment_aggregation
        $table = new xmldb_table('videoassessment_aggregation');
        $field = new xmldb_field('gradebeforeclass', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '-1', 'gradebeforepeer');

        // Conditionally launch add field gradebeforeclass
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2015051902, 'videoassessment');
    }

    if ($oldversion < 2015060502) {
        // Define field order to be added to user_enrolments
        $table = new xmldb_table('user_enrolments');
        $field = new xmldb_field('order', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'enrolid');

        // Conditionally launch add field sort
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2015060502, 'videoassessment');
    }

    if ($oldversion < 2015061701) {
        // Define field sortby to be added to course
        $table = new xmldb_table('course');
        $field = new xmldb_field('sortby', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        // Conditionally launch add field sortby
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2015061701, 'videoassessment');
    }

    if ($oldversion < 2015061702) {
        // Define field sortby to be added to groups
        $table = new xmldb_table('groups');
        $field = new xmldb_field('sortby', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        // Conditionally launch add field sortby
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field order to be added to groups_members
        $table = new xmldb_table('groups_members');
        $field = new xmldb_field('order', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        // Conditionally launch add field order
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2015061702, 'videoassessment');
    }

    /**
     * @author MinhTB VERSION 2
     *
     * Add training field to videoassessment table
     */
    if ($oldversion < 2016030701) {
        // Define field traning to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('training', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'timeavailable');

        // Conditionally launch add field traning
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2016030701, 'videoassessment');
    }

    /**
     * @author MinhTB VERSION 2
     *
     * Add trainingvideo, accepteddifference fields to videoassessment table
     */
    if ($oldversion < 2016030702) {
        // Define field traningvideo to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('trainingvideo', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'training');

        // Conditionally launch add field traningvideo
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field accepteddifference to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('accepteddifference', XMLDB_TYPE_INTEGER, '3', null, null, null, null, 'trainingvideo');

        // Conditionally launch add field accepteddifference
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2016030702, 'videoassessment');
    }

    if ($oldversion < 2016030703) {
        // Define field gradebeforetraining to be added to videoassessment_aggregation
        $table = new xmldb_table('videoassessment_aggregation');
        $field = new xmldb_field('gradebeforetraining', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'gradebeforeclass');

        // Conditionally launch add field gradebeforetraining
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2016030703, 'videoassessment');
    }
    
    if ($oldversion < 2016030803) {
        // Define field trainingvideoid to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('trainingvideoid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'trainingvideo');
    
        // Conditionally launch add field trainingvideoid
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2016030803, 'videoassessment');
    }

    if ($oldversion < 2016030804) {
        // Define field passtraining to be added to videoassessment_aggregation
        $table = new xmldb_table('videoassessment_aggregation');
        $field = new xmldb_field('passtraining', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'gradebeforetraining');

        // Conditionally launch add field passtraining
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2016030804, 'videoassessment');
    }

    if ($oldversion < 2016031100) {
        // Define field trainingdesc to be added to videoassessment
        $table = new xmldb_table('videoassessment');
        $field = new xmldb_field('trainingdesc', XMLDB_TYPE_TEXT, null, null, null, null, null, 'trainingvideoid');
    
        // Conditionally launch add field trainingdesc
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    
        // videoassessment savepoint reached
        upgrade_mod_savepoint(true, 2016031100, 'videoassessment');
    }
    
    return true;
}
