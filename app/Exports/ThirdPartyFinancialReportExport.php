<?php

namespace App\Exports;

use App\Models\Package;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ThirdPartyFinancialReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $thirdPartyId;
    protected $dateFrom;
    protected $dateTo;
    protected $thirdPartyName;

    public function __construct($thirdPartyId, $dateFrom, $dateTo, $thirdPartyName)
    {
        $this->thirdPartyId = $thirdPartyId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->thirdPartyName = $thirdPartyName;
    }

    public function collection()
    {
        $query = Package::where('third_party_application_id', $this->thirdPartyId)
            ->whereNotNull('third_party_application_id')
            ->whereNotNull('receipt_date') // Only packages that have been received/delivered
            ->with(['ThirdPartyApplication', 'Customer', 'Area']);

        // Filter by receipt date (actual delivery date)
        if ($this->dateFrom) {
            $query->whereDate('receipt_date', '>=', Carbon::parse($this->dateFrom)->format('Y-m-d'));
        }
        if ($this->dateTo) {
            $query->whereDate('receipt_date', '<=', Carbon::parse($this->dateTo)->format('Y-m-d'));
        }

        $packages = $query->orderBy('receipt_date', 'desc')->get();

        $totalSellerCost = $packages->sum('seller_cost') ?? 0;
        $totalDeliveryCost = $packages->sum('delivery_cost') ?? 0;
        $netAmount = $totalDeliveryCost - $totalSellerCost;

        $packages->push((object)[
            'is_total_row' => true,
            'seller_cost' => $totalSellerCost,
            'delivery_cost' => $totalDeliveryCost,
            'net_amount' => $netAmount,
            'packages_count' => $packages->count() - 1,
        ]);

        return $packages;
    }

    public function map($pkg): array
    {
        if (isset($pkg->is_total_row)) {
            return [
                'المجموع الكلي',
                '',
                '',
                '',
                '',
                number_format($pkg->packages_count),
                number_format($pkg->seller_cost, 2),
                number_format($pkg->delivery_cost, 2),
                number_format($pkg->net_amount, 2),
            ];
        }

        $sellerCost = $pkg->seller_cost ?? 0;
        $deliveryCost = $pkg->delivery_cost ?? 0;
        $netAmount = $deliveryCost - $sellerCost;

        return [
            $pkg->reference_number ?? '',
            $pkg->Customer->name ?? '',
            $pkg->Area ? $pkg->Area->name : '',
            $pkg->receipt_date ? Carbon::parse($pkg->receipt_date)->format('Y-m-d') : '',
            getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
            $pkg->pieces_count ?? 0,
            number_format($sellerCost, 2),
            number_format($deliveryCost, 2),
            number_format($netAmount, 2),
        ];
    }

    public function headings(): array
    {
        return [
            'رقم المرجع',
            'اسم العميل',
            'منطقة التوصيل',
            'تاريخ الاستلام',
            'الحالة',
            'عدد القطع',
            'المبلغ المستحق للطرف الثالث (seller_cost)',
            'تكلفة التوصيل (delivery_cost)',
            'المبلغ الصافي',
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
                
                // Add title row
                $sheet->insertNewRowBefore(1, 2);
                $sheet->setCellValue('A1', 'تقرير مالي للطرف الثالث: ' . $this->thirdPartyName);
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                if ($this->dateFrom || $this->dateTo) {
                    $dateRange = '';
                    if ($this->dateFrom && $this->dateTo) {
                        $dateRange = 'من ' . Carbon::parse($this->dateFrom)->format('Y-m-d') . ' إلى ' . Carbon::parse($this->dateTo)->format('Y-m-d');
                    } elseif ($this->dateFrom) {
                        $dateRange = 'من ' . Carbon::parse($this->dateFrom)->format('Y-m-d');
                    } elseif ($this->dateTo) {
                        $dateRange = 'حتى ' . Carbon::parse($this->dateTo)->format('Y-m-d');
                    }
                    $sheet->setCellValue('A2', $dateRange);
                    $sheet->mergeCells('A2:' . $highestColumn . '2');
                    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                // Adjust headings row
                $headingsRow = $this->dateFrom || $this->dateTo ? 3 : 2;
                $sheet->getStyle('A' . $headingsRow . ':' . $highestColumn . $headingsRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                
                $sheet->getStyle('A1:' . $highestColumn . ($highestRow + 2))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                $sheet->getStyle('A' . ($highestRow + 2) . ':' . $highestColumn . ($highestRow + 2))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }
}

