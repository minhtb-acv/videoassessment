<?php
/* MinhTB VERSION 2 */
namespace videoassess;

use \videoassess\va;
use \videoassess\form\assign_class;

require_once '../../../config.php';
require_once $CFG->dirroot . '/mod/videoassessment/locallib.php';
require_once $CFG->dirroot . '/mod/videoassessment/class/form/assign_class.php';

$cmid = optional_param('id', null, PARAM_INT);
$cm = get_coursemodule_from_id('videoassessment', $cmid, 0, false, MUST_EXIST);
require_login($cm->course, true, $cm);

if (isset($_POST['sort']) && isset($_POST['id']) && isset($_POST['groupid'])) {
    $sort = $_POST['sort'];
    $groupid = $_POST['groupid'];
    $id = $_POST['id'];

    $cm = get_coursemodule_from_id('videoassessment', $id, 0, false, MUST_EXIST);

    $course = $DB->get_record('course', array('id' => $cm->course));
    $context = \context_module::instance($cm->id);
    $va = new \videoassess\va($context, $cm, $course);

    if ($sort == assign_class::SORT_MANUALLY) {
        $students = $va->get_students_sort($groupid, true);

        if (!empty($groupid)) {
            $table = '{groups_members}';
        } else {
            $table = '{user_enrolments}';
        }

        $i = 1;
        $html = '<ul id="manually-list">';
        foreach ($students as $k => $student) {
            $sql = "
                UPDATE $table t
                SET t.sortorder = :order
                WHERE t.id = :id
            ";

            $params = array(
                'order' => $i,
                'id' => $student->orderid
            );

            $DB->execute($sql, $params);

            $html .= '<li data-orderid="' . $student->orderid . '" class="clearfix">';
            $html .= '<div class="name">' . fullname($student) . '</div>';
            $html .= '</li>';
            $i++;
        }

        $html .= '</ul><div id="manually-hidden"></div>';

        $html .= "<script type='text/javascript'>";
        $html .= "
        group = $('#manually-list').sortable({
                group: 'manually-list',
                onDrop: function(item, container, _super) {
                    var data = group.sortable('serialize').get();

                    var html = '';
                    for (x in data[0]) {
                        var obj = data[0][x];
                        html += '<input type=\"hidden\" name=\"orderid[]\" value=\"' + obj.orderid + '\" />';
                    }

                    $('#manually-hidden').html(html);

                    _super(item, container);
                }
            });
        ";
        $html .= "</script>";
    } else {
        $order_sql = '';
        if ($sort == assign_class::SORT_NAME) {
            $order_sql .= ' ORDER BY CONCAT(u.firstname, " ", u.lastname)';
        } else {
            $order_sql .= ' ORDER BY u.id';
        }

        $order_sql .= ' ASC';

        $students = $va->get_students_sort($groupid, false, $order_sql);

        $html = '<ul class="id_order_students">';
        foreach ($students as $k => $student) {
            $html .= '<li class="clearfix">';
            $html .= '<div class="name">' . fullname($student) . '</div>';
            $html .= '</li>';
        }

        $html .= '</ul>';
    }

    echo $html; die;
}

$course = $DB->get_record('course', array('id' => $cm->course));
$context = \context_module::instance($cm->id);
$va = new \videoassess\va($context, $cm, $course);

$va->teacher_only();

$PAGE->requires->css('/mod/videoassessment/style.css');
$PAGE->requires->css('/mod/videoassessment/font/font-awesome/css/font-awesome.min.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/videoassessment/jquery-sortable.js', true);
$PAGE->requires->js('/mod/videoassessment/assignclass/assignclass.js');

$url = new \moodle_url('/mod/videoassessment/assignclass/index.php', array('id' => $cm->id));
$PAGE->set_url($url);

$students = $va->get_students_sort(true);

$groups = $DB->get_records('groups', array('courseid' => $course->id), '', 'id, name');
$groupid = optional_param('groupid', 0, PARAM_INT);
$group = $DB->get_record('groups', array('id' => $groupid), 'sortby');
$sortby = (empty($groupid)) ? $course->sortby : $group->sortby;

$form = new assign_class(null, (object)array(
    'va' => $va,
    'sortby' => $sortby,
    'students' => $students,
    'groups' => $groups,
    'groupid' => $groupid
));

if ($data = $form->get_data()) {
    $sortby = optional_param('sortby', $form::SORT_ID, PARAM_INT);
    $groupid = optional_param('groupid', 0, PARAM_INT);
    $orderid_arr = optional_param_array('orderid', array(), PARAM_INT);

    if (!empty($orderid_arr)) {
        $i = 1;

        if (!empty($groupid)) {
            $table = '{groups_members}';
        } else {
            $table = '{user_enrolments}';
        }

        foreach ($orderid_arr as $orderid) {
            $sql = "
                UPDATE $table as t
                SET t.sortorder = :order
                WHERE t.id = :id
            ";

            $params = array(
                'order' => $i,
                'id' => $orderid
            );

            $DB->execute($sql, $params);
            $i++;
        }
    }

    if (!empty($groupid)) {
        $table = '{groups}';
        $url .= '&groupid=' . $groupid;
    } else {
        $table = '{course}';
    }

    $sql = "
        UPDATE $table
        SET sortby = :sortby
        WHERE id = :id
    ";

    $id = (!empty($groupid)) ? $groupid : $course->id;

    $params = array(
        'sortby' => $sortby,
        'id' => $id
    );

    $DB->execute($sql, $params);
    redirect($url);
}

echo $OUTPUT->header($va);
echo $OUTPUT->heading(va::str('assignclass'));

$form->display();

echo $OUTPUT->footer();
