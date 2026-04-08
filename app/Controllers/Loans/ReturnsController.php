<?php

namespace App\Controllers\Loans;

use App\Libraries\QRGenerator;
use App\Models\BookModel;
use App\Models\FineModel;
use App\Models\FinesPerDayModel;
use App\Models\LoanModel;
use App\Models\MemberModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\RESTful\ResourceController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;


class ReturnsController extends ResourceController
{
    protected LoanModel $loanModel;
    protected FineModel $fineModel;
    protected MemberModel $memberModel;
    protected BookModel $bookModel;

    public function __construct()
    {
        $this->loanModel   = new LoanModel();
        $this->fineModel   = new FineModel();
        $this->memberModel = new MemberModel();
        $this->bookModel   = new BookModel();

        helper('upload');
    }

    public function index()
    {
        $itemPerPage = 20;
        $builder = $this->loanModel
            ->select('
                loans.id,
                loans.uid,
                loans.quantity,
                loans.loan_date,
                loans.due_date,
                loans.return_date,
                members.first_name,
                members.last_name,
                books.title
            ')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('books', 'loans.book_id = books.id', 'LEFT')
            ->where('loans.return_date IS NOT NULL')
            ->where('loans.deleted_at', null);
    
        // Jika ada pencarian
        if ($this->request->getGet('search')) {
            $keyword = $this->request->getGet('search');
            $builder->groupStart()
                ->like('members.first_name', $keyword)
                ->orLike('members.last_name', $keyword)
                ->orLike('books.title', $keyword)
            ->groupEnd();
        }
    
        $loans = $builder->paginate($itemPerPage, 'returns');
    
        $data = [
            'loans'       => $loans,
            'pager'       => $this->loanModel->pager,
            'currentPage' => $this->request->getVar('page_returns') ?? 1,
            'itemPerPage' => $itemPerPage,
            'search'      => $this->request->getGet('search') ?? ''
        ];
    
        return view('returns/index', $data);
    }
    

    /**
     * show detail (only for returned loans).
     * If not found, redirect back with flash message (no 404 thrown here).
     */
    public function show($uid = null)
{
    $loan = $this->loanModel
        ->select('
            loans.*,
            members.first_name,
            members.last_name,
            members.email,
            members.address,
            books.title as book_title,
            books.author,
            books.publisher,
            racks.name as rack_name
        ')
        ->join('members', 'members.id = loans.member_id')
        ->join('books', 'books.id = loans.book_id')
        ->join('racks', 'racks.id = books.rack_id', 'LEFT')
        ->where('loans.uid', $uid)
        ->where('loans.return_date IS NOT NULL')
        ->first();

    if (empty($loan)) {
        return redirect()->to('admin/returns')->with('msg', 'Loan not found or not returned yet.');
    }

    // Jika ingin generate QR code otomatis saat belum ada
    if (empty($loan['qr_code']) && $this->request->getGet('update-qr-code')) {
        $qrGenerator = new \App\Libraries\QRGenerator();
        $qrCodeLabel = substr($loan['first_name'] . '_' . $loan['last_name'], 0, 12) . '_' . substr($loan['book_title'], 0, 12);
        $qrCode = $qrGenerator->generateQRCode(
            data: $loan['uid'],
            labelText: $qrCodeLabel,
            dir: LOANS_QR_CODE_PATH,
            filename: $qrCodeLabel
        );
        $this->loanModel->update($loan['id'], ['qr_code' => $qrCode]);
        $loan['qr_code'] = $qrCode; // update array untuk view
    }

    $data = [
        'title' => 'Detail Pengembalian',
        'loan'  => $loan
    ];

    return view('returns/show', $data);
}

    
    

    /**
     * AJAX search for loan to return (keeps current behavior)
     */
    public function searchLoan()
    {
        if (! $this->request->isAJAX()) {
            return view('returns/search_loan');
        }

        $param = $this->request->getVar('param');
        if (empty($param)) return;

        $loans = $this->loanModel
            ->select('members.*, books.*, loans.*')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('books', 'loans.book_id = books.id', 'LEFT')
            ->groupStart()
                ->like('first_name', $param, insensitiveSearch: true)
                ->orLike('last_name', $param, insensitiveSearch: true)
                ->orLike('email', $param, insensitiveSearch: true)
                ->orLike('title', $param, insensitiveSearch: true)
                ->orLike('author', $param, insensitiveSearch: true)
                ->orLike('publisher', $param, insensitiveSearch: true)
            ->groupEnd()
            ->orWhere('loans.uid', $param)
            ->orWhere('members.uid', $param)
            ->findAll();

        $loans = array_values(array_filter((array)$loans, fn($loan) => ($loan['deleted_at'] == null && $loan['return_date'] == null)));

        if (empty($loans)) {
            return view('returns/loan', ['msg' => 'Loan not found']);
        }

        return view('returns/loan', ['loans' => $loans]);
    }

    /**
     * show "create return" page (expects POST from search result with loan-uid)
     * Note: make sure route $routes->post('returns/new', 'Loans\ReturnsController::new')
     */
    public function new()
    {
        $loanUid = $this->request->getVar('loan-uid');

        if (empty($loanUid)) {
            session()->setFlashdata(['msg' => 'Select loan first', 'error' => true]);
            return redirect()->to('admin/returns/new/search');
        }

        $loans = $this->loanModel
            ->select('members.*, books.*, categories.name as category, loans.*')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('books', 'loans.book_id = books.id', 'LEFT')
            ->join('categories', 'books.category_id = categories.id', 'LEFT')
            ->where('loans.uid', $loanUid)
            ->findAll();

        $loan = array_values(array_filter((array)$loans, fn($l) => ($l['deleted_at'] == null && $l['return_date'] == null)));

        if (empty($loan)) {
            session()->setFlashdata(['msg' => 'Loan not found or already returned', 'error' => true]);
            return redirect()->to('admin/returns/new/search');
        }

        $data = [
            'loan'       => $loan[array_key_first($loan)],
            'validation' => \Config\Services::validation()
        ];

        return view('returns/create', $data);
    }

    /**
     * process return (expects loan_uid in POST)
     */
    public function create()
    {
        $date = Time::parse($this->request->getVar('date') ?? 'now', locale: 'id');
        $loanUid = $this->request->getVar('loan_uid');

        if (empty($loanUid)) {
            return redirect()->to('admin/returns')->with('msg', 'Invalid request');
        }

        $loan = $this->loanModel->where('uid', $loanUid)->first();

        if (empty($loan)) {
            return redirect()->to('admin/returns')->with('msg', 'Loan not found');
        }

        $loanDueDate = Time::parse($loan['due_date'], locale: 'id');
        $isLate = $date->isAfter($loanDueDate);

        if ($isLate) {
            $this->loanModel->update($loan['id'], ['return_date' => $date->toDateTimeString()]);
            $finePerDay = FinesPerDayModel::getAmount();
            $daysLate = $date->today()->difference($loanDueDate)->getDays();
            $totalFine = abs($daysLate) * $loan['quantity'] * $finePerDay;
            $this->fineModel->save(['loan_id' => $loan['id'], 'fine_amount' => $totalFine]);
        } else {
            deleteLoansQRCode($loan['qr_code']);
            $this->loanModel->update($loan['id'], ['return_date' => $date->toDateTimeString(), 'qr_code' => null]);
        }

        session()->setFlashdata(['msg' => 'Success', 'error' => false]);
        return redirect()->to('admin/returns');
    }

    /**
     * revert return (mark as not returned)
     */
    public function delete($uid = null)
    {
        // existing logic kept but make it safe: if not found, redirect
        $loans = $this->loanModel
            ->select('members.*, books.*, loans.*')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('books', 'loans.book_id = books.id', 'LEFT')
            ->where('loans.uid', $uid)
            ->findAll();

        $loans = array_values(array_filter((array)$loans, fn($loan) => ($loan['deleted_at'] == null && $loan['return_date'] != null)));

        if (empty($loans)) {
            session()->setFlashdata(['msg' => 'Loan not found or not returned', 'error' => true]);
            return redirect()->to('admin/returns');
        }

        $loan = $loans[0];

        $qrGenerator = new QRGenerator();

        $qrCodeLabel = substr($loan['first_name'] . ($loan['last_name'] ? " {$loan['last_name']}" : ''), 0, 12) . '_' . substr($loan['title'], 0, 12);

        $qrCode = $qrGenerator->generateQRCode(
            data: $loan['uid'],
            labelText: $qrCodeLabel,
            dir: LOANS_QR_CODE_PATH,
            filename: $qrCodeLabel
        );

        if (! $this->loanModel->update($loan['id'], ['return_date' => null, 'qr_code' => $qrCode])) {
            deleteLoansQRCode($qrCode);
            session()->setFlashdata(['msg' => 'Update failed', 'error' => true]);
            return redirect()->to('admin/returns/' . $loan['uid']);
        }

        $isLate = Time::parse($loan['return_date'])->isAfter(Time::parse($loan['due_date']));

        if ($isLate) {
            $fine = $this->fineModel->where('loan_id', $loan['id'])->first();
            if (!empty($fine)) $this->fineModel->delete($fine['id']);
        }

        session()->setFlashdata(['msg' => 'Success', 'error' => false]);
        return redirect()->to('admin/returns');
    }

    /**
     * Export returned loans -> Excel
     */
    public function exportExcel()
    {
        $loans = $this->loanModel
            ->select('members.first_name, members.last_name, books.title, loans.quantity, loans.loan_date, loans.due_date, loans.return_date')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('books', 'loans.book_id = books.id', 'LEFT')
            ->where('loans.return_date IS NOT NULL')
            ->findAll();
    
        if (empty($loans)) {
            return redirect()->to('admin/returns')->with('msg', 'Belum ada data pengembalian untuk diexport.');
        }
    
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama Peminjam');
        $sheet->setCellValue('C1', 'Judul Buku');
        $sheet->setCellValue('D1', 'Jumlah');
        $sheet->setCellValue('E1', 'Tanggal Pinjam');
        $sheet->setCellValue('F1', 'Tenggat');
        $sheet->setCellValue('G1', 'Tanggal Pengembalian');
    
        $row = 2;
        $no = 1;
        foreach ($loans as $loan) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, "{$loan['first_name']} {$loan['last_name']}"); // gabung nama lengkap
            $sheet->setCellValue('C' . $row, $loan['title']);
            $sheet->setCellValue('D' . $row, $loan['quantity']);
            $sheet->setCellValue('E' . $row, $loan['loan_date']);
            $sheet->setCellValue('F' . $row, $loan['due_date']);
            $sheet->setCellValue('G' . $row, $loan['return_date']);
            $row++;
        }
    
        $writer = new Xlsx($spreadsheet);
        $filename = 'data_pengembalian_' . date('Ymd_His') . '.xlsx';
    
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');
    
        $writer->save('php://output');
        exit;
    }



    public function exportPdf()
    {
        $loans = $this->loanModel
            ->select('members.first_name, members.last_name, books.title, loans.quantity, loans.loan_date, loans.due_date, loans.return_date')
            ->join('members', 'loans.member_id = members.id', 'LEFT')
            ->join('books', 'loans.book_id = books.id', 'LEFT')
            ->where('loans.return_date IS NOT NULL')
            ->findAll();
    
        if (empty($loans)) {
            return redirect()->to('admin/returns')->with('msg', 'Belum ada data pengembalian untuk diexport.');
        }
    
        // Buat field member_name untuk view
        foreach ($loans as &$loan) {
            $loan['member_name'] = "{$loan['first_name']} {$loan['last_name']}";
        }
    
        $html = view('returns/pdf_template', ['loans' => $loans]);
    
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
    
        $dompdf->stream('data_pengembalian_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
        exit;
    }
    
}
