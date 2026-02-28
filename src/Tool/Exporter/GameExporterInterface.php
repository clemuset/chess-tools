<?php

namespace Cmuset\ChessTools\Tool\Exporter;

use Cmuset\ChessTools\Model\Game;

interface GameExporterInterface
{
    public function export(Game $game): string;
}
