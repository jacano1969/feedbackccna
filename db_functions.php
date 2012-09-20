<?php

require_once('defines.php');

defined('MOODLE_INTERNAL') || die();

//	functie care insereaza un modul de feedback
//	- instructor_id - id-ul instructorului care adauga modulul
//	- denumire - denumirea modulului
//	- section - sectiunea din curs unde se afla modulul
//	- course_id - id-ul cursului unde se afla modulul
//	- which_way - daca feedback-ul este dat de profesor studentului sau invers
function insert_feedback_module($instructor_id, $feedback_id, $course_id, $section, $denumire, $which_way, $type) {
    global $DB;

    // inserez modulul de feedback cu drepturile care trebuie, pentru a fi afisat
    $allow = FEED_NOT_ALLOWED;
    if( $which_way == STUDENT_FOR_TEACHER) $allow = DEFAULT_FEED_ALLOWED;
    elseif( $which_way == TEACHER_FOR_STUDENT) $allow = FEED_ALLOWED;

    $record = new stdClass();
    $record->feedback_id = $feedback_id;
    $record->instructor_id = $instructor_id;
    $record->denumire = $denumire;
    $record->allow = $allow;
    $record->section = $section;
    $record->course_id = $course_id;
    $record->which_way = $which_way;
    $record->type = $type;

    $DB->insert_record("feedbackccna_module", $record);
}

function delete_feedback_module($course_id, $section, $feedback_id) {
    global $DB;

    $modis = get_feedback_module_id($course_id, $section, $feedback_id);
    foreach($modis as $id) {
        $DB->delete_records('feedbackccna_answer', array('module_id'=>$id->id));
        $DB->delete_records('feedbackccna_module', array('id'=>$id->id));
    }
}

//  functie de inserat automat intrare unui modul
function setup_feedback_module($feedback, $instructor_id) {

    insert_feedback_module($instructor_id, 0,
        $feedback->course, $feedback->section,
        $feedback->name, STUDENT_FOR_TEACHER, FEED_TYPE_PRE);
    insert_feedback_module($instructor_id, 0,
        $feedback->course, $feedback->section,
        $feedback->name, TEACHER_FOR_STUDENT, FEED_TYPE_PRE);
    insert_feedback_module($instructor_id, 0,
        $feedback->course, $feedback->section,
        $feedback->name, STUDENT_FOR_TEACHER, FEED_TYPE_LAB);
    insert_feedback_module($instructor_id, 0,
        $feedback->course, $feedback->section,
        $feedback->name, TEACHER_FOR_STUDENT, FEED_TYPE_LAB);

}

//	functie de inserare raspuns la intrebare
//	- module_id - id-ul modulului la care s-a raspuns
//	- student_id - id-ul studentului care a raspuns
//	- answer - valoarea efectiva a raspunsului
function insert_feedback_answer($module_id, $student_id, $answer) {
    global $DB;

    $record = new stdClass();
    $record->module_id = $module_id;
    $record->student_id = $student_id;
    $record->answer = $answer;

    $DB->insert_record("feedbackccna_answer",$record);
}

function update_feedback_answer($id, $module_id, $student_id, $answer) {
    global $DB;

    $record = new stdClass();
    $record->id = $id;
    $record->module_id = $module_id;
    $record->student_id = $student_id;
    $record->answer = $answer;

    $DB->update_record("feedbackccna_answer", $record);
}

function delete_feedback_answer($id) {
    global $DB;

    $record = array('id' => $id);

    $DB->delete_records("feedbackccna_answer", $record);
}

//	functie de schimbat valoarea campului allow din modulul de feedback
//	- module_id - id-ul modulului de modificat
//	- allow - noua valoare a campului allow
function set_allow_feedback($module_id, $allow) {
    global $DB;

    $DB->update_record("feedbackccna_module", array('id'=>$module_id, 'allow'=>$allow));
}

//	functie de obtinut modulul de feedback dintr-un curs si o sectiune
//	- course_id -id-ul cursului
//	- section - saptamana sau topicul
//	- which_way - daca feedback-ul este dat de student pt profesor sau invers
function get_feedback_module($course_id, $section, $which_way) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT * FROM {feedbackccna_module}
        WHERE course_id = ?
        AND section = ?
        AND which_way = ?
        AND allow='".FEED_ALLOWED."'",
        array($course_id, $section, $which_way));
}

function get_feedback_module_id($course_id, $section, $feedback_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT id FROM {feedbackccna_module}
        WHERE course_id = ?
        AND section = ?
        AND feedback_id = ?",
        array($course_id, $section, $feedback_id));
}

// functie de hack - MihaiZ nu stie ce spune
function get_correct_section($section_id) {
    global $DB;

    return $DB->get_field_sql("SELECT section FROM {course_sections} WHERE id='".$section_id."'");
}

