<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use Jsms;

class routinesController extends Controller
{
    public function __construct() {
        $allowedFromScripts = array(
            'downloadCompaniesAndPrices.php',
            'harvestDownloadedCompaniesAndPrices.php',
            'materializeRawDataPerMinute.php',
            'materializeForPerCompanyPerTradingDay.php',
            'testSms.php',
        );

        if (!in_array(basename($_SERVER['SCRIPT_FILENAME']), $allowedFromScripts))
            exit("The script only runs if started from one of the command line scripts.\n");
    }

    /* being ran every minute on weekdays; Will dump into json file. */
    public function downloadCompaniesAndPrices() {
    	if (!config('app.download_raw_data_beyond_trading_window')) {
    		if (date("N") > 5) {
    			exit("Environment doesn't allow download raw data beyong trading hours.\n");
    		}

    		$currentDateTime = new \DateTime(date("Y-m-d H:i:s"));	//today
    		// note: give allowance.
    		$am_trade_start = new \DateTime(date("Y-m-d 09:29:00"));
    		$am_trade_end = new \DateTime(date("Y-m-d 12:02:00"));
    		$pm_trade_start = new \DateTime(date("Y-m-d 01:29:00"));
    		$pm_trade_end = new \DateTime(date("Y-m-d 15:32:00"));

    		if (!($currentDateTime >= $am_trade_start && $currentDateTime <= $am_trade_end) || !($currentDateTime >= $pm_trade_start && $currentDateTime <= $pm_trade_end)) {
    			exit("Environment doesn't allow download raw data beyong trading hours.\n");
    		}
    	}

    	$client = new Client();

		$response = $client->get('http://phisix-api4.appspot.com/stocks.json');
		$data = json_decode($response->getBody(), TRUE);

		if (!isset($data['as_of']))
			exit("No data in upstream.\n");

        $asOf = $data['as_of'];
        preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
        $asOfDateOnly = $match[0];
        preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
        $asOfTimeOnly = $match[0];

        // now we want replace ":" to "_"
        $pattern = '/:/';
        $replacement = '_';
        $formatedTime = preg_replace($pattern, $replacement, $asOfTimeOnly);
        file_put_contents("/var/log/pse_monitor/raw_data/{$asOfDateOnly}T{$formatedTime}.json", json_encode($data));

        print "Success!\n";
    }

    private function createOrUpdateCompany(Array $stock) {
        // Company::updateOrCreate(['companyName' => $stock['name'], 'symbol' => $stock['symbol'], 'created_at' => date('Y-m-d H:i:s')]);
        DB::insert("INSERT INTO companies(companyName, symbol, created_at) VALUES(?, ?, now())
                ON DUPLICATE KEY UPDATE companyName = ?, symbol = ?", [$stock['name'], $stock['symbol'], $stock['name'], $stock['symbol']]);
    }

    /* Will insert new record or update if record already exists. */
    public function harvestDownloadedCompaniesAndPrices() {
        foreach (glob("/var/log/pse_monitor/raw_data/*.json") as $filename) {
            $data = json_decode(file_get_contents($filename), TRUE);    // returns assoc array
            $stocks = $data['stock'];
            $asOf = $data['as_of'];
            preg_match("/(\d{4}-\d{2}-\d{2})/", $asOf, $match);
            $asOfDateOnly = $match[0];
            preg_match("/(\d{2}:\d{2}:\d{2})/", $asOf, $match);
            $asOfTimeOnly = $match[0];
            $asOfDateTime = $asOfDateOnly . " " . $asOfTimeOnly;
            $asOfDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $asOfDateTime);
            
            foreach ($stocks as $stock) {
                $this->createOrUpdateCompany($stock);

                DB::insert("INSERT INTO raw_records(symbol, amount, percentChange, volume, asOf, created_at) VALUES(?, ?, ?, ?, ?, now())
                    ON DUPLICATE KEY UPDATE symbol = ?, amount = ?, percentChange = ?, volume = ?, asOf = ?",
                    [$stock['symbol'], $stock['price']['amount'], $stock['percent_change'], $stock['volume'], $asOfDateTime,
                    $stock['symbol'], $stock['price']['amount'], $stock['percent_change'], $stock['volume'], $asOfDateTime]);
            }

            $basename = basename($filename);
            rename($filename, "/var/log/pse_monitor/raw_data/processed/$basename");
        }

        print "Success!\n";
    }

