# Move

`Cmuset\ChessTools\Model\Move`

Represents a parsed SAN move with full chess metadata: the piece moved, source and destination squares, capture/check/checkmate flags, castling type, promotion, and move annotation.

## Static Factory

```php
Move::fromSAN(string $san, ColorEnum $color = ColorEnum::WHITE): Move
// Parses a SAN string into a Move object.
// The color is required to assign the correct PieceEnum value.
// Throws SANParsingException on invalid SAN.
```

```php
use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Enum\ColorEnum;

$move = Move::fromSAN('Nf3', ColorEnum::WHITE);
$move = Move::fromSAN('e8=Q+', ColorEnum::WHITE);
$move = Move::fromSAN('O-O-O', ColorEnum::BLACK);
$move = Move::fromSAN('exd5', ColorEnum::WHITE);
```

## Export

```php
$move->getSAN(): string
// Reconstructs the SAN string from internal fields.
// Produces the canonical form: piece letter (if not pawn) + disambiguation +
// capture 'x' + destination + promotion '=X' + check '+' or checkmate '#'.
```

## Piece and destination

```php
$move->getPiece(): ?PieceEnum
$move->setPiece(?PieceEnum $piece): void

$move->getTo(): ?CoordinatesEnum
$move->setTo(?CoordinatesEnum $square): void
```

## Source square and disambiguation

The SAN format allows partial disambiguation (`Nbd7`, `R1e4`). The full source square is computed later by `MoveResolver` or `MoveApplier`.

```php
$move->getSquareFrom(): ?CoordinatesEnum
$move->setSquareFrom(?CoordinatesEnum $square): void
// Full source square — set by MoveResolver or MoveApplier.

$move->getFileFrom(): ?string
$move->setFileFrom(?string $file): void
// File disambiguation extracted from SAN, e.g. 'b' in 'Nbd7'.

$move->getRankFrom(): ?int
$move->setRowFrom(?int $rank): void
// Rank disambiguation extracted from SAN, e.g. 1 in 'R1e4'.
```

## Boolean flags

```php
$move->isCapture(): bool
$move->setIsCapture(bool $capture): void

$move->isCheck(): bool
$move->setIsCheck(bool $check): void

$move->isCheckmate(): bool
$move->setIsCheckmate(bool $checkmate): void

$move->isCastling(): bool
// Derived: true when getCastling() is not null.
```

## Castling

```php
$move->getCastling(): ?CastlingEnum
$move->setCastling(?CastlingEnum $castling): void
```

When `getCastling()` is non-null the move is a castling move. The `piece` is set to the king, and `to` is null (the destination is implied by the `CastlingEnum`).

## Promotion

```php
$move->getPromotion(): ?PieceEnum
// e.g. PieceEnum::WHITE_QUEEN for 'e8=Q'
$move->setPromotion(?PieceEnum $piece): void
```

## Annotation

```php
$move->getAnnotation(): ?string
// Move quality annotation appended to the SAN: '!', '?', '!!', '??', '!?', '?!'
$move->setAnnotation(?string $annotation): void
```

Annotations are extracted by `SANParser` but are **not** re-emitted by `MoveExporter`; they appear in PGN only when explicitly written.

## Example

```php
use Cmuset\ChessTools\Model\Move;
use Cmuset\ChessTools\Enum\ColorEnum;
use Cmuset\ChessTools\Enum\PieceEnum;
use Cmuset\ChessTools\Enum\CoordinatesEnum;

$move = Move::fromSAN('Nbd7+', ColorEnum::BLACK);
$move->getPiece();    // PieceEnum::BLACK_KNIGHT
$move->getTo();       // CoordinatesEnum::D7
$move->getFileFrom(); // 'b'
$move->isCheck();     // true
$move->isCapture();   // false
$move->getSAN();      // 'Nb...d7+' (full form after squareFrom is resolved)

$move = Move::fromSAN('exd5', ColorEnum::WHITE);
$move->getPiece();    // PieceEnum::WHITE_PAWN
$move->isCapture();   // true
$move->getTo();       // CoordinatesEnum::D5
```
