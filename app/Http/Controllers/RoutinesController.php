<?php

namespace App\Http\Controllers;

/*use Illuminate\Http\Request;

use App\Http\Requests;*/

use Illuminate\Support\Facades\DB;

// use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use Carbon\Carbon;

use Jsms;

class RoutinesController extends Controller
{
    public function __construct() {
        $allowedFromScripts = array(
            'downloadCompaniesAndPrices.php',
            'harvestDownloadedCompaniesAndPrices.php',
            'materializeRawDataPerMinute.php',
            'materializeForPerCompanyPerTradingDay.php',
            'sendDailyAlertsToSubscribers.php', //
            'testSms.php',  // if you want to test SMS.
            'artisan',  // used for to run "php artisan route:list"

            /* The following, when there's specific date to be downloaded from the upstream. */
            'downloadCompaniesAndPricesByDate.php',
            'harvestDownloadedCompaniesAndPricesPerCompany.php',

            /* If current date is missing. */
            'downloadCompaniesAndPricesByCurrentDate.php',

            'sendSmsMessages.php',

            // SMS load status
            'alertAdministratorLoadStatus.php',

            'downloadSmsMessages.php', // Ask for a json formatted list of SMS to be sent from a downstream.
            'sendPerMinuteAlertsToSubscribers.php',

            'deleteOldRecords.php',
        );

        $allowedFromScriptsOnHolidays = array(
            'testSms.php',  // if you want to test SMS.
            'artisan',  // used for to run "php artisan route:list"

            'alertAdministratorLoadStatus.php' // SMS load status
        );

        if (!in_array(basename($_SERVER['SCRIPT_FILENAME']), $allowedFromScripts))
            exit("The script only runs if started from one of the command line scripts listed.\n");

        // Check if today's holiday.
        $holidays = DB::table('holidays')->select('theDate')->whereDate('theDate', DB::raw('CURDATE()'))->count();

        if ($holidays)
            if (!in_array(basename($_SERVER['SCRIPT_FILENAME']), $allowedFromScriptsOnHolidays))
                exit("Only selected scripts are allowed to run on holidays.\n");
    }

    /* being ran every minute on weekdays; Will dump into json file.
    *   I noticed 3:20 PM is the last transaction in pse not 3:30 PM.
    */
    public function downloadCompaniesAndPrices() {
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

    /* Run as needed. Will dump into json file. No restriction on when to run.
        I created this one because I noticed upstream data doesn't include index percent_change.
        After running this, run
        1. $this->harvestDownloadedCompaniesAndPricesPerCompany().
        2. $this->materializeRawDataPerMinute();
        3. $this->materializeForPerCompanyPerTradingDay().
        4. $this->sendDailyAlertsToSubscribers().
    */
    public function downloadCompaniesAndPricesByDate($date = NULL) {
        if ($date == NULL)
            exit("Specify the date.\n");

        $date = \DateTime::createFromFormat('Y-m-d', $date);
        $date = $date->format('Y-m-d');

        $symbols = \App\Company::select('symbol')->get();

        foreach ($symbols as $symbol) {
            try {
                $client = new Client();
                $response = $client->get("http://phisix-api4.appspot.com/stocks/{$symbol->symbol}.{$date}.json");
            } catch(RequestException  $e) {
                echo Psr7\str($e->getRequest());
                if ($e->hasResponse())
                    echo Psr7\str($e->getResponse());

                continue;
            }

            if ($response->getStatusCode() != 200)
                continue;

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
            file_put_contents("/var/log/pse_monitor/raw_data/perCompany/{$symbol->symbol}_{$asOfDateOnly}T{$formatedTime}.json", json_encode($data));
        }

        print "Success!\n";
    }

    /* Run if you want current date. Will dump into json file. No restriction on when to run.
        I realized running $this->downloadCompaniesAndPricesByDate() can miss a company because it iterate from DB result. So if there are new, it won't get the data.
        After running this, run
        1. $this->harvestDownloadedCompaniesAndPrices().
        2. $this->materializeRawDataPerMinute.php
        3. $this->materializeForPerCompanyPerTradingDay().
        4. 4. $this->sendDailyAlertsToSubscribers().
    */
    public function downloadCompaniesAndPricesByCurrentDate() {
        try {
            $client = new Client();
            $response = $client->get("http://phisix-api.appspot.com/stocks.json");
        } catch(RequestException  $e) {
            echo Psr7\str($e->getRequest());
            if ($e->hasResponse())
                echo Psr7\str($e->getResponse());
        }

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

            if (!isset($data['stock'])) {
                continue;
            }

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

    /* Will insert new record or update if record already exists.
        I created this one because I noticed upstream data doesn't include index percent_change. */
    public function harvestDownloadedCompaniesAndPricesPerCompany() {
        foreach (glob("/var/log/pse_monitor/raw_data/perCompany/*.json") as $filename) {
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
                    [$stock['symbol'], $stock['price']['amount'], 0, $stock['volume'], $asOfDateTime,
                    $stock['symbol'], $stock['price']['amount'], 0, $stock['volume'], $asOfDateTime]);
            }

            $basename = basename($filename);
            rename($filename, "/var/log/pse_monitor/raw_data/processed/perCompany/$basename");
        }

        print "Success!\n";
    }


