<?php

namespace App\Exports;

use App\Models\Package;
use App\Models\Seller;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SellerReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $sellerId;
    protected $sellerName;

    public function __construct($sellerId, $sellerName)
    {
        $this->sellerId = $sellerId;
        $this->sellerName = $sellerName;
    }

    public function collection()
    {
        $today = today()->toDateString();

        $packages = Package::where('seller_id', $this->sellerId)
            ->where(function ($query) use ($today) {
                $query->whereDate('delivery_date', $today)
                    ->orWhereDate('delivery_date_1', $today)
                    ->orWhereDate('delivery_date_2', $today)
                    ->orWhereDate('delivery_date_3', $today);
            })
            ->with(['Customer', 'Seller', 'area'])
            ->get();

        $totalAmountDue = $packages->where('status', 1)->sum(function($pkg) {
            return ($pkg->paid_amount ?? 0) - ($pkg->delivery_cost ?? 0);
        });

        $totalDeliveryCost = $packages->filter(function($pkg) {
            return $pkg->status == 1 || ($pkg->status == 3 && $pkg->failure_reason == 'client_refuse_to_accept_order');
        })->sum('delivery_cost');

        $packages->push((object)[
            'is_total_row' => true,
            'amount_due' => $totalAmountDue,
            'delivery_cost' => $totalDeliveryCost,
        ]);

        return $packages;
    }

    public function map($pkg): array
    {
        if (isset($pkg->is_total_row)) {
            return [
                'المجموع',           // Customer Name
                '',                  // Area
                '',                  // Product Type
                '',                  // Status
                '',                  // Reason
                '',                  // Pieces Count
                '',                  // Delivered Pieces Count
                '',                  // Delivery Fee Payer
                number_format($pkg->amount_due),
                number_format($pkg->delivery_cost),
            ];
        }

        $amountDue = '';
        if ($pkg->status == 1) {
            $amountDue = number_format(($pkg->paid_amount ?? 0) - ($pkg->delivery_cost ?? 0));
        }

        $deliveryCost = '';
        if ($pkg->status == 1 || ($pkg->status == 3 && $pkg->failure_reason == 'client_refuse_to_accept_order')) {
            $deliveryCost = number_format($pkg->delivery_cost ?? 0);
        }

        return [
            $pkg->Customer->name ?? '',
            $pkg->area->name ?? '',
            $pkg->product_type ?? '---',
            getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
            getReasonMessage($pkg->failure_reason) ?? '---',
            $pkg->pieces_count ?? '---',
            $pkg->delivered_pieces_count ?? '---',
            getDeliveryFeePayer($pkg->delivery_fee_payer, $pkg->status, $pkg->failure_reason) ?? '---',
            $amountDue,
            $deliveryCost,
        ];
    }

    public function headings(): array
    {
        return [
            'اسم الزبون',
            'منطقة التوصيل',
            'نوع المنتج',
            'الحالة',
            'السبب',
            'عدد المنتجات',
            'عدد المنتجات الموصلة',
            'جهة تحمّل تكلفة التوصيل',
            'المبلغ المستحق',
            'أجور التوصيل',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $sheet->setRightToLeft(true);
                
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                
                $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }
}

