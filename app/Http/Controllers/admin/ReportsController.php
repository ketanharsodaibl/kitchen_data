<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\JobType;
date_default_timezone_set('UTC');
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportsController extends Controller
{
    public function index()
    {

        // $spreadsheet = new Spreadsheet();
        // $sheet = $spreadsheet->getActiveSheet();
        // $sheet->setCellValue('A1', 'Hello World !');

        // $writer = new Xlsx($spreadsheet);
        // $writer->save('hello world.xlsx');

        // $getJobDetails = DB::select("SELECT jb.job_id,jb.client_id,jb.job_title,jb.address_1,jb.address_2,jb.city,jb.state,jb.zipcode,jb.apartment_number,jb.super_name,jb.super_phone_number,jb.contractor_name,jb.contractor_phone_number,jb.contractor_email,jb.working_employee_id,jb.job_client_id,jb.plumbing_installation_date,jb.delivery_datetime,jb.installation_datetime,jb.installation_employee_id,jb.stone_installation_datetime,jb.stone_installation_employee_id,jb.start_date,jb.end_date,jbt.job_status_name
        // FROM jobs AS jb
        // JOIN job_types AS jbt ON jbt.job_status_id = jb.job_status_id
        // WHERE jb.job_status_id = 1");

        // $excelArray = [];
        // $excelArray[] = ['job_id', 'client_id', 'job_title', 'address_1', 'address_2', 'city', 'state', 'zipcode', 'apartment_number', 'super_name', 'super_phone_number', 'contractor_name', 'contractor_phone_number', 'contractor_email', 'working_employee_id', 'job_client_id', 'plumbing_installation_date', 'delivery_datetime', 'installation_datetime', 'installation_employee_id', 'stone_installation_datetime', 'stone_installation_employee_id', 'start_date', 'end_date', 'job_status_name'];

        /*$excelDetails = new Exports();
        $getDetail = $excelDetails->collection();
        echo '<pre>';
        $getDetail = json_decode(json_encode($getDetail), true);
        array_unshift($getDetail,array('job_id'=>'job_id','client_id'=>'client_id'));
        print_r($getDetail);
        die;*/
        //return Excel::download(new Exports, 'joblist.xlsx');

        // foreach ($getJobDetails as $job) {
        //     $excelArray[] = json_decode(json_encode($job), true);
        // }
        // return Excel::download(new InvoicesExport, 'invoices.xlsx');
        // return response()->download($getJobDetails, 'Kitchen_Jobs_' . date('Y_m_d') . '.xlsx');

        return view('admin.reports')->with('jobStatusList', JobType::all());
    }

    public function downloadJobExcel(Request $request)
    {
        $status_id = $requets->get('status_id');

        $getJobDetails = DB::select("SELECT jb.job_id,jb.client_id,jb.job_title,jb.address_1,jb.address_2,jb.city,jb.state,jb.zipcode,jb.apartment_number,jb.super_name,jb.super_phone_number,jb.contractor_name,jb.contractor_phone_number,jb.contractor_email,jb.working_employee_id,jb.job_client_id,jb.plumbing_installation_date,jb.delivery_datetime,jb.installation_datetime,jb.installation_employee_id,jb.stone_installation_datetime,jb.stone_installation_employee_id,jb.start_date,jb.end_date,jbt.job_status_name
        FROM jobs AS jb
        JOIN job_types AS jbt ON jbt.job_status_id = jb.job_status_id
        WHERE jb.job_status_id = '{$status_id}'");

        $path = public_path('/admin/csv/prospect.csv');
        return response()->download('Kitchen_Jobss_' . date('Y_m_d') . '.xlsx');
    }
}
