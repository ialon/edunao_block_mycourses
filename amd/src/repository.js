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
 * A javascript module to retrieve enrolled coruses from the server.
 *
 * @module block_mycourses/repository
 * @copyright  2018 Bas Brands <base@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Retrieve a list of enrolled courses.
 *
 * Valid args are:
 * string classification    future, inprogress, past
 * int limit                number of records to retreive
 * int Offset               offset for pagination
 * int sort                 sort by lastaccess or name
 *
 * @method getEnrolledCoursesByRole
 * @param {object} args The request arguments
 * @return {promise} Resolved with an array of courses
 */
export const getEnrolledCoursesByRole = args => {
    const request = {
        methodname: 'block_mycourses_get_enrolled_courses_by_role',
        args: args
    };

    return Ajax.call([request])[0];
};

/**
 * Set the favourite state on a list of courses.
 *
 * Valid args are:
 * Array courses  list of course id numbers.
 *
 * @param {Object} args Arguments send to the webservice.
 * @return {Promise} Resolve with warnings.
 */
export const setFavouriteCourses = args => {
    const request = {
        methodname: 'core_course_set_favourite_courses',
        args: args
    };

    return Ajax.call([request])[0];
};

/**
 * Set the visibility for a course
 *
 * Valid args are:
 * Array courses list of course id numbers.
 *
 * @param {Object} args Arguments send to the webservice.
 * @return {Promise} Resolve with warnings.
 */
export const setInvisibilityCourse = args => {
    const request = {
        methodname: 'block_mycourses_update_courses',
        args: args
    };

    return Ajax.call([request])[0];
};
