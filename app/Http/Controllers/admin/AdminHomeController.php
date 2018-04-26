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

class AdminHomeController extends Controller
{
	/*show login*/
	public function showLogin()
	{
		return view('admin.login');
	}

	/*do login*/
	public function doLogin(Request $request){

		$email = $request->input('admin_email');
		$password = $request->input('admin_password');
		//$insertLogin = Admin::where('email',$email)->update(['password' => bcrypt('admin')]);
		$checkLogin = Admin::where('email',$email)->first();
		if(!empty($checkLogin)) {
			if(Hash::check($password, $checkLogin->password)) {
				Session::put('name',$checkLogin->first_name.' '.$checkLogin->last_name);
				Session::put('email',$checkLogin->email);
				Session::put('login_type_id',$checkLogin->login_type_id);
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
		return view('admin.dashboard');
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
				Mail::send('emails.sendtemppassword',array(
					'temp_password' => $temporaryPwd
				), function($message)use($email){
					$message->from(env('FromMail','kitchen@gmail.com'),'KITCHEN');
					$message->to($email)->subject('KITCHEN | Forgot Password');
				});
			} catch (\Exception $e){
				Session::flash('invalidMail', 'Something went wrong. Please try again.');
				return back();
			}
			Session::flash('validMail', 'An email containing your temporary login password has been sent to your verified email address. You can change your password from your profile.');
			return back();
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
		$hidden_email = $request->get('hidden_email');
		$checkPassword = Admin::where('email',$hidden_email)->first();
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
}
