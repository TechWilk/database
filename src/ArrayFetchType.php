<?php

declare(strict_types=1);

namespace TechWilk\Database;

enum ArrayFetchType: int
{
    case BOTH = 0;
    case NUM = 1;
    case ASSOC = 2;
}
