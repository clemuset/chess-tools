<?php

namespace Cmuset\PgnParser\Tests\Tool\Resolver;

use Cmuset\PgnParser\Enum\ResultEnum;
use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Tool\Resolver\GameResolver;
use PHPUnit\Framework\TestCase;

class GameResolverTest extends TestCase
{
    private GameResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = GameResolver::create();
    }

    public function testResolveSingleMove(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNode('e4');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('1. e4', $beforePgn);
        self::assertStringNotContainsString('e2e4', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('1. e2e4', $afterPgn);
    }

    public function testResolveGameWithTwoMoves(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'c5');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('1. e4 c5', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('1. e2e4 c7c5', $afterPgn);
    }

    public function testResolveOpeningSequence(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'e5', 'Nf3', 'Nc6', 'Bb5');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('1. e4 e5', $beforePgn);
        self::assertStringContainsString('2. Nf3 Nc6', $beforePgn);
        self::assertStringContainsString('3. Bb5', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('1. e2e4 e7e5', $afterPgn);
        self::assertStringContainsString('2. Ng1f3 Nb8c6', $afterPgn);
        self::assertStringContainsString('3. Bf1b5', $afterPgn);
    }

    public function testResolveGameWithCapture(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'e5', 'Nf3', 'Nc6', 'Bb5', 'a6', 'Bxc6');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('Bxc6', $beforePgn);
        self::assertStringNotContainsString('Bb5xc6', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('Bb5xc6', $afterPgn);
    }

    public function testResolveGameWithCheckMate(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'e5', 'Bc4', 'Nc6', 'Qh5', 'Nf6', 'Qf7');

        $beforePgn = $game->getPGN();

        self::assertSame('1. e4 e5 2. Bc4 Nc6 3. Qh5 Nf6 4. Qf7 *', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertSame('1. e2e4 e7e5 2. Bf1c4 Nb8c6 3. Qd1h5 Ng8f6 4. Qh5xf7# 1-0', $afterPgn);
    }

    public function testResolveGamePreservesTags(): void
    {
        $game = new Game();
        $game->setTag('Event', 'World Championship');
        $game->setTag('Site', 'Paris');
        $game->setTag('White', 'Carlsen');
        $game->setTag('Black', 'Caruana');
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'c5');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('[Event "World Championship"]', $beforePgn);
        self::assertStringContainsString('[White "Carlsen"]', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('[Event "World Championship"]', $afterPgn);
        self::assertStringContainsString('[White "Carlsen"]', $afterPgn);
        self::assertStringContainsString('[Black "Caruana"]', $afterPgn);
        self::assertStringContainsString('1. e2e4 c7c5', $afterPgn);
    }

    public function testResolveGameWithResult(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'c5', 'Nf3');
        $game->setResult(ResultEnum::WHITE_WINS);

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('1-0', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('1-0', $afterPgn);
        self::assertStringContainsString('e2e4', $afterPgn);
    }

    public function testResolveComplexGame(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'));
        $game->addMoveNodes('e4', 'c5', 'Nf3', 'd6', 'd4', 'cxd4', 'Nxd4', 'Nf6', 'Nc3', 'a6');

        $beforePgn = $game->getPGN();

        self::assertStringNotContainsString('c7c5', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('e2e4', $afterPgn);
        self::assertStringContainsString('c7c5', $afterPgn);
        self::assertStringContainsString('d2d4', $afterPgn);
        self::assertStringContainsString('c5xd4', $afterPgn);
        self::assertStringContainsString('Nf3xd4', $afterPgn);
    }

    public function testResolveGameFromNonStandardPosition(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('r1bqkb1r/pppp1ppp/2n2n2/1B2p3/4P3/5N2/PPPP1PPP/RNBQK2R w KQkq - 4 4'));
        $game->addMoveNodes('O-O', 'Nxe4');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('1. O-O Nxe4', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('1. O-O', $afterPgn);
        self::assertStringContainsString('Nf6xe4', $afterPgn);
    }

    public function testResolveGameWithAmbiguousMove(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('8/8/8/8/2R1R3/8/8/k6K w - - 0 1'));
        $game->addMoveNode('Red4');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('Red4', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('Re4d4', $afterPgn);
    }

    public function testResolveGameWithPromotion(): void
    {
        $game = new Game();
        $game->setInitialPosition(Position::fromFEN('8/P7/8/8/8/8/8/k6K w - - 0 1'));
        $game->addMoveNode('a8=Q');

        $beforePgn = $game->getPGN();

        self::assertStringContainsString('a8=Q', $beforePgn);

        $this->resolver->resolve($game);

        $afterPgn = $game->getPGN();

        self::assertStringContainsString('a7a8=Q', $afterPgn);
    }
}
