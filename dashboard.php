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

$no_of_tabs = 4;

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

$form = new dash_1_4_form(new moodle_url('/mod/feedbackccna/dashboard.php', array('type'=>$type)), array('group_array' => $group_array));

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

        } else {

            $gr_array[$course->id] = $course;
            $gr_array_id[] .= $course->id;

        }

    }

}

function sortByVFL($a, $b) {

    if ((($b->value)*100 - ($a->value)*100) == 0) {

        if (strcmp($a->firstname, $b->firstname) == 0) {

            return strcmp($a->lastname, $b->lastname);

        } else {

            return strcmp($a->firstname, $b->firstname);

        }

    } else {

        return (($b->value)*100 - ($a->value)*100);

    }

}

function sortByVN($a, $b) {

    if ((($b->value)*100 - ($a->value)*100) == 0) {

        return strcmp($a->fullname, $b->fullname);

    } else {

        return (($b->value)*100 - ($a->value)*100);

    }

}

// best student EU ^^
if ($type == 1) {

    $form->display();
    if ($_POST) {
        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = $new_array;

        $table =  new html_table();
	$table->tablealign = "center";
	$table->head = array("Pozitie", "ID", "Prenume", "Nume", "Medie");

        foreach ($new_array as $object) {

            $result = average_rating_student_percourse($object->id, $gr_array_id);
            $new_array2[$object->id]->value = reset($result)->rez;

        }

        usort($new_array2, 'sortByVFL');

        $count = 1;

        foreach ($new_array2 as $object2) {

            $table->data[] = array($count++, $object2->id, $object2->firstname,
                $object2->lastname, $object2->value);
        }

        if($count > 1) {

            echo html_writer::table($table);

        } else {

            echo get_string('absents','feedbackccna');

        }

    }

// most feedback EU
} elseif ($type == 2) {

	$form->display();
    if ($_POST) {
    	$new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = $new_array;

		$table =  new html_table();
		$table->tablealign = "center";
		$table->head = array(get_string("pozitie","feedbackccna"),
                             get_string("id_student","feedbackccna"),
                             get_string("prenume","feedbackccna"),
                             get_string("nume","feedbackccna"),
                             get_string("feedcount","feedbackccna"));

        foreach ($new_array as $object) {
			$result = user_given_feedback_count($gr_array_id, $object->id);
			$new_array2[$object->id]->value = $result;
        }
        usort($new_array2, 'sortByVFL');
        $count = 1;
        foreach ($new_array2 as $object2) {
	    $table->data[] = array($count++, $object2->id, $object2->lastname,
	    $object2->firstname, $object2->value);
        }
		if($count > 1)
			echo html_writer::table($table);
		else
            echo get_string('absents','feedbackccna');
    }

// best attendance EU
} elseif ($type == 3) {

    $form->display();
    if ($_POST) {
        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = $new_array;

		$table =  new html_table();
		$table->tablealign = "center";
		$table->head = array(get_string("pozitie","feedbackccna"),
                             get_string("id_student","feedbackccna"),
                             get_string("prenume","feedbackccna"),
                             get_string("nume","feedbackccna"),
                             get_string("attendance","feedbackccna"));

        foreach ($new_array as $object) {
			$result = user_presence_count($gr_array_id, $object->id);
            $new_array2[$object->id]->value = $result;
        }
        usort($new_array2, 'sortByVFL');
        $count = 1;
        foreach ($new_array2 as $object2) {
	    $table->data[] = array($count++, $object2->id, $object2->lastname,
	    $object2->firstname, $object2->value);
        }
		if($count > 1){
			echo html_writer::table($table);}
		else{
            echo get_string('absents','feedbackccna');}

    }

// best team EU
} elseif ($type == 4) {

    $form->display();

    if ($_POST) {

        $new_array = average_team_rating($gr_array_id);

        $table =  new html_table();
	$table->tablealign = "center";
	$table->head = array("Pozitie", "ID", "Prenume", "Nume", "Medie");

        usort($new_array, 'sortByVN');

        $count = 1;

        foreach ($new_array as $object) {

            $table->data[] = array($count++, $object->id, $object->fullname,
                $object->value);

        }

        if($count > 1) {

            echo html_writer::table($table);

        } else {

            echo 'Nu s-a dat feedback pe cursurile selectate';

        }

    }

}

echo $OUTPUT->footer();

