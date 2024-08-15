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
 * Form for editing My courses block instances.
 *
 * @package    block_mycourses
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Josemaria Bolanos <josemabol@gmail.com>
 */

class block_myoverview_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $options = array(
            BLOCK_MYOVERVIEW_ROLE_TEACHER => get_string('teacher', 'block_myoverview'),
            BLOCK_MYOVERVIEW_ROLE_STUDENT => get_string('student', 'block_myoverview')
        );

        $mform->addElement('select', 'config_myrole', get_string('displayrole', 'block_myoverview'), $options);
        $mform->setDefault('config_myrole', BLOCK_MYOVERVIEW_ROLE_TEACHER);
    }
}
