<?php

require_once 'abstract/DataLoader.php';
require_once 'abstract/DataMapper.php';

require_once 'util/DataLoaderImpl.php';
require_once 'util/DataMapperImpl.php';
require_once 'util/DataFormatterImpl.php';

require_once 'dto/UserAgent.php';


class Main
{
    private static int $PAGE_UNSET_STATUS = PHP_INT_MIN;
    private static int $LENGTH_DEFAULT_VALUE = PHP_INT_MAX;

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


        if ($options["page"] == Main::$PAGE_UNSET_STATUS) {

            $data_info = $this->dataLoader->parseAllPages(
                $options["name"],
                UserAgent::$DEFAULT_USER_AGENT);

            $records = $data_info->allRecords();

        } else {

            $data_info = $this->dataLoader->parsePage(
                $options["name"],
                $options["page"],
                UserAgent::$DEFAULT_USER_AGENT);

            $records = $data_info->allRecords();
        }


        $trade_marks = $this->dataMapper->mapRecordsToTradeMarkInfoList($records);

        print_r($trade_marks);
        print_r($options);
        print_r(array_slice($trade_marks, $options["offset"],
            $options["length"], true));


        if ($options["page_count"]) {

            echo "\n\nTotal number of pages: " . $data_info->getTotalPages();
        }

        if ($options["record_count"]) {
            echo "\n\nTotal number of records: " . $data_info->getTotalRecords();
        }

    }

    private function getValidatedScriptArgs(): array
    {

        $options = getopt("n:o:l:p:rc");
        $valid_options = [];

        if (!$options["n"]) {
            throw new Exception("Search name is empty. Add -n <name> and try again.");
        }

        $valid_options["name"] = $options["n"];

        $valid_options["page_count"] =  array_key_exists("c", $options);
        $valid_options["record_count"] = array_key_exists("r", $options);

        $valid_options["offset"] = (int)($options["o"] ?? 0);
        $valid_options["length"] = (int)($options["l"] ?? Main::$LENGTH_DEFAULT_VALUE);

        if (array_key_exists("p", $options)) {
            preg_match('/\d+/', $options["p"], $match);

            if (!$match) {
                throw new Exception("Page number is in incorrect format.");
            }
        }

        $valid_options["page"] = (int) ($options["p"] ?? Main::$PAGE_UNSET_STATUS);

        return $valid_options;
    }
}

//Entry point

(new Main(new DataLoaderImpl(new DataFormatterImpl()), new DataMapperImpl()))->main();
