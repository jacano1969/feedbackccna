<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Internal library of functions for module feedbackccna
 *
 * All the feedbackccna specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage feedbackccna
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function insert_feedback($user_id, $course_id, $week, $role, $type, $rating) {
	global $DB;
	$record = new stdClass();

	$record->user_id	= $user_id;
	$record->course_id  = $course_id;
	$record->week 		= $week;
	$record->role 		= $role;
	$record->type 		= $type;
	$record->rating 	= $rating;

	$DB->insert_records('feedbackccna_data',$record);
}

function get_week_feedback_for_user($user_id, $course_id, $week, $role, $type) {
	global $DB;
	
	return $DB->get_records_sql("SELECT type, value FROM {feedbackccna_data} WHERE 
			user_id 	= $user_id AND
			course_id	= $course_id AND
			week 		= $week AND
			role		= $role AND
			type 		= $type");
}

function get_week_feedback_for_teacher($course_id, $week, $type, $role) {
	global $DB;

	return $DB->get_records_sql("SELECT type, value FROM {feedbackccna_data} WHERE
			course_id	= $course_id AND
			week 		= $week AND
			role 		= $role AND
			type		= $type");
}


