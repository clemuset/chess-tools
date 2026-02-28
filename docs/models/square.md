# Square

`Cmuset\ChessTools\Model\Square`

Represents a single board square: a fixed `CoordinatesEnum` paired with an optional `PieceEnum`. Squares are owned and managed by `Position`; they are not created directly in application code.

## Constructor

```php
new Square(CoordinatesEnum $coordinates, ?PieceEnum $piece = null)
```

## Methods

```php
$square->getCoordinates(): CoordinatesEnum
// The immutable square coordinate (e.g. CoordinatesEnum::E4).

$square->getPiece(): ?PieceEnum
// The piece currently on this square, or null if empty.

$square->setPiece(?PieceEnum $piece): void
// Places a piece on this square, or clears it when null.

$square->isEmpty(): bool
// Returns true when no piece occupies this square.
```

## Obtaining squares

Squares are retrieved from a `Position` object — never constructed directly:

```php
use Cmuset\ChessTools\Enum\CoordinatesEnum;

// Single square by coordinates:
$square = $position->getSquare(CoordinatesEnum::E4);

// All 64 squares as array<string, Square>:
$squares = $position->getSquares(); // keyed by 'a1'–'h8'

// Squares matching a piece:
$squares = $position->find(PieceEnum::WHITE_PAWN); // Square[]

// First square matching a piece:
$square = $position->findOne(PieceEnum::BLACK_KING); // ?Square
```

## Usage

```php
use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Enum\PieceEnum;

$square = $position->getSquare(CoordinatesEnum::E1);
$square->getCoordinates(); // CoordinatesEnum::E1
$square->getPiece();       // PieceEnum::WHITE_KING (in the starting position)
$square->isEmpty();        // false

$square->setPiece(null);
$square->isEmpty();        // true
```

`Square` objects are returned by piece-search and attacker-search methods on `Position`, and by `PieceMoveApplier` when locating the source of a move.
