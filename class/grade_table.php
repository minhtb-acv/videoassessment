<?php
/**
 * Video assessment
 *
 * @package videoassessment
 * @author  VERSION2 Inc.
 * @version $Id: grade_table.php 1031 2014-04-13 08:06:02Z malu $
 */

namespace videoassess;

class grade_table {
    /**
     * @var va
     */
    private $va;
    /**
     * @var int
     */
    public $instance;
    /**
     * @var \stdClass
     */
    public $data;
    /**
     * @var \stdClass
     */
    public $classes;
    /**
     * @var string
     */
    private $domid;
    /**
     * @var string
     */
    public $domclass = 'gradetable';
    /**
     * @var array
     */
    public $startcolumns = array('before' => 1, 'after' => 6);
    /**
     * @var string
    */
    public $usersort = 'u.firstname, u.lastname';
    /**
     * @var string
     */
    public $emptygradetext = '-';
    /**
     * @var string
     */
    public $hiddengradetext = '-';

    /**
     * @param va $videoassessment
     */
    function __construct(va $va) {
        $this->va = $va;
        $this->instance = $va->va->id;
        $this->cm = $va->cm;
    }

    /**
     * 教員用一覧表示
     */
    public function print_teacher_grade_table() {
        global $CFG, $DB;

        $this->domid = 'gradetableteacher';

        $this->setup_header();

        $cm = $this->cm;
        $context = $this->va->context;

        $users = $this->va->get_students();
        if (!empty($users)) {
            $users = array_keys($users);
        }

        // if groupmembersonly used, remove users who are not in any group
        if ($users and !empty($CFG->enablegroupmembersonly) and $cm->groupmembersonly) {
            if ($groupingusers = groups_get_grouping_members($cm->groupingid, 'u.id', 'u.id')) {
                $users = array_intersect($users, array_keys($groupingusers));
            }
        }

        if ($users) {
            $users = $this->va->get_students();

            foreach ($users as $user) {
                $agg = $this->va->get_aggregated_grades($user->id);
                    foreach (array(
                            'userid', 'gradebeforeteacher', 'gradebeforeself', 'gradebeforepeer', 'gradeafterteacher',
                            'gradeafterself', 'gradeafterpeer',
                            'gradebefore', 'gradeafter', 'videoassessment')
                            as $field) {
                        if ($agg) {
                            $user->$field = $agg->$field;
                        }
                }

                $this->add_user_data($user);
            }
        }

        return $this->print_html();
    }

    /**
     * 学生用表示
     */
    public function print_self_grade_table() {
        global $DB, $USER;

        $this->domid = 'gradetableself';

        $user = $this->va->get_aggregated_grades($USER->id);

        $this->setup_header();
        $this->add_user_data($user);

        if ($this->va->va->delayedteachergrade) {
            if ($user->gradebeforeself == -1) {
                $this->data[2][4] = $this->hiddengradetext;
                $this->data[2][5] = $this->hiddengradetext;
            }
        }

        return $this->print_html();
    }

    /**
     * 他学生一覧表示
     */
    public function print_peer_grade_table() {
        global $DB, $USER;

        $this->domid = 'gradetablepeer';

        $this->setup_header();

        $peers = $this->va->get_peers($USER->id);
        foreach ($peers as $peer) {
            $user = $this->va->get_aggregated_grades($peer);
            $this->add_user_data($user);
            if ($this->va->va->delayedteachergrade) {
                $row = count($this->data) - 1;
                if ($user->gradebeforeself == -1) {
                    $this->data[$row][4] = $this->hiddengradetext;
                    $this->data[$row][5] = $this->hiddengradetext;
                }
            }
        }

        return $this->print_html();
    }

