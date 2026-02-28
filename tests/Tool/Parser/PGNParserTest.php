<?php

namespace Cmuset\ChessTools\Tests\Tool\Parser;

use Cmuset\ChessTools\Enum\ColorEnum;
use Cmuset\ChessTools\Enum\ResultEnum;
use Cmuset\ChessTools\Model\Game;
use Cmuset\ChessTools\Tool\Parser\PGNParser;
use PHPUnit\Framework\TestCase;

class PGNParserTest extends TestCase
{
    private PGNParser $parser;

    protected function setUp(): void
    {
        $this->parser = PGNParser::create();
    }

    public function testParseGameWithTagsAndResultInTag(): void
    {
        $pgn = <<<'PGN'
[Event "Test"]
[Result "1-0"]

1. e4 e5 2. Nf3 Nc6 3. Bb5 *
PGN;
        $game = $this->parser->parse($pgn);
        self::assertInstanceOf(Game::class, $game);
        $mainLine = $game->getMainLine();

        self::assertSame('Test', $game->getTag('Event'));
        self::assertSame(ResultEnum::WHITE_WINS, $game->getResult());
        self::assertSame(ColorEnum::WHITE, $mainLine['1.']->getColor());

        // Traverse first three moves
        $n1 = $mainLine['1.']; // 1. e4
        $n2 = $mainLine['1...']; // ... e5
        $n3 = $mainLine['2.']; // 2. Nf3
        self::assertSame(1, $n1->getMoveNumber());
        self::assertSame('e4', $n1->getMove()->getSAN());
        self::assertSame(1, $n2->getMoveNumber());
        self::assertSame('e5', $n2->getMove()->getSAN());
        self::assertSame(2, $n3->getMoveNumber());
        self::assertSame('Nf3', $n3->getMove()->getSAN());
    }

    public function testParseGameWithoutTagsResultAtEnd(): void
    {
        $pgn = '1. e4 e5 2. Nf3 Nc6 3. Bb5 a6 1-0';
        $game = $this->parser->parse($pgn);
        self::assertInstanceOf(Game::class, $game);

        self::assertSame(ResultEnum::WHITE_WINS, $game->getResult());
        self::assertEmpty($game->getTags());
    }

    public function testParseCommentsAndNagsAndVariation(): void
    {
        $pgn = <<<'PGN'
[Event "VarTest"]

1. e4 {Central control} e5 2. Nf3 $1 (2... Nc6 3. Bb5) 2... d6 *
PGN;
        $game = $this->parser->parse($pgn);
        self::assertInstanceOf(Game::class, $game);
        $mainLine = $game->getMainLine();

        // Root -> 1.e4
        $e4 = $mainLine['1.'];
        self::assertSame('Central control', $e4->getComment());
        $nf3 = $mainLine['2.'];
        self::assertTrue(in_array(1, $nf3->getNags()));
        $variationNodes = $nf3->getVariations();
        self::assertCount(1, $variationNodes);
        $variation = $variationNodes[0];
        // Variation root has its own mainline (2... Nc6 3. Bb5)
        $nc6 = $variation['2...'] ?? null;
        self::assertNotNull($nc6);
        self::assertSame(2, $nc6->getMoveNumber());
        self::assertSame(ColorEnum::BLACK, $nc6->getColor());
    }

    public function testParseReturnsSingleGameForSingleGameInput(): void
    {
        $pgn = <<<'PGN'
[Event "Solo"]
[Result "1-0"]

1. e4 e5 1-0
PGN;
        $result = $this->parser->parse($pgn);

        self::assertInstanceOf(Game::class, $result);
        self::assertSame('Solo', $result->getTag('Event'));
    }

    public function testParseReturnsTwoGamesFromFile(): void
    {
        $pgn = file_get_contents(__DIR__ . '/../../resources/two_games.pgn');
        $result = $this->parser->parse($pgn);

        self::assertIsArray($result);
        self::assertCount(2, $result);

        self::assertSame('Game One', $result[0]->getTag('Event'));
        self::assertSame(ResultEnum::WHITE_WINS, $result[0]->getResult());
        self::assertSame('e4', $result[0]->getMove('1.')->getSAN());

        self::assertSame('Game Two', $result[1]->getTag('Event'));
        self::assertSame(ResultEnum::BLACK_WINS, $result[1]->getResult());
        self::assertSame('d4', $result[1]->getMove('1.')->getSAN());
    }

    public function testParseReturnsThreeGamesInlineSeparatedByOnlyStar(): void
    {
        $pgn = <<<'PGN'
[Event "Alpha"]
1. e4 *
[Event "Beta"]
1. d4 *
[Event "Gamma"]
1. c4 *
PGN;
        $result = $this->parser->parse($pgn);

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertSame('Alpha', $result[0]->getTag('Event'));
        self::assertSame('Beta', $result[1]->getTag('Event'));
        self::assertSame('Gamma', $result[2]->getTag('Event'));
    }

    public function testParseIgnoresResultTokenInsideBraceComment(): void
    {
        $pgn = <<<'PGN'
[Event "Game One"]
[Result "1-0"]
1. e4 {The score was 1-0 after this} e5 1-0
[Event "Game Two"]
[Result "0-1"]
1. d4 d5 0-1
PGN;
        $result = $this->parser->parse($pgn);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertSame('Game One', $result[0]->getTag('Event'));
        self::assertSame('Game Two', $result[1]->getTag('Event'));
    }

    public function testParseIgnoresResultTokenInsideTagValue(): void
    {
        $pgn = <<<'PGN'
[Event "Game One"]
[Result "1-0"]
1. e4 e5 1-0
[Event "Game Two"]
[Result "0-1"]
1. d4 d5 0-1
PGN;
        $result = $this->parser->parse($pgn);

        self::assertIsArray($result);
        self::assertCount(2, $result);
    }

    public function testParseDetectsMultipleGamesWithNoResultToken(): void
    {
        $pgn = '[Tag "Alpha"] 1. e4 [Tag "Beta"] 1. d4';
        $result = $this->parser->parse($pgn);

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertSame('Alpha', $result[0]->getTag('Tag'));
        self::assertSame('e4', $result[0]->getMainLine()['1.']->getMove()->getSAN());
        self::assertSame('Beta', $result[1]->getTag('Tag'));
        self::assertSame('d4', $result[1]->getMainLine()['1.']->getMove()->getSAN());
    }
}
