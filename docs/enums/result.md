# ResultEnum

`Cmuset\ChessTools\Enum\ResultEnum`

String-backed PHP enum encoding the four possible game results as they appear in PGN.

## Cases

| Case         | Value       | Meaning                                |
|--------------|-------------|----------------------------------------|
| `WHITE_WINS` | `'1-0'`     | White won                              |
| `BLACK_WINS` | `'0-1'`     | Black won                              |
| `DRAW`       | `'1/2-1/2'` | Drawn game                             |
| `ONGOING`    | `'*'`       | Game not yet finished / result unknown |

## Static Factories

```php
ResultEnum::fromColor(ColorEnum $color): ResultEnum
// ColorEnum::WHITE → ResultEnum::WHITE_WINS
// ColorEnum::BLACK → ResultEnum::BLACK_WINS
```

## Usage

```php
use Cmuset\ChessTools\Enum\ResultEnum;
use Cmuset\ChessTools\Enum\ColorEnum;

ResultEnum::WHITE_WINS->value; // '1-0'
ResultEnum::DRAW->value;       // '1/2-1/2'

ResultEnum::fromColor(ColorEnum::BLACK)->value; // '0-1'

// On a Game:
$game->getResult();                      // ?ResultEnum
$game->setResult(ResultEnum::DRAW);
echo $game->getResult()->value;          // '1/2-1/2'

// Parse from a string:
ResultEnum::from('1-0'); // ResultEnum::WHITE_WINS
```

## Role in the library

- **`PGNParser`** reads the `[Result "..."]` tag or the trailing result token (`1-0`, `0-1`, `1/2-1/2`, `*`) from the move text and stores it on the `Game` object.
- **`GameExporter`** appends `$game->getResult()->value` to the exported PGN string.
- **`GameResolver::resolve()`** can automatically set the result to `WHITE_WINS`, `BLACK_WINS`, or `DRAW` when it detects checkmate or stalemate at the end of the main line.
