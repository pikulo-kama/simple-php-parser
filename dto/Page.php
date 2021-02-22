<?php


class Page
{

    private int $page_id;

    private array $records;

    public function __construct(int $page_id, array $records)
    {
        $this->page_id = $page_id;
        $this->records = $records;
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function getPageId(): int
    {
        return $this->page_id;
    }
}