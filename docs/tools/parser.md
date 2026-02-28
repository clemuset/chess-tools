# Parsers

The library provides three parsers under `src/Tool/Parser/`, one for each notation format.

---

## PGNParser

`Cmuset\ChessTools\Tool\Parser\PGNParser`

Parses Portable Game Notation strings into `Game` objects. Handles single and multi-game files, arbitrarily nested variations, comments, NAGs, and custom starting positions via the `[FEN "..."]` tag.

### Interface

```php
interface PGNParserInterface {
    public function parse(string $pgn): Game|Game[];
}
```

### Instantiation

```php
// Static factory with default sub-parsers (recommended):
$parser = PGNParser::create();

// Full dependency-injection constructor:
$parser = new PGNParser(new FENParser(), new SANParser());
```

The simplest entry point is the static shortcut on `Game`:

```php
use Cmuset\ChessTools\Model\Game;

$game  = Game::fromPGN($singleGamePgn);  // Game
$games = Game::fromPGN($multiGamePgn);   // Game[]
```

### Constants

```php
PGNParser::INITIAL_FEN
// 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1'
// Used as the default starting position when no [FEN] tag is present.
```

### Token types (internal constants)

The tokenizer produces these token types before building the move tree:

| Constant | Value | Description |
|---|---|---|
| `T_COMMENT` | `'comment'` | `{ }` brace comment or `;` line comment |
| `T_NAG` | `'nag'` | Numeric Annotation Glyph (`$1`, `$2`, …) |
| `T_MOVENUM` | `'movenum'` | Move number (`1.`, `1...`) |
| `T_LPAR` | `'lpar'` | Opening parenthesis — starts a variation |
| `T_RPAR` | `'rpar'` | Closing parenthesis — ends a variation |
| `T_SAN` | `'san'` | A move in SAN notation |
| `T_RESULT` | `'result'` | Game result token (`1-0`, `0-1`, `1/2-1/2`, `*`) |

### What is parsed

- `[Tag "value"]` header pairs (any number, any order)
- `[FEN "..."]` tag — sets a custom initial position
- Move numbers (`1.`, `1...` with ellipsis for black)
- SAN moves including castling (`O-O`, `O-O-O`, `0-0`, `0-0-0`)
- Brace comments `{ text }` (before-move and after-move)
- Semicolon line comments `; text` (treated as comments)
- NAGs (`$1`, `$2`, …)
- Variations in `( )` parentheses, arbitrarily nested
- Game result tokens — stored on the `Game` and used as end-of-game markers
- Multiple games in a single string

### Multi-game files

When the input contains more than one game (detected by finding a second tag block after movetext), the parser returns `Game[]`:

```php
$result = Game::fromPGN($file);

if (is_array($result)) {
    foreach ($result as $game) {
        echo $game->getTag('White') . ' vs ' . $game->getTag('Black') . "\n";
    }
}
```

### Error handling

Throws `Cmuset\ChessTools\Tool\Parser\Exception\PGNParsingException` (extends `ParsingException`) on malformed input.

---

## SANParser

`Cmuset\ChessTools\Tool\Parser\SANParser`

Parses a Standard Algebraic Notation string into a `Move` object.

### Interface

```php
interface SANParserInterface {
    public function parse(string $san, ColorEnum $color): Move;
}
```

### Usage

```php
use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Enum\ColorEnum;

// Via Move static factory:
$move = Move::fromSAN('Nf3', ColorEnum::WHITE);
$move = Move::fromSAN('e8=Q+', ColorEnum::WHITE);
$move = Move::fromSAN('O-O-O', ColorEnum::BLACK);
$move = Move::fromSAN('Bxd5#', ColorEnum::BLACK);

// Directly:
$parser = new SANParser();
$move   = $parser->parse('exd5', ColorEnum::WHITE);
```

### Accepted SAN forms

