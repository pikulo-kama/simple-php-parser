<?php

interface DataLoader
{

    public function getDataInfoDto(string $trade_mark, string $user_agent): DataInfoDto;

}

