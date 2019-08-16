<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

declare(strict_types = 1);

namespace Tuleap\Docman\REST\v1\Metadata;

class MetadataToUpdate
{
    /**
     * @var \Docman_Metadata
     */
    private $metadata;
    /**
     * @var array | string
     */
    private $value;

    private function __construct(\Docman_Metadata $metadata, $value)
    {
        $this->metadata = $metadata;
        $this->value    = $value;
    }

    public static function buildMetadataRepresentation(\Docman_Metadata $metadata, $value): self
    {
        return new self($metadata, $value);
    }

    /**
     * @return \Docman_Metadata
     */
    public function getMetadata(): \Docman_Metadata
    {
        return $this->metadata;
    }

    /**
     * @return array | string
     */
    public function getValue()
    {
        return $this->value;
    }
}