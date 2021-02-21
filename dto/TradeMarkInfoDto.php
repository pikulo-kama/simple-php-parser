<?php


class TradeMarkInfoDto
{

    protected string $number;
    protected string $logo_url;
    protected string $name;
    protected string $classes;
    protected string $status_1;
    protected string $status_2;
    protected string $details_page_url;

    public function __construct(array $fields)
    {

        $this->number = $fields["number"];
        $this->logo_url = $fields["logo_url"] ?? "NONE";
        $this->name = $fields["name"];
        $this->classes = $fields["classes"];
        $this->status_1 = $fields["status_1"];
        $this->status_2 = $fields["status_2"];
        $this->details_page_url = $fields["details_page_url"];
    }

    public function __serialize(): array
    {

        return [
            "number" => $this->number,
            "logo_url" => $this->logo_url,
            "name" => $this->name,
            "status_1" => $this->status_1,
            "status_2" => $this->status_2,
            "details_page_url" => $this->details_page_url
        ];
    }

    public function __unserialize(array $data): void
    {

        $this->number = data["number"];
        $this->logo_url = data["logo_url"];
        $this->name = data["name"];
        $this->status_1 = data["status_1"];
        $this->status_2 = data["status_2"];
        $this->details_page_url = data["details_page_url"];
    }
}

?>