<?php

function get_courses_where_student($uid) {
	global $CFG, $DB;
	list($usql_uid, $params_uid) = $DB->get_in_or_equal($uid);
	$sql = "SELECT c.id, c.fullname, c.startdate, c.numsections
			FROM {course} c
			INNER JOIN {context} cx
			ON c.id = cx.instanceid
			AND cx.contextlevel = 50
			INNER JOIN {role_assignments} ra
			ON cx.id = ra.contextid
			AND ra.userid $usql_uid
			INNER JOIN {role} r
			ON ra.roleid = r.id
			AND r.id = 5
			ORDER BY c.fullname";
	return $DB->get_records_sql($sql, $params_uid);
}

