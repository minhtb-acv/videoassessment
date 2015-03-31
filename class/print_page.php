<?php
namespace videoassess;

defined('MOODLE_INTERNAL') || die();

class print_page {
    /**
     *
     * @var va
     */
    private $va;
    /**
     *
     * @var \mod_videoassessment_print_renderer
     */
    private $output;

    public function __construct(va $va) {
        global $PAGE;

        $PAGE->set_pagelayout('embedded');
        $this->va = $va;
        $this->output = $PAGE->get_renderer('mod_videoassessment', 'print');

        $PAGE->set_title($this->va->va->name);

        $PAGE->requires->css('/mod/videoassessment/styles-print.css');
    }

    public function do_action() {
        $action = optional_param('action', null, PARAM_ALPHA);

        switch ($action) {
            case 'report':
                $this->rubric_report();
                break;
        }
    }

    private function rubric_report() {
        global $OUTPUT, $PAGE, $DB;

        echo $this->output->header();
        $o = '';

        $userid = optional_param('userid', 0, PARAM_INT);

        $rubric = new rubric($this->va);

        if ($userid) {
            $users = array($DB->get_record('user', array('id' => $userid), 'id, lastname, firstname'));
        } else {
            $users = $this->va->get_students();
        }

        $firstpage = true;
        foreach ($users as $user) {
            $userid = $user->id;

            $gradingstatus = $this->va->get_grading_status($userid);
            $usergrades = $this->va->get_aggregated_grades($userid);
            if (!$gradingstatus->any) {
                continue;
            }

            if ($firstpage) {
                $firstpage = false;
            } else {
                $o .= \html_writer::tag('div', '', array('class' => 'pagebreak'));
            }

            $o .= $OUTPUT->heading(fullname($user));
            $o .= \html_writer::start_tag('div', array('class' => 'report-rubrics'));
            foreach ($this->va->timings as $timing) {
                if (!$gradingstatus->$timing) {
                    continue;
                }

                $o .= $OUTPUT->heading($this->va->str('allscores'), 3);
                $timinggrades = array();
                foreach ($this->va->gradertypes as $gradertype) {
                    $gradingarea = $timing.$gradertype;
                    $o .= $OUTPUT->heading(
                            $this->va->timing_str($timing, null, 'ucfirst').' - '.va::str($gradertype),
                            4, 'main', 'heading-'.$gradingarea);
                    $gradinginfo = grade_get_grades($this->va->course->id, 'mod', 'videoassessment',
                            $this->va->instance, $userid);
                    $o .= \html_writer::start_tag('div', array('id' => 'rubrics-'.$gradingarea));
                    if ($controller = $rubric->get_available_controller($gradingarea)) {
                        $gradeitems = $this->va->get_grade_items($gradingarea, $userid);
                        foreach ($gradeitems as $gradeitem) {
                            $o .= $controller->render_grade($PAGE, $gradeitem->id, $gradinginfo, '', false);

                            $timinggrades[] = \html_writer::tag('span', (int)$gradeitem->grade, array('class' => 'rubrictext-'.$gradertype));

                            if ($gradeitem->submissioncomment) {
                                $grader = $DB->get_record('user', array('id' => $gradeitem->grader));
                                $o .= \html_writer::start_tag('div', array('class' => 'comment comment-'.$gradertype))
                                    .$OUTPUT->user_picture($grader)
                                    .' '.fullname($grader)
                                    .\html_writer::empty_tag('br')
                                    .format_text($gradeitem->submissioncomment)
                                    .\html_writer::end_tag('div');
                            }
                        }
                    }
                    $o .= \html_writer::end_tag('div');
                }
                if ($timinggrades) {
                    $timinggrades[] = \html_writer::tag('span', (int)$usergrades->{'grade'.$timing}, array('class' => 'rubrictext-total'));
                	$o .= $OUTPUT->container(get_string('grade').': '.implode(', ', $timinggrades), 'finalgrade');
                }
            }
            $o .= \html_writer::end_tag('div');
        }

        $PAGE->requires->js_init_call('M.mod_videoassessment.report_combine_rubrics', null, false,
                $this->va->jsmodule);
        $PAGE->requires->js_init_call('M.mod_videoassessment.init_print');

        echo $o;

        $PAGE->blocks->show_only_fake_blocks();
        echo $this->output->footer();
    }
}
