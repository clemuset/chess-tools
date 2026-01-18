<?php

namespace Cmuset\PgnParser\Tool\Resolver;

use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Tool\MoveApplier\PieceMoveApplier\PieceMoveApplier;

class MoveResolver implements MoveResolverInterface
{
    public static function create(): self
    {
        return new self();
    }

    public function resolve(Position $position, Move $move): void
    {
        if (!$move->isCastling()) {
            $pieceToMove = $move->getPiece();
            $pieceMoveApplier = PieceMoveApplier::createFromPiece($pieceToMove);

            $fromSquare = $pieceMoveApplier->findWherePieceIs($position, $move);
            $move->setSquareFrom($fromSquare);

            $isCapture = null !== $position->getPieceAt($move->getTo())
                || ($pieceToMove->isPawn() && $position->getEnPassantTarget() === $move->getTo());
            $move->setIsCapture($isCapture);
        }

        ($nextPos = clone $position)->applyMove($move);
        $move->setIsCheckmate($nextPos->isCheckmate());

        if (!$move->isCheckmate()) {
            $move->setIsCheck($nextPos->isCheck());
        }
    }
}
