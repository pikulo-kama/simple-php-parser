<?php

include_once 'abstract/DataFormatter.php';
include_once 'abstract/DataLoader.php';

include_once 'dto/TradeMarkInfoDto.php';
include_once 'DataFormatterImpl.php';
include_once 'DataMapperImpl.php';
include_once 'dto/Page.php';
include_once 'dto/DataInfoDto.php';


class DataLoaderImpl implements DataLoader
{

    private string $searchPageGetUrl = "https://search.ipaustralia.gov.au/trademarks/search/advanced";
    private string $searchPostUrl = "https://search.ipaustralia.gov.au/trademarks/search/doSearch";

    private DataFormatter $dataFormatter;

    public function __construct(DataFormatter $dataFormatter) {
        $this->dataFormatter = $dataFormatter;
    }

    private function getConnection(string $trade_mark, string $user_agent)
    {

        $ch = curl_init($this->searchPostUrl);

        $csrf_token = $this->getCsrfToken();

        $post_fields = http_build_query([
            "_csrf" => $csrf_token,
            "wv[0]" => $trade_mark

        ], '', '&');


        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_COOKIE, "XSRF-TOKEN=" . $csrf_token);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);


        return $ch;
    }

    public function parsePage(string $trade_mark, int $page_id, string $user_agent): DataInfoDto {

        $ch = $this->getConnection($trade_mark, $user_agent);
        $response = curl_exec($ch);


        curl_setopt($ch, CURLOPT_HTTPGET, 1);

        $page_count = $this->getPageCount($response);
        $records_count = $this->getRecordsCount($response);

        $base_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $url = $base_url . "&p=" . $page_id;

        $pages = [];


        curl_setopt($ch, CURLOPT_URL, $url);

        $page_records = $this->dataFormatter->reformat(curl_exec($ch));

        array_push($pages, new Page($page_id, $page_records));


        curl_close($ch);

        return new DataInfoDto(
            $pages, $page_count, $records_count
        );

    }

    public function parseAllPages(string $trade_mark, string $user_agent): DataInfoDto {

        $ch = $this->getConnection($trade_mark, $user_agent);
        $response = curl_exec($ch);

        curl_setopt($ch, CURLOPT_HTTPGET, 1);

        $page_count = $this->getPageCount($response);
        $records_count = $this->getRecordsCount($response);

        $base_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $pages = [];

        // Parse all pagination

        foreach (range(0, $page_count) as $page_id) {
            $url = $base_url . "&p=" . $page_id;

            curl_setopt($ch, CURLOPT_URL, $url);
            $page_records = $this->dataFormatter->reformat(curl_exec($ch));

            array_push($pages, new Page($page_id, $page_records));
        }

        curl_close($ch);

        return new DataInfoDto(
            $pages, $page_count, $records_count
        );
    }

    private function getPageCount(string $response, int $pagination_size = 100): int {
        
        return intdiv($this->getRecordsCount($response), $pagination_size);
    }
    
    private function getRecordsCount(string $response) {

        $pattern = '/pagination-count"> Results \d+ to \d+ of (?<count>[\d\,]+)/';
        $response = $this->dataFormatter->removeNewLines($response);

        preg_match($pattern, $response, $matches);


        return (int) str_replace(',', '', $matches["count"]);
    }


    private function getCsrfToken(): string
    {

        $content = file_get_contents($this->searchPageGetUrl);
        $pattern = '/.*<input type="hidden" name="_csrf" value="(.*)".*/';

        preg_match($pattern, $content, $matches);
        $token = $matches[1];

        return $token;
    }
}