<?php

require_once 'abstract/DataLoader.php';
require_once 'abstract/DataMapper.php';

require_once 'util/DataLoaderImpl.php';
require_once 'util/DataMapperImpl.php';
require_once 'util/DataFormatterImpl.php';

require_once 'dto/UserAgent.php';


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

        $options = $this->getValidatedScriptArgs();

        $raw_html = $this->dataLoader->getRecords($options["name"], UserAgent::$DEFAULT_USER_AGENT);
        $trade_marks_info = $this->dataMapper->mapRecordsToTradeMarkInfoList($raw_html);

        print_r(array_slice($trade_marks_info,
            $options["offset"],
            $options["length"],
            true));
    }

    private function getValidatedScriptArgs(): array
    {

        $options = getopt("n:o:l:");

        $offset = (int)($options["o"] ?? 0);
        $length = (int)($options["l"] ?? PHP_INT_MAX);

        if (!$options["n"]) {
            throw new Exception("Search name is empty. Add -n <name> and try again.");
        }

        return [
            "name" => $options["n"],
            "offset" => $offset,
            "length" => $length
        ];
    }
}

//Entry point

(new Main(new DataLoaderImpl(new DataFormatterImpl()), new DataMapperImpl()))->main();
