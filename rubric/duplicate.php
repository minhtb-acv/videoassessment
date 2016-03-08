<?php
/**
 * @author Le Xuan Anh
 * Version2
 *
 * Duplicate Rubric from teacher's rubric to other rubric
 */
namespace videoassess;

use \videoassess\va;
use \videoassess\form\assign_class;

global $CFG, $DB, $OUTPUT, $PAGE;

require_once '../../../config.php';
require_once($CFG->dirroot.'/grade/grading/lib.php');
require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';

$cmid = optional_param('id', null, PARAM_INT);
$cm = get_coursemodule_from_id('videoassessment', $cmid, 0, false, MUST_EXIST);
require_login($cm->course, true, $cm);

$course = $DB->get_record('course', array('id' => $cm->course));
$context = \context_module::instance($cm->id);

$areasGrading = $DB->get_records('grading_areas', array('contextid' => $context->id));

if (is_array($areasGrading)) {
    foreach ($areasGrading as $area) {
        if ($area->areaname == 'beforeteacher') {
            $areaTeacherId = $area->id;
        }
    }
}

$gradingDefinitions = $DB->get_record('grading_definitions', array('areaid' => $areaTeacherId));

/**
 * Get information of Rubric
 */
$manager = get_grading_manager($areaTeacherId);
// get the currently active method
$method = $manager->get_active_method();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('duplicaterubric', 'videoassessment'));

echo '<h3>' . $gradingDefinitions->name . '<span class="status ready"> Ready for use</span></h3>';
if (!empty($method)) {
    $output = $PAGE->get_renderer('core_grading');
    $controller = $manager->get_controller($method);
    echo $output->box($controller->render_preview($PAGE), 'definition-preview');
}

echo $OUTPUT->footer();
