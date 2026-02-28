<?php

namespace Cmuset\ChessTools\Tool\Validator;

use Cmuset\ChessTools\Model\Game;
use Cmuset\ChessTools\Tool\Validator\Model\GameViolation;

interface GameValidatorInterface
{
    public function validate(Game $game): ?GameViolation;
}
