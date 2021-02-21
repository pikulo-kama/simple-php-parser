<?php

include_once 'abstract/DataFormatter.php';


class DataFormatterImpl implements DataFormatter
{

    public function reformat($data): array
    {
        $one_line_data = $this->removeNewLines($data);
        $records = $this->splitRecords($one_line_data);

        return $records;
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