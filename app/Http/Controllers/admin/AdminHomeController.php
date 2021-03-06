<?php

namespace App\Http\Controllers\admin;
date_default_timezone_set('UTC');
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use URL;
use Hash;
use Session;
use Mail;
use Validator;
use App\Admin;
use App\JobType;
use App\Job;
use App\Client;

class AdminHomeController extends Controller
{
	/*show login*/
	public function showLogin()
	{
		return view('admin.login');
	}

	/*do login*/
	public function doLogin(Request $request) {
		$email = $request->input('admin_email');
		$password = $request->input('admin_password');
		$checkLogin = Admin::where('email',$email)->where('is_deleted',0)->whereIn('login_type_id', [1, 2, 9, 10])->first();
		if(!empty($checkLogin)) {
			if($checkLogin->password == md5($password) || Hash::check($password, $checkLogin->password)) {
				Session::put('employee_id', $checkLogin->id);
				Session::put('name',$checkLogin->first_name.' '.$checkLogin->last_name);
				Session::put('email',$checkLogin->email);
				Session::put('login_type_id',$checkLogin->login_type_id);
				if($checkLogin->login_type_id == 9){
					$clientDetail = Client::selectRaw('note_status')->where('client_id', $checkLogin->id)->first();
					Session::put('job_notes_status', $clientDetail->note_status);
				}
				return redirect()->route('dashboard');
			}else {
				Session::flash('invalid', 'Invalid email or password combination. Please try again.');
				return back();
			}

		}else {
			Session::flash('invalid', 'Invalid email or password combination. Please try again.');
			return back();
		}
	}

	/*showdashboard*/
	public function showDashboard(){
		$getJobTypeDetail = JobType::selectRaw('job_status_id,job_status_name')->whereNotIn('job_status_id', [10, 11, 12])->get();
		$stoneEmployeeList = DB::select("SELECT id,UPPER(CONCAT(first_name,' ',last_name)) AS employee_name FROM admin_users WHERE is_deleted = 0 AND login_type_id = 6");
		$installEmployeeList = DB::select("SELECT id,UPPER(CONCAT(first_name,' ',last_name)) AS employee_name FROM admin_users WHERE is_deleted = 0 AND login_type_id = 5");

		return view('admin.dashboard')->with('jobTypeDetails',$getJobTypeDetail)->with('stoneEmployeeList', $stoneEmployeeList)->with('installEmployeeList', $installEmployeeList);
	}

	public function logout()
	{
		Session::flush();
		/*Session::put('email','');*/
		return redirect()->route('login');
	}

	public function showForgotPassword(){
		return view('admin.forgot_password');
	}

	public function sendForgotPasswordEmail(Request $request){
		$email = $request->input('txtemail');
		$checkEmail = Admin::where('email',$email)->first();
		if(!empty($checkEmail)){
			$temporaryPwd = str_random(8);
			Admin::where('email',$email)->update(['password'=>Hash::make($temporaryPwd)]);

			try{
				Mail::send('emails.AdminPanel_ForgotPassword',array(
					'temp_password' => $temporaryPwd
					), function($message)use($email){
					$message->from(env('FromMail','askitchen18@gmail.com'),'A&S KITCHEN');
					$message->to($email)->subject('A&S KITCHEN | Forgot Password');
				});
			} catch (\Exception $e){
				Session::flash('invalidMail', 'Something went wrong. Please try again.');
				return back();
			}
			Session::flash('validMail', 'An email containing your temporary login password has been sent to your verified email address. You can change your password from your profile.');
			return back();
		}
	}

	public function sendTemporaryPasswordEmail(Request $request){
		$getUserEmail = Admin::selectRaw('email')->where('is_deleted',0)->get();

		if(sizeof($getUserEmail) > 0){
			foreach($getUserEmail as $userEmail ) {
				$email = $userEmail->email;
				$temporaryPwd = str_random(8);
				Admin::where('email',$email)->update(['password'=>Hash::make($temporaryPwd)]);

				try{
					Mail::send('emails.AdminPanel_TempPassword',array(
						'temp_password' => $temporaryPwd
						), function($message)use($email){
						$message->from(env('FromMail','askitchen18@gmail.com'),'A&S KITCHEN');
						$message->to($email)->subject('A&S KITCHEN | Temporary New Password');
					});
				} catch (\Exception $e){
					Session::flash('invalidMail', 'Something went wrong. Please try again.');
					return back();
				}
			}
		}
	}

