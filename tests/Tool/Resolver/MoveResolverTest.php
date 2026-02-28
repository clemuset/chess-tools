<?php

namespace Cmuset\ChessTools\Tests\Tool\Resolver;

use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\Resolver\MoveResolver;
use PHPUnit\Framework\TestCase;

class MoveResolverTest extends TestCase
{
    private MoveResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = MoveResolver::create();
    }

    public function testResolveSimplePawnMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $move = Move::fromSAN('e4');

        self::assertSame('e4', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('e2e4', $move->getSAN());
    }

    public function testResolveKnightMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $move = Move::fromSAN('Nf3');

        self::assertSame('Nf3', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('Ng1f3', $move->getSAN());
    }

    public function testResolveCaptureMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/ppp1pppp/8/3p4/4P3/8/PPPP1PPP/RNBQKBNR w KQkq d6 0 2');
        $move = Move::fromSAN('exd5');

        self::assertSame('exd5', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('e4xd5', $move->getSAN());
    }

    public function testResolveEnPassantCapture(): void
    {
        $position = Position::fromFEN('rnbqkbnr/ppp1pppp/8/3pP3/8/8/PPPP1PPP/RNBQKBNR w KQkq d6 0 3');
        $move = Move::fromSAN('exd6');

        self::assertSame('exd6', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('e5xd6', $move->getSAN());
    }

    public function testResolveCheckMove(): void
    {
        $position = Position::fromFEN('rnbqkb1r/pppppppp/5n2/8/4N3/8/PPPPPPPP/RNBQKB1R w KQkq - 0 1');
        $move = Move::fromSAN('Nf6');

        self::assertSame('Nf6', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('Ne4xf6+', $move->getSAN());
    }

    public function testResolveAmbiguousKnightMove(): void
    {
        $position = Position::fromFEN('8/2N5/8/2N5/8/8/8/k6K w - - 0 1');
        $move = Move::fromSAN('N7e6');

        self::assertSame('N7e6', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('Nc7e6', $move->getSAN());
    }

    public function testResolveAmbiguousRookMove(): void
    {
        $position = Position::fromFEN('8/8/8/8/2R1R3/8/8/k6K w - - 0 1');
        $move = Move::fromSAN('Red4');

        self::assertSame('Red4', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('Re4d4', $move->getSAN());
    }

    public function testResolvePromotionMove(): void
    {
        $position = Position::fromFEN('8/P7/8/8/8/8/8/k6K w - - 0 1');
        $move = Move::fromSAN('a8=Q');

        self::assertSame('a8=Q', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('a7a8=Q+', $move->getSAN());
    }

    public function testResolveQueenMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppp1ppp/8/4p3/4P3/8/PPPP1PPP/RNBQKBNR w KQkq - 0 2');
        $move = Move::fromSAN('Qh5');

        self::assertSame('Qh5', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('Qd1h5', $move->getSAN());
    }

    public function testResolveCastlingKingside(): void
    {
        $position = Position::fromFEN('rnbqkbnr/ppp2ppp/3p4/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 0 4');
        $move = Move::fromSAN('O-O');

        self::assertSame('O-O', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('O-O', $move->getSAN());
    }

    public function testResolveBishopCapture(): void
    {
        $position = Position::fromFEN('rnbqkb1r/pppp1ppp/5n2/4p3/2B1P3/8/PPPP1PPP/RNBQK1NR w KQkq - 2 3');
        $move = Move::fromSAN('Bxf7');

        self::assertSame('Bxf7', $move->getSAN());

        $this->resolver->resolve($position, $move);

        self::assertSame('Bc4xf7+', $move->getSAN());
    }
}
