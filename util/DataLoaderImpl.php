<?php

include_once 'abstract/DataLoader.php';
include_once 'dto/TradeMarkInfoDto.php';
include_once 'DataMapperImpl.php';


class DataLoaderImpl implements DataLoader
{

    private string $searchPageGetUrl = "https://search.ipaustralia.gov.au/trademarks/search/advanced";
    private string $searchPostUrl = "https://search.ipaustralia.gov.au/trademarks/search/doSearch";


    public function getRawData(string $trade_mark, string $user_agent): string
    {

        $ch = curl_init($this->searchPostUrl);

        $csrf_token = $this->getCsrfToken();

        $post_fields = http_build_query([
            "_csrf" => $csrf_token,
            "wv[0]" => $trade_mark

        ], '', '&');


        curl_exec($ch);

        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        curl_setopt($ch, CURLOPT_COOKIEJAR, "../cookie.txt");
        curl_setopt($ch, CURLOPT_COOKIE, "XSRF-TOKEN=" . $csrf_token);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);


        $response = curl_exec($ch);


        if ($error = curl_error($ch)) {
            // Handle error here
        }

        curl_close($ch);

        return $response;
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

?>
