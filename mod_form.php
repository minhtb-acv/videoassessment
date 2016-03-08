<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/course/moodleform_mod.php';
require_once $CFG->dirroot . '/mod/videoassessment/bulkupload/lib.php';

use videoassess\va;

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

        $mform->addElement('text', 'name', get_string('videoassessmentname', 'videoassessment'), array('size' => '64'));
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

        /**
         * @author Le Xuan Anh Version2
         */
        $this->manageVideo();
        $this->standard_grading_coursemodule_elements_to_grading();
        //---
        
        $mform->addElement('radio', 'class', get_string('class', 'videoassessment'), get_string('open', 'videoassessment'), 1);
        $mform->addElement('radio', 'class', null, get_string('close', 'videoassessment'), 0);
        $mform->setType('class', PARAM_INT);
        $mform->setDefault('class', 1);

        /* MinhTB VERSION 2 07-03-2016 */
        foreach (array('teacher', 'self', 'peer', 'class', 'training') as $gradingtype) {
            if (empty($this->current->{'advancedgradingmethod_before' . $gradingtype})) {
                $this->current->{'advancedgradingmethod_before' . $gradingtype} = 'rubric';
            }
        }
        /* END MinhTB VERSION 2 07-03-2016 */

//         $mform->addElement('text', 'beforelabel', get_string('yourwordforx', '', get_string('before', 'videoassessment')), array('maxlength' => 40));
//         $mform->setType('beforelabel', PARAM_TEXT);
//         $mform->addHelpButton('beforelabel', 'timinglabel', 'videoassessment');
//         $mform->addElement('text', 'afterlabel', get_string('yourwordforx', '', get_string('after', 'videoassessment')), array('maxlength' => 40));
//         $mform->setType('afterlabel', PARAM_TEXT);
//         $mform->addHelpButton('afterlabel', 'timinglabel', 'videoassessment');

        $mform->addElement('header', 'ratings', get_string('ratings', 'videoassessment'));
        $mform->addElement('static', 'ratingerror');
        for ($i = 100; $i >= 0; $i--) {
            $ratingopts[$i] = $i . '%';
        }
        $mform->addElement('select', 'ratingteacher', get_string('teacher', 'videoassessment'), $ratingopts);
        $mform->setDefault('ratingteacher', 40);
        $mform->addHelpButton('ratingteacher', 'ratingteacher', 'videoassessment');
        $mform->addElement('select', 'ratingself', get_string('self', 'videoassessment'), $ratingopts);
        $mform->setDefault('ratingself', 20);
        $mform->addHelpButton('ratingself', 'ratingself', 'videoassessment');
        $mform->addElement('select', 'ratingpeer', get_string('peer', 'videoassessment'), $ratingopts);
        $mform->setDefault('ratingpeer', 20);
        $mform->addHelpButton('ratingpeer', 'ratingpeer', 'videoassessment');
        $mform->addElement('select', 'ratingclass', get_string('class', 'videoassessment'), $ratingopts);
        $mform->setDefault('ratingclass', 20);
        $mform->addHelpButton('ratingclass', 'ratingclass', 'videoassessment');

        $mform->addElement('selectyesno', 'delayedteachergrade', get_string('delayedteachergrade', 'videoassessment'));
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

        $ratingsum = $data['ratingteacher'] + $data['ratingself'] + $data['ratingpeer'] + $data['ratingclass'];
        if ($ratingsum != 100) {
            $errors['ratingerror'] = get_string('settotalratingtoahundredpercent', 'videoassessment');
        }

        return $errors;
    }
    
    public function get_data() {
        global $USER;
    
        $mform =& $this->_form;
    
        if (!$this->is_cancelled() and $this->is_submitted() and $this->is_validated()) {
            $data = $mform->exportValues();
            unset($data['sesskey']); // we do not need to return sesskey
            unset($data['_qf__'.$this->_formname]);   // we do not need the submission marker too
            if (empty($data)) {
                return NULL;
            } else {
                if ($data['training'] && !empty($data['trainingvideo'])) {
                    $fs = get_file_storage();
                    $upload = new \videoassessment_bulkupload($data['coursemodule']);
    
                    $files = $fs->get_area_files(\context_user::instance($USER->id)->id, 'user', 'draft', $data['trainingvideo']);
    
                    if (!empty($files)) {
                        foreach ($files as $file) {
                            if ($file->get_filename() == '.') {
                                continue;
                            }
    
                            $upload->create_temp_dirs();
                            $tmpname = $upload->get_temp_name($file->get_filename());
                            $tmppath = $upload->get_tempdir().'/upload/'.$tmpname;
                            $file->copy_content_to($tmppath);
    
                            $data['trainingvideoid'] = $upload->video_data_add($tmpname, $file->get_filename());
    
                            $upload->convert($tmpname);
                        }
                    }
                }
                
                $data['trainingvideo'] = 0;
    
                return (object)$data;
            }
        } else {
            return NULL;
        }
    }

    /**
     * @author Le Xuan Anh Version2
     */
    public function standard_grading_coursemodule_elements_to_grading() {
        global $COURSE, $CFG, $DB, $PAGE;
        $mform = & $this->_form;

        if ($this->_features->hasgrades) {

            if (!$this->_features->rating || $this->_features->gradecat) {
                $mform->addElement('header', 'modstandardgrade', get_string('grade', 'videoassessment'));
            }

            /* MinhTB VERSION 2 07-03-2016 */
            $mform->addElement('select', 'training', get_string('trainingpretest', 'videoassessment'), array(
                '0' => get_string('no', 'videoassessment'),
                '1' => get_string('yes', 'videoassessment')
            ));
            $mform->setDefault('training', 0);
            /* END MinhTB VERSION 2 07-03-2016 */
            
            $mform->addElement('filemanager', 'trainingvideo',
                get_string('trainingvideo', 'videoassessment'),
                null,
                array(
                    'subdirs' => 0,
                    'maxbytes' => $COURSE->maxbytes,
                    'maxfiles' => 1,
                    'accepted_types' => array('video', 'audio')
                )
            );
            $mform->addElement('hidden', 'trainingvideoid');
            $mform->setType('trainingvideoid', PARAM_INT);
            
            for ($i = 100; $i >= 0; $i--) {
                $ratingopts[$i] = $i . '%';
            }
            $mform->addElement('select', 'accepteddifference', get_string('accepteddifference', 'videoassessment'), $ratingopts);
            $mform->setDefault('accepteddifference', 20);
            $mform->addHelpButton('accepteddifference', 'accepteddifference', 'videoassessment');

            //if supports grades and grades arent being handled via ratings
            if (!$this->_features->rating) {
                $mform->addElement('modgrade', 'grade', get_string('grade'));
                $mform->addHelpButton('grade', 'modgrade', 'grades');
                $mform->setDefault('grade', $CFG->gradepointdefault);
            }

            if ($this->_features->advancedgrading
                    and ! empty($this->current->_advancedgradingdata['methods'])
                    and ! empty($this->current->_advancedgradingdata['areas'])) {

                if (count($this->current->_advancedgradingdata['areas']) == 1) {
                    // if there is just one gradable area (most cases), display just the selector
                    // without its name to make UI simplier
                    $areadata = reset($this->current->_advancedgradingdata['areas']);
                    $areaname = key($this->current->_advancedgradingdata['areas']);
                    $mform->addElement('select', 'advancedgradingmethod_' . $areaname, get_string('gradingmethod', 'core_grading'), $this->current->_advancedgradingdata['methods']);
                    $mform->addHelpButton('advancedgradingmethod_' . $areaname, 'gradingmethod', 'core_grading');
                } else {
                    // the module defines multiple gradable areas, display a selector
                    // for each of them together with a name of the area
                    $areasgroup = array();
                    foreach ($this->current->_advancedgradingdata['areas'] as $areaname => $areadata) {
                        $areasgroup[] = $mform->createElement('select', 'advancedgradingmethod_' . $areaname, $areadata['title'], $this->current->_advancedgradingdata['methods']);
                        $areasgroup[] = $mform->createElement('static', 'advancedgradingareaname_' . $areaname, '', $areadata['title']);
                    }
                    $mform->addGroup($areasgroup, 'advancedgradingmethodsgroup', get_string('gradingmethods', 'core_grading'), array(' ', '<br />'), false);
                }
            }

            if ($this->_features->gradecat) {
                $mform->addElement('select', 'gradecat', get_string('gradecategoryonmodform', 'grades'), grade_get_categories_menu($COURSE->id, $this->_outcomesused));
                $mform->addHelpButton('gradecat', 'gradecategoryonmodform', 'grades');
            }
            
            $module = array(
                    'name' => 'mod_videoassessment',
                    'fullpath' => '/mod/videoassessment/mod_form.js',
                    'requires' => array('node', 'event'),
                    'strings' => array(array('changetraingingwarning', 'mod_videoassessment'))
            );
            
            $PAGE->requires->js_init_call('M.mod_videoassessment.init_training_change', null, false, $module);
        }
    }

    public function manageVideo() {
        global $COURSE, $CFG, $DB, $PAGE;


        $cm = $PAGE->cm;
        
        if (!$cm) {
            return;
        }

        $viewurl = new moodle_url('/mod/videoassessment/view.php', array('id' => $cm->id));
        $context = context_module::instance($cm->id);

        $va = $DB->get_record('videoassessment', array('id' => $cm->instance));
        $course = $DB->get_record('course', array('id' => $va->course));

        require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';
        $vaobj = new va($context, $cm, $course);
        $isteacher = $vaobj->is_teacher();

        $mform = & $this->_form;
        $mform->addElement('header', 'managevideos', get_string('managevideo', 'videoassessment'));

        if ($isteacher) {
            if (va::uses_mobile_upload()) {
                $takevideoLink = new moodle_url($viewurl, array('action' => 'upload'));
                $takevideoText = get_string('takevideo', 'videoassessment');
                $mform->addElement('html', "<a class='managelink' href='$takevideoLink'>$takevideoText</a>");
            } else {
                $uploadvideoLink = new moodle_url($viewurl, array('action' => 'upload'));
                $uploadvideoText = get_string('uploadvideo', 'videoassessment');
                $mform->addElement('html', "<a class='managelink' href='$uploadvideoLink'>$uploadvideoText</a>");
                
                $bulkuploadLink = new moodle_url('/mod/videoassessment/bulkupload/index.php', array('cmid' => $cm->id));
                $bulkuploadText = get_string('videoassessment:bulkupload', 'videoassessment');
                $mform->addElement('html', "<a class='managelink' href='$bulkuploadLink'>$bulkuploadText</a>");
            }

            $deletevideosLink = new moodle_url('/mod/videoassessment/deletevideos.php', array('id' => $cm->id));
            $deletevideosText = get_string('deletevideos', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$deletevideosLink'>$deletevideosText</a>");

            $associateLink = new moodle_url($viewurl, array('action' => 'videos'));
            $associateText = get_string('associate', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$associateLink'>$associateText</a>");

            $assessText = get_string('assess', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$viewurl'>$assessText</a>");

            $assignpeersLink = new moodle_url('/mod/videoassessment/view.php', array('id' => $cm->id, 'action' => 'peers'));
            $assignpeersText = get_string('assignpeers', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$assignpeersLink'>$assignpeersText</a>");

            $publishvideosLink = new moodle_url($viewurl, array('action' => 'publish'));
            $publishvideosText = get_string('publishvideos', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$publishvideosLink'>$publishvideosText</a>");

            /* MinhTB VERSION 2 */
            $assignClassLink = new moodle_url('/mod/videoassessment/assignclass/index.php', array('id' => $cm->id));
            $assignClassText = get_string('assignclass', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$assignClassLink'>$assignClassText</a>");
            /* End */

            /* Le Xuan Anh VERSION 2 */
            $duplicateRubricLink = new moodle_url('/mod/videoassessment/rubric/duplicate.php', array('id' => $cm->id));
            $duplicateRubricText = get_string('duplicaterubric', 'videoassessment');
            $mform->addElement('html', "<a class='managelink' href='$duplicateRubricLink'>$duplicateRubricText</a>");
            /* End */
        }
    }
}
