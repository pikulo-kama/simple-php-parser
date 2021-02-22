<?php

include_once 'abstract/DataFormatter.php';
include_once 'abstract/DataLoader.php';

include_once 'dto/TradeMarkInfoDto.php';
include_once 'DataFormatterImpl.php';
include_once 'DataMapperImpl.php';


class DataLoaderImpl implements DataLoader
{

    private string $searchPageGetUrl = "https://search.ipaustralia.gov.au/trademarks/search/advanced";
    private string $searchPostUrl = "https://search.ipaustralia.gov.au/trademarks/search/doSearch";

    private DataFormatter $dataFormatter;

    public function __construct(DataFormatter $dataFormatter) {
        $this->dataFormatter = $dataFormatter;
    }

    public function getRecords(string $trade_mark, string $user_agent): array
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


        $response = curl_exec($ch);


        if ($error = curl_error($ch)) {
            // Handle error here
        }

        // Set request method back to GET

        curl_setopt($ch, CURLOPT_HTTPGET, 1);

        $page_count = $this->getPageCount($this->dataFormatter->removeNewLines($response));
        $base_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $all_records = [];

        // Parse all pagination

        foreach (range(0, $page_count) as $page) {
            $url = $base_url . "&p=" . $page;

            curl_setopt($ch, CURLOPT_URL, $url);
            $page_records = $this->dataFormatter->reformat(curl_exec($ch));

            $all_records = array_merge($all_records, $page_records);
        }

        curl_close($ch);

        return $all_records;
    }

    private function getPageCount(string $response, int $pagination_size = 100): int {

        $pattern = '/pagination-count"> Results \d+ to \d+ of (?<count>[\d\,]+)/';
        preg_match($pattern, $response, $matches);


        $records_count =  (int) str_replace(',', '', $matches["count"]);

        return intdiv($records_count, $pagination_size);
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