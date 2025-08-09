<?php

declare(strict_types=1);

namespace MyVendor\Ticket\Query;

use MyVendor\Ticket\Entity\Ticket;
use Ray\MediaQuery\Annotation\DbQuery;

interface TicketQueryInterface
{
    #[DbQuery('ticket_item')]
    public function item(string $id): Ticket|null;

    /** @return array<Ticket> */
    #[DbQuery('ticket_list')]
    public function list(): array;
}