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
$PAGE->set_title('Dashboard');
$PAGE->set_heading('Dashboard');
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add('Dashboard');

echo $OUTPUT->header();

build_tabs_local('dash_'.$type, $no_of_tabs);

$groups = $DB->get_records('course_categories',null,'path ASC');

foreach ($groups as $group) {

    if ($group->parent == 0) {

        $hierarchy[$group->id] = $group->name;

    } else {

        $i = $group->id;
        $tok = strtok($group->path,'/');
        $hierarchy[$i] = '';

        while ($tok !== false) {

            foreach ($groups as $group) {

                if ($group->id == $tok) {

                    $hierarchy[$i].= $group->name.'/';

                }

            }

            $tok = strtok("/");

        }

    }

}

asort($hierarchy);

// aici bagam un select - id 0 pentru toate cursurile, id-ul cursului/categoriei pentru toti copiii
// also, daca am selectat o categorie, category trebuie sa fie 1
// presupunem, for starters:
$course_id = 0;
$category = 0;

$categories = $DB->get_records('course_categories');
$courses = $DB->get_records('course');

foreach ($courses as $course) {

    $ok = 1;

    $current = $DB->get_records_sql("SELECT * FROM {course} WHERE id = ?", array($course->id));
    $current = $current[$course->id];

    if ($category == 1) {

        while ($current->id != $course_id) {

            if ($current->id == 0) {

                $ok = 0;
                break;

            } else {

                if (isset($current->parent)) {

                    $parent = $current->parent;

                } else {

                    $parent = $current->category;

                }

                $current = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE id = ?", array($parent));
                $current = $current[$parent];

            }

        }

        if ($ok) {

            // get all the courses that are sub-categories of the above mentioned
            $group_array[$course->id] = $course;

        }

    } else {

        if (($current->id != $course_id) and ($course_id != 0)) {

            $ok = 0;

        }

    }

    echo $ok." ";

}

// best student EU ^^
if ($type == 1) {



// most feedback EU
} elseif ($type == 2) {



// best attendance EU
} elseif ($type == 3) {



}

echo $OUTPUT->footer();