    /**
    * The diff bet materialized null and 0 is that the former is untouched.
    */
    public function materializeRawDataPerMinute() {
        $rawRecords = DB::select("SELECT id, symbol, amount, percentChange, volume, asOf FROM raw_records WHERE (materialized IS NULL OR materialized = 0)");

        $rawRecords2 = [];
        foreach ($rawRecords as $rawRecord) {
            $rawRecords2[$rawRecord->id] = [];
            $rawRecords2[$rawRecord->id]['symbol'] = $rawRecord->symbol;
            $rawRecords2[$rawRecord->id]['price'] = $rawRecord->amount;
            $rawRecords2[$rawRecord->id]['percentChange'] = $rawRecord->percentChange;
            $rawRecords2[$rawRecord->id]['volume'] = $rawRecord->volume;
            $rawRecords2[$rawRecord->id]['asOf'] = $rawRecord->asOf;
        }

        foreach ($rawRecords2 as $rawRecordId => $rawRecord) {
            $symbol = $rawRecord['symbol'];
            $price = $rawRecord['price'];
            $asOf = $rawRecord['asOf'];
            $percentChange = $rawRecord['percentChange'];
            $volume = $rawRecord['volume'];
            DB::statement("call sp_aggregate_per_minute('$symbol', $price, '$asOf', $percentChange, $volume, $rawRecordId)");
        }

        print "Success!\n";
    }

    public function materializeForPerCompanyPerTradingDay() {
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
                    ORDER BY companyId, asOf";
            $records = db::select($sql);

            $arrayPerCompany = [];  $IDlist = [];
            // this will overwrite with the latest one since it's already being ORDERed BY
            foreach ($records as $record) {
                $IDlist[] = $record->id;
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

            // DB::table('aggregate_per_minute')->whereIn('id', $IDlist)->update(['materialized' => 1]);
        }

        print "Success!\n";
    }


