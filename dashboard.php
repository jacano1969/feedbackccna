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

$url = new moodle_url('/mod/feedbackccna/dashboard.php', array('type'=>$type));
$PAGE->set_url($url);
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
//$groups_array['10'] = $temp;
$groups_array2['10'] = $temp;

foreach ($groups as $group) {

    $temp = new stdClass();
    $temp->name = $group->name;
    $temp->id = $group->id;
    $temp->category = 1;
    $temp->parent = $group->parent;
    $group_array[$temp->category.$temp->id] = $temp->name;
    //$groups_array[$temp->category.$temp->id] = $temp;
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
/*
print_r($groups_array);
echo "<br />";
 */
$course_id = 0;
$category = 1;

$form = new dash_1_form(null, array('group_array' => $group_array));
if ($entry = $form->get_data() and confirm_sesskey($USER->sesskey)) {

    $var = "select-one";
    $course_id = $groups_array2[$entry->$var]->id;
    $category = $groups_array2[$entry->$var]->category;

}

/*
if (isset($entry)) {

    echo $course_id." ".$category."<br />";

}
*/

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

            // get all the courses that are sub-categories of the above mentioned
            $gr_array[$course->id] = $course;
            $gr_array_id[] .= $course->id;

        }

    } else {

        if ($current->id != $course_id) {

            $ok = 0;

        }

    }

}

// best student EU ^^
if ($type == 1) {

    if ($_POST) {

        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);

        print_r($new_array);

    } else {

        $form->display();

    }

// most feedback EU
} elseif ($type == 2) {

    //TODO 2

// best attendance EU
} elseif ($type == 3) {

    //TODO 3

}

echo $OUTPUT->footer();

