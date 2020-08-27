<?php

namespace Unit\Service;

interface UnitAware
{
    public function setUnitService(UnitInterface $service): self;
}
