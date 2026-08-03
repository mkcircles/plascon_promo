<?php

namespace App\Http\Controllers;

use App\Models\Codes;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CodesController extends Controller
{
    public function generateCodes(Request $request)
    {
        $brand = $request->brand;
        $count = $request->count;

        $prefix = $this->getPrefix($brand);
        // dd($prefix);
        $code = $prefix['code'];
        $brand = $prefix['brand'];

        for ($i = 0; $i < $request->count; $i++) {
            if (strlen($code) == 3) {   //Generate 6 Digit Code
                $data = $this->createCode(5);
            } elseif (strlen($code) == 4) {    //Generate 4 Digit Code
                $data = $this->createCode(4);
            }
            $finalCode = $code . '' . $data;
            if ($this->checkExistance($finalCode)) {
                echo $i . ' => ' . $finalCode . ' :: ' . $brand . '<br/>';
                $this->saveCode($finalCode, $brand);
            }
        }
        $count = Codes::where('brand', $brand)->count();
        $balance = $request->count - $count;
        for ($j = 0; $i < $balance; $j++) {
            if (strlen($code) == 2) {   //Generate 6 Digit Code
                $data = $this->createCode(6);
            } elseif (strlen($code) == 4) {    //Generate 4 Digit Code
                $data = $this->createCode(4);
            }
            $finalCode = $code . '' . $data;
            if ($this->checkExistance($finalCode)) {
                $this->saveCode($finalCode, $brand);
            }
        }

    }


    private function getPrefix($brand)
    {
        switch (strtolower($brand)) {
            case 'Mirinda': {
                return $data = ['code' => 'CBM', 'brand' => 'Mirinda'];
                break;
            }
            case 'Pepsi': {
                return $data = ['code' => 'CBP', 'brand' => 'Pepsi'];
                break;
            }
            case 'Mountain Dew': {
                return $data = ['code' => 'CBD', 'brand' => 'Mountain Dew'];
                break;
            }
            case '7Up': {
                return $data = ['code' => 'CBS', 'brand' => '7Up'];
                break;
            }
            case 'Evervess': {
                return $data = ['code' => 'CBE', 'brand' => 'Evervess'];
                break;
            }
            case 'Aquafina': {
                return $data = ['code' => 'CBA', 'brand' => 'Aquafina'];
                break;
            }
            default: {
                return $data = ['code' => 'CBT', 'brand' => 'Test'];
                break;
            }
        }
    }

    private function createCode($int)
    {
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $int; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Check if Code already exists
     * @param $finalCode
     * @return bool
     */
    private function checkExistance($finalCode)
    {
        $code = Codes::where('code', $finalCode)->first();
        return is_null($code) ? true : false;
    }

    private function saveCode($finalCode, $brand)
    {
        $mytime = Carbon::now()->toDateTimeString();

        Codes::insert([
            'code' => $finalCode,
            'brand' => $brand,
            'status' => 'Pending',
            'inMessageId' => '',
            'prizeWon' => '',
            'created_at' => $mytime
        ]);

        return;
    }

    public function getCodes()
    {
        $codes = Codes::orderBy('id', 'desc')->paginate(50);
        return response()->json($codes);
    }

    public function getBrandCodes($name)
    {
        $name = str_replace("-", " ", $name);
        $codes = Codes::where('brand', $name)->orderBy('id', 'desc')->paginate(50);
        return response()->json($codes);
    }

    public function getUsedCodes()
    {
        $codes = Codes::where('status', 'used')->paginate(50);
        return response()->json($codes);
    }

    public function getCodeData()
    {
        $codes = Codes::paginate(100);
        return Datatables::of($codes)->make(true);
    }

    public function searchCode($query)
    {
        $codes = Codes::where('code', $query)->orderBy('id', 'desc')->paginate(100);
        return Datatables::of($codes)->make(true);
    }


    public function export(Request $request)
    {
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $codes = Codes::getReviewExport($this->hw->healthwatchID)->get();
        $columns = array('Code', 'Brand', 'Status', 'Phone Number', 'Prize Won');

        $callback = function () use ($codes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($codes as $code) {
                fputcsv($file, array($code->code, $code->brand, $code->status, $code->inMessageId, $code->prizeWon));
            }
            fclose($file);
        };
        return Response::stream($callback, 200, $headers);
    }



    public function getCodesReport(Request $request)
    {
        $from = $request->fromDate;
        $to = $request->todate;
        $prize = $request->prize;
        $brand = $request->brand;

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        // Enforce status = 'used' to prevent misuse of scratch codes
        $codes = DB::table('codes')
            ->where('status', 'used')
            ->whereBetween('updated_at', [$from, $to]);

        if ($prize && $prize != 'Any') {
            if (strtolower($prize) == 'pen') {
                $p = 'Pen';
            } else {
                $p = 'Airtime - 2000';
            }
            $codes = $codes->where('prizeWon', $p);
        }
        if ($brand && $brand != 'all') {
            $codes = $codes->where('brand', $brand);
        }
        $codes = $codes->get();

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=used-codes-report.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        $columns = array('PHONE NUMBER', 'CODE', 'STATUS', 'PRIZE WON', 'DATE');

        $callback = function () use ($codes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($codes as $code) {
                fputcsv($file, array($code->inMessageId, $code->code, $code->status, $code->prizeWon, $code->updated_at));
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function getBrandUsageChart(Request $request)
    {
        $days = (int) $request->get('days', 7);
        if (!in_array($days, [7, 30])) {
            $days = 7;
        }

        // Get the list of dates for the last N days
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }

        // Get all distinct brands in the database
        $brands = DB::table('codes')
            ->select('brand')
            ->distinct()
            ->whereNotNull('brand')
            ->where('brand', '<>', '')
            ->pluck('brand')
            ->toArray();

        if (empty($brands)) {
            $brands = ['Mirinda Fruity', 'Mirinda Green Apple', 'Mirinda Orange', 'Mirinda Pineapple'];
        }

        // Query counts
        $startDate = now()->subDays($days - 1)->startOfDay();
        $results = DB::table('codes')
            ->select(DB::raw('DATE(updated_at) as date'), 'brand', DB::raw('count(*) as count'))
            ->where('status', 'used')
            ->where('updated_at', '>=', $startDate)
            ->groupBy('date', 'brand')
            ->get();

        // Group by brand and date
        $dataByBrand = [];
        foreach ($brands as $brand) {
            $dataByBrand[$brand] = array_fill_keys($dates, 0);
        }

        foreach ($results as $row) {
            $brand = $row->brand;
            foreach ($brands as $b) {
                if (strtolower($brand) === strtolower($b)) {
                    $brand = $b;
                    break;
                }
            }
            if (isset($dataByBrand[$brand])) {
                $dataByBrand[$brand][$row->date] = (int) $row->count;
            }
        }

        // Format for ChartJS/ApexCharts
        $datasets = [];
        $colors = [
            'Mirinda Fruity' => '#ec4899',       // Pink
            'Mirinda Green Apple' => '#10b981',  // Green
            'Mirinda Orange' => '#f97316',       // Orange
            'Mirinda Pineapple' => '#eab308',    // Yellow
            'Pepsi' => '#005cb4',
            'Mirinda' => '#f87979',
            'Mountain Dew' => '#10b981',
            '7Up' => '#3b82f6',
            'Evervess' => '#8b5cf6',
            'Aquafina' => '#06b6d4',
        ];

        // Fallback color palette for any other dynamically added brands
        $palette = ['#ec4899', '#10b981', '#f97316', '#eab308', '#3b82f6', '#8b5cf6', '#06b6d4'];
        $colorIndex = 0;

        foreach ($brands as $brand) {
            $color = $colors[$brand] ?? $palette[$colorIndex % count($palette)];
            $colorIndex++;

            $datasets[] = [
                'label' => $brand,
                'borderColor' => $color,
                'backgroundColor' => $color . '20',
                'data' => array_values($dataByBrand[$brand]),
                'tension' => 0.3
            ];
        }

        return response()->json([
            'dates' => $dates,
            'datasets' => $datasets
        ]);
    }
}
