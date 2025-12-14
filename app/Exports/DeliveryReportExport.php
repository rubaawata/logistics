<?php

namespace App\Exports;

use App\Models\Delivery;
use App\Models\Package;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DeliveryReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $deliveryId;
    protected $deliveryName;

    public function __construct($deliveryId, $deliveryName)
    {
        $this->deliveryId = $deliveryId;
        $this->deliveryName = $deliveryName;
    }

    public function collection()
    {
        $today = today()->toDateString();

        $packages = Package::where('delivery_id', $this->deliveryId)
            ->where(function ($query) use ($today) {
                $query->whereDate('delivery_date', $today)
                    ->orWhereDate('delivery_date_1', $today)
                    ->orWhereDate('delivery_date_2', $today)
                    ->orWhereDate('delivery_date_3', $today);
            })
            ->with(['Customer', 'Seller', 'area'])
            ->get();

        $totalPackageCost = $packages->sum('package_cost');
        $totalDeliveryCost = $packages->sum('delivery_cost');
        $totalPaidAmount   = $packages->sum('paid_amount');

        $packages->push((object)[
            'is_total_row'  => true, 
            'package_cost'  => $totalPackageCost,
            'delivery_cost' => $totalDeliveryCost,
            'paid_amount'   => $totalPaidAmount,
        ]);

        return $packages;
    }

    public function map($pkg): array
    {
        if (isset($pkg->is_total_row)) {
            return [
                'المجموع الكلي',    // Customer Name
                '',                 // Area
                '',                 // Type
                '',                 // Status
                '',                 // Reason
                '',                 // Pieces
                number_format($pkg->package_cost),
                number_format($pkg->delivery_cost),
                number_format($pkg->paid_amount),
            ];
        }

        return [
            $pkg->Customer->name ?? '',
            $pkg->area->name ?? '',
            $pkg->product_type ?? '---',
            getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
            getReasonMessage($pkg->failure_reason) ?? '---',
            $pkg->pieces_count ?? '---',
            number_format($pkg->package_cost ?? 0),
            number_format($pkg->delivery_cost ?? 0),
            number_format($pkg->paid_amount ?? 0),
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
            'المبلغ المستحق',
            'أجور التوصيل',
            'المبلغ المستلم من العميل',
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