<?php

namespace Laraspace\Console\Commands;

use Illuminate\Console\Command;
use Laraspace\TransactionMaster;
use Laraspace\TransactionDetails;
use Carbon\Carbon;
class UpdateTransactionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:update_status';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update transaction status based on start and end date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    /*Note: Once the purchased subject period is completed. The transaction status will change to expire*/
    public function handle()
    {
        $currentDate = Carbon::now()->toDateString();
        $transactions = TransactionDetails::where('status','1')->whereDate('end_date', '<', $currentDate)->update(['status' => '2']);

        $this->info('Transaction status updated successfully.');
    }
}