    /* Intended to run at the end of the trading day. */
    public function sendDailyAlertsToSubscribers() {
        $sql = "SELECT alerts.id, companies.symbol, alerts.priceCondition, alerts.price alertPrice, MPCD.price currentPrice, MPCD.asOf, users.mobileNo
                FROM alerts JOIN materialize_per_company_daily MPCD ON alerts.companyId = MPCD.companyId
                    JOIN companies ON alerts.companyId = companies.id
                    JOIN subscriptions ON subscriptions.id = alerts.subscriptionId
                    JOIN users ON users.id = subscriptions.userId
                WHERE DATE_FORMAT(alerts.updated_at, '%Y-%m-%d') <= DATE_FORMAT(MPCD.asOf, '%Y-%m-%d')
                    AND sentToSms = 0
                    AND alerts.updated_at < NOW()
                    AND DATE_FORMAT(MPCD.asOf, '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')
                    AND users.active = 1
                    AND (CASE
                            WHEN priceCondition = 'movesBelow' THEN MPCD.price < alerts.price
                            WHEN priceCondition = 'movesAbove' THEN MPCD.price > alerts.price
                        END)";

        $records = DB::select($sql);

        foreach ($records as $record) {
            $priceCondition = "";

            if ($record->priceCondition == 'movesAbove') {
                if ($record->alertPrice < $record->currentPrice) {
                    $priceCondition = "above";
                }
            } elseif ($record->priceCondition == 'movesBelow') {
                if ($record->alertPrice > $record->currentPrice) {
                    $priceCondition = "below";
                }
            }

            if ($priceCondition != "") {
                $message = "PSE Alert!";
                $message .= "\n{$record->symbol} has already reached $priceCondition your alert price " . floatval($record->alertPrice) . ". As of {$record->asOf}, " . floatval($record->currentPrice) . ". ";
                $message .= "\nVisit " . str_replace("http://", "", config("app.url")) . " to set new alert.";

                DB::beginTransaction();
                    DB::table('alerts')->where('id', $record->id)->update(['sentToSms' => 1]);
                    DB::table('smsMessages')->insert(['alertId' => $record->id, 'recipient' => $record->mobileNo, 'message'=> $message]);
                DB::commit();
            }
        }
    }


    /* All outgoing SMS messages should be sent by this; This will scan smsMessages table and send it to recipient. */
    /* 6/11/2018: Task of sending messages has already been localized. */
    public function sendSmsMessages() {
        $sms = new Jsms\Sms;
        $sms->delayInSeconds = 10;  // so far setting to 10 doesn't have an issue with the modem.
        print "Set device: " . $sms->setDevice(config('app.device_port')) . "\n";
        print "Open device: " . $sms->openDevice() . "\n";
        print "Set baud rate: " . $sms->setBaudRate(config('app.baud_rate')) . "\n";

        while (true) {
            $records = DB::table('smsMessages')->select('id', 'alertId', 'recipient', 'message', 'status')->whereIn('status', ['draft', 'outbox'])->get();

            $smsMessages = [];
            foreach ($records as $record)
                $smsMessages[] = ['id'=> $record->id, 'alertId' => $record->alertId, 'recipient' => $record->recipient, 'message' => $record->message, 'status' => $record->status];

            foreach ($smsMessages as $smsMessage) {
                $mobilePrefix = substr($smsMessage['recipient'], 0, 4);
                $telco = DB::table('telcos')->select('network')->where('mobilePrefix', $mobilePrefix)->first();

                if ($telco) {   // We do not send to unknown TelCo
                    $simCards = DB::table('simCards')->select('allowedToBeSent', 'allowedToSameNetwork', 'allowedToOtherNetwork', 'sentToSameNetwork', 'sentToOtherNetwork', 'sentMessages')->where('id', 1)->first();

                    $consideredAsOtherNetwork = explode(',', config('app.considered_as_other_network'));
                    if (!in_array($telco->network, $consideredAsOtherNetwork) &&
                        ($simCards->allowedToSameNetwork >= $simCards->sentToSameNetwork) &&
                        ($simCards->allowedToBeSent >= $simCards->sentMessages)) { // meaning they're in the same network because I currently use TnT
                        $allowedToSendMessage = true;
                    } elseif (in_array($telco->network, $consideredAsOtherNetwork) &&
                        ($simCards->allowedToOtherNetwork >= $simCards->sentToOtherNetwork) &&
                        ($simCards->allowedToBeSent >= $simCards->sentMessages)) { // meaning they're not in the same network because I currently use TnT
                        $allowedToSendMessage = true;
                    } else {
                        $allowedToSendMessage = false;
                    }

                    if ($allowedToSendMessage) {
                        $sentMessage = $sms->sendSMS($smsMessage['recipient'], $smsMessage['message']);

                        print "Message sent: ${sentMessage}; id: ${smsMessage['id']} \n";

                        if ($sentMessage) {
                            DB::beginTransaction();
                                if ($smsMessage['alertId'])
                                    DB::table('alerts')->where('id', $smsMessage['alertId'])->update(['sentToSms' => 1]);

                                DB::table('smsMessages')->where('id', $smsMessage['id'])->update(['status' => 'sent']);

                                $simCards = DB::table('simCards')->where('id', 1);
                                if (!in_array($telco->network, $consideredAsOtherNetwork))
                                    $simCards->increment('sentToSameNetwork');
                                else
                                    $simCards->increment('sentToOtherNetwork');

                                DB::table('simCards')->where('id', 1)->increment('sentMessages');

                                switch ($telco->network) {
                                    case 'Smart':
                                        $simCards->increment('sentToSmart');
                                        break;
                                    case 'Tnt':
                                        $simCards->increment('sentToTnt');
                                        break;
                                    case 'Sun':
                                        $simCards->increment('sentToSun');
                                        break;
                                    case 'Globe':
                                        $simCards->increment('sentToGlobe');
                                        break;
                                    case 'Tm':
                                        $simCards->increment('sentToTm');
                                        break;
                                    default:
                                        // make sure that network is known i.e., if you add new network in the telcos table, add column for it in order to be counted on every sms sent
                                        break;
                                }
                            DB::commit();
                        } else {
                            DB::table('smsMessages')->where('id', $smsMessage['id'])->update(['status' => 'outbox']);
                        }
                    }
                }
            }

            sleep(30);
        }

        // This is unreachable due to new implementation instead of running this with cron job.
        print "Device closed: " . $sms->closeDevice() . "\n";
    }

