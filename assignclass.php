<?php
/* MinhTB VERSION 2 */
namespace videoassess;

use \videoassess\va;
use \videoassess\form\assign_class;

require_once '../../config.php';
require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';
require_once $CFG->dirroot . '/mod/videoassessment/class/form/assign_class.php';

if (isset($_POST['sort']) && isset($_POST['id'])) {
    $sort = $_POST['sort'];
    $id = $_POST['id'];

    $cm = get_coursemodule_from_id('videoassessment', $id, 0, false, MUST_EXIST);

    $course = $DB->get_record('course', array('id' => $cm->course));
    $context = \context_module::instance($cm->id);
    $va = new \videoassess\va($context, $cm, $course);

    $students = $va->get_students_sort(true);

    if ($sort == assign_class::SORT_MANUALLY) {
        $i = 1;
        $html = '<ul id="id_manually">';
        $count_student = count($students);
        foreach ($students as $k => $student) {
            $sql = "
                UPDATE {user_enrolments} ue
                SET ue.order = :order
                WHERE ue.id = :id
            ";

            $params = array(
                'order' => $i,
                'id' => $student->ueid
            );

            $DB->execute($sql, $params);

            $html .= '<li data-ueid="' . $student->ueid . '" class="clearfix">';
            $html .= '<div class="name">' . fullname($student) . '</div>';
            $html .= '<div class="sort-button">';

            if ($i != 1)
                $html .= '<a href="#" class="up"><i class="fa fa-long-arrow-up"></i></a>';

            if ($i != $count_student)
                $html .= '<a href="#" class="down"><i class="fa fa-long-arrow-down"></i></a>';
            $html .= '</div>';
            $html .= '</li>';
            $i++;
        }

        $html .= '</ul>';
        echo $html; die;
    } else {
        die;
    }
}

if (isset($_POST['resort'])) {
    $ueid_1 = $_POST['ueid_1'];
    $ueid_2 = $_POST['ueid_2'];

    $ue_1 = $DB->get_record('user_enrolments', array('id' => $ueid_1));
    $ue_2 = $DB->get_record('user_enrolments', array('id' => $ueid_2));

    $sql_1 = "
        UPDATE {user_enrolments} ue
        SET ue.order = :order
        WHERE ue.id = :id
    ";

    $params_1 = array(
        'order' => $ue_2->order,
        'id' => $ueid_1
    );

    $sql_2 = "
        UPDATE {user_enrolments} ue
        SET ue.order = :order
        WHERE ue.id = :id
    ";

    $params_2 = array(
        'order' => $ue_1->order,
        'id' => $ueid_2
    );

    $DB->execute($sql_1, $params_1);
    $DB->execute($sql_2, $params_2);

    echo 1; die;
}

class page_assign_class extends page {
    public function execute() {
        $this->va->teacher_only();

        $this->view();
    }

    private function view() {
        global $CFG, $DB, $PAGE;

        $PAGE->requires->css('/mod/videoassessment/style.css');
        $PAGE->requires->css('/mod/videoassessment/font/font-awesome/css/font-awesome.min.css');
        $PAGE->requires->jquery();
        $PAGE->requires->js('/mod/videoassessment/assignclass.js');

        $cmid = optional_param('id', null, PARAM_INT);
        $cm = get_coursemodule_from_id('videoassessment', $cmid, 0, false, MUST_EXIST);

        $course = $DB->get_record('course', array('id' => $cm->course));
        $context = \context_module::instance($cm->id);
        $va = new \videoassess\va($context, $cm, $course);

        $students = $va->get_students_sort(true);

        $form = new assign_class(null, (object)array(
            'va' => $this->va,
            'sort' => $va->va->sort,
            'order' => $va->va->order,
            'students' => $students,
        ));

        if ($data = $form->get_data()) {
            $sortby = optional_param('sortby', $form::SORT_ID, PARAM_INT);
            $order = optional_param('order', $form::ORDER_ASC, PARAM_INT);

            $sql = "
                UPDATE {videoassessment} v
                SET v.sort = :sortby, v.order = :order
                WHERE v.id = :id
            ";

            $params = array(
                'sortby' => $sortby,
                'order' => $order,
                'id' => $this->va->va->id
            );

            $DB->execute($sql, $params);
            redirect($this->url);
        }

        echo $this->header();
        echo $this->output->heading(va::str('assignclass'));

        $form->display();

        echo $this->output->footer();
    }
}

$page = new page_assign_class('/mod/videoassessment/assignclass.php');
$page->execute();