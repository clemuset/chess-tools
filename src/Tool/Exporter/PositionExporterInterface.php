<?php

namespace Cmuset\PgnParser\Tool\Exporter;

use Cmuset\PgnParser\Model\Position;

interface PositionExporterInterface
{
    public function export(Position $position): string;
}