    private function setup_header() {
        $this->data = array();
        $this->classes = array();

        $row1 = array();
        $row2 = array();

        $timing = 'before';
        $s = $this->startcolumns[$timing];
        $row1[$s + 1] = get_string('self', 'videoassessment');
        $row1[$s + 2] = get_string('peer', 'videoassessment');
        $row1[$s + 3] = get_string('teacher', 'videoassessment');
        $row1[$s + 4] = get_string('total', 'videoassessment');
        $row2[$s] = get_string('weighting', 'videoassessment');
        $row2[$s + 1] = $this->va->va->ratingself.'%';
        $row2[$s + 2] = $this->va->va->ratingpeer.'%';
        $row2[$s + 3] = $this->va->va->ratingteacher.'%';

        $this->add_data($row1);
        $this->add_data($row2);
    }

    /**
     *
     * @param array $row
     * @param string $class
     */
    private function add_data($row, $class = null) {
        $this->data[] = $row;
        $this->classes[] = $class;
    }

    /**
     *
     * @global \moodle_database $DB
     * @global \core_renderer $OUTPUT
     * @global \stdClass $USER
     * @param \stdClass $user
     */
    private function add_user_data($user) {
        global $DB, $OUTPUT, $USER;

        if (!isset($user->picture)) {
            $tmp = $DB->get_record('user', array('id' => $user->userid), \user_picture::fields());
            foreach (explode(',', \user_picture::fields()) as $field) {
                $user->$field = $tmp->$field;
            }
        }

        $row = array();
        $class = array();
        $row[0] = $OUTPUT->user_picture($user).' '.fullname($user);
        if ($this->va->is_user_graded($user->id)
                && ($this->va->is_teacher() || $user->id == $USER->id)) {
            $row[0] .= \html_writer::empty_tag('br')
                .$OUTPUT->action_link(new \moodle_url($this->va->viewurl,
                        array('action' => 'report', 'userid' => $user->id)),
                        va::str('seereport'));
						
			$url = new \moodle_url('/mod/videoassessment/print.php',
						array('id' => $this->va->cm->id, 'action' => 'report', 'userid' => $user->id));
			$row[0] .= \html_writer::empty_tag('br')
				.$OUTPUT->action_link($url, va::str('printreport'),
						new \popup_action('click', $url, 'popup',
                            array('width' => 800, 'height' => 700, 'menubar' => true)));
						
            if ($this->va->is_teacher()) {
                $row[0] .= \html_writer::empty_tag('br')
                    .$OUTPUT->action_link(new \moodle_url('/mod/videoassessment/managegrades.php',
                            array('id' => $this->va->cm->id, 'userid' => $user->id)),
                            va::str('managegrades'));
            }
        }
        //$pixresource = new \pix_icon('icon', get_string('pluginname', 'resource'), 'resource');
        //$pixdownload = new \pix_icon('t/download', get_string('download'));
        $strdownload = get_string('download');
        $mobile = va::uses_mobile_upload();

        $timing = 'before';
        $s = $this->startcolumns[$timing];
        $row[$s + 1] = $this->format_grade($user->{'grade'.$timing.'self'});
        $row[$s + 2] = $this->format_grade($user->{'grade'.$timing.'peer'});
        $row[$s + 3] = $this->format_grade($user->{'grade'.$timing.'teacher'});
        $row[$s + 4] = $this->format_grade($user->{'grade'.$timing});
        $class[0] = 'user';
        $class[$s + 1] = $class[$s + 2] = $class[$s + 3] = 'mark';
        $class[$s + 4] = 'totalmark';

        if ($video = $this->va->get_associated_video($user->id, $timing)) {
            $url = $video->get_url(true);
            $content = $video->render_thumbnail(va::str('previewvideo'));
            $row[$s] = \html_writer::tag(
                    'a', $content, array(
                            'onclick' => 'M.mod_videoassessment.videos_show_video_preview_by_user('.$user->id.',\''.$timing.'\')',
                            'href' => 'javascript:void(0)'
                    )
            );
            if ($this->va->is_teacher() ||
                $user->id == $USER->id && $this->is_emptygrade($user->{'grade'.$timing.'peer'})
                                       && $this->is_emptygrade($user->{'grade'.$timing.'teacher'}))
            {
                $str = $mobile ? va::str('retakevideo') : va::str('reuploadvideo');
                $row[$s] .= \html_writer::tag('div',
                    $OUTPUT->action_link(
                        new \moodle_url($this->va->viewurl, array('action' => 'upload', 'user' => $user->id, 'timing' => $timing)),
                        $str, null, array('class' => 'button-upload'))
                    );
            }
            if ($this->va->is_teacher()) {
                $row[$s] .= \html_writer::tag('div',
                    $OUTPUT->action_link($url, $strdownload, null, array('class' => 'button-download')),
                    array('style' => 'margin-top:5px'));
            }
        } else {
            if ($this->va->is_teacher() ||
                $user->id == $USER->id && $this->is_emptygrade($user->{'grade'.$timing.'peer'})
                                       && $this->is_emptygrade($user->{'grade'.$timing.'teacher'}))
            {
                $str = $mobile ? va::str('takevideo') : va::str('uploadvideo');
                $row[$s] = \html_writer::tag('div',
                    $OUTPUT->action_link(
                        new \moodle_url($this->va->viewurl, array('action' => 'upload', 'user' => $user->id, 'timing' => $timing)),
                        $str, null, array('class' => 'button-upload'))
                    );
            } else {
                $row[$s] = get_string('novideo', 'videoassessment');
            }
        }

        $type = $this->va->get_grader_type($user->id);

        if ($type) {
            switch ($type) {
                case 'self':
                    $linkcell = $s + 1;
                    break;
                case 'peer':
                    $linkcell = $s + 2;
                    break;
                case 'teacher':
                    $linkcell = $s + 3;
                    break;
            }

            //if ($user->{'grade'.$timing.$type} > -1) {
            if ($this->va->is_graded_by_current_user($user->id, $timing.$type)) {
                $button = 'assessagain';
            } else {
                $button = 'firstassess';
            }
            $url = new \moodle_url($this->va->viewurl,
                    array('action' => 'assess', 'userid' => $user->id));
            $row[$linkcell] .= '<br />' . $OUTPUT->action_link($url,
                    get_string($button, 'videoassessment'), null,
                    array('class' => 'button-'.$button));
        }
        $this->add_data($row, $class);
    }

