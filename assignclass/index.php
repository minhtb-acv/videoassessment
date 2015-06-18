<?php
/* MinhTB VERSION 2 */
namespace videoassess;

use \videoassess\va;
use \videoassess\form\assign_class;

require_once '../../../config.php';
require_once('lib.php');
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

        $i = 1;
        $html = '<ul id="manually-list">';
        foreach ($students as $k => $student) {
            $sql = "
                UPDATE {user_enrolments} ue
                SET ue.order = :order
                WHERE ue.id = :id
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

$cmid = optional_param('id', null, PARAM_INT);
$cm = get_coursemodule_from_id('videoassessment', $cmid, 0, false, MUST_EXIST);

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
                SET t.order = :order
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

/* Group */

$courseid = $cm->course;
$groupid  = optional_param('group', false, PARAM_INT);
$userid   = optional_param('user', false, PARAM_INT);
$action   = groups_param_action();
// Support either single group= parameter, or array groups[]
if ($groupid) {
    $groupids = array($groupid);
} else {
    $groupids = optional_param_array('groups', array(), PARAM_INT);
}
$singlegroup = (count($groupids) == 1);

$returnurl = $CFG->wwwroot.'/mod/videoassessment/assignclass/index.php?id='.$courseid;

// Get the course information so we can print the header and
// check the course id is valid

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$url = new \moodle_url('/mod/videoassessment/assignclass/index.php', array('id'=>$courseid));
if ($userid) {
    $url->param('user', $userid);
}
if ($groupid) {
    $url->param('group', $groupid);
}
$PAGE->set_url($url);

// Make sure that the user has permissions to manage groups.
//require_login($course);

$context = \context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

$PAGE->requires->js('/mod/videoassessment/assignclass/clientlib.js');

// Check for multiple/no group errors
if (!$singlegroup) {
    switch($action) {
        case 'ajax_getmembersingroup':
        case 'showgroupsettingsform':
        case 'showaddmembersform':
        case 'updatemembers':
            print_error('errorselectone', 'group', $returnurl);
    }
}

switch ($action) {
    case false: //OK, display form.
        break;

    case 'ajax_getmembersingroup':
        $roles = array();
        if ($groupmemberroles = groups_get_members_by_role($groupids[0], $courseid, 'u.id, ' . get_all_user_name_fields(true, 'u'))) {
            foreach($groupmemberroles as $roleid=>$roledata) {
                $shortroledata = new stdClass();
                $shortroledata->name = $roledata->name;
                $shortroledata->users = array();
                foreach($roledata->users as $member) {
                    $shortmember = new stdClass();
                    $shortmember->id = $member->id;
                    $shortmember->name = fullname($member, true);
                    $shortroledata->users[] = $shortmember;
                }
                $roles[] = $shortroledata;
            }
        }
        echo json_encode($roles);
        die;  // Client side JavaScript takes it from here.

    case 'deletegroup':
        if (count($groupids) == 0) {
            print_error('errorselectsome','group',$returnurl);
        }
        $groupidlist = implode(',', $groupids);
        redirect(new \moodle_url('/mod/videoassessment/assignclass/delete.php', array('courseid'=>$courseid, 'groups'=>$groupidlist)));
        break;

    case 'showcreateorphangroupform':
        redirect(new \moodle_url('/mod/videoassessment/assignclass/group.php', array('courseid'=>$courseid)));
        break;

    case 'showautocreategroupsform':
        redirect(new \moodle_url('/mod/videoassessment/assignclass/autogroup.php', array('courseid'=>$courseid)));
        break;

    case 'showimportgroups':
        redirect(new \moodle_url('/mod/videoassessment/assignclass/import.php', array('id'=>$courseid)));
        break;

    case 'showgroupsettingsform':
        redirect(new \moodle_url('/mod/videoassessment/assignclass/group.php', array('courseid'=>$courseid, 'id'=>$groupids[0])));
        break;

    case 'updategroups': //Currently reloading.
        break;

    case 'removemembers':
        break;

    case 'showaddmembersform':
        redirect(new \moodle_url('/mod/videoassessment/assignclass/members.php', array('group'=>$groupids[0])));
        break;

    case 'updatemembers': //Currently reloading.
        break;

    default: //ERROR.
        print_error('unknowaction', '', $returnurl);
        break;
}

// Print the page and form
$strgroups = get_string('groups');
$strparticipants = get_string('participants');

/// Print header
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header($va);
echo $OUTPUT->heading(va::str('assignclass'));

$form->display();

// Add tabs
//$currenttab = 'groups';
//require('tabs.php');

$disabled = 'disabled="disabled"';

// Some buttons are enabled if single group selected.
$showaddmembersform_disabled = $singlegroup ? '' : $disabled;
$showeditgroupsettingsform_disabled = $singlegroup ? '' : $disabled;
$deletegroup_disabled = count($groupids) > 0 ? '' : $disabled;

echo $OUTPUT->heading(format_string($course->shortname, true, array('context' => $context)) .' '.$strgroups, 3);
echo '<form id="groupeditform" action="index.php" method="post">'."\n";
echo '<div>'."\n";
echo '<input type="hidden" name="id" value="' . $courseid . '" />'."\n";

echo '<table cellpadding="6" class="generaltable generalbox groupmanagementtable boxaligncenter" summary="">'."\n";
echo '<tr>'."\n";


echo "<td>\n";
echo '<p><label for="groups"><span id="groupslabel">'.get_string('groups').':</span><span id="thegrouping">&nbsp;</span></label></p>'."\n";

$onchange = 'M.core_group.membersCombo.refreshMembers();';

echo '<select name="groups[]" multiple="multiple" id="groups" size="15" class="select" onchange="'.$onchange.'"'."\n";
echo ' onclick="window.status=this.selectedIndex==-1 ? \'\' : this.options[this.selectedIndex].title;" onmouseout="window.status=\'\';">'."\n";

$groups = groups_get_all_groups($courseid);
$selectedname = '&nbsp;';
$preventgroupremoval = array();

if ($groups) {
    // Print out the HTML
    foreach ($groups as $group) {
        $select = '';
        $usercount = $DB->count_records('groups_members', array('groupid'=>$group->id));
        $groupname = format_string($group->name).' ('.$usercount.')';
        if (in_array($group->id,$groupids)) {
            $select = ' selected="selected"';
            if ($singlegroup) {
                // Only keep selected name if there is one group selected
                $selectedname = $groupname;
            }
        }
        if (!empty($group->idnumber) && !has_capability('moodle/course:changeidnumber', $context)) {
            $preventgroupremoval[$group->id] = true;
        }

        echo "<option value=\"{$group->id}\"$select title=\"$groupname\">$groupname</option>\n";
    }
} else {
    // Print an empty option to avoid the XHTML error of having an empty select element
    echo '<option>&nbsp;</option>';
}

echo '</select>'."\n";
echo '<p><input type="submit" name="act_updatemembers" id="updatemembers" value="'
    . get_string('showmembersforgroup', 'group') . '" /></p>'."\n";
echo '<p><input type="submit" '. $showeditgroupsettingsform_disabled . ' name="act_showgroupsettingsform" id="showeditgroupsettingsform" value="'
    . get_string('editgroupsettings', 'group') . '" /></p>'."\n";
echo '<p><input type="submit" '. $deletegroup_disabled . ' name="act_deletegroup" id="deletegroup" value="'
    . get_string('deleteselectedgroup', 'group') . '" /></p>'."\n";

echo '<p><input type="submit" name="act_showcreateorphangroupform" id="showcreateorphangroupform" value="'
    . get_string('creategroup', 'group') . '" /></p>'."\n";

echo '<p><input type="submit" name="act_showautocreategroupsform" id="showautocreategroupsform" value="'
    . get_string('autocreategroups', 'group') . '" /></p>'."\n";

echo '<p><input type="submit" name="act_showimportgroups" id="showimportgroups" value="'
    . get_string('importgroups', 'core_group') . '" /></p>'."\n";

echo '</td>'."\n";
echo '<td>'."\n";

echo '<p><label for="members"><span id="memberslabel">'.
    get_string('membersofselectedgroup', 'group').
    ' </span><span id="thegroup">'.$selectedname.'</span></label></p>'."\n";
//NOTE: the SELECT was, multiple="multiple" name="user[]" - not used and breaks onclick.
echo '<select name="user" id="members" size="15" class="select"'."\n";
echo ' onclick="window.status=this.options[this.selectedIndex].title;" onmouseout="window.status=\'\';">'."\n";

$member_names = array();

$atleastonemember = false;
if ($singlegroup) {
    if ($groupmemberroles = groups_get_members_by_role($groupids[0], $courseid, 'u.id, ' . get_all_user_name_fields(true, 'u'))) {
        foreach($groupmemberroles as $roleid=>$roledata) {
            echo '<optgroup label="'.s($roledata->name).'">';
            foreach($roledata->users as $member) {
                echo '<option value="'.$member->id.'">'.fullname($member, true).'</option>';
                $atleastonemember = true;
            }
            echo '</optgroup>';
        }
    }
}

if (!$atleastonemember) {
    // Print an empty option to avoid the XHTML error of having an empty select element
    echo '<option>&nbsp;</option>';
}

echo '</select>'."\n";

echo '<p><input type="submit" ' . $showaddmembersform_disabled . ' name="act_showaddmembersform" '
    . 'id="showaddmembersform" value="' . get_string('adduserstogroup', 'group'). '" /></p>'."\n";
echo '</td>'."\n";
echo '</tr>'."\n";
echo '</table>'."\n";

echo '</div>'."\n";
echo '</form>'."\n";

$PAGE->requires->js_init_call('M.core_group.init_index', array($CFG->wwwroot, $courseid));
$PAGE->requires->js_init_call('M.core_group.groupslist', array($preventgroupremoval));
/* End */

echo $OUTPUT->footer();

function groups_param_action($prefix = 'act_') {
    $action = false;
//($_SERVER['QUERY_STRING'] && preg_match("/$prefix(.+?)=(.+)/", $_SERVER['QUERY_STRING'], $matches)) { //b_(.*?)[&;]{0,1}/

    if ($_POST) {
        $form_vars = $_POST;
    }
    elseif ($_GET) {
        $form_vars = $_GET;
    }
    if ($form_vars) {
        foreach ($form_vars as $key => $value) {
            if (preg_match("/$prefix(.+)/", $key, $matches)) {
                $action = $matches[1];
                break;
            }
        }
    }
    if ($action && !preg_match('/^\w+$/', $action)) {
        $action = false;
        print_error('unknowaction');
    }
    ///if (debugging()) echo 'Debug: '.$action;
    return $action;
}
