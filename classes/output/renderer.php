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
 * myoverviewcustom block rendrer
 *
 * @package    block_myoverviewcustom
 * @copyright  2016 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_myoverviewcustom\output;
defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;

/**
 * myoverviewcustom block renderer
 *
 * @package    block_myoverviewcustom
 * @copyright  2016 Ryan Wyllie <ryan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Return the main content for the block overview.
     *
     * @param main $main The main renderable
     * @return string HTML string
     */
    public function render_main(main $main) {
        global $USER;

        if (!count(enrol_get_all_users_courses($USER->id, true))) {
            return $this->render_from_template(
                'block_myoverviewcustom/zero-state',
                $main->export_for_zero_state_template($this)
            );
        }
        return $this->render_from_template('block_myoverviewcustom/main', $main->export_for_template($this));
    }
}
