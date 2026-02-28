<?php

namespace Cmuset\ChessTools\Tool\MoveApplier;

use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Model\Position;

interface MoveApplierInterface
{
    public function apply(Position $position, Move $move): void;
}