	public function editMyProfile($email){
		$getAdminDetail = Admin::where('email',$email)->first();
		if(!empty($getAdminDetail)) {
			return view('admin.adminprofile')->with('adminDetail',$getAdminDetail)->with('accountSetting',1);
		}
	}

	public function store(Request $request) {
		$hidden_adminID = $request->get('hidden_adminId');
		$admin_firstName = $request->get('admin_firstName');
		$admin_lastName = $request->get('admin_lastName');
		$admin_contactNo = $request->get('admin_contactNo');
		$admin_email = $request->get('admin_email');

		$checkEmailExist = Admin::selectRaw('email')->where('email',$admin_email)->where('id','<>',$hidden_adminID)->where('is_deleted','<>',1)->first();
		if(isset($checkEmailExist->email)) {
			$response['key'] = 2;
			echo json_encode($response);
		} else {
			$getDetail = Admin::where('id',$hidden_adminID)->first();
			$getSessionEmail = Session::get('email');
			if($getSessionEmail == $getDetail->email) {
				Session::pull('name');
				Session::put('name',$admin_firstName.' '.$admin_lastName);
				$response['name'] = $admin_firstName.' '.$admin_lastName;
			}
			$getDetail->first_name = $admin_firstName;
			$getDetail->last_name = $admin_lastName;
			$getDetail->phone_number = (new AdminHomeController)->replacePhoneNumber($admin_contactNo);
			$getDetail->email = $admin_email;
			$getDetail->save();

			$response['key'] = 1;
			//Session::put('successMessage', 'Admin detail has been updated successfully.');
			echo json_encode($response);
		}
	}

	public function changePassword(Request $request) {

		$current_password = $request->get('current_password');
		$new_password = $request->get('new_password');
		$hidden_Id = $request->get('hidden_Id');
		$checkPassword = Admin::where('id',$hidden_Id)->first();
		if(!empty($checkPassword)) {
			if(Hash::check($current_password,$checkPassword->password)) {
				$checkPassword->password = Hash::make($new_password);
				$checkPassword->save();
				return 1;
			}else {
				return 2;
			}
		}
	}

