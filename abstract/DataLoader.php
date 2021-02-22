<?php

interface DataLoader
{

    public function parsePage(string $trade_mark, int $page_id, string $user_agent): DataInfoDto;

    public function parseAllPages(string $trade_mark, string $user_agent);

}