| Pattern | Example | Notes |
|---|---|---|
| Pawn push | `e4`, `d5` | No piece letter; color determines the piece |
| Piece move | `Nf3`, `Bb5` | Uppercase piece letter + destination |
| Disambiguation | `Nbd7`, `R1e4`, `Qd1e2` | File, rank, or full source square |
| Capture | `exd5`, `Nxf6` | `x` before the destination square |
| En passant | `exd6 e.p.` | The `e.p.` suffix is stripped automatically |
| Promotion | `e8=Q`, `axb1=N` | `=` followed by piece letter |
| Check | `Bg5+` | `+` suffix |
| Checkmate | `Qh7#` | `#` suffix |
| Kingside castle | `O-O` | Also accepts `0-0` |
| Queenside castle | `O-O-O` | Also accepts `0-0-0` |
| Annotation | `Nf3!`, `e5?`, `Rxe6!!` | `!`, `?`, `!!`, `??`, `!?`, `?!` |

### SAN validation pattern

```php
SANParser::SAN_PATTERN
// Regex used to validate the SAN string before parsing.
```

### Note on source squares

The parser only populates `fileFrom`, `rowFrom`, or `squareFrom` when the SAN contains an explicit disambiguation (`Nbd7`, `R1e4`). The full source square is computed later by `MoveResolver` or `MoveApplier`.

### Error handling

Throws `Cmuset\ChessTools\Tool\Parser\Exception\SANParsingException` on invalid SAN strings.

---

## FENParser

`Cmuset\ChessTools\Tool\Parser\FENParser`

Parses a Forsyth-Edwards Notation string into a `Position` object.

### Interface

```php
interface FENParserInterface {
    public function parse(string $fen): Position;
}
```

### Usage

```php
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\Parser\PGNParser;

// Via Position static factory:
$pos = Position::fromFEN(PGNParser::INITIAL_FEN);
$pos = Position::fromFEN('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1');

// Directly:
$parser   = new FENParser();
$position = $parser->parse($fen);
```

### FEN format

A valid FEN string has exactly six space-separated fields:

```
<piece placement> <side to move> <castling> <en passant> <halfmove clock> <fullmove number>
```

| Field | Example | Description |
|---|---|---|
| Piece placement | `rnbqkbnr/pppp...` | Ranks 8→1, `/` separator; digits for empty squares |
| Side to move | `w` or `b` | Who plays next |
| Castling | `KQkq` or `-` | Remaining castling rights |
| En passant | `e3` or `-` | Target square of a two-step pawn advance (rank 3 or 6) |
| Halfmove clock | `0` | Plies since last capture or pawn move |
| Fullmove number | `1` | Increments after every black move |

### FEN validation pattern

```php
FENParser::FEN_PATTERN
// Regex used to validate the FEN string before parsing.
```

### Error handling

Throws `Cmuset\ChessTools\Tool\Parser\Exception\FENParsingException` when:
- The FEN string does not match the expected format.
- The en passant target square is not on rank 3 or rank 6.

---

## Exception hierarchy

All parser exceptions extend the same base class:

```
ParsingException (abstract)
├── PGNParsingException
├── SANParsingException
└── FENParsingException
```

```php
use Cmuset\ChessTools\Tool\Parser\Exception\PGNParsingException;
use Cmuset\ChessTools\Tool\Parser\Exception\SANParsingException;
use Cmuset\ChessTools\Tool\Parser\Exception\FENParsingException;

try {
    $game = Game::fromPGN($input);
} catch (PGNParsingException $e) {
    echo 'PGN error: ' . $e->getMessage();
}

try {
    $move = Move::fromSAN('invalid!', ColorEnum::WHITE);
} catch (SANParsingException $e) {
    echo 'SAN error: ' . $e->getMessage();
}

try {
    $pos = Position::fromFEN('not a fen');
} catch (FENParsingException $e) {
    echo 'FEN error: ' . $e->getMessage();
}
```
