<?php

namespace Unit\Service;

interface ReferenceAware
{
    public function setReferenceService(Reference $service): self;
}
