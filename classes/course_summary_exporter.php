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
 * Class for exporting a course summary from an stdClass.
 *
 * @package    core_course
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace block_mycourses;

 defined('MOODLE_INTERNAL') || die();

use renderer_base;

/**
 * Class for exporting a course summary from an stdClass.
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_summary_exporter extends \core_course\external\course_summary_exporter {

    /**
     * Constructor - saves the persistent object, and the related objects.
     *
     * @param mixed $data - Either an stdClass or an array of values.
     * @param array $related - An optional list of pre-loaded objects related to this object.
     */
    public function __construct($data, $related = array()) {
        if (!array_key_exists('role', $related)) {
            $related['role'] = BLOCK_MYCOURSES_ROLE_STUDENT;
        }
        parent::__construct($data, $related);
    }

    protected static function define_related() {
        // We cache the context so it does not need to be retrieved from the course.
        return array(
            'context' => '\\context',
            'isfavourite' => 'bool?',
            'role' => 'string?'
        );
    }

    protected function get_other_values(renderer_base $output) {
        $values = parent::get_other_values($output);
        
        // Get hidden value
        $values['hidden'] = boolval(get_user_preferences('block_mycourses_hidden_course_' . $this->data->id, 0));

        // Add isteacher value
        $isteacher = $this->related['role'] === BLOCK_MYCOURSES_ROLE_TEACHER;
        $values['isteacher'] = $isteacher;

        // Check if the user is a teacher to add KPI data
        $values['kpidata'] = $isteacher ? self::get_kpi_data($this->data) : '';

        return $values;
    }

    public static function define_other_properties() {
        $properties = parent::define_other_properties();

        // Add KPI property
        $properties['kpidata'] = [
            'type' => PARAM_CLEANHTML,
            'null' => NULL_ALLOWED
        ];

        // Add isteacher property
        $properties['isteacher'] = [
            'type' => PARAM_BOOL,
            'null' => NULL_ALLOWED
        ];

        return $properties;
    }

    /**
     * Get the course KPI data for teachers
     *
     * @param object $course
     * @return string Empty for learners, KPI data for teachers
     */
    public static function get_kpi_data($course) {
        // Count of student enrolments
        $enrolled = self::get_enrolled_users_count($course);
        $enrolledstr = get_string('kpi:learners:enrolled', 'block_mycourses', $enrolled);

        // Dummy data:
        // Edtime: count of students who have started the course
        $startedstr = get_string('kpi:learners:started', 'block_mycourses', 10);

        // Count of student completions
        $completed = self::get_completion_count($course);
        $completedstr = get_string('kpi:learners:completed', 'block_mycourses', $completed);

        // Dummy data:
        // Local Sharecourse: number of course shares by students
        $sharedstr = get_string('kpi:learners:shared', 'block_mycourses', 3);

        // Average grade of students who have completed the course
        $averagegrade = self::get_average_completion_grade($course);
        $averagegradestr = get_string('percents', 'moodle', round($averagegrade, 2));

        // Dummy data:
        // Edtime: Average time to complete the course
        $timetocomplete = '1h03m';

        // Last time the course was accessed
        $lastaccessed = self::get_last_accessed($course);
        if ($lastaccessed === null) {
            $lastaccessedstr = get_string('never');
        } else {
            $lastaccessedstr = get_string('ago', 'core_message', format_time(time() - $lastaccessed));
        }

        $data = [
            'learners' => [
                'label' => get_string('kpi:learners', 'block_mycourses'),
                'value' => [
                    $enrolledstr,
                    $startedstr,
                    $completedstr,
                    $sharedstr
                ]
            ]
        ];
        $data['timetocomplete'] = [
            'label' => get_string('kpi:timetocomplete', 'block_mycourses'),
            'value' => $timetocomplete
        ];
        if ($completed > 0) {
            $data['completiongrade'] = [
                'label' => get_string('kpi:completiongrade', 'block_mycourses'),
                'value' => $averagegradestr
            ];
        }
        $data['lastaccessed'] = [
            'label' => get_string('kpi:lastaccessed', 'block_mycourses'),
            'value' => $lastaccessedstr
        ];

        $html = '';

        $html .= \html_writer::start_tag('div', ['class' => 'kpi-container']);
        foreach ($data as $kpi) {
            $html .= \html_writer::start_tag('div', ['class' => 'kpi']);
            $html .= \html_writer::tag('span', $kpi['label'], ['class' => 'kpi-label']) . ' ';
            $kpivalue = is_array($kpi['value']) ? implode(', ', $kpi['value']) : $kpi['value'];
            $html .= \html_writer::tag('span', $kpivalue, ['class' => 'kpi-value']);
            $html .= \html_writer::end_tag('div');
        }
        $html .= \html_writer::end_tag('div');

        return $html;
    }

    /**
     * Returns the count of enrolled users in a course.
     *
     * @param object $course The course object.
     * @return int The count of enrolled users.
     */
    public static function get_enrolled_users_count($course) {
        global $DB;

        $context = \context_course::instance($course->id);

        // Get the role ids for the archetypes.
        $roleids = $DB->get_records_menu('role', ['archetype' => 'student'], '', 'id, shortname');
        list($insql, $params) = $DB->get_in_or_equal(array_keys($roleids), SQL_PARAMS_NAMED, 'e');

        $sql = "SELECT COUNT(DISTINCT ue.userid)
                  FROM {user_enrolments} ue
             LEFT JOIN {role_assignments} ra ON ra.userid = ue.userid
                 WHERE ra.roleid $insql AND ra.contextid = :contextid";
        $params['contextid'] = $context->id;
        $count = $DB->count_records_sql($sql, $params);

        return $count;
    }
    
    /**
     * Retrieves the completion count for a given course.
     *
     * @param object $course The course object.
     * @return int The number of completed course instances.
     */
    public static function get_completion_count($course) {
        global $DB;

        $sql = "SELECT COUNT(DISTINCT cc.userid)
                  FROM {course_completions} cc
                 WHERE cc.course = :courseid
                   AND cc.timecompleted IS NOT NULL";
        $count = $DB->count_records_sql($sql, ['courseid' => $course->id]);

        return $count;
    }

    public static function get_average_completion_grade($course) {
        global $DB;

        $sql = "SELECT AVG(gg.finalgrade/gg.rawgrademax) * 100
                  FROM {course_completions} cc
             LEFT JOIN {grade_items} gi ON gi.courseid = cc.course AND gi.itemtype = 'course'
             LEFT JOIN {grade_grades} gg ON gg.userid = cc.userid AND gg.itemid = gi.id
                 WHERE cc.course = :courseid AND cc.timecompleted IS NOT NULL";
        $average = $DB->get_field_sql($sql, ['courseid' => $course->id]);

        return round($average, 2);
    }

    /**
     * Retrieves the last accessed time for a given course.
     *
     * @param object $course The course object.
     * @return string The formatted last accessed time.
     */
    public static function get_last_accessed($course) {
        global $DB;

        $context = \context_course::instance($course->id);

        // Get the role ids for the archetypes.
        $roleids = $DB->get_records_menu('role', ['archetype' => 'student'], '', 'id, shortname');
        list($insql, $params) = $DB->get_in_or_equal(array_keys($roleids), SQL_PARAMS_NAMED, 'e');

        $sql = "SELECT MAX(ula.timeaccess)
                  FROM {role_assignments} ra
             LEFT JOIN {user_lastaccess} ula ON ula.userid = ra.userid
                 WHERE ra.roleid $insql AND ra.contextid = :contextid AND ula.courseid = :courseid";
        $params['contextid'] = $context->id;
        $params['courseid'] = $course->id;

        $lastaccess = $DB->get_field_sql($sql, $params); 

        return $lastaccess;
    }
}
