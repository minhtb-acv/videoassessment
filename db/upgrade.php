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

    return true;
}