    public function materializeRawDataPerMinute() {
    	$rawRecords = DB::select("SELECT id, symbol, amount, percentChange, volume, asOf FROM raw_records WHERE materialized IS NULL OR materialized = 0");

    	foreach ($rawRecords as $rawRecord) {
    		$rawRecordId = $rawRecord->id;
    		$symbol = $rawRecord->symbol;
    		$price = $rawRecord->amount;
    		$percentChange = $rawRecord->percentChange;
    		$volume = $rawRecord->volume;
    		$asOf = $rawRecord->asOf;

    		DB::insert("call sp_aggregate_per_minute('$symbol', $price, '$asOf', $percentChange, $volume, $rawRecordId)");
    	}

        print "Success!\n";
    }    

    public function materializeForPerCompanyPerTradingDay() {
        // we only allow 
        $currentDateTime = new \DateTime(date("Y-m-d H:i:s"));  //today
        $pm_trade_end = new \DateTime(date("Y-m-d 15:30:00"));

        // if ($currentDateTime < $pm_trade_end)
        //     exit("This should be ran at 3.:30PM after trading hours.");

        $sql = "SELECT DATE_FORMAT(asOf, '%Y-%m-%d') AS asOf FROM aggregate_per_minute 
            WHERE materialized IS NULL OR materialized = 0
            GROUP BY DATE_FORMAT(asOf, '%Y-%m-%d')
            ORDER BY DATE_FORMAT(asOf, '%Y-%m-%d')";

        $tableDates = DB::select($sql);

        $latest = [];
        foreach ($tableDates as $tableDate) {
            $latest[$tableDate->asOf] = [];

            $sql = "SELECT id, companyId, price, asOf, percentChange, volume
                    FROM aggregate_per_minute
                    WHERE DATE_FORMAT(asOf, '%Y-%m-%d') = '$tableDate->asOf'
                    AND (materialized IS NULL OR materialized = 0)
                    ORDER BY companyId, asOf;";
            $records = db::select($sql);

            $arrayPerCompany = [];
            // this will overwrite the latest one.
            foreach ($records as $record) {
                $arrayPerCompany[$record->companyId] = [];
                $arrayPerCompany[$record->companyId]['id'] = $record->id;
                $arrayPerCompany[$record->companyId]['price'] = $record->price;
                $arrayPerCompany[$record->companyId]['asOf'] = $record->asOf;
                $arrayPerCompany[$record->companyId]['percentChange'] = $record->percentChange;
                $arrayPerCompany[$record->companyId]['volume'] = $record->volume;
            }

            $latest[$tableDate->asOf] = $arrayPerCompany;

            foreach ($latest as $latestAsOf => $arrayPerCompany) {
                foreach ($arrayPerCompany as $companyId => $value) {
                    DB::statement("call sp_materialize_per_company_daily(
                        {$value['id']},
                        $companyId,
                        {$value['price']},
                        '{$value['asOf']}',
                        {$value['percentChange']},
                        {$value['volume']}
                    )");
                }
            }
        }

        file_put_contents('/tmp/array.txt', print_r($latest, TRUE));

        print "Success!\n";
    }

    public function performEOD() {
    	DB::statement("call sp_perform_eod()");
    }

    public function sendAlertsToSubscribers() {
        // ended here.. to be continued.
    }

    public function sendSmsAlert() {
        
    }

    public function testSMS() {
        $sms = new Jsms\Sms;
        $sms->delayInSeconds = 6;
        print "Set device: " . $sms->setDevice('/dev/ttyUSB2') . "\n";
        print "Open device: " . $sms->openDevice() . "\n";
        print "Set baud rate: " . $sms->setBaudRate(115200) . "\n";
        print "Sent message: " . $sms->sendSMS('09332162333', 'I miss you.') . "\n";
        $sms->sendCmd("ATi");
        print $sms->getDeviceResponse() . "\n";
        print "Device closed: " . $sms->closeDevice() . "\n"; 
    }
}
