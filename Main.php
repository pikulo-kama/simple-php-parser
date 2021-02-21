<?php

require_once 'abstract/DataLoader.php';
require_once 'abstract/DataMapper.php';

require_once 'dto/UserAgent.php';
require_once 'util/DataLoaderImpl.php';
require_once 'util/DataMapperImpl.php';


class Main
{

    private DataLoader $dataLoader;
    private DataMapper $dataMapper;

    public function __construct(DataLoader $dataLoader, DataMapper $dataMapper)
    {
        $this->dataLoader = $dataLoader;
        $this->dataMapper = $dataMapper;
    }

    public function main()
    {
        $trade_mark_name = $this->validateTradeMarkOrThrowError();

        $raw_html = $this->dataLoader->getRawData($trade_mark_name, UserAgent::$DEFAULT_USER_AGENT);
        $trade_marks_info = $this->dataMapper->mapRawDataToTradeMarkDtoList($raw_html);

        print_r($trade_marks_info);
    }

    private function validateTradeMarkOrThrowError(): ?string
    {

        $options = getopt("n:");

        if (!$options["n"]) {
            throw new Exception("Search name is empty. Add -n <name> and try again.");
        }

        return $options["n"];
    }
}

//Entry point
(new Main(new DataLoaderImpl(), new DataMapperImpl()))->main();

?>
