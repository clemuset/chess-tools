<?php

namespace Cmuset\PgnParser\Tool\Resolver;

use Cmuset\PgnParser\Model\Game;

interface GameResolverInterface
{
    public function resolve(Game $game): void;
}
