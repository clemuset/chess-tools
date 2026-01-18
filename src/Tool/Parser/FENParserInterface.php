<?php

namespace Cmuset\PgnParser\Tool\Parser;

use Cmuset\PgnParser\Model\Position;

interface FENParserInterface
{
    public function parse(string $fen): Position;
}
