<?php

include_once 'dto/TradeMarkInfoDto.php';


interface DataMapper
{

    public function mapRecordToTradeMarkInfo(string $record): TradeMarkInfoDto;

    public function mapRecordsToTradeMarkInfoList(array $records): array;

}
