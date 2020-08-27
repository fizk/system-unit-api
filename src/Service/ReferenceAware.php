<?php

namespace Unit\Service;

interface ReferenceAware
{
    public function setReferenceService(ReferenceInterface $service): self;
}
