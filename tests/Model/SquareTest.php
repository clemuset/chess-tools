<?php

namespace Cmuset\ChessTools\Tests\Model;

use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Enum\PieceEnum;
use Cmuset\ChessTools\Model\Square;
use PHPUnit\Framework\TestCase;

class SquareTest extends TestCase
{
    public function testSquareInitializationIsEmpty(): void
    {
        $square = new Square(CoordinatesEnum::E4);
        self::assertTrue($square->isEmpty());
        self::assertNull($square->getPiece());
    }

    public function testSetAndGetPiece(): void
    {
        $square = new Square(CoordinatesEnum::E4);
        $square->setPiece(PieceEnum::WHITE_KNIGHT);
        self::assertFalse($square->isEmpty());
        self::assertSame(PieceEnum::WHITE_KNIGHT, $square->getPiece());
    }

    public function testClearPiece(): void
    {
        $square = new Square(CoordinatesEnum::E4, PieceEnum::WHITE_BISHOP);
        $square->setPiece(null);
        self::assertTrue($square->isEmpty());
    }
}
