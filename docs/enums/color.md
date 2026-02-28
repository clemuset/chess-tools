# ColorEnum

`Cmuset\ChessTools\Enum\ColorEnum`

String-backed PHP enum representing the two sides of the board.

## Cases

| Case    | Value | Description                         |
|---------|-------|-------------------------------------|
| `WHITE` | `'w'` | White side (uppercase FEN notation) |
| `BLACK` | `'b'` | Black side (lowercase FEN notation) |

## Methods

```php
$color->opposite(): ColorEnum
```

Returns the other color.

```php
ColorEnum::WHITE->opposite(); // ColorEnum::BLACK
ColorEnum::BLACK->opposite(); // ColorEnum::WHITE
```

## Usage

```php
use Cmuset\ChessTools\Enum\ColorEnum;

$side = ColorEnum::WHITE;
$side->value;      // 'w'
$side->opposite(); // ColorEnum::BLACK

// Typical uses:
$position->getSideToMove();         // ColorEnum
$position->setSideToMove(ColorEnum::BLACK);
$piece->color();                    // ColorEnum
PieceEnum::king(ColorEnum::WHITE);  // PieceEnum::WHITE_KING
```

`ColorEnum` is used throughout the library wherever side-specific logic is needed: piece color, side to move in `Position`, castling right ownership, en passant validation, and square color computation in `CoordinatesEnum`.