    /* Alert the administrator if the load is about to expire or the number of sms sent is about to reach its allowed. */
    /* To be implemented soon: Instead of sending SMS on its own, give it to $this->sendSmsMessages(). */
    public function alertAdministratorLoadStatus() {
        $status = DB::select('CALL sp_getSmsLoadStatus()')[0];
        DB::table('smsMessage')->insert(['recipient' => 'XXXXXXXXXXX', 'message' => "PSE Alert!\nSMS unli is about to expire on {$status->dateLoadExpiry} or other network bal is less than or equal to 10."]);
    }

    public function testSms() {
        $message = 'Decode this.... ' . str_random(40);
        DB::table('smsMessages')->insert(['recipient' => '09065165124', 'message'=> $message]);
    }


    /**
    * Sends per minute alert to subscribers to alert prices.
    *
    * @param <none>
    * @return void
    */
    public function sendPerMinuteAlertsToSubscribers() {
        $records = DB::select('CALL sp_getSubscribersForPerMinAlert()');

        foreach ($records as $record) {
            $priceCondition = "";

            if ($record->priceCondition == 'movesAbove') {
                $priceCondition = "above";
            } elseif ($record->priceCondition == 'movesBelow') {
                $priceCondition = "below";
            }

            if ($priceCondition !== "") {
                $message = "PSE Alert!";
                $message .= "\n{$record->symbol} has already reached $priceCondition your alert price " . floatval($record->alertPrice) . ". As of {$record->asOf}, " . floatval($record->currentPrice) . ". ";
                $message .= "\nVisit " . str_replace("http://", "", config("app.url")) . " to set new alert.";
                DB::beginTransaction();
                    DB::table('alerts')->where('id', $record->id)->update(['sentToSms' => 1]);
                    DB::table('smsMessages')->insert(['alertId' => $record->id, 'recipient' => $record->mobileNo, 'message'=> $message]);
                DB::commit();
            }
        }
    }

    /**
    * Delete records of more than $days days already to free up space.
    *
    */
    public function deleteOldRecords($days = 60) {
        if ($days < 60) {
            print "Not allowed to delete below 60 days.\n";
            return;
        }

        $carbon = Carbon::now();
        $date = $carbon->subDays($days);

        DB::beginTransaction();
            DB::table('raw_records')->whereDate('created_at', '<', $date)->delete();
            DB::table('aggregate_per_minute')->whereDate('created_at', '<', $date)->delete();
        DB::commit();

        print "Successfully deleted old records!\n";
    }
}
