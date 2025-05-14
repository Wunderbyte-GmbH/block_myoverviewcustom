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
 * Settings for the myoverviewcustom block
 *
 * @package    block_myoverviewcustom
 * @copyright  2019 Tom Dickman <tomdickman@catalyst-au.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/blocks/myoverviewcustom/lib.php');

    // Presentation options heading.
    $settings->add(new admin_setting_heading(
        'block_myoverviewcustom/appearance',
        get_string('appearance', 'admin'),
        ''
    ));

    // Display Course Categories on Dashboard course items (cards, lists, summary items).
    $settings->add(new admin_setting_configcheckbox(
        'block_myoverviewcustom/displaycategories',
        get_string('displaycategories', 'block_myoverviewcustom'),
        get_string('displaycategories_help', 'block_myoverviewcustom'),
        1
    ));

    // Enable / Disable available layouts.
    $choices = [BLOCK_MYOVERVIEWCUSTOMVIEW_CARD => get_string('card', 'block_myoverviewcustom')];
    $settings->add(new admin_setting_configmulticheckbox(
        'block_myoverviewcustom/layouts',
        get_string('layouts', 'block_myoverviewcustom'),
        get_string('layouts_help', 'block_myoverviewcustom'),
        $choices,
        $choices
    ));
    unset($choices);

    // Enable / Disable course filter items.
    $settings->add(new admin_setting_heading(
        'block_myoverviewcustom/availablegroupings',
        get_string('availablegroupings', 'block_myoverviewcustom'),
        get_string('availablegroupings_desc', 'block_myoverviewcustom')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_myoverviewcustom/displaygroupingallincludinghidden',
        get_string('allincludinghidden', 'block_myoverviewcustom'),
        '',
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_myoverviewcustom/displaygroupingall',
        get_string('all', 'block_myoverviewcustom'),
        '',
        1
    ));

    $choices = \core_customfield\api::get_fields_supporting_course_grouping();
    if ($choices) {
        $choices  = ['' => get_string('choosedots')] + $choices;
        $settings->add(new admin_setting_configselect(
            'block_myoverviewcustom/customfiltergrouping',
            get_string('customfiltergrouping', 'block_myoverviewcustom'),
            '',
            '',
            $choices
        ));
    } else {
        $settings->add(new admin_setting_configempty(
            'block_myoverviewcustom/customfiltergrouping',
            get_string('customfiltergrouping', 'block_myoverviewcustom'),
            get_string('customfiltergrouping_nofields', 'block_myoverviewcustom')
        ));
    }

    $settings->add(new admin_setting_configcheckbox(
        'block_myoverviewcustom/displaygroupingfavourites',
        get_string('favourites', 'block_myoverviewcustom'),
        '',
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'block_myoverviewcustom/displaygroupinghidden',
        get_string('hiddencourses', 'block_myoverviewcustom'),
        '',
        1
    ));
}
