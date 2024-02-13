<?php

namespace App\Entity;

class Homepage
{
    protected array $facts;
    protected array $milestones;

    public function __construct(array $facts, array $milestones)
    {
        $this->facts = $facts;
        $this->milestones = $milestones;
    }

    public function getFacts(): array
    {
        return $this->facts;
    }

    public function getMilestones(): array
    {
        return $this->milestones;
    }
}