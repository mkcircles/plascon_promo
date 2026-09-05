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
            case 'vinyl silk':
            case 'vinyl-silk':
                return ['code' => 'PLVS', 'brand' => 'Vinyl Silk'];
            case 'weatherguard':
                return ['code' => 'PLWG', 'brand' => 'Weatherguard'];
            case 'anti-mosquito':
            case 'anti mosquito':
                return ['code' => 'PLAM', 'brand' => 'Anti-Mosquito'];
            case 'super gloss':
            case 'super-gloss':
                return ['code' => 'PLSG', 'brand' => 'Super Gloss'];
            case 'roof paint':
            case 'roof-paint':
                return ['code' => 'PLRP', 'brand' => 'Roof Paint'];
            case 'arua':
                return ['code' => 'KPMF', 'brand' => 'Arua'];
            case 'fort portal':
                return ['code' => 'KPMZ', 'brand' => 'Fort Portal'];
            case 'gulu':
                return ['code' => 'KPMF', 'brand' => 'Gulu'];
            case 'jinja':
                return ['code' => 'KPMA', 'brand' => 'Jinja'];
            case 'masaka':
                return ['code' => 'KPMZ', 'brand' => 'Masaka'];
            case 'mbale':
                return ['code' => 'KPMF', 'brand' => 'Mbale'];
            case 'mbarara':
                return ['code' => 'KPMA', 'brand' => 'Mbarara'];
            case 'lira':
                return ['code' => 'KPMZ', 'brand' => 'Lira'];
            case 'kampala':
                return ['code' => 'KP', 'brand' => 'Kampala'];
            default:
                return ['code' => 'PL', 'brand' => 'Plascon'];
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

    public function getDistinctBrands()
    {
        $brands = Codes::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand', 'asc')
            ->pluck('brand');

        return response()->json($brands);
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
        $brand = $request->brand;

        $from = Carbon::parse($from)->startOfDay();
        $to = Carbon::parse($to)->endOfDay();

        // Enforce status = 'used' to prevent misuse of scratch codes
        $codes = DB::table('codes')
            ->where('status', 'used')
            ->whereBetween('updated_at', [$from, $to]);

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
        $columns = array('PHONE NUMBER', 'CODE', 'BRAND', 'STATUS', 'DATE');

        $callback = function () use ($codes, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($codes as $code) {
                fputcsv($file, array($code->inMessageId, $code->code, $code->brand, $code->status, $code->updated_at));
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
            $brands = ['Vinyl Silk', 'Weatherguard', 'Anti-Mosquito'];
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
            'Vinyl Silk' => '#e31b23',          // Plascon Red
            'Weatherguard' => '#005cb4',        // Plascon Blue
            'Anti-Mosquito' => '#10b981',       // Green
            'Super Gloss' => '#f59e0b',         // Amber/Gold
            'Roof Paint' => '#8b5cf6',          // Purple
            'Kampala' => '#e31b23',
            'Jinja' => '#005cb4',
            'Mbarara' => '#10b981',
            'Gulu' => '#f59e0b',
            'Arua' => '#8b5cf6',
            'Fort Portal' => '#06b6d4',
            'Mbale' => '#ec4899',
            'Masaka' => '#6366f1',
            'Lira' => '#14b8a6',
        ];

        // Fallback color palette for any other dynamically added brands
        $palette = ['#e31b23', '#005cb4', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#6366f1'];
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
