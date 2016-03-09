<?php
use videoassess\va;

defined('MOODLE_INTERNAL') || die();

/**
 *
 * @param stdClass $va
 * @param mod_videoassessment_mod_form $form
 * @return int
 */
function videoassessment_add_instance($va, $form) {
    global $DB;

    $va->id = $DB->insert_record('videoassessment', $va);
    
    return $va->id;
}

/**
 *
 * @param stdClass $va
 * @param mod_videoassessment_mod_form $form
 * @return boolean
 */
function videoassessment_update_instance($va, $form) {
	global $DB, $CFG;

	$va->id = $va->instance;

	$oldva = $DB->get_record('videoassessment', array('id' => $va->id));

	$DB->update_record('videoassessment', $va);

	if ($oldva->ratingteacher != $va->ratingteacher
			|| $oldva->ratingself != $va->ratingself
			|| $oldva->ratingpeer != $va->ratingpeer) {
		require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';
		$cm = get_coursemodule_from_instance('videoassessment', $va->id, 0, false, MUST_EXIST);
		$course = $DB->get_record('course', array('id' => $va->course), '*', MUST_EXIST);
		$vaobj = new videoassess\va(context_module::instance($cm->id), $cm, $course);
		$vaobj->regrade();
	}

	return true;
}

/**
 *
 * @param int $id
 * @return boolean
 */
function videoassessment_delete_instance($id) {
    global $DB;

    $DB->delete_records('videoassessment', array('id' => $id));
    $DB->delete_records('videoassessment_aggregation', array('videoassessment' => $id));
    $DB->delete_records('videoassessment_grades', array('videoassessment' => $id));
    $DB->delete_records('videoassessment_grade_items', array('videoassessment' => $id));
    $DB->delete_records('videoassessment_peers', array('videoassessment' => $id));
    $DB->delete_records('videoassessment_videos', array('videoassessment' => $id));
    $DB->delete_records('videoassessment_video_assocs', array('videoassessment' => $id));

    return true;
}

/**
 *
 * @param string $feature
 * @return boolean
 */
function videoassessment_supports($feature) {
    return in_array($feature, array(
            FEATURE_GROUPS,
            FEATURE_GROUPINGS,
            FEATURE_GROUPMEMBERSONLY,
            FEATURE_MOD_INTRO,
            FEATURE_COMPLETION_TRACKS_VIEWS,
            FEATURE_GRADE_HAS_GRADE,
            FEATURE_GRADE_OUTCOMES,
            FEATURE_GRADE_HAS_GRADE,
            FEATURE_BACKUP_MOODLE2,
            FEATURE_SHOW_DESCRIPTION,
            FEATURE_ADVANCED_GRADING,
            FEATURE_BACKUP_MOODLE2,
            FEATURE_IDNUMBER
    ));
}

/**
 * @return array
 */
/* MinhTB VERSION 2 07-03-2016 */
function videoassessment_grading_areas_list() {
    return array(
        'beforeteacher' => get_string('teacher', 'videoassessment'),
        'beforeself' => get_string('self', 'videoassessment'),
        'beforepeer' => get_string('peer', 'videoassessment'),
        'beforeclass' => get_string('class', 'videoassessment'),
        'beforetraining' => get_string('trainingpretest', 'videoassessment')
    );
}
/* END MinhTB VERSION 2 07-03-2016 */

/**
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 */
function mod_videoassessment_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $fullpath = "/{$context->id}/mod_videoassessment/$filearea/".implode('/', $args);

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    // Self Assessment/Peer Assessment のために、他人のファイルの表示を許可する
    if (!has_capability('mod/videoassessment:gradepeer', $context)) {
        send_file_not_found();
    }

    session_get_instance()->write_close(); // unlock session during fileserving
    send_stored_file($file, HOURSECS, 0, $forcedownload);
}

function videoassessment_convert_video($event, $va) {
    global $CFG, $DB, $USER;

    require_once $CFG->dirroot . '/mod/videoassessment/bulkupload/lib.php';

    if ($va->training && !empty($va->trainingvideo)) {
        $fs = get_file_storage();
        $upload = new \videoassessment_bulkupload($event->instanceid);

        $files = $fs->get_area_files(\context_user::instance($USER->id)->id, 'user', 'draft', $va->trainingvideo);

        if (!empty($files)) {
            foreach ($files as $file) {
                if ($file->get_filename() == '.') {
                    continue;
                }

                $upload->create_temp_dirs();
                $tmpname = $upload->get_temp_name($file->get_filename());
                $tmppath = $upload->get_tempdir().'/upload/'.$tmpname;
                $file->copy_content_to($tmppath);

                $videoid = $upload->video_data_add($tmpname, $file->get_filename());

                $upload->convert($tmpname);
                
                $DB->execute("UPDATE {videoassessment} SET trainingvideoid = ?, trainingvideo = 0 WHERE id = ?",
                        array($videoid, $va->id));
            }
        }
    }
}