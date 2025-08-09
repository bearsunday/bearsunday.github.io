<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Entity;

class Ticket
{
    public readonly string $dateCreated;

    public function __construct(
        public readonly string $id,
        public readonly string $title,
        string $date_created
    ) {
        // Convert MySQL datetime to ISO8601 format
        $this->dateCreated = date(DATE_ISO8601, strtotime($date_created));
    }
}