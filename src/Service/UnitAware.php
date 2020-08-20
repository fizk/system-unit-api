<?php

namespace Unit\Service;

interface UnitAware
{
    public function setUnitService(Unit $service): self;
}
