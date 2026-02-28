<?php

namespace Cmuset\ChessTools\Tool\Resolver;

use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Model\Position;

interface MoveResolverInterface
{
    public function resolve(Position $position, Move $move): void;
}