//	functie de obtinut modulul de feedback dintr-un curs si o sectiune
//	- course_id -id-ul cursului
//	- section - saptamana sau topicul
//	- which_way - daca feedback-ul este dat de student pt profesor sau invers
function get_feedback_module_teacher($course_id, $section, $f_id, $which_way) {
    global $DB;

    $section = get_correct_section($section);
    return $DB->get_records_sql(
        "SELECT * FROM {feedbackccna_module}
        WHERE course_id = ?
        AND section = ?
        AND feedback_id = ?
        AND which_way = ?",
        array($course_id, $section, $f_id, $which_way));
}

//	functie de obtinut rating mediu pentru un curs pe tip de intrebare
//	- course_id - id-ul cursului
//	- type - tipul itemului pt care se doreste rezultatul
function average_course_rating_pertype($course_id, $type) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.course_id = ?
        AND m.which_way='".STUDENT_FOR_TEACHER."'
        AND m.type = ?", array($course_id, $type));
}

//	functie de obtinut rating mediu pentru un curs
//	- course_id - id-ul cursului
function average_course_rating($course_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.course_id = ?
        AND m.which_way='".STUDENT_FOR_TEACHER."'",
        array($course_id));
}

//	functie de obtinut rating mediu pentru un profesor pe fiecare tip de feedback
//	- instructor_id - id-ul instructorului
//	- type - tipul itemului pt care se doreste rezultatul
function average_instructor_rating_pertype($instructor_id, $type) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.instructor_id = ?
        AND m.which_way='".STUDENT_FOR_TEACHER."'
        AND m.type = ?", array($instructor_id, $type));
}

//	functie de obtinut rating mediu pentru un profesor
//	- instructor_id - id-ul instructorului
//	- type - tipul itemului pt care se doreste rezultatul
function average_instructor_rating($instructor_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.instructor_id = ?
        AND m.which_way='".STUDENT_FOR_TEACHER."'" ,
        array($instructor_id));
}

//	functie de obtinut rating mediu pentru un profesor pe fiecare tip de feedback pe fiecare curs
//	- instructor_id - id-ul instructorului
//	- type - tipul itemului pt care se doreste rezultatul
//	- course_id - id-ul cursului
function average_instructor_rating_pertype_percourse($instructor_id, $type, $course_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.instructor_id = ?
        AND m.which_way='".STUDENT_FOR_TEACHER."'
        AND m.type = ?
        AND m.course_id = ?",
        array($instructor_id, $type, $course_id));
}

//	functie de obtinut rating mediu pentru un profesor pe fiecare curs
//	- instructor_id - id-ul instructorului
//	- course_id - tipul itemului pt care se doreste rezultatul
function average_instructor_rating_percourse($instructor_id, $course_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.instructor_id = ?
        AND m.which_way='".STUDENT_FOR_TEACHER."'
        AND m.course_id = ?",
        array($instructor_id, $course_id));
}

//  functie de obtinut valoare medie feedback student
//  - student_id
function average_rating_student($student_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE  m.which_way='".TEACHER_FOR_STUDENT."'
        AND a.student_id = ?", array($student_id));
}

//  functie de obtinut valoare medie feedback student pe fiecare item
//  - student_id
//  - type
function average_rating_student_pertype($student_id, $type) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE  m.which_way='".TEACHER_FOR_STUDENT."'
        AND a.student_id = ?
        AND m.type = ?", array($student_id, $type));
}
//  functie de obtinut valoare medie
//  - student_id
//  - course_id
function average_rating_student_percourse($student_id, $course_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE  m.which_way='".TEACHER_FOR_STUDENT."'
        AND a.student_id = ?
        AND m.course_id = ?",
        array($student_id, $course_id));
}

//  functie de obtinut valoare medie feedback student
//  - student_id
//  - type
//  - course_id
function average_rating_student_pertype_percourse($student_id, $type, $course_id) {
    global $DB;

    return $DB->get_records_sql(
        "SELECT AVG(a.answer) rez
        FROM {feedbackccna_answer} a
        INNER JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        WHERE m.which_way='".TEACHER_FOR_STUDENT."'
        AND a.student_id = ?
        AND m.type = ?
        AND m.course_id = ?",
        array($student_id, $type, $course_id));
}

