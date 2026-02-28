# CastlingEnum

`Cmuset\ChessTools\Enum\CastlingEnum`

String-backed PHP enum encoding the four possible castling rights. Values match the characters used in the FEN castling field.

## Cases

| Case              | Value | Meaning                              |
|-------------------|-------|--------------------------------------|
| `WHITE_KINGSIDE`  | `'K'` | White may castle kingside (`O-O`)    |
| `WHITE_QUEENSIDE` | `'Q'` | White may castle queenside (`O-O-O`) |
| `BLACK_KINGSIDE`  | `'k'` | Black may castle kingside (`O-O`)    |
| `BLACK_QUEENSIDE` | `'q'` | Black may castle queenside (`O-O-O`) |

## Static Factories

```php
CastlingEnum::kingside(ColorEnum $color): CastlingEnum
// ColorEnum::WHITE → WHITE_KINGSIDE
// ColorEnum::BLACK → BLACK_KINGSIDE

CastlingEnum::queenside(ColorEnum $color): CastlingEnum
// ColorEnum::WHITE → WHITE_QUEENSIDE
// ColorEnum::BLACK → BLACK_QUEENSIDE
```

## Instance Methods

```php
$castling->color(): ColorEnum
// Returns the color that owns this castling right.
```

```php
CastlingEnum::WHITE_KINGSIDE->color();  // ColorEnum::WHITE
CastlingEnum::BLACK_QUEENSIDE->color(); // ColorEnum::BLACK
```

## FEN representation

In FEN the castling field concatenates the values of all remaining rights in order `KQkq`, or `-` when none remain:

```
KQkq  — all four rights available
Kq    — white kingside + black queenside only
-     — no castling rights
```

## Usage

```php
use Cmuset\ChessTools\Enum\CastlingEnum;
use Cmuset\ChessTools\Enum\ColorEnum;

$right = CastlingEnum::kingside(ColorEnum::WHITE); // WHITE_KINGSIDE
$right->value;   // 'K'
$right->color(); // ColorEnum::WHITE

// Position API:
$position->hasCastlingRight(CastlingEnum::WHITE_KINGSIDE);     // bool
$position->removeCastlingRight(CastlingEnum::BLACK_QUEENSIDE);
$position->getCastlingRights(); // CastlingEnum[]

// Move API — castling moves carry a CastlingEnum:
$move->getCastling(); // ?CastlingEnum
$move->isCastling();  // bool
```

Castling rights are revoked automatically by `MoveApplier` when the king or a rook moves, or when a rook's starting square is captured.
