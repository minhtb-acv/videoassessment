<?php
/**
 * @author Le Xuan Anh
 * Version2
 *
 * Duplicate Form
 *
 * Created at 2015/03/08
 */

defined('MOODLE_INTERNAL') || die();

class mod_videoassessment_rubric_form_duplicate extends moodleform {

    public function definition() {

        $dform = $this->_form;

        $dform->addElement('hidden', 'id');
        $dform->setType('id', PARAM_INT);

        $dform->addElement('hidden', 'contextid');
        $dform->setType('contextid', PARAM_INT);

        $this->add_action_buttons(null, get_string('duplicaterubric', 'videoassessment'));
    }
}