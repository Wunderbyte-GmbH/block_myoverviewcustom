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
 * Library functions for overview.
 *
 * @package   block_myoverviewcustom
 * @copyright 2018 Peter Dias
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Constants for the user preferences grouping options
 */
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_ALLINCLUDINGHIDDEN', 'allincludinghidden');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_ALL', 'all');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_INPROGRESS', 'inprogress');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_FUTURE', 'future');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_PAST', 'past');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_FAVOURITES', 'favourites');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_HIDDEN', 'hidden');
define('BLOCK_MYOVERVIEWCUSTOMGROUPING_CUSTOMFIELD', 'customfield');

/**
 * Allows selection of all courses without a value for the custom field.
 */
define('BLOCK_MYOVERVIEWCUSTOMCUSTOMFIELD_EMPTY', -1);

/**
 * Constants for the user preferences sorting options
 * timeline
 */
define('BLOCK_MYOVERVIEWCUSTOMSORTING_TITLE', 'title');
define('BLOCK_MYOVERVIEWCUSTOMSORTING_LASTACCESSED', 'lastaccessed');
define('BLOCK_MYOVERVIEWCUSTOMSORTING_SHORTNAME', 'shortname');

/**
 * Constants for the user preferences view options
 */
define('BLOCK_MYOVERVIEWCUSTOMVIEW_CARD', 'card');
define('BLOCK_MYOVERVIEWCUSTOMVIEW_LIST', 'list');
define('BLOCK_MYOVERVIEWCUSTOMVIEW_SUMMARY', 'summary');

/**
 * Constants for the user paging preferences
 */
define('BLOCK_MYOVERVIEWCUSTOMPAGING_12', 12);
define('BLOCK_MYOVERVIEWCUSTOMPAGING_24', 24);
define('BLOCK_MYOVERVIEWCUSTOMPAGING_48', 48);
define('BLOCK_MYOVERVIEWCUSTOMPAGING_96', 96);
define('BLOCK_MYOVERVIEWCUSTOMPAGING_ALL', 0);

/**
 * Constants for the admin category display setting
 */
define('BLOCK_MYOVERVIEWCUSTOMDISPLAY_CATEGORIES_ON', 'on');
define('BLOCK_MYOVERVIEWCUSTOMDISPLAY_CATEGORIES_OFF', 'off');

/**
 * Get the current user preferences that are available
 *
 * @uses core_user::is_current_user
 *
 * @return array[] Array representing current options along with defaults
 */
function block_myoverviewcustom_user_preferences(): array {
    $preferences['block_myoverviewcustom_user_grouping_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEWCUSTOMGROUPING_ALL,
        'type' => PARAM_ALPHA,
        'choices' => [
            BLOCK_MYOVERVIEWCUSTOMGROUPING_ALLINCLUDINGHIDDEN,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_ALL,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_INPROGRESS,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_FUTURE,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_PAST,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_FAVOURITES,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_HIDDEN,
            BLOCK_MYOVERVIEWCUSTOMGROUPING_CUSTOMFIELD,
        ],
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['block_myoverviewcustom_user_grouping_customfieldvalue_preference'] = [
        'null' => NULL_ALLOWED,
        'default' => null,
        'type' => PARAM_RAW,
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['block_myoverviewcustom_user_sort_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEWCUSTOMSORTING_LASTACCESSED,
        'type' => PARAM_ALPHA,
        'choices' => [
            BLOCK_MYOVERVIEWCUSTOMSORTING_TITLE,
            BLOCK_MYOVERVIEWCUSTOMSORTING_LASTACCESSED,
            BLOCK_MYOVERVIEWCUSTOMSORTING_SHORTNAME,
        ],
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['block_myoverviewcustom_user_view_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEWCUSTOMVIEW_CARD,
        'type' => PARAM_ALPHA,
        'choices' => [
            BLOCK_MYOVERVIEWCUSTOMVIEW_CARD,
            BLOCK_MYOVERVIEWCUSTOMVIEW_LIST,
            BLOCK_MYOVERVIEWCUSTOMVIEW_SUMMARY,
        ],
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['/^block_myoverviewcustom_hidden_course_(\d)+$/'] = [
        'isregex' => true,
        'choices' => [0, 1],
        'type' => PARAM_INT,
        'null' => NULL_NOT_ALLOWED,
        'default' => 0,
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    $preferences['block_myoverviewcustom_user_paging_preference'] = [
        'null' => NULL_NOT_ALLOWED,
        'default' => BLOCK_MYOVERVIEWCUSTOMPAGING_12,
        'type' => PARAM_INT,
        'choices' => [
            BLOCK_MYOVERVIEWCUSTOMPAGING_12,
            BLOCK_MYOVERVIEWCUSTOMPAGING_24,
            BLOCK_MYOVERVIEWCUSTOMPAGING_48,
            BLOCK_MYOVERVIEWCUSTOMPAGING_96,
            BLOCK_MYOVERVIEWCUSTOMPAGING_ALL,
        ],
        'permissioncallback' => [core_user::class, 'is_current_user'],
    ];

    return $preferences;
}

/**
 * Pre-delete course hook to cleanup any records with references to the deleted course.
 *
 * @param stdClass $course The deleted course
 */
function block_myoverviewcustom_pre_course_delete(\stdClass $course) {
    // Removing any favourited courses which have been created for users, for this course.
    $service = \core_favourites\service_factory::get_service_for_component('core_course');
    $service->delete_favourites_by_type_and_item('courses', $course->id);
}
