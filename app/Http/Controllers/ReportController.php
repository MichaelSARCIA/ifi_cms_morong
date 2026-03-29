<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\AuditLogger;

class ReportController extends Controller
{
    private function getReportData($startDate, $endDate, $category)
    {
        $data = compact('startDate', 'endDate', 'category');

        if ($category === 'services' || $category === 'all') {
            $query = \App\Models\ServiceRequest::query()
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
            $serviceType = request('service_type');
            if ($serviceType && $serviceType !== 'all') {
                $query->where('service_type', $serviceType);
            }

            $data['serviceRequestsList'] = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($req) {
                    $req->client_name = $req->applicant_name;
                    $req->request_date = $req->created_at;
                    return $req;
                });
        }

        if ($category === 'donations' || $category === 'all') {
            $data['donations'] = \DB::table('donations')
                ->whereIn('type', ['Donation', 'Tithes', 'Love Offering'])
                ->whereBetween('date_received', [$startDate, $endDate])
                ->orderBy('date_received', 'desc')
                ->get();
        }

        if ($category === 'collections' || $category === 'all') {
            $data['collections'] = \DB::table('donations')
                ->whereIn('type', ['Collection', 'Mass Offering'])
                ->whereBetween('date_received', [$startDate, $endDate])
                ->orderBy('date_received', 'desc')
                ->get();
        }

        if ($category === 'fees' || $category === 'all') {
            $query = \DB::table('payments')
                ->leftJoin('service_requests', 'payments.service_request_id', '=', 'service_requests.id')
                ->select(
                    'payments.payment_method',
                    'payments.amount as amount_paid',
                    'payments.paid_at',
                    'service_requests.service_type',
                    'service_requests.id as request_number',
                    \DB::raw("CONCAT(service_requests.first_name, ' ', service_requests.last_name) as payor_name")
                )
                ->whereBetween('payments.paid_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

            $serviceType = request('service_type');
            if ($serviceType && $serviceType !== 'all') {
                $query->where('service_requests.service_type', $serviceType);
            }

            $data['serviceFees'] = $query->orderBy('payments.paid_at', 'desc')->get();
        }

        if ($category === 'applicants') {
            $query = \App\Models\ServiceRequest::query()
                ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            
            $serviceType = request('service_type');
            if ($serviceType && $serviceType !== 'all') {
                $query->where('service_type', $serviceType);
            }
            
            $data['applicantList'] = $query->orderBy('created_at', 'desc')->get();
            $data['selectedServiceType'] = $serviceType;
        }

        return $data;
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $allCategories = [
            'services'    => ['module' => 'service_requests', 'label' => 'Services Availed'],
            'applicants'  => ['module' => 'service_requests', 'label' => 'List of Applicants'],
            'collections' => ['module' => 'collections',       'label' => 'Collections & Offerings'],
            'donations'   => ['module' => 'donations',         'label' => 'Donations & Tithes'],
            'fees'        => ['module' => 'services_fees',     'label' => 'Service Fees Paid'],
        ];

        $availableCategories = [];
        foreach ($allCategories as $key => $meta) {
            if ($user->hasModule($meta['module'])) {
                $availableCategories[$key] = $meta['label'];
            }
        }

        if (empty($availableCategories)) {
            abort(403, 'You do not have access to any report categories.');
        }

        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-12-31'));
        $category = $request->input('category', array_key_first($availableCategories));

        // Security: force to first available if the requested category isn't allowed
        if (!array_key_exists($category, $availableCategories)) {
            $category = array_key_first($availableCategories);
        }

        $data = $this->getReportData($startDate, $endDate, $category);

        $settings = SystemSetting::pluck('value', 'key')->toArray();
        $data['churchName'] = $settings['church_name'] ?? 'Iglesia Filipina Independiente';
        $data['parishName'] = $settings['parish_name'] ?? 'Parish';
        $data['address'] = $settings['church_address'] ?? '';
        $data['contact'] = $settings['church_contact'] ?? '';
        $data['parishEmail'] = $settings['parish_email'] ?? 'sangeronimo.ifi@gmail.com';
        $data['dioceseName'] = $settings['diocese_name'] ?? 'Diocese of Rizal and Pampanga';
        $data['availableCategories'] = $availableCategories;
        $data['serviceTypes'] = \App\Models\ServiceType::orderBy('name')->pluck('name')->toArray();

        return view('modules.reports.index', $data);
    }

