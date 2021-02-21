<?php

interface DataLoader
{

    public function getRawData(string $trade_mark, string $user_agent): string;

}

?>