	public function showJobDetails(Request $request) {
		$getSessionEmail = Session::get('email');
		$job_statusId = $request->get('jobStatusId');

		if($job_statusId == 0) {
			$jobStatusCond = '';
		}elseif($job_statusId == 5) {
			$jobStatusCond = "AND jb.job_status_id = 5 OR jb.job_status_id = 10 ";
		}elseif($job_statusId == 6) {
			$jobStatusCond = "AND jb.job_status_id = 6 OR jb.job_status_id = 11 ";
		}elseif($job_statusId == 7) {
			$jobStatusCond = "AND jb.job_status_id = 7 OR jb.job_status_id = 12 ";
		}else {
			$jobStatusCond = "AND jb.job_status_id = {$job_statusId} ";
		}

		if(Session::get('login_type_id') == 10 ){
			$getJobDetails = DB::select("SELECT jb.job_title,jb.address_1,jb.address_2,jb.apartment_number,jb.city,jb.state,jb.zipcode,jb.super_name,jb.working_employee_id,jb.sales_employee_id,jb.start_date,jb.end_date,jb.company_clients_id,jb.job_id,jb.job_status_id,cmp.name,jt.job_status_name FROM jobs AS jb JOIN companies AS cmp ON cmp.company_id = jb.company_id JOIN admin_users AS au ON au.login_type_id = 10 AND au.id = jb.sales_employee_id JOIN job_types AS jt ON jt.job_status_id = jb.job_status_id WHERE jb.is_deleted = 0  {$jobStatusCond} ORDER BY jb.created_at DESC");
		}else{
			$getJobDetails = DB::select("SELECT jb.job_title,jb.address_1,jb.address_2,jb.apartment_number,jb.city,jb.state,jb.zipcode,jb.super_name,jb.working_employee_id,jb.start_date,jb.end_date,jb.company_clients_id,jb.job_id,jb.job_status_id,cmp.name, jt.job_status_name FROM jobs AS jb JOIN companies AS cmp ON cmp.company_id = jb.company_id JOIN job_types AS jt ON jt.job_status_id = jb.job_status_id WHERE jb.is_deleted = 0  {$jobStatusCond} ORDER BY jb.created_at DESC");
		}

		$getJobTypeDetails = JobType::selectRaw('job_status_name,job_status_id')->orderBy('display_order')->get();
		$html = '';
		if(!empty($getJobDetails)) {

			if(Session::get('login_type_id') == 9 ) {
				foreach($getJobDetails as $jobDetail) {
					$getDetail = Admin::where('email',$getSessionEmail)->first();
					$session_userId = $getDetail->id;
					$client_id_array = explode(',', $jobDetail->company_clients_id);

					$employeeIds = explode(',', $jobDetail->working_employee_id);
					$getEmployeeName = Admin::selectRaw(" GROUP_CONCAT(UPPER(CONCAT(first_name,' ',last_name))) AS employee_name")->where('is_deleted', 0)->whereIn('id', $employeeIds)->first();
					$employee_name = $getEmployeeName->employee_name;

					/* Address */
					$delimiter = ',' . ' ';
					$job_address = $jobDetail->address_1 . $delimiter;
					$job_address .= (!empty($jobDetail->apartment_number)) ? 'Apartment no: ' . $jobDetail->apartment_number . $delimiter : '';
					$job_address .= (!empty($jobDetail->address_2)) ? $jobDetail->address_2 . $delimiter : '';
					$job_address .= (!empty($jobDetail->city)) ? $jobDetail->city . $delimiter : '';
					$job_address .= (!empty($jobDetail->state)) ? $jobDetail->state . $delimiter : '';
					$job_address .= (!empty($jobDetail->zipcode)) ? $jobDetail->zipcode : '';
					$jobDetail->address = $job_address;


					if(in_array($session_userId, $client_id_array)) {
						$html .='<tr class="changestatus_'.$jobDetail->job_id.'">
						<td class="text-center">
							<span data-toggle="" data-target="#jobDetailModel">
								<a data-toggle="tooltip" data-placement="top" title="View Job" class="btn btn-success btn-circle view-job" data-id="'.$jobDetail->job_id.'">
									<i class="ti-eye"></i>
								</a>
							</span>
						</td>
						<td>'.$jobDetail->job_title.'</td>
						<td>'.$jobDetail->name.'</td>
						<td><div class="word-wrap">'.$employee_name.'</div></td>
						<td>'.$jobDetail->job_status_name.'</td>
						<td><div class="word-wrap">'.$jobDetail->address.'</div></td>
						<td>'.date('m/d/Y',strtotime($jobDetail->start_date)).'</td>
						<td>'.date('m/d/Y',strtotime($jobDetail->end_date)).'</td>
					</tr>';
					}
				}
			}else {
				foreach($getJobDetails as $jobDetail) {
					$employeeIds = explode(',', $jobDetail->working_employee_id);
					$getEmployeeName = Admin::selectRaw(" GROUP_CONCAT(UPPER(CONCAT(first_name,' ',last_name))) AS employee_name")->where('is_deleted', 0)->whereIn('id', $employeeIds)->first();
					$employee_name = $getEmployeeName->employee_name;

					/* Address */
					$delimiter = ',' . ' ';
					$job_address = $jobDetail->address_1 . $delimiter;
					$job_address .= (!empty($jobDetail->apartment_number)) ? 'Apartment no: ' . $jobDetail->apartment_number . $delimiter : '';
					$job_address .= (!empty($jobDetail->address_2)) ? $jobDetail->address_2 . $delimiter : '';
					$job_address .= (!empty($jobDetail->city)) ? $jobDetail->city . $delimiter : '';
					$job_address .= (!empty($jobDetail->state)) ? $jobDetail->state . $delimiter : '';
					$job_address .= (!empty($jobDetail->zipcode)) ? $jobDetail->zipcode : '';
					$jobDetail->address = $job_address;

					$html .='<tr class="changestatus_'.$jobDetail->job_id.'">
					<td class="text-center">
						<span data-toggle="" data-target="#jobDetailModel">
							<a data-toggle="tooltip" data-placement="top" title="View Job" class="btn btn-success btn-circle view-job" data-id="'.$jobDetail->job_id.'">
								<i class="ti-eye"></i>
							</a>
						</span>
						<span data-toggle="modal" data-target="#jobNotesModel">
							<a data-toggle="tooltip" data-placement="top" title="Add Job Notes" class="btn btn-warning btn-circle add-job-note" data-id="'.$jobDetail->job_id.'">
								<i class="ti-plus"></i>
							</a>

						</span>
					</td>
					<td>'.$jobDetail->job_title.'</td>
					<td>'.$jobDetail->name.'</td>
					<td><div class="word-wrap">'.$employee_name.'</div></td>';
					if(Session::get('login_type_id') == 10){
						$html .='<td>'.$jobDetail->job_status_name.'</td>';
					}else {
						$html .='<td><div style="width:300px;">
							<select class="form-control select2 jobType" name="jobType" id="jobType_'.$jobDetail->job_id.'" placeholder="Select your job type" data-id="'.$jobDetail->job_id.'">';

								foreach($getJobTypeDetails as $jobType) {
									$selectJobStatus = (isset($jobDetail->job_status_id) && $jobDetail->job_status_id == $jobType->job_status_id) ? "selected='selected'" : "";
									$html .='<option value="'.$jobType->job_status_id.'" ' .$selectJobStatus.'>'.$jobType->job_status_name.'</option>';
								}

								$html .='</select>
						</div></td>';
					}
					$html .='
					<td><div class="word-wrap">'.$jobDetail->address.'</div></td>
					<td>'.date('m/d/Y',strtotime($jobDetail->start_date)).'</td>
					<td>'.date('m/d/Y',strtotime($jobDetail->end_date)).'</td>
				</tr>';
				}
			}
		}

		$response['html'] = $html;
		echo json_encode($response);
	}

