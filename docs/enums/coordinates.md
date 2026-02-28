# CoordinatesEnum

`Cmuset\ChessTools\Enum\CoordinatesEnum`

String-backed PHP enum covering all 64 board squares from `a1` to `h8`. Values are the standard two-character coordinate strings used in FEN and SAN notation.

## Cases

All 64 squares are declared as cases named with uppercase letters (`A1`–`H8`). The enum value is the lowercase coordinate string:

```php
CoordinatesEnum::E4->value; // 'e4'
CoordinatesEnum::A1->value; // 'a1'
CoordinatesEnum::H8->value; // 'h8'
```

## Instance Methods

### Coordinate decomposition

```php
$sq->file(): string  // 'a'–'h' — the file letter
$sq->rank(): int     // 1–8    — the rank number
```

```php
CoordinatesEnum::E4->file(); // 'e'
CoordinatesEnum::E4->rank(); // 4
```

### Square color

```php
$sq->color(): ColorEnum
// Determines square color from (file_index + rank) % 2 parity.
```

### Navigation

Each direction method returns `null` when the edge of the board is reached.

```php
$sq->up(): ?CoordinatesEnum     // Increase rank (toward rank 8)
$sq->down(): ?CoordinatesEnum   // Decrease rank (toward rank 1)
$sq->left(): ?CoordinatesEnum   // Decrease file (toward 'a')
$sq->right(): ?CoordinatesEnum  // Increase file (toward 'h')
```

```php
CoordinatesEnum::E4->up();    // CoordinatesEnum::E5
CoordinatesEnum::E4->right(); // CoordinatesEnum::F4
CoordinatesEnum::H4->right(); // null  — at the board edge
CoordinatesEnum::A1->down();  // null  — at the board edge
```

### Promotion squares

```php
$sq->isPromotionSquare(ColorEnum $color): bool
// true when $sq is on the promotion rank for the given color:
//   rank 8 for ColorEnum::WHITE, rank 1 for ColorEnum::BLACK
```

```php
CoordinatesEnum::E8->isPromotionSquare(ColorEnum::WHITE); // true
CoordinatesEnum::A1->isPromotionSquare(ColorEnum::BLACK); // true
CoordinatesEnum::D7->isPromotionSquare(ColorEnum::WHITE); // false
```

## Static Methods

```php
CoordinatesEnum::allowedEnPassantTargets(): CoordinatesEnum[]
// Returns the 16 valid en passant target squares:
// all of rank 3 (a3–h3) and all of rank 6 (a6–h6).
```

## Usage

```php
use Cmuset\ChessTools\Enum\CoordinatesEnum;

// From a string value:
CoordinatesEnum::from('e4');    // CoordinatesEnum::E4 (throws on invalid)
CoordinatesEnum::tryFrom('z9'); // null (invalid square)

// Navigate the board:
$sq = CoordinatesEnum::D4;
$sq->up();    // CoordinatesEnum::D5
$sq->left();  // CoordinatesEnum::C4

// Used throughout Position:
$position->getPieceAt(CoordinatesEnum::E1);
$position->setPieceAt(CoordinatesEnum::E8, PieceEnum::WHITE_QUEEN);
$position->getSquare(CoordinatesEnum::A1);
```
