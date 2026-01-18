<?php

namespace Cmuset\PgnParser\Tool\MoveApplier\PieceMoveApplier;

use Cmuset\PgnParser\Enum\CoordinatesEnum;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Tool\MoveApplier\MoveHelper;

class KnightMoveApplier extends PieceMoveApplier
{
    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        return MoveHelper::isKnightMove($from, $to);
    }
}
