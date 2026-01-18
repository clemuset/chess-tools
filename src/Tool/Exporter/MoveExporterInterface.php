<?php

namespace Cmuset\PgnParser\Tool\Exporter;

use Cmuset\PgnParser\Model\Move;

interface MoveExporterInterface
{
    public function export(Move $move): string;
}
