<?php
namespace videoassess\form;

use videoassess\va;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

class video_upload extends \moodleform {
    protected function definition() {
        global $COURSE, $CFG;

        $mform = $this->_form;
        /* @var $va \videoassess\va */
        $va = $this->_customdata->va;

        $mobile = va::uses_mobile_upload();
        if ($mobile) {
        	$mform->updateAttributes(array('enctype' => 'multipart/form-data'));
        	$mform->addElement('hidden', 'mobile', 1);
        	$mform->setType('mobile', PARAM_BOOL);
        }

        $mform->addElement('hidden', 'id', required_param('id', PARAM_INT));
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'upload');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'user', optional_param('user', 0, PARAM_INT));
        $mform->setType('user', PARAM_INT);
        $mform->addElement('hidden', 'timing', optional_param('timing', '', PARAM_ALPHA));
        $mform->setType('timing', PARAM_ALPHA);

        $mform->addElement('header', 'videohdr', get_string('upload', 'videoassessment'));

        $maxbytes = $COURSE->maxbytes;
        if ($CFG->version < va::MOODLE_VERSION_23) {
        	$acceptedtypes = array('*');
        } else {
        	$acceptedtypes = array('video', 'audio');
        }

        if ($mobile) {
			$input = \html_writer::empty_tag('input',
					array(
							'type' => 'file',
							'name' => 'mobilevideo',
							'accept' => 'video/*',
							'capture' => 'camcorder'
					));
			$mform->addElement('static', 'mobilevideo', va::str('video'), $input);
        } else {
            $str = va::str('video');
//            if ($timing = optional_param('timing', null, PARAM_ALPHA)) {
//                $str .= ' (' . $va->timing_str($timing) . ')';
//            }
            $mform->addElement('filemanager', 'video',
                    $str,
                    null,
                    array(
                            'subdirs' => 0,
                            'maxbytes' => $maxbytes,
                            'maxfiles' => 1,
                            'accepted_types' => $acceptedtypes
                    )
            );
        }

        $this->add_action_buttons(true, get_string('upload'));
    }

    /**
     *
     * @param array $data
     * @param array $files
     * @return string[]
     */
    public function validation($data, $files) {
    	$errors = array();

    	if (isset($data['mobile'])) {
    		if (empty($files['mobilevideo'])) {
    			$errors['mobilevideo'] = va::str('erroruploadvideo');
    		}
    	}

    	return $errors;
    }
}
