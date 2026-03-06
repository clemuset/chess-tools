<?php

namespace Cmuset\ChessTools\Model;

use Cmuset\ChessTools\Enum\CastlingEnum;
use Cmuset\ChessTools\Enum\ColorEnum;
use Cmuset\ChessTools\Enum\CoordinatesEnum;
use Cmuset\ChessTools\Enum\PieceEnum;
use Cmuset\ChessTools\Tool\Exporter\MoveExporter;
use Cmuset\ChessTools\Tool\Parser\SANParser;

class Move
{
    private ?PieceEnum $piece = null;
    private ?CoordinatesEnum $squareFrom = null;
    private ?string $fileFrom = null;
    private ?int $rowFrom = null;
    private ?CoordinatesEnum $to = null;
    private ?PieceEnum $promotion = null;
    private bool $isCapture = false;
    private bool $isCheck = false;
    private bool $isCheckmate = false;
    private ?CastlingEnum $castling = null;
    private ?string $annotation = null;

    public static function fromSAN(string $san, ColorEnum $color = ColorEnum::WHITE): Move
    {
        return new SANParser()->parse($san, $color);
    }

    public function getSAN(): string
    {
        return new MoveExporter()->export($this);
    }

    public function getPiece(): ?PieceEnum
    {
        return $this->piece;
    }

    public function setPiece(?PieceEnum $piece): void
    {
        $this->piece = $piece;
    }

    public function getSquareFrom(): ?CoordinatesEnum
    {
        return $this->squareFrom;
    }

    public function setSquareFrom(?CoordinatesEnum $squareFrom): void
    {
        $this->squareFrom = $squareFrom;
    }

    public function getFileFrom(): ?string
    {
        return $this->fileFrom;
    }

    public function setFileFrom(?string $fileFrom): void
    {
        $this->fileFrom = $fileFrom;
    }

    public function getRankFrom(): ?int
    {
        return $this->rowFrom;
    }

    public function setRowFrom(?int $rowFrom): void
    {
        $this->rowFrom = $rowFrom;
    }

    public function getTo(): ?CoordinatesEnum
    {
        return $this->to;
    }

    public function setTo(?CoordinatesEnum $to): void
    {
        $this->to = $to;
    }

    public function getPromotion(): ?PieceEnum
    {
        return $this->promotion;
    }

    public function setPromotion(?PieceEnum $promotion): void
    {
        $this->promotion = $promotion;
    }

    public function isCapture(): bool
    {
        return $this->isCapture;
    }

    public function setIsCapture(bool $isCapture): void
    {
        $this->isCapture = $isCapture;
    }

    public function isCheck(): bool
    {
        return $this->isCheck;
    }

    public function setIsCheck(bool $isCheck): void
    {
        $this->isCheck = $isCheck;
    }

    public function isCheckmate(): bool
    {
        return $this->isCheckmate;
    }

    public function setIsCheckmate(bool $isCheckmate): void
    {
        $this->isCheckmate = $isCheckmate;
    }

    public function getCastling(): ?CastlingEnum
    {
        return $this->castling;
    }

    public function setCastling(?CastlingEnum $castling): void
    {
        $this->castling = $castling;
    }

    public function isCastling(): bool
    {
        return null !== $this->castling;
    }

    public function getAnnotation(): ?string
    {
        return $this->annotation;
    }

    public function setAnnotation(?string $annotation): void
    {
        $this->annotation = $annotation;
    }

    public function toArray(): array
    {
        return [
            'san' => $this->getSAN(),
            'piece' => $this->piece?->value,
            'squareFrom' => $this->squareFrom?->value,
            'fileFrom' => $this->fileFrom,
            'rankFrom' => $this->rowFrom,
            'to' => $this->to?->value,
            'promotion' => $this->promotion?->value,
            'isCapture' => $this->isCapture,
            'isCheck' => $this->isCheck,
            'isCheckmate' => $this->isCheckmate,
            'castling' => $this->castling?->value,
            'annotation' => $this->annotation,
        ];
    }
}
