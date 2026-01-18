<?php

namespace Cmuset\PgnParser\Tool\Validator;

use Cmuset\PgnParser\Model\Position;
use Cmuset\PgnParser\Tool\Validator\Enum\PositionViolationEnum;

interface PositionValidatorInterface
{
    /**
     * @return PositionViolationEnum[]
     */
    public function validate(Position $position): array;
}
