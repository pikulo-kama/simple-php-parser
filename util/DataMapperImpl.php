<?php

include_once 'abstract/DataMapper.php';
include_once 'dto/TradeMarkInfoDto.php';
include_once 'dto/TradeMarkField.php';


class DataMapperImpl implements DataMapper
{

    private static string $LOGO_URL = "logo_url";

    private string $details_page_url_regex;
    private string $logo_url_regex;
    private string $name_regex;
    private string $classes_regex;
    private string $statuses_regex;


    public function __construct()
    {

        $this->logo_url_regex = '<td class="trademark image".*src="(?<logo_url>[\w\:\/\.\-]+)"';
        $this->details_page_url_regex = '(?<details_page_url>\/trademarks\/search\/view\/(?<number>\d+))';
        $this->name_regex = 'trademark words" (?>colspan="2")?>(?<name>[^<]+)';
        $this->classes_regex = 'classes\D*(?<classes>[^<]+)';
        $this->statuses_regex = 'status.*<\/i> (?><span>)?(?<statuses>[^<]+)';
    }

    public function mapRawDataToTradeMarkDtoList(string $data): array
    {
        $data = $this->removeNewLines($data);
        $records = $this->splitRecords($data);

        $trade_marks = [];

        foreach ($records as $record) {

            $trade_mark = $this->mapRecordToTradeMarkInfo($record);
            array_push($trade_marks, $trade_mark);
        }

        return $trade_marks;
    }

    private function mapRecordToTradeMarkInfo(string $record): TradeMarkInfoDto
    {

        $main_pattern = '/.*' .
            implode('.*', [
                $this->details_page_url_regex,
                $this->name_regex,
                $this->classes_regex,
                $this->statuses_regex
            ]) .
            '/m';

        // This pattern finds all data except image reference

        preg_match($main_pattern, $record, $matches);

        // FIX: Try to join this two expressions in one.
        // Reason why I separated regex in two, because using ()? operator
        // even if there was image it was omitted.

        preg_match('/' . $this->logo_url_regex . '/m', $record, $image_match);


        // Add 'url to image' field only if it exists

        if (array_key_exists(DataMapperImpl::$LOGO_URL, $image_match)) {

            $matches = array_merge($matches, $image_match);
        }

        // Get 'statuses' from $matches than split them in
        // 'status_1' and 'status_2' and then add them back to $matches array

        $matches = array_merge($matches, $this->splitStatuses($matches["statuses"]));

        return new TradeMarkInfoDto($matches);

    }

    private function splitStatuses(string $merged_status): array
    {

        $split_statuses = explode(":", $merged_status, 2);

        return [
            "status_1" => $split_statuses[0],
            "status_2" => $split_statuses[1] ?? ""
        ];
    }

    private function splitRecords(string $data): array
    {

        return array_slice(explode("<tr data-markurl=", $data), 1);
    }

    private function removeNewLines(string $data): string
    {

        return trim(preg_replace('/[\n\r]+/', ' ', $data));
    }
}

?>