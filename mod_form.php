<?php
defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/course/moodleform_mod.php';

/**
 * @see moodleform_mod
 */
class mod_videoassessment_mod_form extends moodleform_mod {
    const MAX_USED_PEERS_LIMIT = 3;
    const DEFAULT_USED_PEERS = 1;

    protected $_videoassessmentinstance = null;

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('videoassessmentname', 'videoassessment'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->add_intro_editor(false, get_string('description', 'videoassessment'));

        $mform->addElement('selectyesno', 'allowstudentupload', get_string('allowstudentupload', 'videoassessment'));
        $mform->setDefault('allowstudentupload', 1);
        $mform->addHelpButton('allowstudentupload', 'allowstudentupload', 'videoassessment');

        $this->standard_grading_coursemodule_elements();

        if (empty($this->_instance)) {
            foreach (array('before', 'after') as $timing) {
                foreach (array('teacher', 'self', 'peer') as $gradingtype) {
                    $this->current->{'advancedgradingmethod_'.$timing.$gradingtype} = 'rubric';
                }
            }
        }

        $mform->addElement('text', 'beforelabel',
                get_string('yourwordforx', '', get_string('before', 'videoassessment')),
                array('maxlength' => 40));
        $mform->setType('beforelabel', PARAM_TEXT);
        $mform->addHelpButton('beforelabel', 'timinglabel', 'videoassessment');
        $mform->addElement('text', 'afterlabel',
                get_string('yourwordforx', '', get_string('after', 'videoassessment')),
                array('maxlength' => 40));
        $mform->setType('afterlabel', PARAM_TEXT);
        $mform->addHelpButton('afterlabel', 'timinglabel', 'videoassessment');

        $mform->addElement('header', 'ratings', get_string('ratings', 'videoassessment'));
        $mform->addElement('static', 'ratingerror');
        for ($i = 100; $i >= 0; $i--) {
            $ratingopts[$i] = $i.'%';
        }
        $mform->addElement('select', 'ratingteacher', get_string('teacher', 'videoassessment'),
                           $ratingopts);
        $mform->setDefault('ratingteacher', 60);
        $mform->addHelpButton('ratingteacher', 'ratingteacher', 'videoassessment');
        $mform->addElement('select', 'ratingself', get_string('self', 'videoassessment'),
                           $ratingopts);
        $mform->setDefault('ratingself', 20);
        $mform->addHelpButton('ratingself', 'ratingself', 'videoassessment');
        $mform->addElement('select', 'ratingpeer', get_string('peer', 'videoassessment'),
                           $ratingopts);
        $mform->setDefault('ratingpeer', 20);
        $mform->addHelpButton('ratingpeer', 'ratingpeer', 'videoassessment');

        $mform->addElement('selectyesno', 'delayedteachergrade',
                get_string('delayedteachergrade', 'videoassessment'));
        $mform->addHelpButton('delayedteachergrade', 'delayedteachergrade', 'videoassessment');


        $students = get_enrolled_users($this->context);
        $maxusedpeers = min(count($students), self::MAX_USED_PEERS_LIMIT);
        $usedpeeropts = range(0, $maxusedpeers);
        $mform->addElement('select', 'usedpeers', get_string('usedpeers', 'videoassessment'), $usedpeeropts);
        $mform->setDefault('usedpeers', min(self::DEFAULT_USED_PEERS, $maxusedpeers));
        $mform->addHelpButton('usedpeers', 'usedpeers', 'videoassessment');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        // Allow plugin videoassessment types to do any extra validation after the form has been submitted
        $errors = parent::validation($data, $files);

        $ratingsum = $data['ratingteacher'] + $data['ratingself'] + $data['ratingpeer'];
        if ($ratingsum != 100) {
            $errors['ratingerror'] = get_string('settotalratingtoahundredpercent', 'videoassessment');
        }

        return $errors;
    }
}

