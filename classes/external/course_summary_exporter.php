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
 * @package    block_myoverviewcustom
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myoverviewcustom\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;
use core_course\customfield\course_handler;

/**
 * Class for exporting a course summary from an stdClass.
 *
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_summary_exporter extends \core\external\exporter {
    /**
     * Constructor - saves the persistent object, and the related objects.
     *
     * @param mixed $data - Either an stdClass or an array of values.
     * @param array $related - An optional list of pre-loaded objects related to this object.
     */
    public function __construct($data, $related = []) {
        if (!array_key_exists('isfavourite', $related)) {
            $related['isfavourite'] = false;
        }
        parent::__construct($data, $related);
    }

    protected static function define_related() {
        // We cache the context so it does not need to be retrieved from the course.
        return ['context' => '\\context', 'isfavourite' => 'bool?'];
    }

    protected function get_other_values(renderer_base $output) {
        global $CFG;
        $courseimage = self::get_course_image($this->data);
        if (!$courseimage) {
            $courseimage = $output->get_generated_image_for_id($this->data->id);
        }
        $progress = self::get_course_progress($this->data);
        $hasprogress = false;
        if ($progress === 0 || $progress > 0) {
            $hasprogress = true;
        }
        $progress = floor($progress ?? 0);
        $coursecategory = \core_course_category::get($this->data->category, MUST_EXIST, true);

        $handler = course_handler::create();
        $datas = $handler->get_instance_data($this->data->id);
        foreach ($datas as $data) {
            if ($data->get_field()->get('shortname') === 'kategorie') {
                $cvalue = $data->get_value();
            }
        }

        return [
            'fullnamedisplay' => get_course_display_name_for_list($this->data),
            'viewurl' => (new moodle_url('/course/view.php', ['id' => $this->data->id]))->out(false),
            'courseimage' => $courseimage,
            'progress' => $progress,
            'hasprogress' => $hasprogress,
            'isfavourite' => $this->related['isfavourite'],
            'hidden' => boolval(get_user_preferences('block_myoverview_hidden_course_' . $this->data->id, 0)),
            'showshortname' => $CFG->courselistshortnames ? true : false,
            'coursecategory' => $coursecategory->name,
            'customfieldvalue' => $cvalue,
        ];
    }

    public static function define_properties() {
        return [
            'id' => [
                'type' => PARAM_INT,
            ],
            'fullname' => [
                'type' => PARAM_TEXT,
            ],
            'shortname' => [
                'type' => PARAM_TEXT,
            ],
            'idnumber' => [
                'type' => PARAM_RAW,
            ],
            'summary' => [
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'summaryformat' => [
                'type' => PARAM_INT,
                'default' => FORMAT_MOODLE,
            ],
            'startdate' => [
                'type' => PARAM_INT,
            ],
            'enddate' => [
                'type' => PARAM_INT,
            ],
            'visible' => [
                'type' => PARAM_BOOL,
            ],
            'showactivitydates' => [
                'type' => PARAM_BOOL,
                'null' => NULL_ALLOWED,
            ],
            'showcompletionconditions' => [
                'type' => PARAM_BOOL,
                'null' => NULL_ALLOWED,
            ],
            'pdfexportfont' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
        ];
    }

    /**
     * Get the formatting parameters for the summary.
     *
     * @return array
     */
    protected function get_format_parameters_for_summary() {
        return [
            'component' => 'course',
            'filearea' => 'summary',
        ];
    }

    public static function define_other_properties() {
        return [
            'fullnamedisplay' => [
                'type' => PARAM_TEXT,
            ],
            'viewurl' => [
                'type' => PARAM_URL,
            ],
            'courseimage' => [
                'type' => PARAM_RAW,
            ],
            'progress' => [
                'type' => PARAM_INT,
                'optional' => true,
            ],
            'hasprogress' => [
                'type' => PARAM_BOOL,
            ],
            'isfavourite' => [
                'type' => PARAM_BOOL,
            ],
            'hidden' => [
                'type' => PARAM_BOOL,
            ],
            'timeaccess' => [
                'type' => PARAM_INT,
                'optional' => true,
            ],
            'showshortname' => [
                'type' => PARAM_BOOL,
            ],
            'coursecategory' => [
                'type' => PARAM_TEXT,
            ],
            'customfieldvalue' => [
                'type' => PARAM_TEXT,
            ],

        ];
    }

    /**
     * Get the course image if added to course.
     *
     * @param object $course
     * @return string|false url of course image or false if it's not exist.
     */
    public static function get_course_image($course) {
        $image = \cache::make('core', 'course_image')->get($course->id);

        if (is_null($image)) {
            $image = false;
        }

        return $image;
    }

    /**
     * Get the course pattern datauri.
     *
     * The datauri is an encoded svg that can be passed as a url.
     * @param object $course
     * @return string datauri
     * @deprecated 3.7
     */
    public static function get_course_pattern($course) {
        global $OUTPUT;
        debugging('course_summary_exporter::get_course_pattern() is deprecated. ' .
            'Please use $OUTPUT->get_generated_image_for_id() instead.', DEBUG_DEVELOPER);
        return $OUTPUT->get_generated_image_for_id($course->id);
    }

    /**
     * Get the course progress percentage.
     *
     * @param object $course
     * @return int progress
     */
    public static function get_course_progress($course) {
        return \core_completion\progress::get_course_progress_percentage($course);
    }

    /**
     * Get the course color.
     *
     * @param int $courseid
     * @return string hex color code.
     * @deprecated 3.7
     */
    public static function coursecolor($courseid) {
        global $OUTPUT;
        debugging('course_summary_exporter::coursecolor() is deprecated. ' .
            'Please use $OUTPUT->get_generated_color_for_id() instead.', DEBUG_DEVELOPER);
        return $OUTPUT->get_generated_color_for_id($courseid);
    }

        /**
         * Get the course color.
         *
         * @param int $courseid
         * @return string hex color code.
         * @deprecated 3.7
         */
    public static function getcustomfieldvalue($courseid) {
        global $OUTPUT;
        debugging('course_summary_exporter::coursecolor() is deprecated. ' .
            'Please use $OUTPUT->get_generated_color_for_id() instead.', DEBUG_DEVELOPER);
        return $OUTPUT->get_generated_color_for_id($courseid);
    }
}
