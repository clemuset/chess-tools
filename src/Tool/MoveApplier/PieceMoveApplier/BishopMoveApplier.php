<?php

namespace Cmuset\ChessTools\Tool\MoveApplier\PieceMoveApplier;

use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\MoveApplier\MoveHelper;

class BishopMoveApplier extends PieceMoveApplier
{
    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        return MoveHelper::isSlidingMove($from, $to) && MoveHelper::isPathClear($from, $to, $position);
    }
}
