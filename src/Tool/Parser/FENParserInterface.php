<?php

namespace Cmuset\ChessTools\Tool\Parser;

use Cmuset\ChessTools\Model\Position;

interface FENParserInterface
{
    public function parse(string $fen): Position;
}
