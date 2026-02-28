<?php

namespace Cmuset\ChessTools\Tool\MoveApplier\PieceMoveApplier;

use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\MoveApplier\MoveHelper;

class QueenMoveApplier extends PieceMoveApplier
{
    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        $isQueenMove = MoveHelper::isStraightMove($from, $to) || MoveHelper::isSlidingMove($from, $to);

        return $isQueenMove && MoveHelper::isPathClear($from, $to, $position);
    }
}
