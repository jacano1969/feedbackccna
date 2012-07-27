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

/*
function insert_feedback_module($type, $instructor_id, $course_id, $section, $denumire, $which_way);
	functie care insereaza un modul de feedback
	parametri:
	-type - tipul feedback-ului prezentare/laborator
	-instructor_id - id-ul instructorului care adauga modulul
	-denumire - denumirea modulului
	-section - sectiunea din curs unde se afla modulul
	-course_id - id-ul cursului unde se afla modulul
	-which_way - daca feedback-ul este dat de profesor studentului sau invers
*/

function insert_feedback_module($type, $instructor_id, $course_id, $section, $denumire, $which_way) {
	global $DB;

	$record = new stdClass();
	$record->type = $type;
	$record->instructor_id = $instructor_id;
	$record->denumire = $denumire;
	$record->allow = $which_way === STUDENT_FOR_TEACHER ? DEFAULT_FEEDBACK_ALLOWED : FEEDBACK_ALLOWED;
	$record->section = $section;
	$record->course_id = $course_id;
	$record->which_way = $which_way;
	
	$DB->insert_record("module", $record);
}

/*
	functie de inserare
	parametri:
	- module_id - id-ul modulului la care s-a raspuns
	- student_id - id-ul studentului care a raspuns
	- answer - valoarea efectiva a raspunsului
*/
function insert_feedback_answer($module_id, $student_id, $answer) {
	global $DB;

	$record = new stdClass();
	$record->student_id = $student_id;
	$record->module_id = $module_id;
	$record->answer = $answer;

	$DB->insert_record("answer",$record);
}
