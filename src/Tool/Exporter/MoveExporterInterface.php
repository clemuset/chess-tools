<?php

namespace Cmuset\ChessTools\Tool\Exporter;

use Cmuset\ChessTools\Model\Move;

interface MoveExporterInterface
{
    public function export(Move $move): string;
}
