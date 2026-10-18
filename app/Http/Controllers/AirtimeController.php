<?php

namespace App\Http\Controllers;

use App\Models\Airtime;
use Illuminate\Http\Request;

class AirtimeController extends Controller
{
    /**
     * Display a listing of the resource with search and status filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Airtime::query();

        $this->applySearchAndFilters($query, $request);

        $airtime = $query->orderBy('id', 'desc')->paginate(50)->withQueryString();
        return response()->json($airtime);
    }

    /**
     * Get aggregate metrics for airtime dispatches.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSummary()
    {
        $totalCount = Airtime::count();
        $totalAmount = (float) Airtime::sum('amount');
        $sentCount = Airtime::whereIn('status', ['Sent', 'sent', 'Success', 'success'])->count();
        $pendingCount = Airtime::whereIn('status', ['Pending', 'pending'])->count();
        $failedCount = Airtime::whereIn('status', ['Failed', 'failed'])->count();

        return response()->json([
            'total_count' => $totalCount,
            'total_amount' => $totalAmount,
            'sent_count' => $sentCount,
            'pending_count' => $pendingCount,
            'failed_count' => $failedCount,
        ]);
    }

    /**
     * Search airtime records by phone, transaction ID, response ID, etc.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $phone
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAirtime(Request $request, $phone = null)
    {
        $query = Airtime::query();
        $searchTerm = $phone ?? $request->input('search');

        if (!empty($searchTerm)) {
            $this->filterBySearchTerm($query, $searchTerm);
        }

        if ($request->filled('status') && strtolower($request->status) !== 'all') {
            $this->filterByStatus($query, $request->status);
        }

        $airtime = $query->orderBy('id', 'desc')->paginate(50)->withQueryString();
        return response()->json($airtime);
    }

    /**
     * Apply search term and status filters to query.
     */
    private function applySearchAndFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $this->filterBySearchTerm($query, $request->search);
        }

        if ($request->filled('status') && strtolower($request->status) !== 'all') {
            $this->filterByStatus($query, $request->status);
        }
    }

    /**
     * Filter query by status with case and synonym tolerance.
     */
    private function filterByStatus($query, $status)
    {
        $normalized = strtolower(trim($status));
        if ($normalized === 'sent' || $normalized === 'success') {
            $query->whereIn('status', ['Sent', 'sent', 'Success', 'success']);
        } elseif ($normalized === 'pending') {
            $query->whereIn('status', ['Pending', 'pending']);
        } elseif ($normalized === 'failed') {
            $query->whereIn('status', ['Failed', 'failed']);
        } else {
            $query->where('status', $status);
        }
    }

    /**
     * Filter query by search term across multiple fields and phone number formats.
     */
    private function filterBySearchTerm($query, $search)
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $cleanDigits = preg_replace('/[^0-9]/', '', $search);

        // Build phone variants to match different national/international phone notations
        $phoneVariants = array_filter([$search, $cleanDigits]);

        if (!empty($cleanDigits)) {
            // Local 07XXXXXXXX (10 digits starting with 0) -> 2567XXXXXXXX, 7XXXXXXXX
            if (str_starts_with($cleanDigits, '0') && strlen($cleanDigits) >= 2) {
                $phoneVariants[] = '256' . substr($cleanDigits, 1);
                $phoneVariants[] = substr($cleanDigits, 1);
            }
            // 7XXXXXXXX (9 digits starting with 7) -> 2567XXXXXXXX, 07XXXXXXXX
            elseif (strlen($cleanDigits) === 9 && str_starts_with($cleanDigits, '7')) {
                $phoneVariants[] = '256' . $cleanDigits;
                $phoneVariants[] = '0' . $cleanDigits;
            }
            // 2567XXXXXXXX (12 digits starting with 256) -> 07XXXXXXXX, 7XXXXXXXX
            elseif (str_starts_with($cleanDigits, '256') && strlen($cleanDigits) >= 4) {
                $phoneVariants[] = '0' . substr($cleanDigits, 3);
                $phoneVariants[] = substr($cleanDigits, 3);
            }
        }

        $phoneVariants = array_values(array_unique(array_filter($phoneVariants)));

        $query->where(function ($q) use ($search, $phoneVariants) {
            $q->where(function ($sub) use ($phoneVariants) {
                foreach ($phoneVariants as $idx => $variant) {
                    if ($idx === 0) {
                        $sub->where('msisdn', 'like', "%{$variant}%");
                    } else {
                        $sub->orWhere('msisdn', 'like', "%{$variant}%");
                    }
                }
            });

            // Also search transactionId, responseId, response, channel, and amount
            $q->orWhere('transactionId', 'like', "%{$search}%")
              ->orWhere('responseId', 'like', "%{$search}%")
              ->orWhere('response', 'like', "%{$search}%")
              ->orWhere('channel', 'like', "%{$search}%")
              ->orWhere('inMessageId', $search)
              ->orWhere('amount', 'like', "%{$search}%");
        });
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Airtime $airtime)
    {
        //
    }

    public function edit(Airtime $airtime)
    {
        //
    }

    public function update(Request $request, Airtime $airtime)
    {
        //
    }

    public function destroy(Airtime $airtime)
    {
        //
    }

    public function updateStatus(Request $request)
    {
        $data = $request->all();
        $airtime = Airtime::where('responseId', $data['requestId'])->first();
        if ($airtime) {
            $airtime->status = $data['status'];
            $airtime->response = $data['description'];
            $airtime->save();
        }
        return response()->json(['status' => 'success']);
    }

    public function receiveEtherOneCallBack(Request $request)
    {
        //Write response to the Log File
        $logFile = 'etherOneCallBack.log';
        $file = storage_path('logs/' . $logFile);
        $log = fopen($file, 'a');
        fwrite($log, $request->getContent());
        fclose($log);
    }
}
