<?php
/**
 * @author Le Xuan Anh
 * Version2
 *
 * Duplicate Rubric from teacher's rubric to other rubric
 *
 * Created at 2015/03/08
 */
global $CFG, $DB, $OUTPUT, $PAGE;

require_once '../../../config.php';
require_once($CFG->dirroot.'/grade/grading/lib.php');
require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';
require_once $CFG->dirroot . '/mod/videoassessment/rubric/form_duplicate.php';

$cmid = optional_param('id', null, PARAM_INT);
$cm = get_coursemodule_from_id('videoassessment', $cmid, 0, false, MUST_EXIST);
require_login($cm->course, true, $cm);

$course = $DB->get_record('course', array('id' => $cm->course));
$context = \context_module::instance($cm->id);
$areas = array(); // Array Id of Class, Peer, Self in Grading_Areas's Table
$definitionIds = array(); // Array Id Definition in grading_definitions's table
$criteriaIds = array(); // Array Id Criteria in gradingform_rubric_criteria's table

// Get data from videoassessment table
$currentVideoAssessment = $DB->get_record('videoassessment', array('id' => $cm->instance), 'training');
// Get data from grading_areas table
$areasGrading = $DB->get_records('grading_areas', array('contextid' => $context->id));

if (is_array($areasGrading)) {
    foreach ($areasGrading as $area) {
        if ($area->areaname == 'beforeteacher') {
            $areaTeacherId = $area->id;
        } else {
            if ($currentVideoAssessment->training == 0) {
                if ($area->areaname != 'beforetraining') {
                    $areas[$area->id] = get_string($area->areaname, 'videoassessment');
                }
            } else {
                $areas[$area->id] = get_string($area->areaname, 'videoassessment');
            }
        }
    }
}

$gradingDefinitionTeacher = $DB->get_record('grading_definitions', array('areaid' => $areaTeacherId));
// Check exist teacher's rubric
if (!$gradingDefinitionTeacher) {
    redirect(new \moodle_url('/grade/grading/manage.php', array('areaid' => $areaTeacherId)), get_string('pleasedefinerubricforteacher', 'videoassessment'));
}
// Get information of Rubric
$manager = get_grading_manager($areaTeacherId);
// Get the currently active method
$method = $manager->get_active_method();

$PAGE->requires->css(new \moodle_url('/mod/videoassessment/duplicate.css'));

$url = new moodle_url('/mod/videoassessment/rubric/duplicate.php', array('id' => $cmid));
$PAGE->set_url($url);
$PAGE->add_body_class('duplicate-page');

// Set default data for duplicate form
$data = new stdClass;
$data->id = $cmid;
$data->contextid = $context->id;

// Create form duplicate
$dForm = new \mod_videoassessment_rubric_form_duplicate('', array('areas' => $areas));
$dForm->set_data($data);