    public function export(Request $request)
    {
        $user = auth()->user();

        $allCategories = [
            'services'    => 'service_requests',
            'collections' => 'collections',
            'donations'   => 'donations',
            'fees'        => 'services_fees',
        ];

        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-12-31'));
        $category = $request->input('category', 'services');

        // Block access to unauthorized categories
        if (!isset($allCategories[$category]) || !$user->hasModule($allCategories[$category])) {
            abort(403, 'Access to this report category is not permitted.');
        }

        $data = $this->getReportData($startDate, $endDate, $category);

        $settings = SystemSetting::pluck('value', 'key')->toArray();
        $churchName = $settings['church_name'] ?? 'Iglesia Filipina Independiente';
        $parishName = $settings['parish_name'] ?? 'Parish';
        $address = $settings['church_address'] ?? '';
        $contact = $settings['church_contact'] ?? '';
        $parishEmail = $settings['parish_email'] ?? '';

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(10);
        $section = $phpWord->addSection([
            'paperSize' => 'A4',
            'marginTop' => 700,
            'marginBottom' => 700,
            'marginLeft' => 700,
            'marginRight' => 700,
        ]);

        $fontHeader = ['bold' => true, 'size' => 14];
        $fontTitle = ['bold' => true, 'size' => 12];
        $fontNormal = ['size' => 10];
        $paraCenter = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $paraLeft = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::START];
        
        $listFontTitle = ['bold' => true, 'size' => 11];
        $listFontValue = ['size' => 11];
        $listFontLabel = ['italic' => true, 'color' => '555555', 'size' => 11];

