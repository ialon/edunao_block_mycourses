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
 * Library functions for my courses.
 *
 * @package   block_mycourses
 * @copyright 2018 Peter Dias
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Constants for the user preferences grouping options
 */
define('BLOCK_MYCOURSES_GROUPING_ALLINCLUDINGHIDDEN', 'allincludinghidden');
define('BLOCK_MYCOURSES_GROUPING_ALL', 'all');
define('BLOCK_MYCOURSES_GROUPING_INPROGRESS', 'inprogress');
define('BLOCK_MYCOURSES_GROUPING_FUTURE', 'future');
define('BLOCK_MYCOURSES_GROUPING_PAST', 'past');
define('BLOCK_MYCOURSES_GROUPING_FAVOURITES', 'favourites');
define('BLOCK_MYCOURSES_GROUPING_HIDDEN', 'hidden');
define('BLOCK_MYCOURSES_GROUPING_CUSTOMFIELD', 'customfield');

/**
 * Allows selection of all courses without a value for the custom field.
 */
define('BLOCK_MYCOURSES_CUSTOMFIELD_EMPTY', -1);

/**
 * Constants for the user preferences sorting options
 * timeline
 */
define('BLOCK_MYCOURSES_SORTING_TITLE', 'title');
define('BLOCK_MYCOURSES_SORTING_LASTACCESSED', 'lastaccessed');
define('BLOCK_MYCOURSES_SORTING_SHORTNAME', 'shortname');

/**
 * Constants for the user preferences view options
 */
define('BLOCK_MYCOURSES_VIEW_CARD', 'card');
define('BLOCK_MYCOURSES_VIEW_LIST', 'list');
define('BLOCK_MYCOURSES_VIEW_SUMMARY', 'summary');

/**
 * Constants for the user paging preferences
 */
define('BLOCK_MYCOURSES_PAGING_5', 5);
define('BLOCK_MYCOURSES_PAGING_10', 10);
define('BLOCK_MYCOURSES_PAGING_15', 15);
define('BLOCK_MYCOURSES_PAGING_20', 20);
define('BLOCK_MYCOURSES_PAGING_ALL', 0);

/**
 * Constants for the admin category display setting
 */
define('BLOCK_MYCOURSES_DISPLAY_CATEGORIES_ON', 'on');
define('BLOCK_MYCOURSES_DISPLAY_CATEGORIES_OFF', 'off');

/**
 * Constants for the user role
 */
define('BLOCK_MYCOURSES_ROLE_TEACHER', 'teacher');
define('BLOCK_MYCOURSES_ROLE_STUDENT', 'student');

/**
 * Get the current user preferences that are available
 *
 * @uses core_user::is_current_user
 *
 * @return array[] Array representing current options along with defaults
 */
