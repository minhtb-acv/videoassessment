<?php
function xmldb_videoassessment_install() {
    global $DB;

	$dbman = $DB->get_manager();
	
    // Define field order to be added to user_enrolments
	$table = new xmldb_table('user_enrolments');
	$field = new xmldb_field('order', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'enrolid');

	// Conditionally launch add field sort
	if (!$dbman->field_exists($table, $field)) {
		$dbman->add_field($table, $field);
	}
	
	// Define field sortby to be added to course
	$table = new xmldb_table('course');
	$field = new xmldb_field('sortby', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

	// Conditionally launch add field sortby
	if (!$dbman->field_exists($table, $field)) {
		$dbman->add_field($table, $field);
	}
	
	// Define field sortby to be added to groups
	$table = new xmldb_table('groups');
	$field = new xmldb_field('sortby', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

	// Conditionally launch add field sortby
	if (!$dbman->field_exists($table, $field)) {
		$dbman->add_field($table, $field);
	}

	// Define field order to be added to groups_members
	$table = new xmldb_table('groups_members');
	$field = new xmldb_field('order', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

	// Conditionally launch add field order
	if (!$dbman->field_exists($table, $field)) {
		$dbman->add_field($table, $field);
	}
}
