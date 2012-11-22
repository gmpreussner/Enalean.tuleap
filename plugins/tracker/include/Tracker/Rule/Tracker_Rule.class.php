<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

/**
* Rule between two dynamic fields
*
* For a tracker, if a source field is selected to a specific value,
* then target field will react, depending of the implementation of the rule.
*
* @abstract
*/
/* abstract */ class Tracker_Rule {
    const RULETYPE_HIDDEN       = 1;
    const RULETYPE_DISABLED     = 2;
    const RULETYPE_MANDATORY    = 3;
    const RULETYPE_VALUE        = 4;
    const RULETYPE_DATE         = 5;
    
    var $id;
    var $tracker_id;
    var $source_field;
    var $target_field;
    var $source_value;
    
    
    function Tracker_Rule($id, $tracker_id, $source_field, $source_value, $target_field) {
        $this->id                = $id;
        $this->tracker_id        = $tracker_id;
        $this->source_field      = $source_field;
        $this->source_value      = $source_value;
        $this->target_field      = $target_field;
    }
}
?>