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
 * This file contains the submission confirmation form
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Assignment submission confirmation form
 *
 * @package   mod_assign
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_assign_confirm_submission_form extends moodleform {
    /**
     * Define the form - called by parent constructor
     */
    public function definition() {
        $mform = $this->_form;

        list($requiresubmissionstatement,
             $submissionstatement,
             $coursemoduleid,
             $data) = $this->_customdata;

        if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement', '', $submissionstatement);
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
		
			
			$mform->addElement('checkbox', 'submissionstatement2', '', 'I have read and understood the details of the assessment.');
            $mform->addRule('submissionstatement2', get_string('required'), 'required', null, 'client');
		
			$mform->addElement('checkbox', 'submissionstatement3', '', 'I have been informed of the conditions of the assessment and the appeals process and understand, I may appeal if I believe the assessment is not equitable, fair or just.');
            $mform->addRule('submissionstatement3', get_string('required'), 'required', null, 'client');
			
			$mform->addElement('checkbox', 'submissionstatement4', '', 'I agree to participate in role-plays/ discussion sessions (if the unit demands it) to pass the unit or else I will be marked not satisfactory for the whole unit.');
            $mform->addRule('submissionstatement4', get_string('required'), 'required', null, 'client');
			
			$mform->addElement('checkbox', 'submissionstatement5', '', 'I agree to submit the additional files/ supplementary evidence, if required as per my assessment instructions.');
            $mform->addRule('submissionstatement5', get_string('required'), 'required', null, 'client');
			
			$mform->addElement('checkbox', 'submissionstatement6', '', 'I agree to participate in this assessment, and I am ready to be assessed.');
            $mform->addRule('submissionstatement6', get_string('required'), 'required', null, 'client');
			
			$mform->addElement('checkbox', 'submissionstatement7', '', 'I certify that the attached is my own work; except where I have acknowledged the other people’s work.');
            $mform->addRule('submissionstatement7', get_string('required'), 'required', null, 'client');
			
			$mform->addElement('checkbox', 'submissionstatement8', '', 'I agree that plagiarism in any form will not be accepted by ACCIT.');
            $mform->addRule('submissionstatement8', get_string('required'), 'required', null, 'client');



			$mform->addElement('checkbox', 'submissionstatement9', '', 'I have retained a copy of my assessment for future reference.');
            $mform->addRule('submissionstatement9', get_string('required'), 'required', null, 'client'); 
        }
		
		/* if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement', '', 'dddddddddddddddddddddd');
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement2', '', $submissionstatement2);
            $mform->addRule('submissionstatement2', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement3', '', $submissionstatement3);
            $mform->addRule('submissionstatement3', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement4', '', $submissionstatement4);
            $mform->addRule('submissionstatement4', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement5', '', $submissionstatement5);
            $mform->addRule('submissionstatement5', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement6', '', $submissionstatement6);
            $mform->addRule('submissionstatement6', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement7', '', $submissionstatement7);
            $mform->addRule('submissionstatement7', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement8', '', $submissionstatement8);
            $mform->addRule('submissionstatement8', get_string('required'), 'required', null, 'client');
        }
		if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement9', '', $submissionstatement9);
            $mform->addRule('submissionstatement9', get_string('required'), 'required', null, 'client');
        }
		/*if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement2', '', $submissionstatement);
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
        }*//*if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement2', '', $submissionstatement);
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
        }*//*if ($requiresubmissionstatement) {
            $mform->addElement('checkbox', 'submissionstatement2', '', $submissionstatement);
            $mform->addRule('submissionstatement', get_string('required'), 'required', null, 'client');
        }*/

        $mform->addElement('static', 'confirmmessage', '', get_string('confirmsubmission', 'mod_assign'));
        $mform->addElement('hidden', 'id', $coursemoduleid);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'action', 'confirmsubmit');
        $mform->setType('action', PARAM_ALPHA);
        $this->add_action_buttons(true, get_string('continue'));

        if ($data) {
            $this->set_data($data);
        }
    }

}
