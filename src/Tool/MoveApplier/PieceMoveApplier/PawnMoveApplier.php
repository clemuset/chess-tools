<?php

namespace Cmuset\ChessTools\Tool\MoveApplier\PieceMoveApplier;

use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\MoveApplier\MoveHelper;

class PawnMoveApplier extends PieceMoveApplier
{
    public function apply(Position $position, Move $move): void
    {
        parent::apply($position, $move);

        if (null !== $move->getPromotion()) {
            $position->setPieceAt($move->getTo(), $move->getPromotion());
        }
    }

    public function canMove(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        return (MoveHelper::isPawnMove($from, $to, $position->getSideToMove())
            && MoveHelper::isPathClear($from, $to, $position))
            || $this->isAttacking($from, $to, $position);
    }

    public function isAttacking(CoordinatesEnum $from, CoordinatesEnum $to, Position $position): bool
    {
        return MoveHelper::isPawnCaptureMove($from, $to, $position->getSideToMove())
            && (null !== $position->getPieceAt($to) || $position->getEnPassantTarget() === $to);
    }
}
