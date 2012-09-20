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

$group_array = array();
$groups_array = array();
$groups_array2 = array();

$temp = new stdClass();
$temp->name = 'All courses';
$group_array[] = $temp->name;
$temp->id = 0;
$temp->category = 1;
$temp->parent = 0;
$groups_array['10'] = $temp;
$groups_array2['10'] = $temp;

foreach ($groups as $group) {

    $temp = new stdClass();
    $temp->name = $group->name;
    $group_array[] = $temp->name;
    $temp->id = $group->id;
    $temp->category = 1;
    $temp->parent = $group->parent;
    $groups_array[$temp->category.$temp->id] = $temp;
    $groups_array2[$temp->category.$temp->id] = $temp;

}

foreach ($groups2 as $group) {

    $temp = new stdClass();
    $temp->name = $group->fullname;
    $group_array[] = $temp->name;
    $temp->id = $group->id;
    $temp->category = 0;
    $temp->parent = $group->category;
    $groups_array[$temp->category.$temp->id] = $temp;
    $groups_array2[$temp->category.$temp->id] = $temp;

}

$course_id = 0;
$category = 1;

$form = new dash_1_form(null, array('group_array' => $group_array));
if ($entry = $form->get_data() and confirm_sesskey($USER->sesskey)) {

    $course_id = $groups_array2[$entry->category.$entry->id]->id;
    $category = $groups_array2[$entry->category.$entry->id]->category;

}

if (isset($entry)) {

    echo $course_id." ".$category;

}
/*
print_r($groups_array);
echo '<br />';
 */
foreach ($groups_array as $course) {

    $ok = 1;

    if ($category == 1) {

        while ($course->id != $course_id) {

            if ($course->id == 0) {

                $ok = 0;
                break;

            } else {

                $course = $groups_array2['1'.$course->parent];

            }

        }

        if ($ok) {

            // get all the courses that are sub-categories of the above mentioned
            $gr_array[$course->id] = $course;

        }

    } else {

        if ($current->id != $course_id) {

            $ok = 0;

        }

    }

    //echo $ok." ";

}

// best student EU ^^
if ($type == 1) {

    if ($_POST) {

        print_r($gr_array);

    } else {

        $form->display();

    }

// most feedback EU
} elseif ($type == 2) {



// best attendance EU
} elseif ($type == 3) {



}

echo $OUTPUT->footer();

