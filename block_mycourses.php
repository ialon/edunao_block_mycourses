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
 * Contains the class for the My Courses block.
 *
 * @package    block_mycourses
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/mycourses/lib.php');

/**
 * My Courses block class.
 *
 * @package    block_mycourses
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mycourses extends block_base {

    /**
     * Init.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_mycourses');
        $this->config = new stdClass();
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }
        $config = $this->config;
        $group = get_user_preferences('block_mycourses_user_grouping_preference_' . $this->instance->id);
        $sort = get_user_preferences('block_mycourses_user_sort_preference_' . $this->instance->id);
        $view = get_user_preferences('block_mycourses_user_view_preference_' . $this->instance->id);
        $paging = get_user_preferences('block_mycourses_user_paging_preference_' . $this->instance->id);
        $customfieldvalue = get_user_preferences('block_mycourses_user_grouping_customfieldvalue_preference_' . $this->instance->id);

        $renderable = new \block_mycourses\output\main($config, $group, $sort, $view, $paging, $customfieldvalue);
        $renderer = $this->page->get_renderer('block_mycourses');

        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Allow the block to have a configuration page.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * allow instances to have their own configuration
     *
     * @return boolean
     */
    public function instance_allow_config() {
        return true;
    }

    /**
     * instance specialisations (must have instance allow config true)
     *
     */
    public function specialization() {
        // Set default values for new instances.
        if (empty($this->config->myrole)) {
            $this->config->myrole = BLOCK_MYCOURSES_ROLE_TEACHER;
        }
        $this->title = get_string('blocktitle:' . $this->config->myrole, 'block_mycourses');
        $this->config->instance = $this->instance->id;
    }

    /**
     * Controls whether multiple block instances are allowed.
     *
     * @return bool
     */
    public function instance_allow_multiple(): bool {
        return true;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        $instanceconfig = $this->config;

        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = get_config('block_mycourses');

        // Get the customfield values (if any).
        if ($configs->displaygroupingcustomfield) {
            $group = get_user_preferences('block_mycourses_user_grouping_preference_' . $this->instance->id);
            $sort = get_user_preferences('block_mycourses_user_sort_preference_' . $this->instance->id);
            $view = get_user_preferences('block_mycourses_user_view_preference_' . $this->instance->id);
            $paging = get_user_preferences('block_mycourses_user_paging_preference_' . $this->instance->id);
            $customfieldvalue = get_user_preferences('block_mycourses_user_grouping_customfieldvalue_preference_' . $this->instance->id);

            $renderable = new \block_mycourses\output\main($instanceconfig, $group, $sort, $view, $paging, $customfieldvalue);
            $customfieldsexport = $renderable->get_customfield_values_for_export();
            if (!empty($customfieldsexport)) {
                $configs->customfieldsexport = json_encode($customfieldsexport);
            }
        }

        return (object) [
            'instance' => new stdClass(),
            'plugin' => $configs,
        ];
    }

    /**
     * Disable block editing on the my courses page.
     *
     * @return boolean
     */
    public function instance_can_be_edited() {
        if ($this->page->blocks->is_known_region(BLOCK_POS_LEFT) || $this->page->blocks->is_known_region(BLOCK_POS_RIGHT)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Hide the block header on the my courses page.
     *
     * @return boolean
     */
    public function hide_header() {
        if ($this->page->blocks->is_known_region(BLOCK_POS_LEFT) || $this->page->blocks->is_known_region(BLOCK_POS_RIGHT)) {
            return false;
        } else {
            return true;
        }
    }
}

