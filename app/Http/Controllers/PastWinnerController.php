<?php

namespace App\Http\Controllers;

use App\Models\PastWinner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PastWinnerController extends Controller
{
    /**
     * Display a listing of the resource with optional search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $status)
    {
        $query = PastWinner::with('user')->where('category', $status);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $cleanSearch = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('msisdn', 'like', "%{$search}%");
                if (!empty($cleanSearch)) {
                    $q->orWhere('msisdn', 'like', "%{$cleanSearch}%");
                }
            });
        }

        $pastWinners = $query->orderBy('id', 'desc')->paginate(50);
        return response()->json($pastWinners);
    }

    /**
     * Store newly created resource(s) in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'msisdn' => 'required',
            'category' => 'required|in:winner,blacklisted',
        ]);

        $rawInput = (string) $request->msisdn;
        $items = preg_split('/[\r\n,;]+/', $rawInput, -1, PREG_SPLIT_NO_EMPTY);
        $added = [];
        $skipped = [];

        $user = Auth::guard('sanctum')->user();
        $userId = $user ? $user->id : 1;

        foreach ($items as $item) {
            $cleaned = preg_replace('/[^0-9]/', '', trim($item));
            if (empty($cleaned)) {
                continue;
            }

            // Normalize Ugandan numbers
            if (str_starts_with($cleaned, '0') && strlen($cleaned) === 10) {
                $cleaned = '256' . substr($cleaned, 1);
            } elseif (strlen($cleaned) === 9 && str_starts_with($cleaned, '7')) {
                $cleaned = '256' . $cleaned;
            }

            if (strlen($cleaned) !== 12 || !str_starts_with($cleaned, '256')) {
                $skipped[] = [
                    'number' => trim($item),
                    'reason' => 'Invalid phone number format. Must be a valid 12-digit Ugandan number (e.g. 07XXXXXXXX or 256XXXXXXXXX).'
                ];
                continue;
            }

            // Check if already in this category
            $exists = PastWinner::where([
                'msisdn' => $cleaned,
                'category' => $request->category,
            ])->exists();

            if ($exists) {
                $skipped[] = [
                    'number' => $cleaned,
                    'reason' => "Number {$cleaned} is already on the {$request->category} list."
                ];
                continue;
            }

            $pastWinner = PastWinner::create([
                'msisdn' => $cleaned,
                'addedBy' => $userId,
                'category' => $request->category,
            ]);

            $pastWinner->load('user');
            $added[] = $pastWinner;
        }

        if (empty($added) && !empty($skipped)) {
            $firstError = $skipped[0]['reason'];
            return response()->json([
                'message' => count($skipped) === 1 ? $firstError : 'None of the provided numbers could be added.',
                'skipped' => $skipped,
            ], 422);
        }

        $categoryName = $request->category === 'blacklisted' ? 'blacklist' : 'past winners';
        $msg = count($added) === 1
            ? "Phone number {$added[0]->msisdn} added successfully to {$categoryName}."
            : count($added) . " numbers added successfully to {$categoryName}.";

        if (!empty($skipped)) {
            $msg .= " (" . count($skipped) . " skipped due to duplicate or invalid format)";
        }

        return response()->json([
            'message' => $msg,
            'data' => count($added) === 1 ? $added[0] : $added,
            'added' => $added,
            'skipped' => $skipped,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int|string  $pastWinner
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($pastWinner)
    {
        $record = PastWinner::find($pastWinner);
        if (!$record) {
            return response()->json(['message' => 'Record not found or already removed.'], 404);
        }

        $msisdn = $record->msisdn;
        $category = $record->category === 'blacklisted' ? 'blacklist' : 'past winners';
        $record->delete();

        return response()->json([
            'message' => "Phone number {$msisdn} removed successfully from {$category}."
        ]);
    }
}
