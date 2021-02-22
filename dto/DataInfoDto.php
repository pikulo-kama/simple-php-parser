<?php


class DataInfoDto
{

    private array $pages;

    private int $total_pages;

    private int $total_records;


    public function __construct(array $pages, int $total_pages, int $total_records)
    {

        $this->pages = $pages;
        $this->total_pages = $total_pages;
        $this->total_records = $total_records;
    }

    public function getTotalPages() {
        return $this->total_pages + 1;
    }

    public function getTotalRecords() {
        return $this->total_records;
    }

    public function allRecords(): array
    {

        $all_records = [];

        foreach ($this->pages as $page) {

            $all_records = array_merge($all_records, $page->getRecords());
        }

        return $all_records;
    }

    public function byPageId(int $id) {

        $id--;

        // Try access as element

        $page_by_id = $this->pages[$id];

        if ($page_by_id->getPageId() == $id) {
            return $page_by_id->getRecords();
        }

        // Search in other case

        foreach ($this->pages as $page) {

            if ($page->getPageId() == $id) {
                return $page->getRecords();
            }
        }

        throw new Exception("No page with id " . $id);
    }
}