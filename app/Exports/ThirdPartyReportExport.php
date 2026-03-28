<?php

namespace App\Exports;

use App\Models\Package;
use App\Models\ThirdPartyApplication;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ThirdPartyReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $thirdPartyId;
    protected $thirdPartyName;

    public function __construct($thirdPartyId, $thirdPartyName)
    {
        $this->thirdPartyId = $thirdPartyId;
        $this->thirdPartyName = $thirdPartyName;
    }

    public function collection()
    {
        $today = today()->toDateString();

        $packages = Package::where('third_party_application_id', $this->thirdPartyId)
            ->where(function ($query) use ($today) {
                $query->whereDate('delivery_date', $today)
                    ->orWhereDate('delivery_date_1', $today)
                    ->orWhereDate('delivery_date_2', $today)
                    ->orWhereDate('delivery_date_3', $today);
            })
            ->with(['Customer', 'ThirdPartyApplication', 'area'])
            ->get();

        $total_paid_amount = 0;
        $total_delivery_cost = 0;
        $total_shipments_cost = 0;
        $total = 0;

        foreach ($packages as $package) {
            $total_paid_amount += $package->paid_amount ?? 0;

            if ($package->status == 1 || $package->status == 3) {
                $total_delivery_cost += $package->delivery_cost ?? 0;

                $seller_cost = $package->status == 1 ? ($package->seller_cost ?? 0) : 0;

                $current_total = ($package->paid_amount ?? 0)
                    - (($package->delivery_cost ?? 0) + $seller_cost + ($package->cost_of_shipments ?? 0));

                $total += $current_total;
            }

            $total_shipments_cost += $package->cost_of_shipments ?? 0;
        }

        // صف المجموع
        $packages->push((object)[
            'is_total_row' => true,
            'total_paid_amount' => $total_paid_amount,
            'total_delivery_cost' => $total_delivery_cost,
            'total_shipments_cost' => $total_shipments_cost,
            'total' => $total,
        ]);

        return $packages;
    }

    public function map($pkg): array
    {
        // صف المجموع
        if (isset($pkg->is_total_row)) {
            return [
                'المجموع',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                number_format($pkg->total_paid_amount),
                number_format($pkg->total_delivery_cost),
                number_format($pkg->total_shipments_cost),
                number_format($pkg->total),
            ];
        }

        $deliveryCost = '';
        $current_total = '';

        if ($pkg->status == 1 || $pkg->status == 3) {
            $deliveryCost = number_format($pkg->delivery_cost ?? 0);

            $seller_cost = $pkg->status == 1 ? ($pkg->seller_cost ?? 0) : 0;

            $current_total = number_format(
                ($pkg->paid_amount ?? 0)
                    - (($pkg->delivery_cost ?? 0) + $seller_cost + ($pkg->cost_of_shipments ?? 0))
            );
        }

        return [
            $pkg->Customer->name ?? '',
            $pkg->reference_number ?? '',
            $pkg->area->name ?? '',
            $pkg->shipments_company_name ?? '',
            getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
            $pkg->status != 1 ? getReasonMessage($pkg->failure_reason) : '---',
            number_format($pkg->seller_cost ?? 0),
            number_format($pkg->package_cost ?? 0),
            number_format($pkg->paid_amount ?? 0),
            $deliveryCost,
            number_format($pkg->cost_of_shipments ?? 0),
            $current_total,
        ];
    }

    public function headings(): array
    {
        return [
            'اسم الزبون',
            'رقم الطلب',
            'منطقة التوصيل',
            'شركة الشحن',
            'الحالة',
            'السبب',
            'الدفع للتاجر',
            'سعر الشحنة',
            'المبلغ المستلم من العميل',
            'أجور التوصيل',
            'أجور الشحن',
            'المبلغ المستحق'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
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
