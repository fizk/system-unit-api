<?php

namespace Unit\Service;

use MongoDB\Database;

interface DatabaseAware
{
    public function setDriver(Database $driver): self;
}
