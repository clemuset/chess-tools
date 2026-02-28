<?php

namespace Cmuset\ChessTools\Tool\Validator\Model;

use Cmuset\ChessTools\Tool\Validator\Enum\MoveViolationEnum;
use Cmuset\ChessTools\Tool\Validator\Enum\PositionViolationEnum;

class GameViolation
{
    public function __construct(
        private readonly string $path,
        private readonly MoveViolationEnum $moveViolation,
        /** @var PositionViolationEnum[] */
        private readonly array $positionViolations = []
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMoveViolation(): MoveViolationEnum
    {
        return $this->moveViolation;
    }

    /**
     * @return PositionViolationEnum[]
     */
    public function getPositionViolations(): array
    {
        return $this->positionViolations;
    }
}
