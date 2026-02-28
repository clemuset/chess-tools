# PieceEnum

`Cmuset\ChessTools\Enum\PieceEnum`

String-backed PHP enum representing all 12 piece types (6 types × 2 colors). Values follow FEN notation: uppercase letters for white, lowercase for black.

## Cases

| Case           | Value | Case           | Value |
|----------------|-------|----------------|-------|
| `WHITE_KING`   | `'K'` | `BLACK_KING`   | `'k'` |
| `WHITE_QUEEN`  | `'Q'` | `BLACK_QUEEN`  | `'q'` |
| `WHITE_ROOK`   | `'R'` | `BLACK_ROOK`   | `'r'` |
| `WHITE_BISHOP` | `'B'` | `BLACK_BISHOP` | `'b'` |
| `WHITE_KNIGHT` | `'N'` | `BLACK_KNIGHT` | `'n'` |
| `WHITE_PAWN`   | `'P'` | `BLACK_PAWN`   | `'p'` |

## Static Factories

Each factory accepts a `ColorEnum` and returns the corresponding piece.

```php
PieceEnum::king(ColorEnum $color): PieceEnum
PieceEnum::queen(ColorEnum $color): PieceEnum
PieceEnum::rook(ColorEnum $color): PieceEnum
PieceEnum::bishop(ColorEnum $color): PieceEnum
PieceEnum::knight(ColorEnum $color): PieceEnum
PieceEnum::pawn(ColorEnum $color): PieceEnum
```

```php
PieceEnum::king(ColorEnum::WHITE);    // PieceEnum::WHITE_KING
PieceEnum::queen(ColorEnum::BLACK);   // PieceEnum::BLACK_QUEEN
PieceEnum::rook(ColorEnum::WHITE);    // PieceEnum::WHITE_ROOK
PieceEnum::bishop(ColorEnum::BLACK);  // PieceEnum::BLACK_BISHOP
PieceEnum::knight(ColorEnum::WHITE);  // PieceEnum::WHITE_KNIGHT
PieceEnum::pawn(ColorEnum::BLACK);    // PieceEnum::BLACK_PAWN
```

## Instance Methods

```php
$piece->color(): ColorEnum
// Returns ColorEnum::WHITE or ColorEnum::BLACK depending on the piece case.

$piece->isPawn(): bool
// Returns true only for WHITE_PAWN and BLACK_PAWN.

$piece->opposite(): PieceEnum
// Returns the same piece type for the opposite color.
// PieceEnum::WHITE_ROOK->opposite() === PieceEnum::BLACK_ROOK
```

## Usage

```php
use Cmuset\ChessTools\Enum\PieceEnum;
use Cmuset\ChessTools\Enum\ColorEnum;

$piece = PieceEnum::WHITE_ROOK;
$piece->value;      // 'R'
$piece->color();    // ColorEnum::WHITE
$piece->isPawn();   // false
$piece->opposite(); // PieceEnum::BLACK_ROOK

PieceEnum::pawn(ColorEnum::BLACK)->value; // 'p'

// Searching on a Position:
$position->find(PieceEnum::WHITE_PAWN);           // Square[]
$position->findOne(PieceEnum::BLACK_KING);         // ?Square
$position->findByFile(PieceEnum::WHITE_ROOK, 'e'); // Square[]
```
