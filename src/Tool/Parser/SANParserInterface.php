<?php

namespace Cmuset\ChessTools\Tool\Parser;

use Cmuset\ChessTools\Enum\ColorEnum;
use Cmuset\ChessTools\Model\Move;

interface SANParserInterface
{
    public function parse(string $san, ColorEnum $color): Move;
}
