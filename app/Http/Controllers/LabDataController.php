<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LabDataController extends Controller
{
    /**
     * Base URL for the Laboratory System
     */
    protected string $labApiUrl;

    public function __construct()
    {
        $this->labApiUrl = rtrim(env('LAB_SYSTEM_URL', 'http://127.0.0.1:8000'), '/');
    }

    /**
     * Fetch payments and laboratory transactions from Laboratory System API
     */
    public function fetchLabPayments(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $search = $request->input('search');

        $queryParams = [];
        if ($startDate && $endDate) {
            $queryParams['from'] = $startDate.' 00:00:00';
            $queryParams['to'] = $endDate.' 23:59:59';
        } elseif ($startDate) {
            $queryParams['date'] = $startDate;
        }

        $isOnline = false;
        $errorMessage = null;
        $summary = [
            'total_records' => 0,
            'total_collected' => 0,
            'total_invoiced' => 0,
            'total_tests_conducted' => 0,
        ];
        $transactions = collect([]);

        try {
            // Attempt API call to Laboratory system /fetch-payments
            $response = Http::timeout(10)
                ->acceptJson()
                ->get("{$this->labApiUrl}/fetch-payments", $queryParams);

            if ($response->successful()) {
                $isOnline = true;
                $responseData = $response->json();

                $summary = $responseData['summary'] ?? $summary;
                $rawTransactions = collect($responseData['data'] ?? []);

                // Client-side search filtering if search term provided
                if (! empty($search)) {
                    $searchLower = strtolower($search);
                    $rawTransactions = $rawTransactions->filter(function ($item) use ($searchLower) {
                        $inv = strtolower($item['invoice_number'] ?? '');
                        $patient = strtolower($item['patient']['name'] ?? '');
                        $company = strtolower($item['company']['name'] ?? '');

                        return str_contains($inv, $searchLower)
                            || str_contains($patient, $searchLower)
                            || str_contains($company, $searchLower);
                    });

                    // Recalculate summary for filtered search
                    $summary['total_records'] = $rawTransactions->count();
                    $summary['total_collected'] = (float) $rawTransactions->sum('payment.paid_amount');
                    $summary['total_invoiced'] = (float) $rawTransactions->sum('payment.total_amount');
                    $summary['total_tests_conducted'] = (int) $rawTransactions->sum('total_tests_count');
                }

                $transactions = $rawTransactions;
            } else {
                $errorMessage = 'Laboratory System returned status: '.$response->status();
            }
        } catch (\Exception $e) {
            $isOnline = false;
            $errorMessage = "Unable to connect to Laboratory System at [{$this->labApiUrl}]. Please verify that the Laboratory application server is running.";
        }

        return view('lab.payments', [
            'isOnline' => $isOnline,
            'errorMessage' => $errorMessage,
            'summary' => $summary,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'labApiUrl' => $this->labApiUrl,
        ]);
    }
}
