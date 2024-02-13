<?php

namespace App\Entity;

class Milestone
{
    protected string $name;
    protected string $logo;
    protected string $location;
    protected ?string $position;
    protected string $period;
    protected string $tagline;
    protected string $body;

    public function __construct(string $name, string $logo, string $location, string $period, string $tagline, string $body)
    {
        $this->name = $name;
        $this->logo = $logo;
        $this->location = $location;
        $this->period = $period;
        $this->tagline = $tagline;
        $this->body = $body;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getPeriod(): string
    {
        return $this->period;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}