	function setnotesbadge()
	{
		$badge = 0;
		if(Session::get('login_type_id') == 9) {
            $client_id = Session::get('employee_id');
			$getJobId = Job::selectRaw('job_id')->where('company_clients_id', 'like', '%'.$client_id.'%')->pluck('job_id')->toArray();

			$countBagde = DB::table('job_notes as jn')
			->join('jobs as j', 'j.job_id', 'jn.job_id')
			->where('jn.is_deleted', 0)
			->where('jn.is_seen', 0)
			->whereIn('jn.job_id', $getJobId)
			->count();
		}else {
			$countBagde = DB::table('job_notes as jn')
			->join('jobs as j', 'j.job_id', 'jn.job_id')
			->where('jn.is_deleted', 0)
			->where('jn.is_seen', 0)
			->count();
		}

		if(!empty($countBagde))
		{
			$response['count'] = $countBagde;
			$response['key'] = 1;
			return $response;
		}
		else
		{
			$response['key'] = 2;
			return $response;
		}
	}

	function replacePhoneNumber($phone_number)
	{
		$replace_phone_number = preg_replace('/\D/', '', $phone_number);
		return $replace_phone_number;
	}

	function formatPhoneNumber($phone_number)
	{
		$replace_phone_number = preg_replace('/\D/', '', $phone_number);
		$format_phone_number = substr_replace(substr_replace(substr_replace($replace_phone_number, '(', 0,0), ') ', 4,0), ' - ', 9,0);
		return $format_phone_number;
	}

	function getuserid() {
		$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < 3; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		$userid = $randomString.mt_rand(10000,99999);
		$check = Admin::where('id',$userid)->first();
		if (empty($check)){
			return $userid;
		} else {
			$this->getuserid();
		}
	}
}