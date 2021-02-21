<?php

interface DataLoader
{

    public function getRecords(string $trade_mark, string $user_agent): array;

}

