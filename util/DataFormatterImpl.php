<?php

include_once 'abstract/DataFormatter.php';


class DataFormatterImpl implements DataFormatter
{

    public function reformat($data): array
    {
        $one_line_data = $this->removeNewLines($data);
        return $this->splitRecords($one_line_data);
    }

    private function splitRecords(string $data): array
    {

        return array_slice(explode("<tr data-markurl=", $data), 1);
    }

    public function removeNewLines(string $data): string
    {

        return trim(preg_replace('/[\n\r]+/', ' ', $data));
    }

}