<?php

namespace App\Exports;

use App\Model\WebUsers;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class ExportBasic implements FromCollection, WithColumnFormatting
{
    private $data;

    public function __construct($data)
    {   
        $this->data = $data;     //Inject data 
    }

  /**
  * @return \Illuminate\Support\Collection
  */
    public function collection()
    {
        return $this->data;
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }

}
