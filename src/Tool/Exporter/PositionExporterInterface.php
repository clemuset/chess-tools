<?php

namespace Cmuset\ChessTools\Tool\Exporter;

use Cmuset\ChessTools\Model\Position;

interface PositionExporterInterface
{
    public function export(Position $position): string;
}
