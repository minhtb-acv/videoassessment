<?php
require_once '../../config.php';
require_once $CFG->dirroot.'/mod/videoassessment/locallib.php';

/* MinhTB VERSION 2 03-03-2016 */
if (optional_param('ajax', null, PARAM_ALPHANUM)) {
    $action = optional_param('action', null, PARAM_ALPHANUM);

    if ($action == 'getcoursesbycategory') {
        $catid = optional_param('catid', null, PARAM_INT);
        $courseopts = array();
        $html = "";

        if (!empty($catid)) {
            $courses = \videoassess\va::get_courses_managed_by($USER->id, $catid);
            array_walk($courses, function (\stdClass $a) use (&$courseopts) {
                $courseopts[$a->id] = $a->fullname;
            });

            $html = "<option value='0'>" . '('.get_string('new').')' . "</option>";

            foreach ($courseopts as $catid => $catname) {
                $html .= "<option value='$catid'>$catname</option>";
            }
        }

        echo json_encode(array(
            'html' => $html,
        ));
        die;
    } elseif ($action == 'getsectionsbycourse') {
        $courseid = optional_param('courseid', null, PARAM_INT);
        $sectionopts = array();
        $html = "";

        if (!empty($courseid)) {
            $modinfo = get_fast_modinfo($courseid);
            $sections = $modinfo->get_section_info_all();

            if (!empty($sections)) {
                foreach ($sections as $key => $section) {
                    $sectionopts[$section->__get('id')] = get_section_name($courseid, $section->__get('section'));
                }

                foreach ($sectionopts as $sectionnum => $sectionname) {
                    $html .= "<option value='$sectionnum'>$sectionname</option>";
                }
            }
        }

        echo json_encode(array(
            'html' => $html,
        ));
        die;
    }
}
/* END MinhTB VERSION 2 03-03-2016 */

$id = required_param('id', PARAM_INT);
$url = new moodle_url('/mod/videoassessment/view.php', array('id' => $id));
if ($action = optional_param('action', null, PARAM_ALPHA)) {
    $url->param('action', $action);
}
$cm = get_coursemodule_from_id('videoassessment', $id);
$course = $DB->get_record('course', array('id' => $cm->course));
require_login($cm->course, true, $cm);
$PAGE->set_url($url);
$PAGE->set_heading($cm->name);
/* MinhTB VERSION 2 */
$PAGE->requires->jquery();
/* END */

$context = context_module::instance($cm->id);

$va = new videoassess\va($context, $cm, $course);
echo $va->view($action);
