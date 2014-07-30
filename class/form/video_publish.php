<?php
namespace videoassess\form;

use \videoassess\va;
use \videoassess\video;

defined('MOODLE_INTERNAL') || die();

class video_publish extends \moodleform {
    /**
     *
     * @global \stdClass $CFG
     * @global \moodle_database $DB
     * @global \core_renderer $OUTPUT
     * @global \moodle_page $PAGE
     * @global \stdClass $USER
     */
    public function definition() {
        global $CFG, $DB, $OUTPUT, $PAGE, $USER;
        require_once $CFG->libdir . '/coursecatlib.php';

        $mform = $this->_form;
        /* @var $va \videoassess\va */
        $va = $this->_customdata->va;

        $mform->addElement('hidden', 'action', 'publish');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'id', $va->cm->id);
        $mform->setType('id', PARAM_INT);

        $courseopts = array();
        $categories = \coursecat::make_categories_list('moodle/course:create');
        if (!empty($categories)) {
            $courseopts[0] = '('.get_string('new').')';
        }
        $courses = \videoassess\va::get_courses_managed_by($USER->id);
        array_walk($courses, function (\stdClass $a) use (&$courseopts) {
            $courseopts[$a->id] = $a->fullname;
        });
        $mform->addElement('select', 'course', get_string('existingcourse', 'videoassessment'), $courseopts);
        $mform->addHelpButton('course', 'existingcourse', 'videoassessment');

        if (!empty($categories)) {
            $mform->addElement('static', 'courseor', get_string('or', 'videoassessment'));
            $mform->addElement('select', 'category', get_string('category'), $categories);
            $mform->addElement('text', 'fullname', get_string('fullnamecourse'), array('size' => 64));
            $mform->setType('fullname', PARAM_TEXT);
            $mform->addElement('text', 'shortname', get_string('shortnamecourse'), array('size' => 64));
            $mform->setType('shortname', PARAM_TEXT);
        }

        ob_start();
        $table = new \flexible_table('video-publish');
        $table->set_attribute('class', 'generaltable');
        $table->define_baseurl(new \moodle_url($va->viewurl, array('action' => 'publish')));
        $columns = array(
                'checkbox',
                'thumbnail',
        		'name',
        		'size',
                'grade'
        );
        $checkall = \html_writer::empty_tag('input', array(
        		'type' => 'checkbox',
        		'id' => 'all-video-check'
        ));
        $headers = array(
                $checkall,
                va::str('video'),
        		va::str('originalname'),
        		get_string('size'),
                get_string('grade')
        );
        $table->define_columns($columns);
        $table->define_headers($headers);
        $table->setup();

        $videorecs = $DB->get_records('videoassessment_videos', array('videoassessment' => $va->instance));
        $o = '';
        foreach ($videorecs as $videorec) {
            $video = new video($va->context, $videorec);

            if (empty($videorec->grade)) {
                $videorec->grade = -1;
            }
            $videorec->gradecell = '';

            $assocs = $va->get_video_associations($videorec->id);
            $gradecell = '';
            foreach ($assocs as $assoc) {
                if ($user = $DB->get_record('user', array('id' => $assoc->associationid))) {
                    $gradecell .= $OUTPUT->user_picture($user).' ';
                    $gradecell .= fullname($user).' ';
                }
                $grade = $va->get_aggregated_grades($assoc->associationid);
                $timing = $assoc->timing;
				$prop = 'grade' . $timing;
				if ($grade->$prop != -1) {
					$gradecell .= va::str($timing) . ': ' . $grade->$prop . ' ';

					$videorec->grade = max($videorec->grade, $grade->$prop);
				}
				$gradecell .= \html_writer::empty_tag('br');
            }
            $videorec->gradecell = $gradecell;

            $videorec->link = $video->render_thumbnail_with_preview();

            if ($video->has_file()) {
            	$videorec->filesize = $video->file->get_filesize();
            	$videorec->contenthash = $video->file->get_contenthash();
            } else {
            	$videorec->filesize = 0;
            	$videorec->contenthash = '';
            }
        }

        uasort($videorecs, function (\stdClass $a, \stdClass $b) {
        	return $b->grade - $a->grade;
        });

        foreach ($videorecs as $videorec) {
            $table->add_data(array(
                    \html_writer::checkbox('videos['.$videorec->id.']', 1, false, '',
                    		array('class' => 'video-check')),
                    $videorec->link,
            		$videorec->originalname,
            		display_size($videorec->filesize),
                    $videorec->gradecell
            ));
        }

        $table->finish_output();
        $o .= ob_get_contents();
        ob_end_clean();

        $mform->addElement('static', 'videos', va::str('publishvideos_videos'), $o);
        $mform->addHelpButton('videos', 'publishvideos_videos', va::VA);

        $this->add_action_buttons(false, va::str('publishvideos'));
    }

    /**
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!$data['course'] && !$data['fullname']) {
            $errors['fullname'] = va::str('inputnewcoursename');
        }

        return $errors;
    }
}
