<?php

namespace Unit\Event;

use Psr\Http\Message\ServerRequestInterface;

class EntryView
{
    private ServerRequestInterface $request;
    private string $type;
    private string $id;

    public function __construct(ServerRequestInterface $request, string $type, string $id)
    {
        $this->request = $request;
        $this->type = $type;
        $this->id = $id;
    }

    public function __toString(): string
    {
        return print_r([$this->request->getHeaders(), $this->type, $this->id], true);
    }
}
