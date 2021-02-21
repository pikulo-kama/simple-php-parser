<?php

include_once 'dto/TradeMarkInfoDto.php';


interface DataMapper
{

    public function mapRawDataToTradeMarkDtoList(string $data): array;

}

?>