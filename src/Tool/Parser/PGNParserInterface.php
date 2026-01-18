<?php

namespace Cmuset\PgnParser\Tool\Parser;

use Cmuset\PgnParser\Model\Game;

interface PGNParserInterface
{
    public function parse(string $pgn): Game;
}