    /**
     *
     * @return string
     */
    private function print_html() {
        $params = array();
        if ($this->domid) {
            $params['id'] = $this->domid;
        }
        if ($this->domclass) {
            $params['class'] = $this->domclass;
        }

        $o = '';

        $o .= groups_print_activity_menu($this->va->cm, $this->va->viewurl, true);

        $o .= '<h3 class="center">'.$this->va->str('scores').'</h3>';

        $o .= \html_writer::start_tag('table', $params);

        $columncount = 0;
        foreach ($this->data as $row) {
            $columncount = max($columncount, max(array_keys($row)) + 1);
        }

        $parity = 0;
        foreach ($this->data as $r => $row) {
            $o .= \html_writer::start_tag('tr', array('class' => 'r'.$parity));
            $parity ^= 1;

            for ($c = 0; $c < $columncount; $c++) {
                $text = '';
                $params = null;
                if (isset($this->data[$r][$c])) {
                    $text = $this->data[$r][$c];
                }
                if (!empty($this->classes[$r][$c])) {
                    $params['class'] = $this->classes[$r][$c];
                }
                $o .= \html_writer::tag('td', $text, $params);
            }
            $o .= \html_writer::end_tag('tr');
        }
        $o .= \html_writer::end_tag('table');

        return $o;
    }

    /**
     *
     * @param int|null $grade
     * @return string
     */
    private function format_grade($grade) {
        if ($this->is_emptygrade($grade)) {
            return $this->emptygradetext;
        }
        return $grade;
    }

    /**
     * 
     * @param int|null $grade
     * @return boolean
     */
    private function is_emptygrade($grade) {
        return $grade == -1 || $grade === null;
    }
}
