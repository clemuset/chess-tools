<?php

namespace Cmuset\PgnParser\Tool\Exporter;

use Cmuset\PgnParser\Model\Game;

interface GameExporterInterface
{
    public function export(Game $game): string;
}
