<?php
/**
 * Copyright (c) Enalean, 2016. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tuleap\Svn\Admin\AccessControl;

use Tuleap\Svn\Repository\Repository;

class NullAccessFileHistory extends AccessFileHistory {

    public function __construct(Repository $repository) {
        $id             = null;
        $version_number = 0;
        $content        = '';
        $version_date   = 0;

        parent::__construct($repository, $id, $version_number, $content, $version_date);
    }
}