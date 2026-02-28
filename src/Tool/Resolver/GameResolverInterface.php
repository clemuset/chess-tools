<?php

namespace Cmuset\ChessTools\Tool\Resolver;

use Cmuset\ChessTools\Model\Game;

interface GameResolverInterface
{
    public function resolve(Game $game): void;
}
