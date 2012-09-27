<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once(dirname(__FILE__).'/db_functions.php');
require_once($CFG->dirroot.'/lib/ddllib.php');
require_once(dirname(__FILE__).'/prolib.php');
require_once($CFG->dirroot.'/user/filters/lib.php');

global $CFG;
global $USER;
global $DB;

$no_of_tabs = 5;

$type = optional_param('type', 1, PARAM_INT);

$context = get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_url('/mod/feedbackccna/dashboard.php?type='.$type);
$PAGE->set_context($context);
$PAGE->set_title('Dashboard');
$PAGE->set_heading('Dashboard');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add('Dashboard');

function get_modules_inner($s_id) {

// not finished / functional - 27/09/2012

    $select = "string".$s_id;
    $c_id = '<script type="text/javascript">'/*document.write(*/.'document.getElementById('.$select.').value'./*)*/'</script>';
echo $s_id.' '.$c_id;
    $array = get_feedback_failed_module($c_id, $s_id);
    print_r($array);

    $string = '<option value=1>HEllo</option>';
    return $string;

}

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

// categories
foreach ($groups as $group) {

    $temp = new stdClass();
    $temp->name = $group->name;
    $temp->id = $group->id;
    $temp->category = 1;
    $temp->parent = $group->parent;
    $group_array[$temp->category.$temp->id] = $temp->name;
    $groups_array2[$temp->category.$temp->id] = $temp;

}

// courses
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

$form = new dash_form(new moodle_url('/mod/feedbackccna/dashboard.php', array('type'=>$type)), array('group_array' => $group_array));

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

if ($type != 5) {

    $form->display();

}

if ($_POST and ($type != 5)) {

    $table =  new html_table();
    $table->tablealign = "center";

    // best student EU ^^
    if ($type == 1) {

        $table->head = array("Pozitie", "ID", "Prenume", "Nume", "Medie");

        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = $new_array;

        foreach ($new_array as $object) {

            $result = average_rating_student_percourse($object->id, $gr_array_id);
            $new_array2[$object->id]->value = round(reset($result)->rez, 2);

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

    // most feedback EU
    } elseif ($type == 2) {

        $table->head = array("Pozitie", "ID", "Prenume", "Nume", "Nr raspunsuri");

        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = $new_array;

        foreach ($new_array as $object) {
            $result = user_given_feedback_count($gr_array_id, $object->id);
            $new_array2[$object->id]->value = $result;
        }
        usort($new_array2, 'sortByVFL');
        $count = 1;
        foreach ($new_array2 as $object2) {
            $table->data[] = array($count++, $object2->id, $object2->firstname,
                $object2->lastname, $object2->value);
        }
        if($count > 1){
            echo html_writer::table($table);
        }else{
            echo get_string('absents','feedbackccna');
        }

    // best attendance EU
    } elseif ($type == 3) {

        $table->head = array("Pozitie", "ID", "Prenume", "Nume", "Nr prezente");

        $new_array = get_user_ids_in_courses_by_role($gr_array_id, 5);
        $new_array2 = $new_array;

        foreach ($new_array as $object) {
            $result = user_presence_count($gr_array_id, $object->id);
            $new_array2[$object->id]->value = $result;
        }
        usort($new_array2, 'sortByVFL');
        $count = 1;
        foreach ($new_array2 as $object2) {
            $table->data[] = array($count++, $object2->id, $object2->firstname,
                $object2->lastname, $object2->value);
        }
        if($count > 1){
            echo html_writer::table($table);}
        else{
            echo get_string('absents','feedbackccna');}

    // best team EU
    } elseif ($type == 4) {

        $table->head = array("Pozitie", "ID", "Nume curs", "Medie");

        $new_array = average_team_rating($gr_array_id);
        $new_array2 = $new_array;

        foreach ($new_array as $object) {

            $new_array2[$object->value]->value = round($object->value, 2);

        }

        usort($new_array2, 'sortByVN');

        $count = 1;

        foreach ($new_array2 as $object) {

            $table->data[] = array($count++, $object->id, $object->fullname,
                $object->value);

        }

        if($count > 1) {

            echo html_writer::table($table);

        } else {

            echo 'Nu s-a dat feedback pe cursurile selectate';

        }

    }

// re-enable feedback
} elseif ($type == 5) {

    // not finished / functional - 27/09/2012

    $page       = optional_param('page',0,PARAM_INT);
    $perpage    = optional_param('perpage',20,PARAM_INT);

    $baseurl = new moodle_url('/mod/feedbackccna/dashboard.php', array('type' => $type, 'perpage' => $perpage));

    $fields =  array('realname'=>0, 'lastname'=>1, 'firstname'=>1);

    $ufiltering = new user_filtering($fields, $baseurl);
    $ufiltering->display_add();
    $ufiltering->display_active();

    list($extrasql, $params) = $ufiltering->get_sql_filter();

    $users = get_users_listing('firstname','ASC',$page*$perpage,$perpage,'','','',$extrasql,$params);
    $usercount = get_users(false);
    $usersearchcount = get_users(false, '', false, null, "",'', '','','','*',$extrasql,$params);

    if ($extrasql) {

        echo $OUTPUT->heading("$usersearchcount / $usercount ". get_string('users'));
        $usercount = $usersearchcount;

    } else{

        echo $OUTPUT->heading("$usersearchcount ". get_string('users'));

    }

    echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);

    if (!$users) {

         echo 'Nu exista utilizatori care sa corespunda cautarii';

    } else {

        $table = new html_table();
        $table->head = array('Name', 'Course', 'Modules');
        $table->width = '40%';
        $table->align = array('center', 'center');
        $table->attributes["style"] = 'margin:auto; ';

        foreach ($users as $user) {

            $cell1 = new html_table_cell();
            $cell2 = new html_table_cell();
            $cell3 = new html_table_cell();
            $cell1->style = 'vertical-align:middle';
            $cell2->style = 'vertical-align:middle';
            $cell3->style = 'vertical-align:middle';

            $cell1->text = '<a href="'.$CFG->wwwroot.'/local/profile/personal.php?usr='.$user->id.'">'.$user->firstname.' '.$user->lastname.'</a>';
            $courses=get_courses_where_student($user->id);

            if ($courses) {

                $cell2->text = '<select id="select'.$user->id.'">';

                foreach($courses as $course) {

                    $cell2->text .= '<option value="'.$course->id.'">'.$course->fullname.'</option>';

                }

                $cell2->text .= '</select>';

                $script_select = "select".$user->id;
                echo '<script type="text/javascript">
                    document.getElementById("'.$script_select.'").onchange=
                    \'document.getElementById("'."modules".$user->id.'").innerHTML="'.get_modules_inner($user->id).'"\';
                </script>';

            }

            $cell3->text = '<select id="modules'.$user->id.'" ></select>';
            $table->data[] = array($cell1, $cell2, $cell3);

        }

        echo html_writer::table($table);
        echo $OUTPUT->paging_bar($usercount, $page, $perpage, $baseurl);

    }

    // 0 means all the students
    $student_id = 0;

    //$table->head = array("Pozitie", "ID", "Nume curs", "Medie");

    $new_array = get_closed_feed_modules($gr_array_id, 0);
    $new_array2 = $new_array;

    foreach ($new_array as $object) {

        //$new_array2[$object->value]->value = round($object->value, 2);

    }

    //usort($new_array2, 'sortByVN');

    $count = 1;

    foreach ($new_array2 as $object) {
/*
            $table->data[] = array($count++, $object->id, $object->fullname,
                $object->value);
 */
    }

}

echo $OUTPUT->footer();

