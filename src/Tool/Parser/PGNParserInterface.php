<?php

namespace Cmuset\ChessTools\Tool\Parser;

use Cmuset\ChessTools\Model\Game;

interface PGNParserInterface
{
    /**
     * @return Game|Game[]
     */
    public function parse(string $pgn): Game|array;
}
