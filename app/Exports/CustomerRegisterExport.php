<?php

namespace Laraspace\Exports;

use Laraspace\CustomerRegister;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class CustomerRegisterExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    

    public function collection()
    {
        // Use select to retrieve only the necessary columns
        $data = [];

        $customer = CustomerRegister::get();
        $loginTypes = [
                    1 => "App",
                    2 => "Facebook",
                    3 => "GooglePlus",
                    4 => "Apple"
                ];
        foreach ($customer as $key => $value) {

                // Assign loginType based on deviceType
                $loginType = isset($loginTypes[$value->deviceType]) ? $loginTypes[$value->deviceType] : "Unknown";

                  $data[] = [
                    'id' => $key + 1,
                    'name' => $value->name,
                    'phone' => $value->phone,
                    'email' => $value->email,
                    'birthDate' =>$value->birthDate,
                     'loginType' => $loginType,
                    'deviceType' => ($value->deviceType == 1) ? "Andriod" : "iPhone",
                    'isMarketingConsent' => ($value->isMarketingConsent == true) ? "Yes" : "No",
                ];

                }

        return collect($data);
    }


    public function headings(): array
    {
        return [ 
            "Id",
            "Name",
            "Phone Number",
            "Email",
            "Birth Date",
            "Login Types",
            "Device Type",
            "Accept Marketing Consent Exports"
        ];
    }
}
