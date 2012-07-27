<?php

defined('MOODLE_INTERNAL') || die();

// 0/1 nu se poate/se poate raspunde la feedback
define('FEEDBACK_ALLOWED', 1); // valoare care permite feedback
define('FEEDBACK_NOT_ALLOWED', 0); // valoare care nu permite feedback
define('DEFAULT_FEEDBACK_ALLOWED', FEEDBACK_NOT_ALLOWED); // valoarea implicita la adaugarea in DB
// define-uri pt which_way
// feedback-ul se da de profesor pentru elev
define('TEACHER_FOR_STUDENT', 1);
// feedback-ul se da de elev pentru profesor
define('STUDENT_FOR_TEACHER', 2);
define('FEEDBACK_TYPE_PRE', 1);
define('FEEDBACK_TYPE_LAB', 2);
define('FEEDBACK_STUDENT_LAB_NOT_DONE', 2);
define('FEEDBACK_STUDENT_LAB_DONE', 1);

/*
function insert_feedback_module($type, $instructor_id, $course_id, $section, $denumire, $which_way);
	functie care insereaza un modul de feedback
	parametri:
	- instructor_id - id-ul instructorului care adauga modulul
	- denumire - denumirea modulului
	- section - sectiunea din curs unde se afla modulul
	- course_id - id-ul cursului unde se afla modulul
	- which_way - daca feedback-ul este dat de profesor studentului sau invers
*/

function insert_feedback_module($instructor_id, $course_id, $section, $denumire, $which_way) {
	global $DB;

	// inserez modulul de feedback cu drepturile care trebuie, pentru a fi afisat
	$allow = FEEDBACK_NOT_ALLOWED;
	if( $which_way == STUDENT_FOR_TEACHER) $allow = DEFAULT_FEEDBACK_ALLOWED;
	elseif( $which_way == TEACHER_FOR_STUDENT) $allow = FEEDBACK_ALLOWED;
	
	$record = new stdClass();
	$record->instructor_id = $instructor_id;
	$record->denumire = $denumire;
	$record->allow = $allow;
	$record->section = $section;
	$record->course_id = $course_id;
	$record->which_way = $which_way;
	
	$DB->insert_record("feedbackccna_module", $record);
}

/*
	functie de inserat automat intrare unui modul
*/
function setup_feedback_module($feedbackccna, $instructor_id) {

	insert_feedback_module($instructor_id, $feedbackccna->course_id, $feedback->section, $feedback->name, STUDENT_FOR_TEACHER);
	insert_feedback_module($instructor_id, $feedbackccna->course_id, $feedback->section, $feedback->name, TEACHER_FOR_STUDENT);	
}

/*
	functie de inserare raspuns la intrebare
	parametri:
	- type - tipul feedback-ului prezentare/laborator
	- module_id - id-ul modulului la care s-a raspuns
	- student_id - id-ul studentului care a raspuns
	- answer - valoarea efectiva a raspunsului
*/
function insert_feedback_answer($module_id, $type, $student_id, $answer) {
	global $DB;

	$record = new stdClass();
	$record->type = $type ;
	$record->student_id = $student_id;
	$record->module_id = $module_id;
	$record->answer = $answer;

	$DB->insert_record("feedbackccna_answer",$record);
}

/*
	functie de obtinut modulul de feedback dintr-un curs si o sectiune
	- course_id -id-ul cursului
	- section - saptamana sau topicul
	- which_way - daca feedback-ul este dat de student pt profesor sau invers
*/
function get_feedback_module($course_id, $section, $which_way) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_module} WHERE course_id = ? AND section = ? AND which_way = ?", array($course_id, $section, $which_way) );
}

/*
	functie de obtinut rating mediu pentru un curs
*/

/*
	functie de obtinut rating mediu pentru un profesor
*/

/*
	functie de obtinut nr de laboratoare completate de un utilizator
	- course_id
	- student_id
*/
function get_user_lab_count($course_id, $student_id) {
	global $DB;

	return $DB->count_record_sql("SELECT COUNT(*) FROM {feedbackccna_module} m INNER JOIN {feedbackccna_answer} a ON m.id = a.module_id WHERE a.value ='".FEEDBACK_STUDENT_LAB_DONE."' AND a.type='".FEEDBACK_TYPE_LAB."'");
}

/*
	functie de obtinut nr total de feedback-uri pe curs
	- course_id
*/
function get_feedback_modules_count($course_id) {
	global $DB;

	return $DB->count_record_sql("SELECT COUNT(*) FROM {feedbackccna_module} WHERE course_id ='".$course_id."'");
}