// Post form
if ($data = $dForm->get_data()) {
    /**
     * Check rubric in grading_definitions
     */
    $areas = $data->areas;
    $inAreaIds = implode(',', array_keys($areas));
    $areaDefinitions = $DB->get_records_sql('SELECT areaid FROM {grading_definitions} WHERE areaid IN (' . $areaTeacherId . ',' . $inAreaIds . ')');

//    if (is_array($areaDefinitions)) {
//        foreach ($areaDefinitions as $areaDefinition) {
//            if(in_array($areaDefinition->areaid, array_keys($areas))) {
//                unset($areas[$areaDefinition->areaid]);
//            }
//        }
//    }
//
//    if (is_null($areas) || empty($areas)) {
//        redirect('/mod/videoassessment/view.php?id='.$PAGE->cm->id, get_string('duplicateerrors', 'videoassessment'));
//    }
    /**
     * Insert to grading_definitions table
     * Get id definitions of new record after insert
     */
    // $gradingDefinitionOther : Object use insert data to grading_definitions table (data: peer, self, class)
    $gradingDefinitionOther = clone $gradingDefinitionTeacher;

    foreach ($areas as $areaId => $val) {
        $gradingDefinitionOther->areaid = $areaId;
        $definitionIds[] = $DB->insert_record('grading_definitions', $gradingDefinitionOther);
    }

    if (is_null($definitionIds)) {
        redirect(new \moodle_url('/mod/videoassessment/view.php', array('id' => $PAGE->cm->id)), get_string('duplicateerrors', 'videoassessment'));
    }
    /**
     * Insert to gradingForm_rubric_criteria table
     * Get ids criteria of new record after insert
     */
    $gradingsFormCriteria = $DB->get_records('gradingform_rubric_criteria', array('definitionid' => $gradingDefinitionTeacher->id));
    $criteriaTeacherIds = array(); // Array save criteria teacher data

    if (is_array($gradingsFormCriteria)) {
        foreach ($definitionIds as $definitionId) {
            foreach ($gradingsFormCriteria as $gradingFormCriteria) {
                $gradingFormCriteriaOther = clone $gradingFormCriteria;
                $gradingFormCriteriaOther->definitionid = $definitionId;
                $criteriaIds[] = $DB->insert_record('gradingform_rubric_criteria', $gradingFormCriteriaOther);
                $criteriaTeacherIds[$gradingFormCriteria->id] = $gradingFormCriteria->id;
            }
        }
    }

    if (is_null($criteriaIds)) {
        //Delete grading_definitions
        $definitionIds = implode(',', array_values($definitionIds));
        $DB->delete_records_select('grading_definitions', 'id IN (' . $definitionIds . ')');
        //Redirect
        redirect(new \moodle_url('/mod/videoassessment/view.php', array('id' => $PAGE->cm->id)), get_string('duplicateerrors', 'videoassessment'));
    }
    /**
     * Insert to gradingForm_rubric_levels table
     * Return true | false
     */
    $criteriaTeacherIdLevels = implode(',', array_values($criteriaTeacherIds));
    $gradingRubricLevelsTeacher = $DB->get_records_sql('SELECT * FROM {gradingform_rubric_levels} WHERE criterionid IN (' . $criteriaTeacherIdLevels . ')');
    $criteriaLevelDuplicate = array();

    if (is_array($gradingRubricLevelsTeacher)) {
        foreach ($criteriaIds as $criteriaId) {
            foreach ($gradingRubricLevelsTeacher as $gradingRubricLevelTeacher) {
                $gradingRubricLevelTeacherOther = clone $gradingRubricLevelTeacher;
                $gradingRubricLevelTeacherOther->criterionid = $criteriaId;
                $criteriaLevelDuplicate[] = $gradingRubricLevelTeacherOther;
            }
        }
    }

    $success = $DB->insert_records('gradingform_rubric_levels', $criteriaLevelDuplicate);

    if (!$success) {
        redirect(new \moodle_url('/mod/videoassessment/view.php', array('id' => $PAGE->cm->id)), get_string('duplicatesuccess', 'videoassessment'));
    } else {
        //Delete grading_definitions
        $definitionIds = implode(',', array_values($definitionIds));
        $DB->delete_records_select('grading_definitions', 'id IN (' . $definitionIds . ')');
        //Delete gradingform_rubric_criteria
        $criteriaIds = implode(',', array_values($criteriaIds));
        $DB->delete_records_select('gradingform_rubric_criteria', 'id IN (' . $criteriaIds . ')');
        //Redirect
        redirect(new \moodle_url('/mod/videoassessment/view.php', array('id' => $PAGE->cm->id)), get_string('duplicateerrors', 'videoassessment'));
    }
} else { //Default page
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('duplicaterubric', 'videoassessment'));
    echo '<h3>' . $gradingDefinitionTeacher->name . '<span class="status ready"> Ready for use</span></h3>';

    if (!empty($method)) {
        $output = $PAGE->get_renderer('core_grading');
        $controller = $manager->get_controller($method);
        echo $output->box($controller->render_preview($PAGE), 'definition-preview');
    }

    $dForm->display();
    echo $OUTPUT->footer();
}
