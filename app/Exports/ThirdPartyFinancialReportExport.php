<?php

namespace App\Exports;

use App\Models\Package;
use App\Models\ThirdPartyApplication;
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
        $thirdParty = ThirdPartyApplication::find($this->thirdPartyId);
        $discount = $thirdParty ? ($thirdParty->discount ?? 0) : 0;
        $cancellationFeePercentage = $thirdParty ? ($thirdParty->cancellation_fee_percentage ?? 25) : 25; // Default to 25% if not set

        $query = Package::where('third_party_application_id', $this->thirdPartyId)
            ->whereNotNull('third_party_application_id');
            // Show all packages, but calculate only for status NOT 5 and NOT 6

        // Filter by created_at OR delivery_date
        if ($this->dateFrom || $this->dateTo) {
            $query->where(function($q) {
                // Filter by created_at
                $q->where(function($subQ) {
                    if ($this->dateFrom) {
                        $subQ->whereDate('created_at', '>=', Carbon::parse($this->dateFrom)->format('Y-m-d'));
                    }
                    if ($this->dateTo) {
                        $subQ->whereDate('created_at', '<=', Carbon::parse($this->dateTo)->format('Y-m-d'));
                    }
                })->orWhere(function($subQ) {
                    // Filter by delivery_date
                    if ($this->dateFrom) {
                        $subQ->whereDate('delivery_date', '>=', Carbon::parse($this->dateFrom)->format('Y-m-d'));
                    }
                    if ($this->dateTo) {
                        $subQ->whereDate('delivery_date', '<=', Carbon::parse($this->dateTo)->format('Y-m-d'));
                    }
                });
            });
        }

        $packages = $query->with(['ThirdPartyApplication', 'Customer', 'Area'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate costs with business logic - only for packages NOT status 5 and NOT 6
        // Should receive from customer: package_cost + delivery_cost (when customer pays delivery)
        // Actually received from customer: paid_amount
        // Third party pays: seller_cost (seller_must_get)
        // Third party profit = paid_amount - seller_cost
        // Third party pays: delivery_cost_after_discount (to delivery service)
        // Net = profit - delivery_cost_after_discount
        
        $totalShouldReceive = 0; // package_cost + delivery_cost (what they should get)
        $totalActuallyReceived = 0; // paid_amount (what they actually got)
        $totalSellerCost = 0; // What third party pays to sellers
        $totalDeliveryCostBeforeDiscount = 0;
        $totalDeliveryCostAfterDiscount = 0;

        foreach ($packages as $package) {
            // Only calculate costs for packages that are NOT status 5 and NOT 6
            if (in_array($package->status, [5, 6])) {
                continue; // Skip calculation for status 5 and 6
            }

            // If status is 3 (cancelled) AND package_enter_Hub is 0, delivery company didn't take the order
            // So don't take money - set all amounts to 0
            if ($package->status == 3 && ($package->package_enter_Hub ?? 0) == 0) {
                continue; // Skip calculation - all amounts are 0
            }

            $packageCost = $package->package_cost ?? 0; // customer_must_pay
            $paidAmount = $package->paid_amount ?? 0; // what third party actually received
            $sellerCost = $package->seller_cost ?? 0; // seller_must_get (what third party pays)
            $deliveryCost = $package->delivery_cost ?? 0;
            $deliveryFeePayer = $package->delivery_fee_payer ?? 'customer';

            // What third party should receive: package_cost + delivery_cost (if customer pays delivery)
            $shouldReceive = $packageCost;
            if ($deliveryFeePayer == 'customer') {
                $shouldReceive += $deliveryCost;
            }

            // For cancelled packages (status 3), apply cancellation fee percentage to delivery_cost
            // But only if package_enter_Hub is not 0 (delivery company took the order)
            // For cancelled packages, do NOT apply discount percentage
            if ($package->status == 3 && ($package->package_enter_Hub ?? 0) != 0) {
                $deliveryCost = $deliveryCost * ($cancellationFeePercentage / 100);
                // For cancelled packages, don't apply discount - delivery_cost_after_discount = delivery_cost
                $deliveryCostAfterDiscount = $deliveryCost;
            } else {
                // Apply discount to delivery_cost (what third party pays to delivery service)
                $deliveryCostAfterDiscount = $deliveryCost * (1 - ($discount / 100));
            }

            $totalShouldReceive += $shouldReceive;
            $totalActuallyReceived += $paidAmount;
            $totalSellerCost += $sellerCost;
            $totalDeliveryCostBeforeDiscount += $deliveryCost;
            $totalDeliveryCostAfterDiscount += $deliveryCostAfterDiscount;
        }

        // Third party profit = paid_amount - seller_cost (using actual received amount)
        $totalProfit = $totalActuallyReceived - $totalSellerCost;
        // Net = profit - delivery_cost_after_discount
        $netAmount = $totalProfit - $totalDeliveryCostAfterDiscount;

        $packages->push((object)[
            'is_total_row' => true,
            'should_receive' => $totalShouldReceive,
            'actually_received' => $totalActuallyReceived,
            'seller_cost' => $totalSellerCost,
            'profit' => $totalProfit,
            'delivery_cost_before_discount' => $totalDeliveryCostBeforeDiscount,
            'delivery_cost_after_discount' => $totalDeliveryCostAfterDiscount,
            'discount_amount' => $totalDeliveryCostBeforeDiscount - $totalDeliveryCostAfterDiscount,
            'net_amount' => $netAmount,
            'packages_count' => $packages->count() - 1,
        ]);

        return $packages;
    }

    public function map($pkg): array
    {
        $thirdParty = ThirdPartyApplication::find($this->thirdPartyId);
        $discount = $thirdParty ? ($thirdParty->discount ?? 0) : 0;
        $cancellationFeePercentage = $thirdParty ? ($thirdParty->cancellation_fee_percentage ?? 25) : 25; // Default to 25% if not set

        if (isset($pkg->is_total_row)) {
            return [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                number_format($pkg->packages_count),
                number_format($pkg->seller_cost, 2),
                number_format($pkg->should_receive, 2),
                number_format($pkg->actually_received, 2),
                number_format($pkg->profit, 2),
                number_format($pkg->delivery_cost_before_discount, 2),
                number_format($pkg->delivery_cost_after_discount, 2),
                number_format($pkg->discount_amount, 2),
                number_format($pkg->net_amount, 2),
            ];
        }

        // For packages with status 5 or 6, show 0 in cost columns
        if (in_array($pkg->status, [5, 6])) {
            return [
                $pkg->id ?? '',
                $pkg->reference_number ?? '',
                $pkg->Customer ? $pkg->Customer->name : '',
                $pkg->Area ? $pkg->Area->name : '',
                $pkg->created_at ? Carbon::parse($pkg->created_at)->format('Y-m-d') : '',
                getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
                $pkg->pieces_count ?? 0,
                '0.00', // seller_cost = 0 for status 5 and 6
                '0.00', // should receive = 0
                '0.00', // actually received = 0
                '0.00', // profit = 0
                '0.00', // delivery_cost before discount = 0
                '0.00', // delivery_cost after discount = 0
                '0.00', // discount amount = 0
                '0.00', // net amount = 0
                $pkg->failure_reason ? getReasonMessage($pkg->failure_reason) : '', // failure reason
            ];
        }

        // If status is 3 (cancelled) AND package_enter_Hub is 0, delivery company didn't take the order
        // So don't take money - set all amounts to 0
        $isNotTakenByDelivery = ($pkg->status == 3 && ($pkg->package_enter_Hub ?? 0) == 0);

        // If status is 3 and package_enter_Hub is 0, set all amounts to 0
        if ($isNotTakenByDelivery) {
            return [
                $pkg->id ?? '',
                $pkg->reference_number ?? '',
                $pkg->Customer ? $pkg->Customer->name : '',
                $pkg->Area ? $pkg->Area->name : '',
                $pkg->created_at ? Carbon::parse($pkg->created_at)->format('Y-m-d') : '',
                getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
                $pkg->pieces_count ?? 0,
                '0.00', // seller_cost = 0
                '0.00', // should receive = 0
                '0.00', // actually received = 0
                '0.00', // profit = 0
                '0.00', // delivery_cost before discount = 0
                '0.00', // delivery_cost after discount = 0
                '0.00', // discount amount = 0
                '0.00', // net amount = 0
                $pkg->failure_reason ? getReasonMessage($pkg->failure_reason) : '', // failure reason
            ];
        }

        $packageCost = $pkg->package_cost ?? 0; // customer_must_pay
        $paidAmount = $pkg->paid_amount ?? 0; // what third party actually received
        $sellerCost = $pkg->seller_cost ?? 0; // seller_must_get (what third party pays)
        $deliveryCost = $pkg->delivery_cost ?? 0;
        $deliveryFeePayer = $pkg->delivery_fee_payer ?? 'customer';

        // What third party should receive: package_cost + delivery_cost (if customer pays delivery)
        $shouldReceive = $packageCost;
        if ($deliveryFeePayer == 'customer') {
            $shouldReceive += $deliveryCost;
        }

        // For cancelled packages (status 3), apply cancellation fee percentage to delivery_cost
        // But only if package_enter_Hub is not 0 (delivery company took the order)
        // For cancelled packages, do NOT apply discount percentage
        if ($pkg->status == 3 && ($pkg->package_enter_Hub ?? 0) != 0) {
            $deliveryCost = $deliveryCost * ($cancellationFeePercentage / 100);
            // For cancelled packages, don't apply discount - delivery_cost_after_discount = delivery_cost
            $deliveryCostAfterDiscount = $deliveryCost;
        } else {
            // Apply discount to delivery_cost (what third party pays to delivery service)
            $deliveryCostAfterDiscount = $deliveryCost * (1 - ($discount / 100));
        }

        // Third party profit = paid_amount - seller_cost (using actual received amount)
        $profit = $paidAmount - $sellerCost;
        $discountAmount = $deliveryCost - $deliveryCostAfterDiscount;
        
        // Net = profit - delivery_cost_after_discount
        $netAmount = $profit - $deliveryCostAfterDiscount;

        return [
            $pkg->id ?? '',
            $pkg->reference_number ?? '',
            $pkg->Customer ? $pkg->Customer->name : '',
            $pkg->Area ? $pkg->Area->name : '',
            $pkg->created_at ? Carbon::parse($pkg->created_at)->format('Y-m-d') : '',
            getPackageStatus($pkg->status, $pkg->delivery_date) ?? '---',
            $pkg->pieces_count ?? 0,
            number_format($sellerCost, 2), // What should pay to seller
            number_format($shouldReceive, 2), // package_cost + delivery_cost (what they should get)
            number_format($paidAmount, 2), // paid_amount (what they actually got)
            number_format($profit, 2), // profit = paid_amount - seller_cost
            number_format($deliveryCost, 2), // delivery_cost before discount
            number_format($deliveryCostAfterDiscount, 2), // delivery_cost after discount
            number_format($discountAmount, 2), // discount amount
            number_format($netAmount, 2), // net = profit - delivery_cost_after_discount
            $pkg->failure_reason ? getReasonMessage($pkg->failure_reason) : '', // failure reason
        ];
    }

    public function headings(): array
    {
        return [
            'رقم الشحنة',
            'رقم المرجع',
            'اسم العميل',
            'منطقة التوصيل',
            'تاريخ الإنشاء',
            'الحالة',
            'عدد القطع',
            'المبلغ المستحق دفعه للتاجر (seller_cost)',
            'المبلغ المستحق من العميل (package_cost + delivery_cost)',
            'المبلغ المستلم فعلياً من العميل (paid_amount)',
            'الربح (المستلم فعلياً - المدفوع للتاجر)',
            'تكلفة التوصيل قبل الخصم',
            'تكلفة التوصيل بعد الخصم',
            'مبلغ الخصم على التوصيل',
            'المبلغ الصافي للطرف الثالث (الربح - تكلفة التوصيل بعد الخصم)',
            'سبب الفشل',
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
                    $dateRange = 'تاريخ (الإنشاء أو التوصيل): ';
                    if ($this->dateFrom && $this->dateTo) {
                        $dateRange .= 'من ' . Carbon::parse($this->dateFrom)->format('Y-m-d') . ' إلى ' . Carbon::parse($this->dateTo)->format('Y-m-d');
                    } elseif ($this->dateFrom) {
                        $dateRange .= 'من ' . Carbon::parse($this->dateFrom)->format('Y-m-d');
                    } elseif ($this->dateTo) {
                        $dateRange .= 'حتى ' . Carbon::parse($this->dateTo)->format('Y-m-d');
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

