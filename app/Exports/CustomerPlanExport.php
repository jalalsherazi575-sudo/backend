<?php

namespace Laraspace\Exports;

use Laraspace\UserSubscriptionPlan;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class CustomerPlanExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;

    protected $customerId;
    public function __construct($customerId)
    {
        $this->customerId = $customerId;
       
    }
    

    public function collection()
    {
        // Use select to retrieve only the necessary columns
        $data = [];

        $customerplan = UserSubscriptionPlan::with('customerregister')->where('userId',$this->customerId)->orderby('id','desc')->get();
       $loginTypes = [
                    1 => "App",
                    2 => "Facebook",
                    3 => "GooglePlus",
                    4 => "Apple"
                ];
        foreach ($customerplan as $key => $value) {

                // Assign loginType based on deviceType
                    if ($value->isActive == 0) {
                            $statusName='InActive';
                        } elseif ($value->isActive == 1) {
                            $statusName='Active';
                        } elseif ($value->isActive == 2) {
                            $statusName='Expire'; 
                        } else {
                            $statusName='InActive';
                        }

                  $data[] = [
                    'id' => $key + 1,
                    'userId' => $value->customerregister->name,
                    'packageName' => $value->packageName,
                    'packagePrice' => $value->packagePrice,
                    'packagePeriodInMonth' =>$value->packagePeriodInMonth,
                    'status' => $statusName,
                    'fromDate' => $value->fromDate,
                    'expiryDate' => $value->expiryDate,
                    'createdDate' => $value->createdDate,
                ];

                }

        return collect($data);
    }


    public function headings(): array
    {
        return [ 
            "Id",
            "Customer Name",
            "Package Name",
            "Package Price",
            "Package Period InMonth",
            "status",
            "fromDate",
            "Expiry Date",
            "Created Date",
        ];
    }
}