//	functie de obtinut nr de laboratoare completate de un utilizator
//	- course_id
//	- student_id
function get_user_feedback_count($course_id, $student_id, $type) {
    global $DB;

    return $DB->count_records_sql(
        "SELECT COUNT(*) FROM {feedbackccna_module} m
        INNER JOIN {feedbackccna_answer} a
        ON m.id = a.module_id
        WHERE m.which_way ='".TEACHER_FOR_STUDENT."'
        AND m.type='".$type."'
        AND a.student_id = '".$student_id."'
        AND m.course_id='".$course_id."'");
}

function get_user_answer_count($course_id, $type, $f_id) {
    global $DB;

    return $DB->count_records_sql(
        "SELECT COUNT(*) FROM {feedbackccna_module} m
        INNER JOIN {feedbackccna_answer} a
        ON m.id = a.module_id
        WHERE m.which_way ='".STUDENT_FOR_TEACHER."'
        AND m.type='".$type."'
        AND m.course_id='".$course_id."'
        AND m.feedback_id = '".$f_id."'");
}

function get_user_answer_true($course_id, $student_id, $type, $f_id) {
    global $DB;

    return $DB->count_records_sql(
        "SELECT COUNT(*) FROM {feedbackccna_module} m
        INNER JOIN {feedbackccna_answer} a
        ON m.id = a.module_id
        WHERE m.which_way ='".STUDENT_FOR_TEACHER."'
        AND a.student_id = '".$student_id."'
        AND m.type='".$type."'
        AND m.course_id='".$course_id."'
        AND m.feedback_id = '".$f_id."'");
}

function get_user_absent($course_id, $student_id, $f_id) {
    global $DB;

    return !($DB->count_records_sql(
        "SELECT COUNT(*) FROM {feedbackccna_module} m
        INNER JOIN {feedbackccna_answer} a
        ON m.id = a.module_id
        WHERE m.which_way ='".TEACHER_FOR_STUDENT."'
        AND a.student_id = '".$student_id."'
        AND m.course_id='".$course_id."'
        AND m.feedback_id = '".$f_id."'"));

}

//	functie de obtinut nr total de feedback-uri care au fost activate pe curs
//	- course_id
//  - type - laborator sau prezentare
function get_active_feedbacks_count($course_id, $type) {
    global $DB;
    return $DB->count_records_sql(
			"SELECT COUNT(*) FROM {feedbackccna_module}
			  WHERE type ='".$type."'
				AND course_id='".$course_id."'
				AND allow != '".FEED_NOT_ALLOWED."'
				AND which_way = '".STUDENT_FOR_TEACHER."'");
}

//	functie care returneaza nr de laboratoare la care a participat studentul
//	- id_curs
//	- id_student
function user_completed_labs_count($course_id, $student_id) {

    global $DB;

    return $DB->count_records_sql(
        "SELECT COUNT(*)
        FROM {feedbackccna_module} m
        INNER JOIN {feedbackccna_answer} a
        ON m.id = a.module_id
        WHERE m.which_way ='".TEACHER_FOR_STUDENT."'
        AND m.type='".FEED_TYPE_LAB."'
        AND a.student_id = '".$student_id."'
        AND m.course_id= '".$course_id."'
	AND a.answer != '".LAB_ABSENT."'");
}

//  functie care returneaza nr total de laboratoare sustinute in cadrul cursului
//  - course_id
function user_active_labs_count($course_id) {
	return get_active_feedbacks_count($course_id, FEED_TYPE_LAB);
}

//  functie care obtine ratingul dat de cineva
//  - course_id -
//  - user_id -
//  - section -
//  - which_way -
function get_feedback_answer_records($course_id, $student_id, $section, $f_id, $which_way) {
    global $DB;

    $section = get_correct_section($section);

    $ans_records = $DB->get_records_sql(
        "SELECT * FROM {feedbackccna_answer} a JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        AND a.student_id = ?
        AND m.section = ?
        AND m.feedback_id = ?
        AND m.course_id = ?
        AND m.which_way = ?",
        array($student_id, $section, $f_id, $course_id, $which_way));

    return $ans_records;
}

function get_feedback_answer_id($course_id, $student_id, $section, $f_id, $which_way, $type) {

    global $DB;

    $section = get_correct_section($section);

    $id_records = $DB->get_records_sql(
        "SELECT a.id FROM {feedbackccna_answer} a
        JOIN {feedbackccna_module} m
        ON a.module_id = m.id
        AND a.student_id = ?
        AND m.section = ?
        AND m.course_id = ?
        AND m.which_way = ?
        AND type = ?",
        array($student_id, $section, $course_id, $which_way, $type));

    foreach ($id_records as $id_record) {
        return $id_record->id;
    }

    return 0;

}

function get_user_total($context) {

    return count(get_role_users(5, $context, true));

}

function class_graded($course_id, $f_id) {

    global $DB;

    return $DB->count_records_sql(
        "SELECT COUNT(*) FROM {feedbackccna_module} m
        INNER JOIN {feedbackccna_answer} a
        ON m.id = a.module_id
        WHERE m.which_way ='".TEACHER_FOR_STUDENT."'
        AND m.course_id='".$course_id."'
        AND m.feedback_id = '".$f_id."'");

}

// this function gets all the user ids from users with a specific role,
 // who are enrolled in an array of courses
function get_user_ids_in_courses_by_role($course_array, $role) {

    global $DB;

    return $DB->get_records_sql(
        "SELECT DISTINCT us.id, us.email FROM {user} us
        INNER JOIN {user_enrolments} us_en
        ON us.id = us_en.userid
        INNER JOIN {enrol} en
        ON us_en.enrolid = en.id
        INNER JOIN {context} con
        ON en.courseid = con.instanceid
        INNER JOIN {role_assignments} ro_as
        ON ro_as.contextid = con.id
        AND ro_as.userid = us.id
        WHERE con.contextlevel = 50
        AND ro_as.roleid = ".$role."
        AND en.courseid IN (".implode($course_array, ', ').")");

}

