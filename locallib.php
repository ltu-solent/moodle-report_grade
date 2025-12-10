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
 * This file contains functions used by the grade reports
 *
 * @package    report_grade
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/report/grade/lib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Convert grades to Solent Grade
 *
 * @param int $scaleid
 * @param int $grade
 * @return string
 */
function report_grade_convert_grade_report($scaleid, $grade) {
    $converted = \local_solsits\helper::convert_grade($scaleid, $grade);
    return $converted;
}

/**
 * Gets double marks for submission (basically a filter on doublemarks)
 *
 * @param array $doublemarks Records from doublemarks feedback table with grade info
 * @param int $iteminstance Assignment id
 * @param int $userid
 * @return array [scale, first, second]
 */
function report_grade_get_doublemarks($doublemarks, $iteminstance, $userid) {
    $return = [];
    foreach ($doublemarks as $doublemark) {
        if ($doublemark->userid == $userid && $iteminstance == $doublemark->assignment) {
            $scale = $doublemark->scale < 0 ? ltrim($doublemark->scale, '-') : 0;
            $return = [
                "scale" => $scale,
                "first" => $doublemark->first_grade,
                "second" => $doublemark->second_grade,
            ];
        }
    }
    return $return;
}

/**
 * Gets sample for user submission (basically a filter on all samples)
 *
 * @param array $samples Records from samples feedback table
 * @param int $iteminstance Assignment id
 * @param int $userid
 * @return string
 */
function report_grade_get_sample($samples, $iteminstance, $userid) {
    $return = '';
    foreach ($samples as $sample) {
        if ($sample->userid == $userid && $iteminstance == $sample->assignment) {
            if ($sample->sample == 1) {
                $return = get_string('yes');
            }
        }
    }
    return $return;
}

/**
 * Get external examiner name for currently loaded course
 *
 * @return stdClass
 */
function report_grade_get_external_examiner() {
    global $DB, $COURSE;
    $externalexaminer = $DB->get_record_sql(
        "SELECT CONCAT(u.firstname, ' ', u.lastname) name
            FROM {user} u
            INNER JOIN {role_assignments} ra ON ra.userid = u.id
            INNER JOIN {context} ct ON ct.id = ra.contextid
            INNER JOIN {course} c ON c.id = ct.instanceid
            INNER JOIN {role} r ON r.id = ra.roleid
            WHERE r.shortname = ?
            AND c.id = ?",
        [
            get_config('report_grade', 'externalexaminershortname'),
            $COURSE->id,
        ]
    );
    return $externalexaminer;
}

/**
 * Get moderators' names for currently loaded course
 *
 * @return array
 */
function report_grade_get_moderators() {
    global $DB, $COURSE;
    $externalexaminer = $DB->get_records_sql(
        "SELECT CONCAT(u.firstname, ' ', u.lastname) name
        FROM {user} u
        INNER JOIN {role_assignments} ra ON ra.userid = u.id
        INNER JOIN {context} ct ON ct.id = ra.contextid
        INNER JOIN {course} c ON c.id = ct.instanceid
        INNER JOIN {role} r ON r.id = ra.roleid
        WHERE r.shortname = ?
        AND c.id = ?",
        [get_config('report_grade', 'moderatorshortname'), $COURSE->id]
    );
    return $externalexaminer;
}

/**
 * Returns html link for the externalexaminer page for currently loaded course
 *
 * @return string
 */
function report_grade_get_ee_form_url() {
    global $DB, $COURSE;
    $dbman = $DB->get_manager();
    if ($dbman->table_exists('report_ee')) {
        $url = new moodle_url('/report/ee/index.php', ['id' => $COURSE->id]);
        $url = html_writer::link($url, get_string('reporturl', 'report_grade'), ['class' => 'btn btn-primary']);

        return $url;
    }
    return null;
}
