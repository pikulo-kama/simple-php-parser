<?php

include_once 'abstract/DataMapper.php';

include_once 'dto/TradeMarkInfoDto.php';
include_once 'dto/TradeMarkField.php';


class DataMapperImpl implements DataMapper
{

    private string $details_page_url_regex;
    private string $logo_url_regex;
    private string $name_regex;
    private string $classes_regex;
    private string $statuses_regex;


    public function __construct()
    {

        $this->logo_url_regex = '<td class="trademark image".*src="(?<' .
            TradeMarkField::$LOGO_URL .
            '>[\w\:\/\.\-]+)"';

        $this->details_page_url_regex = '(?<' .
            TradeMarkField::$DETAILS_PAGE_URL .
            '>\/trademarks\/search\/view\/(?<' .
            TradeMarkField::$NUMBER . '>-?\d+))';

        $this->name_regex = 'trademark words" (?>colspan="2")?>(?<' . TradeMarkField::$NAME . '>[^<]+)';

        $this->classes_regex = 'classes ">(?<' . TradeMarkField::$CLASSES . '>[^<]+)';

        $this->statuses_regex = 'status.*<\/i> (?><span>)?(?<' . TradeMarkField::$STATUSES . '>[^<]+)';
    }

    public function mapRecordsToTradeMarkInfoList(array $records): array {
        $trade_marks = [];

        foreach ($records as $record) {

            $trade_mark = $this->mapRecordToTradeMarkInfo($record);
            array_push($trade_marks, $trade_mark);
        }

        return $trade_marks;
    }

    public function mapRecordToTradeMarkInfo(string $record): TradeMarkInfoDto
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

        if (array_key_exists(TradeMarkField::$LOGO_URL, $image_match)) {

            $matches = array_merge($matches, $image_match);
        }

        // Get 'statuses' from $matches than split them in
        // 'status_1' and 'status_2' and then add them back to $matches array

        $matches = array_merge($matches, $this->splitStatuses($matches[TradeMarkField::$STATUSES]));

        return new TradeMarkInfoDto($matches);

    }

    private function splitStatuses(string $merged_status): array
    {

        $split_statuses = explode(":", $merged_status, 2);

        return [
            TradeMarkField::$STATUS_1 => $split_statuses[0],
            TradeMarkField::$STATUS_2 => $split_statuses[1] ?? ""
        ];
    }

}