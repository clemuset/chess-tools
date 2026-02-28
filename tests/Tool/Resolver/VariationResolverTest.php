<?php

namespace Cmuset\ChessTools\Tests\Tool\Resolver;

use Cmuset\ChessTools\Enum\ColorEnum;
use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Model\MoveNode;
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Model\Variation;
use Cmuset\ChessTools\Tool\Resolver\VariationResolver;
use PHPUnit\Framework\TestCase;

class VariationResolverTest extends TestCase
{
    private VariationResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = VariationResolver::create();
    }

    public function testResolveEmptyVariation(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $variation = new Variation();

        $beforePgn = $variation->getPGN();
        $this->resolver->resolve($position, $variation);
        $afterPgn = $variation->getPGN();

        self::assertSame('', $beforePgn);
        self::assertSame('', $afterPgn);
    }

    public function testResolveSingleMove(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');
        $move = Move::fromSAN('e4');
        $node = new MoveNode();
        $node->setMove($move);
        $variation = new Variation($node);

        $beforePgn = $variation->getPGN();

        self::assertSame('1. e4', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertSame('1. e2e4', $afterPgn);
    }

    public function testResolveTwoMoves(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');

        $move1 = Move::fromSAN('e4');
        $node1 = new MoveNode();
        $node1->setMove($move1);

        $move2 = Move::fromSAN('e5', ColorEnum::BLACK);
        $node2 = new MoveNode();
        $node2->setMove($move2);

        $variation = new Variation($node1, $node2);

        $beforePgn = $variation->getPGN();

        self::assertSame('1. e4 e5', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertSame('1. e2e4 e7e5', $afterPgn);
    }

    public function testResolveOpeningSequence(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');

        $variation = new Variation(
            new MoveNode('e4'),
            new MoveNode('c5'),
            new MoveNode('Nf3'),
            new MoveNode('d6')
        );

        $beforePgn = $variation->getPGN();

        self::assertSame('1. e4 c5 2. Nf3 d6', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertSame('1. e2e4 c7c5 2. Ng1f3 d7d6', $afterPgn);
    }

    public function testResolveVariationWithCapture(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');

        $variation = new Variation(
            new MoveNode('e4'),
            new MoveNode('e5'),
            new MoveNode('Nf3'),
            new MoveNode('Nc6'),
            new MoveNode('Bb5'),
            new MoveNode('a6'),
            new MoveNode('Bxc6')
        );

        $beforePgn = $variation->getPGN();

        self::assertStringContainsString('Bxc6', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertStringContainsString('Bb5xc6', $afterPgn);
    }

    public function testResolveVariationWithCheckMate(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');

        $variation = new Variation(
            new MoveNode('e4'),
            new MoveNode('e5'),
            new MoveNode('Bc4'),
            new MoveNode('Nc6'),
            new MoveNode('Qh5'),
            new MoveNode('Nf6'),
            new MoveNode('Qxf7')
        );

        $beforePgn = $variation->getPGN();

        self::assertStringContainsString('Qxf7', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertStringContainsString('Qh5xf7#', $afterPgn);
    }

    public function testResolveVariationWithAmbiguousMove(): void
    {
        $position = Position::fromFEN('8/8/8/8/2R1R3/8/8/k6K w - - 0 1');

        $variation = new Variation(
            new MoveNode('Red4')
        );

        $beforePgn = $variation->getPGN();

        self::assertSame('1. Red4', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertSame('1. Re4d4', $afterPgn);
    }

    public function testResolveVariationWithCastling(): void
    {
        $position = Position::fromFEN('rnbqkbnr/ppp2ppp/3p4/4p3/2B1P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 0 4');

        $variation = new Variation(
            new MoveNode('O-O')
        );

        $beforePgn = $variation->getPGN();

        self::assertSame('1. O-O', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertSame('1. O-O', $afterPgn);
    }

    public function testResolveVariationWithPromotion(): void
    {
        $position = Position::fromFEN('8/P7/8/8/8/8/8/k6K w - - 0 1');

        $variation = new Variation(
            new MoveNode('a8=Q')
        );

        $beforePgn = $variation->getPGN();

        self::assertSame('1. a8=Q', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertSame('1. a7a8=Q+', $afterPgn);
    }

    public function testResolveLongVariation(): void
    {
        $position = Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1');

        $variation = new Variation(
            new MoveNode('e4'),
            new MoveNode('c5'),
            new MoveNode('Nf3'),
            new MoveNode('d6'),
            new MoveNode('d4'),
            new MoveNode('cxd4'),
            new MoveNode('Nxd4'),
            new MoveNode('Nf6')
        );

        $beforePgn = $variation->getPGN();

        self::assertStringNotContainsString('e2e4', $beforePgn);

        $this->resolver->resolve($position, $variation);

        $afterPgn = $variation->getPGN();

        self::assertStringContainsString('e2e4', $afterPgn);
        self::assertStringContainsString('c7c5', $afterPgn);
        self::assertStringContainsString('d2d4', $afterPgn);
    }
}
