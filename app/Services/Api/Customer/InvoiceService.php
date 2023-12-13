<?php

namespace App\Services\Api\Customer;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceSummary;

class InvoiceService
{
    public function checkInvoiceOverlap($customerId, $startDate, $endDate)
    {
        return Invoice::where('customer_id', $customerId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<', $endDate)
                        ->where('end_date', '>', $startDate);
                });
            })->exists();
    }
    public function createOrUpdateInvoice($customerId, $startDate, $endDate)
    {
        return Invoice::firstOrCreate(
            [
                'customer_id' => $customerId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            [
                'total_cost' => null
            ]
        );
    }

    public function countUserEvents($user, $startDate, $endDate)
    {
        $sessions = $user->sessions()
            ->whereBetween('event_date', [$startDate, $endDate])
            ->whereDoesntHave('invoiceDetail')
            ->get()
            ->groupBy('event_type');

        return [
            'registration' => $sessions->get('registration', collect())->count(),
            'activation' => $sessions->get('activation', collect())->count(),
            'appointment' => $sessions->get('appointment', collect())->count(),
            'sessions' => $sessions->collapse()
        ];
    }

    public function createInvoiceDetail($session, $invoiceId)
    {
        InvoiceDetail::create([
            'session_id' => $session->id,
            'invoice_id' => $invoiceId,
            'user_id' => $session->user_id
        ]);
    }

    public function determineCost($eventCounts)
    {
        if ($eventCounts['appointment'] > 0) {
            return [
                'highestCostEvent' => 'appointment',
                'cost' => 200,
                'message' => 'Charged for appointment event'
            ];
        }

        if ($eventCounts['activation'] > 0) {
            $cost = $eventCounts['registration'] > 0 ? 100 : 50;
            return [
                'highestCostEvent' => 'activation',
                'cost' => $cost,
                'message' => $eventCounts['registration'] > 0 ?
                    'Charged for activation event with registration' :
                    'Charged for activation event without registration'
            ];
        }

        if ($eventCounts['registration'] > 0) {
            return [
                'highestCostEvent' => 'registration',
                'cost' => 50,
                'message' => 'Charged for registration event'
            ];
        }

        return [
            'highestCostEvent' => 'none',
            'cost' => 0,
            'message' => 'No chargeable events'
        ];
    }

    public function createInvoiceSummary($userId, $invoiceId, $eventCounts, $costDetails)
    {
        return InvoiceSummary::create([
            'user_id' => $userId,
            'invoice_id' => $invoiceId,
            'number_of_registration' => $eventCounts['registration'],
            'number_of_activation' => $eventCounts['activation'],
            'number_of_appointment' => $eventCounts['appointment'],
            'highest_cost_event' => $costDetails['highestCostEvent'],
            'reason_for_invoice' => $costDetails['message'],
            'cost' => $costDetails['cost']
        ]);
    }

    public function calculateInvoiceTotalCost($customer, $payload, $invoice)
    {
        $invoiceTotalCost = 0;
        foreach ($customer->users as $user) {
            $eventCounts = $this->countUserEvents($user, $payload['start_date'], $payload['end_date']);

            if (count($eventCounts['sessions'])) {

                foreach ($eventCounts['sessions'] as $session) {
                    $this->createInvoiceDetail($session, $invoice->id);
                }

                $costDetails = $this->determineCost($eventCounts);

                $invoiceSummary = $this->createInvoiceSummary($user->id, $invoice->id, $eventCounts, $costDetails);

                $invoiceTotalCost += $invoiceSummary->cost;
            }
        }
        return $invoiceTotalCost;
    }

    public function getInvoiceDetails($invoiceId)
    {
        $invoice = Invoice::withDetailedSummary($invoiceId)->findOrFail($invoiceId);

        $eventFrequencies = $this->calculateEventFrequencies($invoice);
        $invoiceData = $this->transformInvoiceData($invoice, $eventFrequencies);

        return $invoiceData;
    }

    private function calculateEventFrequencies($invoice)
    {
        return $invoice->customer->users->flatMap(function ($user) {
            return $user->invoiceSummaries;
        })->reduce(function ($carry, $summary) {
            return [
                'registration' => $carry['registration'] + $summary->number_of_registration,
                'activation' => $carry['activation'] + $summary->number_of_activation,
                'appointment' => $carry['appointment'] + $summary->number_of_appointment
            ];
        }, ['registration' => 0, 'activation' => 0, 'appointment' => 0]);
    }

    private function transformInvoiceData($invoice, $eventFrequencies)
    {
        return [
            'start_date' => $invoice->start_date,
            'end_date' => $invoice->end_date,
            'total_cost' => $invoice->total_cost,
            'users' => $invoice->customer->users->map(function ($user) {
                return $this->transformUser($user);
            }),
            'invoiced_events' => $this->getInvoicedEvents($invoice),
            'event_frequencies' => $eventFrequencies
        ];
    }

    public function transformUser($user)
    {
        return [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'invoice_summaries' => $user->invoiceSummaries->map(function ($summary) {
                return $this->transformSummary($summary);
            })
        ];
    }

    private function transformSummary($summary)
    {
        return [
            'registrations' => $summary->number_of_registration,
            'activations' => $summary->number_of_activation,
            'appointments' => $summary->number_of_appointment,
            'highest_cost_event' => $summary->highest_cost_event,
            'cost' => $summary->cost,
            'reason_for_invoice' => $summary->reason_for_invoice
        ];
    }

    private function getInvoicedEvents($invoice)
    {
        return $invoice->customer->users->flatMap(function ($user) {
            return $user->invoiceSummaries->pluck('highest_cost_event');
        })->all();
    }
}