        // Header Table
        $headerTable = $section->addTable(['width' => 100 * 50, 'unit' => 'pct', 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $headerTable->addRow();
        
        // Logo
        $logoCell = $headerTable->addCell(2000, ['valign' => 'center']);
        $logoPath = public_path('assets/img/logo.png');
        if (file_exists($logoPath)) {
            $logoCell->addImage($logoPath, [
                'width' => 75,
                'height' => 75,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER
            ]);
        }

        // Text
        $textCell = $headerTable->addCell(8000, ['valign' => 'center']);
        $textCell->addText($churchName, $fontHeader, $paraCenter);
        if ($parishName)
            $textCell->addText($parishName, $fontTitle, $paraCenter);
        if ($address)
            $textCell->addText($address, $fontNormal, $paraCenter);
        if ($contact)
            $textCell->addText("Contact: " . $contact, $fontNormal, $paraCenter);
        if ($parishEmail)
            $textCell->addText("Email: " . $parishEmail, $fontNormal, $paraCenter);

        $section->addTextBreak(1);
        // Horizontal Line
        $section->addLine(['weight' => 1, 'width' => 450, 'height' => 0, 'color' => '000000']);
        $section->addTextBreak(1);

        $categoryNames = [
            'services' => 'Services Availed Report',
            'collections' => 'Collections & Mass Offerings Report',
            'donations' => 'Donations & Tithes Report',
            'fees' => 'Service Fees Processed Report',
        ];

        $reportTitle = $categoryNames[$category] ?? 'System Generated Report';
        $section->addText($reportTitle, ['bold' => true, 'size' => 12], $paraCenter);
        $section->addText("Period: " . date('F d, Y', strtotime($startDate)) . " to " . date('F d, Y', strtotime($endDate)), $fontNormal, $paraCenter);
        $section->addTextBreak(1);

        $phpWord->addTableStyle(
            'BorderlessTable',
            [
                'borderSize' => 0, 
                'borderColor' => 'FFFFFF', // Explicitly white/invisible
                'cellMargin' => 50
            ],
            ['borderBottomSize' => 1, 'borderBottomColor' => 'cccccc']
        );

        if ($category === 'services') {
            if (count($data['serviceRequestsList']) > 0) {
                $table = $section->addTable('BorderlessTable');
                $table->addRow();
                $table->addCell(500)->addText("#", ['bold' => true, 'size' => 11]);
                $table->addCell(2500)->addText("Date", ['bold' => true, 'size' => 11]);
                $table->addCell(3000)->addText("Service Type", ['bold' => true, 'size' => 11]);
                $table->addCell(4000)->addText("Requested By", ['bold' => true, 'size' => 11]);
                
                $index = 1;
                foreach ($data['serviceRequestsList'] as $req) {
                    $table->addRow();
                    $table->addCell(500)->addText($index . ".", ['bold' => true, 'size' => 11]);
                    $table->addCell(2500)->addText(date('F d, Y', strtotime($req->request_date)), ['size' => 11]);
                    $table->addCell(3000)->addText($req->service_type, ['size' => 11]);
                    $table->addCell(4000)->addText($req->client_name, ['bold' => true, 'size' => 11]);
                    $index++;
                }
            } else {
                $section->addText("No services requested in this period.", ['italic' => true], $paraCenter);
            }
        }

        if ($category === 'collections') {
            if (count($data['collections']) > 0) {
                $table = $section->addTable('BorderlessTable');
                $table->addRow();
                $table->addCell(500)->addText("No.", ['bold' => true, 'size' => 11]);
                $table->addCell(2500)->addText("Date", ['bold' => true, 'size' => 11]);
                $table->addCell(4000)->addText("Source/Event", ['bold' => true, 'size' => 11]);
                $table->addCell(3000)->addText("Amount (PHP)", ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
                
                $index = 1;
                foreach ($data['collections'] as $col) {
                    $table->addRow();
                    $table->addCell(500)->addText($index . ".", ['bold' => true, 'size' => 11]);
                    $table->addCell(2500)->addText(date('F d, Y', strtotime($col->date_received)), ['size' => 11]);
                    $table->addCell(4000)->addText($col->donor_name ?? 'N/A', ['bold' => true, 'size' => 11]);
                    $table->addCell(3000)->addText("PHP " . number_format($col->amount, 2), ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
                    $index++;
                }
            } else {
                $section->addText("No collections recorded in this period.", ['italic' => true], $paraCenter);
            }
        }

        if ($category === 'donations') {
            if (count($data['donations']) > 0) {
                $table = $section->addTable('BorderlessTable');
                $table->addRow();
                $table->addCell(500)->addText("No.", ['bold' => true, 'size' => 11]);
                $table->addCell(2500)->addText("Date", ['bold' => true, 'size' => 11]);
                $table->addCell(4000)->addText("Donor Name", ['bold' => true, 'size' => 11]);
                $table->addCell(3000)->addText("Amount (PHP)", ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
                
                $index = 1;
                foreach ($data['donations'] as $don) {
                    $table->addRow();
                    $table->addCell(500)->addText($index . ".", ['bold' => true, 'size' => 11]);
                    $table->addCell(2500)->addText(date('F d, Y', strtotime($don->date_received)), ['size' => 11]);
                    $table->addCell(4000)->addText($don->donor_name ?? 'Anonymous', ['bold' => true, 'size' => 11]);
                    $table->addCell(3000)->addText("PHP " . number_format($don->amount, 2), ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
                    $index++;
                }
            } else {
                $section->addText("No donations received in this period.", ['italic' => true], $paraCenter);
            }
        }

        if ($category === 'fees') {
            if (count($data['serviceFees']) > 0) {
                $table = $section->addTable('BorderlessTable');
                $table->addRow();
                $table->addCell(500)->addText("No.", ['bold' => true, 'size' => 11]);
                $table->addCell(2000)->addText("Date Paid", ['bold' => true, 'size' => 11]);
                $table->addCell(3000)->addText("Service Type", ['bold' => true, 'size' => 11]);
                $table->addCell(2500)->addText("Payor", ['bold' => true, 'size' => 11]);
                $table->addCell(2000)->addText("Amount (PHP)", ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
                
                $index = 1;
                foreach ($data['serviceFees'] as $fee) {
                    $table->addRow();
                    $table->addCell(500)->addText($index . ".", ['bold' => true, 'size' => 11]);
                    $table->addCell(2000)->addText(date('F d, Y', strtotime($fee->paid_at)), ['size' => 11]);
                    $table->addCell(3000)->addText($fee->service_type, ['size' => 11]);
                    $table->addCell(2500)->addText($fee->payor_name ?? 'N/A', ['bold' => true, 'size' => 11]);
                    $table->addCell(2000)->addText("PHP " . number_format($fee->amount_paid, 2), ['size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END]);
                    $index++;
                }
            } else {
                $section->addText("No service fees processed in this period.", ['italic' => true], $paraCenter);
            }
        }


        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $fileName = ucfirst($category) . '_Report_' . date('Y_m_d_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');
        $objWriter->save($tempFile);

        AuditLogger::log('Generate Report', "Exported " . ucfirst($category) . " report (Word).");

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $user = auth()->user();

        $allCategories = [
            'services'    => 'service_requests',
            'applicants'  => 'service_requests',
            'collections' => 'collections',
            'donations'   => 'donations',
            'fees'        => 'services_fees',
        ];

        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-12-31'));
        $category = $request->input('category', 'services');

        // Block access to unauthorized categories
        if (!isset($allCategories[$category]) || !$user->hasModule($allCategories[$category])) {
            abort(403, 'Access to this report category is not permitted.');
        }

        $data = $this->getReportData($startDate, $endDate, $category);

        $settings = SystemSetting::pluck('value', 'key')->toArray();
        $data['churchName'] = $settings['church_name'] ?? 'Iglesia Filipina Independiente';
        $data['parishName'] = $settings['parish_name'] ?? 'Parish';
        $data['address'] = $settings['church_address'] ?? '';
        $data['contact'] = $settings['church_contact'] ?? '';
        $data['parishEmail'] = $settings['parish_email'] ?? 'sangeronimo.ifi@gmail.com';
        $data['dioceseName'] = $settings['diocese_name'] ?? 'Diocese of Rizal and Pampanga';
        $data['logo'] = $settings['church_logo'] ?? null;
        
        $categoryNames = [
            'services' => 'Services Availed Report',
            'applicants' => 'List of Applicants',
            'collections' => 'Collections & Mass Offerings Report',
            'donations' => 'Donations & Tithes Report',
            'fees' => 'Service Fees Processed Report',
        ];
        $reportTitle = $categoryNames[$category] ?? 'System Generated Report';
        
        $serviceType = $request->input('service_type');
        if ($serviceType && $serviceType !== 'all') {
            $reportTitle .= ' (' . $serviceType . ')';
        }
        $data['reportTitle'] = $reportTitle;
        $data['isExport'] = true; // Flag to change view layout for PDF

        $pdf = Pdf::loadView('modules.reports.pdf', $data);
        if ($category === 'applicants') {
            $pdf->setPaper('A4', 'landscape');
        } else {
            $pdf->setPaper('A4', 'portrait');
        }

        $fileName = ucfirst($category) . '_Report_' . date('Y_m_d_His') . '.pdf';

        AuditLogger::log('Generate Report', "Exported " . ucfirst($category) . " report (PDF).");

        return $pdf->download($fileName);
    }
}
