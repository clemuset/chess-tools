<?php

namespace Cmuset\PgnParser\Tool\Resolver;

use Cmuset\PgnParser\Model\Move;
use Cmuset\PgnParser\Model\Position;

interface MoveResolverInterface
{
    public function resolve(Position $position, Move $move): void;
}
