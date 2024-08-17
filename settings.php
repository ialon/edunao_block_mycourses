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
 * Settings for the mycourses block
 *
 * @package    block_mycourses
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/mycourses/lib.php');

    // Presentation options heading.
    $settings->add(new admin_setting_heading('block_mycourses/appearance',
            get_string('appearance', 'admin'),
            ''));

    // Display Course Categories on Dashboard course items (cards, lists, summary items).
    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaycategories',
            get_string('displaycategories', 'block_mycourses'),
            get_string('displaycategories_help', 'block_mycourses'),
            1));

    // Enable / Disable available layouts.
    $choices = array(BLOCK_MYCOURSES_VIEW_CARD => get_string('card', 'block_mycourses'),
            BLOCK_MYCOURSES_VIEW_LIST => get_string('list', 'block_mycourses'),
            BLOCK_MYCOURSES_VIEW_SUMMARY => get_string('summary', 'block_mycourses'));
    $settings->add(new admin_setting_configmulticheckbox(
            'block_mycourses/layouts',
            get_string('layouts', 'block_mycourses'),
            get_string('layouts_help', 'block_mycourses'),
            $choices,
            $choices));
    unset ($choices);

    // Enable / Disable course filter items.
    $settings->add(new admin_setting_heading('block_mycourses/availablegroupings',
            get_string('availablegroupings', 'block_mycourses'),
            get_string('availablegroupings_desc', 'block_mycourses')));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupingallincludinghidden',
            get_string('allincludinghidden', 'block_mycourses'),
            '',
            0));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupingall',
            get_string('all', 'block_mycourses'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupinginprogress',
            get_string('inprogress', 'block_mycourses'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupingpast',
            get_string('past', 'block_mycourses'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupingfuture',
            get_string('future', 'block_mycourses'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupingcustomfield',
            get_string('customfield', 'block_mycourses'),
            '',
            0));

    $choices = \core_customfield\api::get_fields_supporting_course_grouping();
    if ($choices) {
        $choices  = ['' => get_string('choosedots')] + $choices;
        $settings->add(new admin_setting_configselect(
                'block_mycourses/customfiltergrouping',
                get_string('customfiltergrouping', 'block_mycourses'),
                '',
                '',
                $choices));
    } else {
        $settings->add(new admin_setting_configempty(
                'block_mycourses/customfiltergrouping',
                get_string('customfiltergrouping', 'block_mycourses'),
                get_string('customfiltergrouping_nofields', 'block_mycourses')));
    }
    $settings->hide_if('block_mycourses/customfiltergrouping', 'block_mycourses/displaygroupingcustomfield');

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupingfavourites',
            get_string('favourites', 'block_mycourses'),
            '',
            1));

    $settings->add(new admin_setting_configcheckbox(
            'block_mycourses/displaygroupinghidden',
            get_string('hiddencourses', 'block_mycourses'),
            '',
            1));
}
