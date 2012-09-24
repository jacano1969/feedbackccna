<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once(dirname(__FILE__).'/db_functions.php');

global $CFG;
global $USER;
global $DB;

$no_of_tabs = 3;

$type = optional_param('type', 1, PARAM_INT);
$context = get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_url('/mod/feedbackccna/dashboard.php?type='.$type);
$PAGE->set_context($context);
$PAGE->set_title('Dashboard');
$PAGE->set_heading('Dashboard');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add('Dashboard');

echo $OUTPUT->header();

build_tabs_local('dash_'.$type, $no_of_tabs);

$groups = $DB->get_records('course_categories', null, 'path ASC');
$groups2 = $DB->get_records('course');

$gr_array = array();
$gr_array_id = array();
$group_array = array();
$groups_array = array();
$groups_array2 = array();

$temp = new stdClass();
$temp->name = 'All courses';
$temp->id = 0;
$temp->category = 1;
$temp->parent = 0;
$group_array['10'] = $temp->name;
$groups_array2['10'] = $temp;

foreach ($groups as $group) {

    $temp = new stdClass();
    $temp->name = $group->name;
    $temp->id = $group->id;
    $temp->category = 1;
    $temp->parent = $group->parent;
    $group_array[$temp->category.$temp->id] = $temp->name;
    $groups_array2[$temp->category.$temp->id] = $temp;

}

foreach ($groups2 as $group) {

    $temp = new stdClass();
    $temp->name = $group->fullname;
    $temp->id = $group->id;
    $temp->category = 0;
    $temp->parent = $group->category;
    $group_array[$temp->category.$temp->id] = $temp->name;
    $groups_array[$temp->category.$temp->id] = $temp;
    $groups_array2[$temp->category.$temp->id] = $temp;

}

$course_id = 0;
$category = 1;
switch($type) {
	case 1: $form = new dash_1_form(new moodle_url('/mod/feedbackccna/dashboard.php', array('type'=>$type)), array('group_array' => $group_array)); break;
	case 2: $form = new dash_2_form(new moodle_url('/mod/feedbackccna/dashboard.php', array('type'=>$type)), array('group_array' => $group_array)); break;
	case 3: $form = new dash_3_form(new moodle_url('/mod/feedbackccna/dashboard.php', array('type'=>$type)), array('group_array' => $group_array)); break;
}

if ($entry = $form->get_data() and confirm_sesskey($USER->sesskey)) {
    $var = "select-one";
    $course_id = $groups_array2[$entry->$var]->id;
    $category = $groups_array2[$entry->$var]->category;
}

foreach ($groups_array as $course) {

    $ok = 1;
    $current = $course;

    if ($category == 1) {
        while ($current->id != $course_id) {
            if ($current->id == 0) {
                $ok = 0;
                break;
            } else {
                $current = $groups_array2['1'.$current->parent];
            }
        }
        if ($ok) {
            $gr_array[$course->id] = $course;
            $gr_array_id[] .= $course->id;
        }
    } else {
        if ($current->id != $course_id) {
            $ok = 0;
        }
		else {
			$gr_array[$course->id] = $course;
            $gr_array_id[] .= $course->id;
		}
    }
}

function sortByValue($a, $b) {
    return ($b->value)*100 - ($a->value)*100;
}

// best student EU ^^
if ($type == 1) {

    $form->display();

    if ($_POST) {

        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = get_user_ids_in_courses_by_role($gr_array_id, 5);

        $count = 0;

        echo "<table>";
        echo "<strong><tr><td>Pozitie</td><td>ID student</td><td>Prenume</td><td>Nume</td><td>Medie</td></tr></strong>";

        if (!isset($new_array)) {
            echo '</table><br />';
            echo 'Nu exista studenti inrolati in cursurile selectate';
        } else {
        foreach ($new_array as $object) {
            if ($count == 10) {
                break;
            }
            foreach (average_rating_student_percourse($object->id, $gr_array_id) as $avg_object) {
               	$result = round($avg_object->rez, 3);
           	}
            $new_array2[$object->id]->value = $result;
            $count ++;
        }
        usort($new_array2, 'sortByValue');
        $count = 1;
        foreach ($new_array2 as $object2) {
            echo '<tr><td>'.($count++);
            echo '</td><td>'.$object2->id;
            echo '</td><td>'.$object2->firstname;
            echo '</td><td>'.$object2->lastname;
            echo '</td><td>'.$object2->value;
            echo '</td></tr>';
        }
        echo "</table>";
    } 
}
// most feedback EU
} elseif ($type == 2) {
     if ($_POST) {
        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = get_user_ids_in_courses_by_role($gr_array_id, 5);

        $count = 0;

        echo "<table>";
        echo "<strong><tr><td>Pozitie</td><td>ID student</td><td>Prenume</td><td>Nume</td><td>Nr Feedback-uri</td></tr></strong>";

        foreach ($new_array as $object) {
            if ($count == 10) {
                break;
            }
			$result = user_given_feedback_count($gr_array_id, $object->id);
			$new_array2[$object->id]->value = $result;
            $count ++;
        }
        usort($new_array2, 'sortByValue');
        $count = 1;
        foreach ($new_array2 as $object2) {
            echo '<tr><td>'.($count++);
            echo '</td><td>'.$object2->id;
            echo '</td><td>'.$object2->firstname;
            echo '</td><td>'.$object2->lastname;
            echo '</td><td>'.$object2->value;
            echo '</td></tr>';
        }
        echo "</table>";
    } else {
        $form->display();
    }
// best attendance EU
} elseif ($type == 3) {
    if ($_POST) {
        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $count = 0;
        echo "<table>";
        echo "<strong><tr><td>Pozitie</td><td>ID student</td><td>Prenume</td><td>Nume</td><td>Nr prezente</td></tr></strong>";
        foreach ($new_array as $object) {
            if ($count == 10) {
                break;
            }
			$result = user_presence_count($gr_array_id, $object->id);
            $new_array2[$object->id]->value = $result;
            $count ++;
        }
        usort($new_array2, 'sortByValue');
        $count = 1;
        foreach ($new_array2 as $object2) {
            echo '<tr><td>'.($count++);
            echo '</td><td>'.$object2->id;
            echo '</td><td>'.$object2->firstname;
            echo '</td><td>'.$object2->lastname;
            echo '</td><td>'.$object2->value;
            echo '</td></tr>';
        }
        echo "</table>";
    } else {
        $form->display();
    }
}

echo $OUTPUT->footer();


