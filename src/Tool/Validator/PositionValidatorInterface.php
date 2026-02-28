<?php

namespace Cmuset\ChessTools\Tool\Validator;

use Cmuset\ChessTools\Model\Position;
use Cmuset\ChessTools\Tool\Validator\Enum\PositionViolationEnum;

interface PositionValidatorInterface
{
    /**
     * @return PositionViolationEnum[]
     */
    public function validate(Position $position): array;
}
