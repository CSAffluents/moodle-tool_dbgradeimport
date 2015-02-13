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
 * Database grade import plugin.
 *
 * This plugin create grade items and synchronise user grades from external database table.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../../config.php');

/**
 * Database grade import base class.
 *
 * @package    tool_dbgradeimport
 * @copyright  2015 Gilles-Philippe Leblanc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tool_dbgradeimport_base {

    /**
     * @var The name of this plugin.
     */
    const PLUGIN_NAME = 'tool_dbgradeimport';

    /**
     * Returns plugin config value
     * @param  string $name The name of the config to retrieve.
     * @return mixed hash-like object or single value, return false no config found.
     */
    public static function get_config($name = null) {
        return get_config(self::PLUGIN_NAME, $name);
    }

    /**
     * Sets plugin config value
     * @param  string $name name of config
     * @param  string $value string config value, null means delete
     * @return string value
     */
    public static function set_config($name, $value) {
        set_config($name, $value, self::PLUGIN_NAME);
    }

    /**
     * Returns a localized string for this plugin.
     * @param string $identifier The key identifier for the localized string
     * @param string|object|array $a An object, string or number that can be used
     *      within translation strings
     * @return string The localized string.
     */
    public static function get_string($identifier, $a = null) {
        return get_string($identifier, self::PLUGIN_NAME, $a);
    }
}
