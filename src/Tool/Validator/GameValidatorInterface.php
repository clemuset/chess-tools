<?php

namespace Cmuset\PgnParser\Tool\Validator;

use Cmuset\PgnParser\Model\Game;
use Cmuset\PgnParser\Tool\Validator\Model\GameViolation;

interface GameValidatorInterface
{
    public function validate(Game $game): ?GameViolation;
}
