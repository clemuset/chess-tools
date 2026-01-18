<?php

namespace Cmuset\PgnParser\Tool\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Tool\MoveApplier\MoveHelper;

class BishopMoveApplier extends PieceMoveApplier
{
    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        return MoveHelper::isSlidingMove($from, $to) && MoveHelper::isPathClear($from, $to, $position);
    }
}
