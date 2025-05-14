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

namespace block_myoverviewcustom\external;

defined('MOODLE_INTERNAL') || die();

use context_user;
use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

require_once("{$CFG->dirroot}/calendar/externallib.php");
require_once($CFG->libdir . '/externallib.php');
/**
 * Class for fetching courses which have action event(s) and match given filter parameters.
 *
 * @package    block_myoverviewcustom
 * @copyright  2022 Michael Hawkins <michaelh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_enrolled_courses_by_timeline_classification extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters(
            [
                'classification' => new external_value(PARAM_ALPHA, 'future, inprogress, or past'),
                'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                'sort' => new external_value(PARAM_TEXT, 'Sort string', VALUE_DEFAULT, null),
                'customfieldname' => new external_value(
                    PARAM_ALPHANUMEXT,
                    'Used when classification = customfield',
                    VALUE_DEFAULT,
                    null
                ),
                'customfieldvalue' => new external_value(
                    PARAM_RAW,
                    'Used when classification = customfield',
                    VALUE_DEFAULT,
                    null
                ),
                'searchvalue' => new external_value(
                    PARAM_RAW,
                    'The value a user wishes to search against',
                    VALUE_DEFAULT,
                    null
                ),
                'requiredfields' => new external_multiple_structure(
                    new external_value(PARAM_ALPHANUMEXT, 'Field name to be included from the results', VALUE_DEFAULT),
                    'Array of the only field names that need to be returned. If empty, all fields will be returned.',
                    VALUE_DEFAULT,
                    []
                ),
            ]
        );
    }

    /**
     * Get courses matching the given timeline classification.
     *
     * NOTE: The offset applies to the unfiltered full set of courses before the classification
     * filtering is done.
     * E.g.
     * If the user is enrolled in 5 courses:
     * c1, c2, c3, c4, and c5
     * And c4 and c5 are 'future' courses
     *
     * If a request comes in for future courses with an offset of 1 it will mean that
     * c1 is skipped (because the offset applies *before* the classification filtering)
     * and c4 and c5 will be return.
     *
     * @param string $classification past, inprogress, or future
     * @param int $limit Result set limit
     * @param int $offset Offset the full course set before timeline classification is applied
     * @param string|null $sort SQL sort string for results
     * @param string|null $customfieldname
     * @param string|null $customfieldvalue
     * @param string|null $searchvalue
     * @param array $requiredfields Array of the only field names that need to be returned. If empty, all fields will be returned.
     * @return array list of courses and warnings
     */
    public static function execute(
        string $classification,
        int $limit = 0,
        int $offset = 0,
        string $sort = null,
        string $customfieldname = null,
        string $customfieldvalue = null,
        string $searchvalue = null,
        array $requiredfields = []
    ) {
        global $CFG, $PAGE, $USER;
        require_once($CFG->dirroot . '/course/lib.php');

        $params = self::validate_parameters(
            self::execute_parameters(),
            [
                'classification' => $classification,
                'limit' => $limit,
                'offset' => $offset,
                'sort' => $sort,
                'customfieldvalue' => $customfieldvalue,
                'searchvalue' => $searchvalue,
                'requiredfields' => $requiredfields,
            ]
        );

        $classification = $params['classification'];
        $limit = $params['limit'];
        $offset = $params['offset'];
        $sort = $params['sort'];
        $customfieldvalue = $params['customfieldvalue'];
        $searchvalue = clean_param($params['searchvalue'], PARAM_TEXT);
        $requiredfields = $params['requiredfields'];

        switch ($classification) {
            case COURSE_TIMELINE_ALLINCLUDINGHIDDEN:
                break;
            case COURSE_TIMELINE_ALL:
                break;
            case COURSE_TIMELINE_PAST:
                break;
            case COURSE_TIMELINE_INPROGRESS:
                break;
            case COURSE_TIMELINE_FUTURE:
                break;
            case COURSE_FAVOURITES:
                break;
            case COURSE_TIMELINE_HIDDEN:
                break;
            case COURSE_TIMELINE_SEARCH:
                break;
            case COURSE_CUSTOMFIELD:
                break;
            default:
                throw new invalid_parameter_exception('Invalid classification');
        }

        self::validate_context(context_user::instance($USER->id));
        $exporterfields = array_keys(\block_myoverviewcustom\external\course_summary_exporter::define_properties());
        // Get the required properties from the exporter fields based on the required fields.
        $requiredproperties = array_intersect($exporterfields, $requiredfields);
        // If the resulting required properties is empty, fall back to the exporter fields.
        if (empty($requiredproperties)) {
            $requiredproperties = $exporterfields;
        }

        $fields = join(',', $requiredproperties);
        $hiddencourses = get_hidden_courses_on_timeline();

        // If the timeline requires really all courses, get really all courses.
        if ($classification == COURSE_TIMELINE_ALLINCLUDINGHIDDEN) {
            $courses = course_get_enrolled_courses_for_logged_in_user(0, $offset, $sort, $fields, COURSE_DB_QUERY_LIMIT);

            // Otherwise if the timeline requires the hidden courses then restrict the result to only $hiddencourses.
        } else if ($classification == COURSE_TIMELINE_HIDDEN) {
            $courses = course_get_enrolled_courses_for_logged_in_user(
                0,
                $offset,
                $sort,
                $fields,
                COURSE_DB_QUERY_LIMIT,
                $hiddencourses
            );

            // Otherwise get the requested courses and exclude the hidden courses.
        } else if ($classification == COURSE_TIMELINE_SEARCH) {
            // Prepare the search API options.
            $searchcriteria['search'] = $searchvalue;
            $options = ['idonly' => true];
            $courses = course_get_enrolled_courses_for_logged_in_user_from_search(
                0,
                $offset,
                $sort,
                $fields,
                COURSE_DB_QUERY_LIMIT,
                $searchcriteria,
                $options
            );
        } else {
            $courses = course_get_enrolled_courses_for_logged_in_user(
                0,
                $offset,
                $sort,
                $fields,
                COURSE_DB_QUERY_LIMIT,
                [],
                $hiddencourses
            );
        }

        $favouritecourseids = [];
        $ufservice = \core_favourites\service_factory::get_service_for_user_context(\context_user::instance($USER->id));
        $favourites = $ufservice->find_favourites_by_type('core_course', 'courses');

        if ($favourites) {
            $favouritecourseids = array_map(
                function ($favourite) {
                    return $favourite->itemid;
                },
                $favourites
            );
        }

        if ($classification == COURSE_FAVOURITES) {
            [$filteredcourses, $processedcount] = course_filter_courses_by_favourites(
                $courses,
                $favouritecourseids,
                $limit
            );
        } else if ($classification == COURSE_CUSTOMFIELD) {
            [$filteredcourses, $processedcount] = course_filter_courses_by_customfield(
                $courses,
                $customfieldname,
                $customfieldvalue,
                $limit
            );
        } else {
            [$filteredcourses, $processedcount] = course_filter_courses_by_timeline_classification(
                $courses,
                $classification,
                $limit
            );
        }

        $renderer = $PAGE->get_renderer('core');
        $formattedcourses = array_map(function ($course) use ($renderer, $favouritecourseids) {
            if ($course == null) {
                return;
            }
            \context_helper::preload_from_record($course);
            $context = \context_course::instance($course->id);
            $isfavourite = false;
            if (in_array($course->id, $favouritecourseids)) {
                $isfavourite = true;
            }
            $exporter = new \block_myoverviewcustom\external\course_summary_exporter($course, ['context' => $context, 'isfavourite' => $isfavourite]);
            return $exporter->export($renderer);
        }, $filteredcourses);

        $formattedcourses = array_filter($formattedcourses, function ($course) {
            if ($course != null) {
                return $course;
            }
        });

        return [
            'courses' => $formattedcourses,
            'nextoffset' => $offset + $processedcount,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return \core_external\external_description
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'courses' => new external_multiple_structure(\block_myoverviewcustom\external\course_summary_exporter::get_read_structure(), 'Course'),
                'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request'),
            ]
        );
    }
}