function block_mycourses_user_preferences(): array {
    $preferences['/^block_mycourses_user_grouping_preference_(\d)+$/'] = array(
        'isregex' => true,
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYCOURSES_GROUPING_ALL,
        'type' => PARAM_ALPHA,
        'choices' => array(
            BLOCK_MYCOURSES_GROUPING_ALLINCLUDINGHIDDEN,
            BLOCK_MYCOURSES_GROUPING_ALL,
            BLOCK_MYCOURSES_GROUPING_INPROGRESS,
            BLOCK_MYCOURSES_GROUPING_FUTURE,
            BLOCK_MYCOURSES_GROUPING_PAST,
            BLOCK_MYCOURSES_GROUPING_FAVOURITES,
            BLOCK_MYCOURSES_GROUPING_HIDDEN,
            BLOCK_MYCOURSES_GROUPING_CUSTOMFIELD,
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['/^block_mycourses_user_grouping_customfieldvalue_preference_(\d)+$/'] = [
        'isregex' => true,
        'null' => NULL_ALLOWED,
        'default' => null,
        'type' => PARAM_RAW,
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['/^block_mycourses_user_sort_preference_(\d)+$/'] = array(
        'isregex' => true,
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYCOURSES_SORTING_LASTACCESSED,
        'type' => PARAM_ALPHA,
        'choices' => array(
            BLOCK_MYCOURSES_SORTING_TITLE,
            BLOCK_MYCOURSES_SORTING_LASTACCESSED,
            BLOCK_MYCOURSES_SORTING_SHORTNAME
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['/^block_mycourses_user_view_preference_(\d)+$/'] = array(
        'isregex' => true,
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYCOURSES_VIEW_CARD,
        'type' => PARAM_ALPHA,
        'choices' => array(
            BLOCK_MYCOURSES_VIEW_CARD,
            BLOCK_MYCOURSES_VIEW_LIST,
            BLOCK_MYCOURSES_VIEW_SUMMARY
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['/^block_mycourses_hidden_course_(\d)+$/'] = array(
        'isregex' => true,
        'choices' => array(0, 1),
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => 0,
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    $preferences['/^block_mycourses_user_paging_preference_(\d)+$/'] = array(
        'isregex' => true,
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYCOURSES_PAGING_5,
        'type' => PARAM_INT,
        'choices' => array(
            BLOCK_MYCOURSES_PAGING_5,
            BLOCK_MYCOURSES_PAGING_10,
            BLOCK_MYCOURSES_PAGING_15,
            BLOCK_MYCOURSES_PAGING_20,
            BLOCK_MYCOURSES_PAGING_ALL
        ),
        'permissioncallback' => [core_user::class, 'is_current_user'],
    );

    return $preferences;
}

/**
 * Pre-delete course hook to cleanup any records with references to the deleted course.
 *
 * @param stdClass $course The deleted course
 */
function block_mycourses_pre_course_delete(\stdClass $course) {
    // Removing any favourited courses which have been created for users, for this course.
    $service = \core_favourites\service_factory::get_service_for_component('core_course');
    $service->delete_favourites_by_type_and_item('courses', $course->id);
}

/**
 * Returns list of courses user is enrolled into as a specific role.
 *
 * Note: Use {@link enrol_get_all_users_courses()} if you need the list without any capability checks.
 *
 * The $fields param is a list of field names to ADD so name just the fields you really need,
 * which will be added and uniq'd.
 *
 * @param string $role Shortname of the role (teacher or student).
 * @param int $userid User whose courses are returned, defaults to the current user.
 * @param bool $onlyactive Return only active enrolments in courses user may see.
 * @param string|array $fields Extra fields to be returned (array or comma-separated list).
 * @param string|null $sort Comma separated list of fields to sort by, defaults to respecting navsortmycoursessort.
 * @return array
 */
function enrol_get_users_courses_by_role($role, $userid, $onlyactive = false, $fields = null, $sort = null) {
    // Get all the courses.
    $courses = enrol_get_all_users_courses($userid, $onlyactive, $fields, $sort);

    return filter_courses_by_role($courses, $role, $userid);
}

/**
 * Filters an array of courses based on the user's role.
 *
 * This function takes an array of courses and filters it based on the user's role. It checks the role archetype and retrieves the role IDs associated with the archetype. Then, it checks if the user has any role assignments with the retrieved role IDs in the context of each course. If the user has no role assignments for a course, that course is removed from the array.
 *
 * @param array|Traversable $courses An array of courses to be filtered.
 * @param string $role The role of the user.
 * @param int $userid The ID of the user.
 * @return array The filtered array of courses.
 */
function filter_courses_by_role($courses, $role, $userid) {
    global $DB;

    // Find the archetype for the role.
    if ($role === BLOCK_MYCOURSES_ROLE_TEACHER) {
        $archetypes = ['editingteacher', 'teacher'];
    } else if ($role === BLOCK_MYCOURSES_ROLE_STUDENT) {
        $archetypes = ['student'];
    } else {
        return [];
    }

    $filtered = [];

    // Get the role ids for the archetypes.
    list($insql, $params) = $DB->get_in_or_equal($archetypes, SQL_PARAMS_NAMED, 'r');
    $sql = "SELECT r.id, r.shortname
            FROM {role} r
            WHERE r.archetype $insql";
    $roleids = $DB->get_records_sql_menu($sql, $params);

    foreach ($courses as $course) {
        $context = context_course::instance($course->id);

        list($insql, $params) = $DB->get_in_or_equal(array_keys($roleids), SQL_PARAMS_NAMED, 'r');
        $sql = "SELECT ra.id
                    FROM {role_assignments} ra
                    WHERE ra.roleid $insql AND ra.userid = :userid AND ra.contextid = :contextid";
        $params['userid'] = $userid;
        $params['contextid'] = $context->id;
        $assignments = $DB->get_records_sql($sql, $params);

        if (!empty($assignments)) {
            $filtered[] = $course;
        }
    }

    return $filtered;
}
