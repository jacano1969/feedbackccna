<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_feedbackccna_mod_form extends moodleform_mod {

    function definition() {
	
	global $COURSE;
        $mform = $this->_form;

	// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('feedbackccnaname', 'feedbackccna'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->add_intro_editor();

        $this->standard_coursemodule_elements();
        
	$this->add_action_buttons();
    }
}
