<?php


interface DataFormatter
{

    public function reformat( $data ): array;

    public function removeNewLines( string $data ): string;

}
