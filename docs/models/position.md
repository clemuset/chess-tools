# Position

`Cmuset\ChessTools\Model\Position`

Represents a complete board state compatible with FEN notation: piece placement, side to move, castling rights, en passant target, halfmove clock, and fullmove number.

## Static Factories

```php
Position::fromFEN(string $fen): Position
// Parses a FEN string into a Position object.
// Throws FENParsingException on invalid input.
```

```php
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\Parser\PGNParser;

$pos = Position::fromFEN(PGNParser::INITIAL_FEN); // standard starting position
$pos = Position::fromFEN('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1');
```

## Export

```php
$position->getFEN(): string
// Serializes the position back to a valid FEN string.

$position->dump(): string
// Returns an ASCII representation of the board (rank 8 at the top).
// Useful for debugging. Uppercase = white, lowercase = black, '.' = empty.
```

## Board access

```php
$position->getSquares(): array
// All 64 Square objects, keyed by coordinate string ('a1'–'h8').

$position->getSquare(CoordinatesEnum $coordinates): Square
// Returns the Square at the given coordinates.

$position->getPieceAt(CoordinatesEnum $square): ?PieceEnum
// Returns the piece on a square, or null if empty.

$position->setPieceAt(CoordinatesEnum $square, ?PieceEnum $piece): void
// Places a piece on a square, or clears it when null.
```

## Side to move

```php
$position->getSideToMove(): ColorEnum
$position->setSideToMove(ColorEnum $color): void

$position->toggleSideToMove(): void
// Switches from WHITE to BLACK or vice versa (called automatically after each move).
```

## Castling rights

```php
$position->getCastlingRights(): CastlingEnum[]
$position->setCastlingRights(array $rights): void
$position->hasCastlingRight(CastlingEnum $castling): bool
$position->removeCastlingRight(CastlingEnum $castling): void
$position->castlingIsAllowed(CastlingEnum $castling): bool
// Checks both that the right exists and that it is the correct side to move.
```

## En passant

```php
$position->getEnPassantTarget(): ?CoordinatesEnum
// The square a pawn may move to when capturing en passant, or null.

$position->setEnPassantTarget(?CoordinatesEnum $target): void
// Set automatically by MoveApplier after a two-step pawn advance.
```

## Move counters

```php
$position->getHalfmoveClock(): int
// Number of plies since the last capture or pawn move (50-move rule).

$position->setHalfmoveClock(int $n): void

$position->getFullmoveNumber(): int
// Full move count; starts at 1, increments after black moves.

$position->setFullmoveNumber(int $n): void
```

## Move application

```php
$position->applyMove(string|Move $move): void
// Applies a single move to the position, mutating it in place.
// Accepts a SAN string or a Move object. SAN strings are parsed using the
// current side to move as the color.
// Throws MoveApplyingException on illegal moves.

$position->applyVariation(string|Variation $variation): void
// Applies every move in a Variation sequentially.
// Accepts a Variation object or a PGN string (parsed first).
```

## Piece search

```php
$position->find(PieceEnum ...$pieces): Square[]
// Returns all squares containing any of the given piece types.

$position->findByFile(PieceEnum $piece, string $file): Square[]
// Finds all pieces of the given type on the specified file ('a'–'h').

$position->findByRank(PieceEnum $piece, int $rank): Square[]
// Finds all pieces of the given type on the specified rank (1–8).

$position->findOne(PieceEnum $piece): ?Square
// Returns the first square containing the given piece, or null.
```

## Attack queries

```php
$position->findAttackers(Square|CoordinatesEnum $square, ColorEnum $attackerColor): Square[]
// Returns all squares from which a piece of $attackerColor attacks $square.

$position->hasAttacker(Square|CoordinatesEnum $square, ColorEnum $attackerColor): bool
// Returns true when at least one piece of $attackerColor attacks $square.
```

## Game state

```php
$position->isCheck(): bool
// True when the side to move's king is in check.

$position->isCheckmate(): bool
// True when the side to move is in check and has no legal moves.

$position->isStaleMate(): bool
// True when the side to move is not in check but has no legal moves.

$position->getLegalMoves(): Move[]
// Returns all legal moves for the side to move (including castling).
// Each Move has piece and to set; squareFrom is also populated.
```

## Iteration

```php
$position->iterateSquaresWithPiece(?ColorEnum $color = null): iterable
// Generator that yields Square objects for all occupied squares.
// Pass a ColorEnum to restrict to one side; null yields both colors.
```

## Cloning

`Position` implements `__clone()` with deep copying of all 64 squares. Use `clone $position` when you need to speculatively apply moves without mutating the original.

## Example

```php
use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Enum\PieceEnum;
use Cmuset\ChessTools\Tool\Parser\PGNParser;

$pos = Position::fromFEN(PGNParser::INITIAL_FEN);

$pos->getPieceAt(CoordinatesEnum::E1); // PieceEnum::WHITE_KING
$pos->getSideToMove();                 // ColorEnum::WHITE

$pos->applyMove('e4');
$pos->getSideToMove();                 // ColorEnum::BLACK
$pos->getFEN();
// 'rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1'

$legalMoves = $pos->getLegalMoves();   // All legal black moves
$pos->isCheck();                       // false
```
