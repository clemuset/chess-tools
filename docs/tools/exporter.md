# Exporters

The library provides three exporters under `src/Tool/Exporter/`, converting model objects back to string notation.

---

## GameExporter

`Cmuset\ChessTools\Tool\Exporter\GameExporter`

Serializes a `Game` or a standalone `Variation` to PGN text.

### Interface

```php
interface GameExporterInterface {
    public function export(Game|Variation $game): string;
}
```

### Instantiation

```php
// Static factory (recommended):
$exporter = GameExporter::create();

// Full constructor:
$exporter = new GameExporter(new MoveExporter());
```

### Usage

The most convenient entry points are the methods on `Game` and `Variation`:

```php
$game->getPGN(): string
// Full PGN: [Tag "value"] headers, move text with comments, NAGs, nested variations,
// and result token.

$game->getLitePGN(): string
// Moves only — tags, comments, and variations are stripped before export.

$game->getVerbosePgn(): string
// Runs GameResolver before exporting so every move carries a computed source
// square, capture flag, and check/checkmate marker.

$variation->getPGN(): string
// Move text for the variation (no tags, no result token).

$variation->getLitePGN(): string
// Moves only, no comments or sub-variations.
```

Or call the exporter directly:

```php
use Cmuset\ChessTools\Tool\Exporter\GameExporter;

$exporter = GameExporter::create();
echo $exporter->export($game);      // full PGN
echo $exporter->export($variation); // variation move text
```

### Output format

**For a `Game`:**

1. One `[Tag "value"]` line per tag.
2. A blank line.
3. The main-line move text (see below).
4. The result value (`$game->getResult()->value`, e.g. `'1-0'`).

**Move text layout:**

- White moves are preceded by `{moveNumber}.` (e.g. `1.`).
- Black moves that follow a comment or variation are preceded by `{moveNumber}...` (ellipsis notation).
- Before-move comments are wrapped in `{ }` and printed before the move number.
- After-move comments are wrapped in `{ }` and printed after the SAN.
- NAGs are printed as `$N` after the SAN and before any after-move comment.
- Nested variations are printed as `( ... )` blocks after the main move.

### Example output

```
[Event "Linares 1993"]
[White "Kasparov, Garry"]
[Result "1-0"]

1. e4 e5 2. Nf3 Nc6 3. Bb5 {Ruy Lopez} 3... a6 $1
(3... Nf6 {Berlin Defense} 4. O-O)
4. Ba4 Nf6 1-0
```

---

## MoveExporter

`Cmuset\ChessTools\Tool\Exporter\MoveExporter`

Serializes a single `Move` object to its SAN string.

### Interface

```php
interface MoveExporterInterface {
    public function export(Move $move): string;
}
```

### Usage

```php
use Cmuset\ChessTools\Tool\Exporter\MoveExporter;

$exporter = new MoveExporter();
$san = $exporter->export($move);

// Or via Move directly:
$san = $move->getSAN();
```

### Output rules

1. **Castling**: returns `'O-O'` or `'O-O-O'` (always uses the `O` form, not `0`).
2. **Piece letter**: omitted for pawns; uppercase letter for all other pieces.
3. **Source square**: appended when `squareFrom` is set; otherwise `fileFrom` and `rankFrom` are used.
4. **Capture**: `'x'` is inserted before the destination square when `isCapture()` is true.
5. **Destination**: `to->value` appended.
6. **Promotion**: `'=' + uppercase promotion piece value` appended.
7. **Check / Checkmate**: `'+'` or `'#'` appended (checkmate takes priority).

Annotation strings are **not** emitted by `MoveExporter`. They are carried by `Move::getAnnotation()` but need to be appended manually if required.

### Example

```php
use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Enum\ColorEnum;

$move = Move::fromSAN('Nbd7', ColorEnum::BLACK);
// After MoveResolver sets squareFrom to CoordinatesEnum::B8:
echo $move->getSAN(); // 'Nb8d7'

$move = Move::fromSAN('O-O', ColorEnum::WHITE);
echo $move->getSAN(); // 'O-O'

$move = Move::fromSAN('e8=Q#', ColorEnum::WHITE);
echo $move->getSAN(); // 'e8=Q#'
```

---

## PositionExporter

`Cmuset\ChessTools\Tool\Exporter\PositionExporter`

Serializes a `Position` object to a FEN string.

### Interface

```php
interface PositionExporterInterface {
    public function export(Position $position): string;
}
```

### Usage

```php
use Cmuset\ChessTools\Tool\Exporter\PositionExporter;

$exporter = new PositionExporter();
$fen = $exporter->export($position);

// Or via Position directly:
$fen = $position->getFEN();
```

### Output format

Produces a standard six-field FEN string:

```
<piece placement> <side to move> <castling> <en passant> <halfmove clock> <fullmove number>
```

- Piece placement: ranks 8→1, each rank encoded left to right; consecutive empty squares replaced by a digit.
- Castling: remaining rights concatenated (`KQkq` order), or `-` when none.
- En passant: `$position->getEnPassantTarget()->value` or `-` when null.

### Example

```php
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\Parser\PGNParser;

$pos = Position::fromFEN(PGNParser::INITIAL_FEN);
$pos->applyMove('e4');
echo $pos->getFEN();
// 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1'
```
