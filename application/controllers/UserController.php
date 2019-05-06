<?php
defined('BASEPATH') OR exit('No direct script access allowed');
   
class UserController extends CI_Controller {
    
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
	private $response = array();
	
    public function __construct() {
       parent::__construct();
       $this->load->library("session");
       $this->load->helper('url');
	   $this->load->model('UserModel', 'userModel', TRUE);
    }
    
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index(){
        $this->load->view('my_stripe');
    }
     

	public function userRegister(){
		if(!empty($this->input->post())){
			
			$validateUser = $this->userModel->validateUser($this->input->post()); 
			if($validateUser->num_rows()<1){
				$imageResponse = $this->do_upload();
				
				$_POST['profile_image'] = $imageResponse['img_response']['file_name'] ? $imageResponse['img_response']['file_name'] : '' ;

					$result = $this->userModel->insertUser($this->input->post()); 
					
					if($result){
						$id = array('id'=>$result);
						
						$data = $this->userModel->find_user_by_id($id);
						$this->response[Status] = Success;
						$this->response[Message] = 'Sigup Successfully';
						//['rating'] = 0;
						$data_response = $data->result_array()[0];
						$data_response['rating'] = '0';
						$this->response['data'] =$data_response;
						
					}
					else{
						$this->response[Status] = Failure;
						$this->response[Message] = 'Incorrect Information';
					}
				/* }
				else{
					$this->response[Status] = Failure;
					$this->response[Message] = $imageResponse['error'];
				} */
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = 'User Already exist';
			}
		
		}
		else{
		    	$this->response[Status] = Failure;
				$this->response[Message] = 'Please send required parameters';
		}
		apiResponse($this->response);
       // $this->load->view('my_stripe');
    }
	
	public function userLogin(){
		if(!empty($this->input->post())){
			
			$result = $this->userModel->getUser($this->input->post()); 
			
			if($result->num_rows()>0){
				
				$where = array('id'=>$result->result_array()[0]['id']);
				$data = array('token'=>$this->input->post('token'));
				
				$update_result = $this->userModel->updateUser($data,$where);//token update
				
				$data = $this->userModel->find_user_by_id(array('id'=>$result->result_array()[0]['id'])); //fetch user by id
				
				$this->response[Status] = Success;
				$this->response[Message] = DataFound;
				$this->response['data'] = $data->result_array()[0];
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = NoDataFound;
			}
			
			apiResponse($this->response);
			
		}
		
    }
	
	
	public function uploadProfileImage(){
		if(!empty($this->input->post())){
			$imageResponse = $this->do_upload();
			if(isset($imageResponse['img_response']['file_name'])){
				
				$image = array('profile_image'=>$imageResponse['img_response']['file_name']); //user profile Image 
				
				$where = array('id'=>$this->input->post('id')); // Condition
				
				$result = $this->userModel->updateUser($image,$where);  
				
				if($result){
					$this->response[Status] = Success;
					$this->response[Message] = 'Data Uploaded Successfully';
				}
				else{
					$this->response[Status] = Success;
					$this->response[Message] = 'No changes done';
				}
				
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = $imageResponse['error'];
			}
	
	    }
    	else{
		    	$this->response[Status] = Failure;
				$this->response[Message] = 'Please send required parameters';
		}
		
		apiResponse($this->response);
	}
	
	public function updateProfile(){
		if(!empty($this->input->post())){
			$where = array('id'=>$this->input->post('id')); // Condition
			
			$data = array(
				'username'=>$this->input->post('username'),
				'location'=> $this->input->post('location'),
				'about_info'=> $this->input->post('info')
			);
			
						
			$result = $this->userModel->updateUser($data,$where);  
			
			if($result){
				$data = $this->userModel->find_user_by_id(array('id'=>$this->input->post('id'))); //fetch user by id
				$res = $data->result_array()[0];
				$this->response[Status] = Success;
				$this->response[Message] = 'Data Uploaded Successfully';
				$this->response['data'] = $res;
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = 'No changes done';
			}
		}
		else{
		    	$this->response[Status] = Failure;
				$this->response[Message] = 'Please send required parameters';
		}
		
		apiResponse($this->response);
	}
	
	public function do_upload(){
		if(isset($_FILES[key($_FILES)]['name'])){
			
			list($name,$ext) = explode(".",$_FILES[key($_FILES)]['name']);
			
			$config['upload_path']          = getcwd().'/mediaFiles/';
			$config['allowed_types']        = 'gif|jpg|png';
			$config['max_size']             = ''; //2048
			$config['max_width']            = ''; //1024
			$config['max_height']           = ''; //768
			$config['file_name'] = "File" . rand(000000, 999999) . ".".$ext;
			$config['file_ext_tolower'] = TRUE;
			
			$this->load->library('upload', $config);
			
			if ( ! $this->upload->do_upload(key($_FILES))){
					$error = array('error' => $this->upload->display_errors());
					
					return $error;
			}
			else{
					$data = array('img_response' => $this->upload->data());
					return $data ;
			}
		}
		return false;
	}
	
	public function get_match_contacts(){
		if(!empty($this->input->post())){
			$user_contacts = explode(",",$this->input->post('all_contact'));
			array_walk_recursive($user_contacts,function(&$v){$v=str_replace(' ', '', $v);});
			
			$db_contacts = $this->userModel->select_contact();
			
			$db_con = $db_contacts->result_array();
				
			$res_contact = array();	
			
			$actual_contact = array_unique($user_contacts);
			
			$match = array();
			foreach($actual_contact as $key => $value){
				foreach($db_con as $dkey => $dbvalue){
					if(!empty($value) && !empty($dbvalue['contact_no'])){
						$flag= strstr($value,$dbvalue['contact_no']);
						if($flag){
							array_push($match,$dbvalue['contact_no']);
							if(!in_array_r($dbvalue['contact_no'],$res_contact)){
								array_push($res_contact,$dbvalue);
							}
						} 
					}
				}
			}
			if(!empty($res_contact)){
				$this->response[Status] = Success;
				$this->response[Message] = DataFound;
				$this->response['data'] = $res_contact;
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = NoDataFound;
			}
		}
		else{
		    	$this->response[Status] = Failure;
				$this->response[Message] = 'Please send required parameters';
		}
		
		apiResponse($this->response);
		
	}
	
	public function create_group(){
		if(!empty($this->input->post())){
			$imageResponse = $this->do_upload();
			$_POST['group_image'] = $imageResponse['img_response']['file_name'] ? $imageResponse['img_response']['file_name'] : '' ;
			
			$group_members = explode(",",$this->input->post('group_members'));
			
			
			$result = $this->userModel->insert_group($this->input->post()); 
						
			if($result){
				
				$group_info = $this->userModel->find_group_by_id(array('group_id'=>$result));
				
				$group_data = $group_info->result()[0];
				
				$group_name = $group_data->group_name;
				
				$group_id = $group_data->group_id;
				
				$sender_info = $this->userModel->find_user_by_id(array('id'=>$this->input->post('user_id')));
				
				$sender_data = $sender_info->result()[0];
				
				$sender_name = $sender_data->username;
				
				$sender_id = $sender_data->id;
				
				$message = "$sender_name has send you request to join this group $group_name.";
				
				foreach($group_members as $key => $value){
					$res = $this->userModel->insert_group_members($result,$value);
					//send notification here 
					if($res){ 
						$receiver_info = $this->userModel->find_user_by_id(array('id'=>$this->input->post('user_id')));
				
						$receiver_data = $receiver_info->result()[0];
						
						$receiver_name = $receiver_data->username;
						
						$receiver_id = $receiver_data->id;
						
						$send_notification = $this->send_notification(1,$message);
						if($send_notification){
							
							$nf_array = json_decode($send_notification);
							$msg_id = $nf_array->results[0]->message_id;
							
							$data = array('type'=>'1','message'=>$message,'fb_msg_id'=>$msg_id,'user_id'=>$sender_id,'receiver_id'=>$receiver_id,'gid'=>$group_id);
							
							$nf_insert = $this->userModel->insert_notification($data);
							if($nf_insert){
								$this->response[Status] = Success;
								$this->response[Message] = 'You have successfully created Group';
							}
							else{
								$this->response[Status] = Failure;
								$this->response[Message] = $this->db->error();
							}
							
						}
						else{
							$this->response[Status] = Failure;
							$this->response[Message] = 'Incorrect token';
						}
						
					}
					else{
						$this->response[Status] = Failure;
						$this->response[Message] = $this->db->error();
					}
				}
				
				//$this->response[Status] = Success;
				//$this->response[Message] = 'You have successfully created Group';
				
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = 'Incorrect Information';
			}
		}
		else{
		    	$this->response[Status] = Failure;
				$this->response[Message] = 'Please send required parameters';
		}
		
		apiResponse($this->response);
		
	}
	
	public function update_token(){
		
		$where = array('id'=>$this->input->post('id')); // Condition
		
		$data = array(
			'token'=>$this->input->post('token')
		);
					
		$result = $this->userModel->updateUser($data,$where);  
		
		if($result){
			$this->response[Status] = Success;
			$this->response[Message] = 'Data Updated Successfully';
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'No changes done';
		}
			
		apiResponse($this->response);
	}
	
	
	public function fcm_token(){
		 //$target = "AAAAFyP5kX4:APA91bETZPYt66-KUJ_CwoZ9QPiIYN3gLcBEDHeRiz1qqiFyQhlrYHpOnncGmvwLv77J-DXS2m5xj9vQQZg-5E9RWz_rXjgMBpqulGqtySKKw6j-aR_b8RHBwUhyU42m8rze_HS04Zpk";
		 
		 $target = "cJ6s4nxu51Q:APA91bHBqgN5bCvd5wCMUKIxuIqMpvsM5Y7SM5dpjg0p5vMdivyp84vOin0NhqBiKerq4sLImBtl9WIIPrrOibGwcVwolKSU4FKhDLCGQlT0WromWuB3TFT-LwfDHOEtkZpL57lnfrE0";

		$data = array("title"=>"hello","body"=>"notification body");

		$fcmResponse = $this->userModel->sendFCMMessage($data,$target);

		print_r($fcmResponse);
		
	} 
	
	public function send_notification($type='',$message='',$target=''){
		 
		$target = "cJ6s4nxu51Q:APA91bHBqgN5bCvd5wCMUKIxuIqMpvsM5Y7SM5dpjg0p5vMdivyp84vOin0NhqBiKerq4sLImBtl9WIIPrrOibGwcVwolKSU4FKhDLCGQlT0WromWuB3TFT-LwfDHOEtkZpL57lnfrE0";
		
		if($type==1){
			$data = array("title"=>'Group join request',"body"=>$message);
		}
		else{
			$data = array("title"=>'Rating Work',"body"=>$message);
		}

		$fcmResponse = $this->userModel->sendFCMMessage($data,$target);
		
		
		return $fcmResponse;
		
	}
	
	public function forget_password(){
		
		$where = array('email_id'=>$this->input->post('email')); // Condition
		
		$data = $this->userModel->find_user_by_id($where);
		
		if($data->num_rows()<1){
			$this->response[Status] = Failure;
			$this->response[Message] = NoDataFound;
		}
		else{
			$user_data =  $data->result();
			$email = $user_data[0]->email_id;
			$password = generateRandomString();
			$password_arr = array('password'=> md5($password));
			$uresult = $this->userModel->updateUser($password_arr,$where);
	
			$config = Array(
				'protocol' => 'smtp',
				'smtp_host' => '**********', //enter smtp hostname
				'smtp_port' => '***', //port
				'smtp_user' => '**********', //enter smtp username
				'smtp_pass' => '**********', //enter smtp password
				'mailtype'  => 'html', 
				'charset'   => 'iso-8859-1'
			);
			
			$this->load->library('email', $config);
			$this->email->set_newline("\r\n");
			$this->email->from('********', '****'); //from username 
			$this->email->to($email); 
			$this->email->subject('Change your password');
			$this->email->message("Your password has been changed. Please login with this password: &nbsp;  ".$password);  
			// Set to, from, message, etc.

			$result = $this->email->send();
			if($result){
				$this->response[Status] = Success;
				$this->response[Message] = "Password has been sent to your registered Email Address";
			}
			else{
				$this->response[Status] = Failure;	
				$this->response[Message] = $this->email->print_debugger();	
			}
			//print_r($uresult);
		}
		
		apiResponse($this->response);
		
		
	} 
	
	public function user_exist(){
		//$data= array('contact_no'=>this->input->post('phone_number'));
		$data= array($this->input->post('phone_number'));
		
		$result = $this->check_contact($data);
		
		if(!empty($result)){
			$this->response[Status] = Success;
			$this->response[Message] = DataFound;
			$this->response['data'] = '1';
		}
		else{
			$this->response[Status] = Failure;	
			$this->response[Message] = NoDataFound;
			$this->response['data'] = '0';
		}
		apiResponse($this->response);
		
	}
	
	public function check_contact($user_contacts){
		$db_contacts = $this->userModel->select_contact()->result_array();
		
		$res_contact = array();	
		
		$actual_contact = array_unique($user_contacts);
		
		$match = array();
		foreach($actual_contact as $key => $value){
			foreach($db_contacts as $dkey => $dbvalue){
				if(!empty($value) && !empty($dbvalue['contact_no'])){
					$flag= strstr($value,$dbvalue['contact_no']);
					if($flag){
						array_push($match,$dbvalue['contact_no']);
						if(!in_array_r($dbvalue['contact_no'],$res_contact)){
							array_push($res_contact,$dbvalue);
						}
					} 
				}
			}
		}
		return $res_contact;
	}
	
	public function update_password(){
		if(!empty($this->input->post())){
			$where = array('id'=>$this->input->post('user_id')); // Condition
						
			$data = $this->userModel->find_user_by_id($where);
			if($data->num_rows()>0){
				
				$res = $data->result();
				
				if($res[0]->password == md5($this->input->post('old_password')) ){
					
					
						$update_password = $this->userModel->updateUser(array('password'=>md5($this->input->post('new_password'))),$where);
						
						if($update_password){
							$this->response[Status] = Success;
							$this->response[Message] = 'Your password has been updated';
						}
						else{
							$this->response[Status] = Failure;
							$this->response[Message] = 'No changes done';
						}
				}
				else{
					$this->response[Status] = Failure;
					$this->response[Message] = 'Old Password did not match';
				}
				
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = NoDataFound;
			}
		}
		else{
		    	$this->response[Status] = Failure;
				$this->response[Message] = 'Please send required parameters';
		}
		apiResponse($this->response);
	}
	
	public function get_profile($id=''){
		if(!empty($id)){
			$where = array('id'=>$id); // Condition
						
			$data = $this->userModel->find_user_by_id($where);
			if($data->num_rows()>0){
				$result = $data->result_array()[0];
				/* print_r($result);
				die; */
				$this->response[Status] = Success;
				$this->response[Message] = DataFound;
				$this->response['data'] = $result;
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = NoDataFound;
			}
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Invalid request';
		}
		
		apiResponse($this->response);
		
	}
	
	public function update_card_details(){
		if(!empty($this->input->post())){
			$data = $this->input->post();
			$result = $this->userModel->insert_card($data);
			if($result){
				$this->response[Status] = Success;
				$this->response[Message] = 'You have successfully added Card Details';
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = 'Card Details already exist';
			}
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Please send required parameters';
			
		}
		apiResponse($this->response);
	}
	
	public function get_card_details(){
		if(!empty($this->input->post())){
			$data = $this->input->post();
			$result = $this->userModel->get_card_info($this->input->post());
			if($result){
				$this->response[Status] = Success;
				$this->response[Message] = DataFound;
				$this->response['data'] = $result->result_array()[0];
			}
			else{
				$this->response[Status] = Success;
				$this->response[Message] = NoDataFound;
			}
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Please send required parameters';
			
		}
		apiResponse($this->response);
	}
	
	public function get_user_groups(){
		if(!empty($this->input->post())){
			$data = array('id'=>$this->input->post('user_id'));
			
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Please send required parameters';
			
		}
		apiResponse($this->response);
	}
	
	public function send_notification_test(){
		if(!empty($this->input->post())){
			$type = $this->input->post('type');
			
			$result = $this->userModel->insert_notification($data);
			
			if($type==1){
				
			}
			else{
				echo "no 1";
			}
			die;
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Please send required parameters';
		}
		apiResponse($this->response);
	}
	
	public function rate_user(){
		
		if(!empty($this->input->post())){
			$data = array(
				'message'=>$this->input->post('message'),
				'rating'=>$this->input->post('rate'),
				'rate_by'=>$this->input->post('rating_by'),
				'rate_to'=>$this->input->post('rating_to')
			);
			
			$set_tb = $this->userModel->set_table('rate');
			$result = $this->userModel->custom_insert('rate',$data);
			if($result){
				$this->response[Status] = Success;
				$this->response[Message] = 'User rated successfully';
			}
			else{
				$this->response[Status] = Failure;
				$this->response[Message] = 'Rating error';
			}
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Please send required parameters';
		}
		apiResponse($this->response);
	}
	
	public function get_reviews(){
		//user_id
		if(!empty($this->input->post())){
			$where = array('rate_to'=>$this->input->post('user_id'));
			/*$set_tb = $this->userModel->set_table('rate');
			$result = $this->userModel->custom_where($where);*/
			$result = $this->userModel->get_user_rating($where);
			print_r($result->result_array());
			die;
			if($result){
				$this->response[Status] = Success;
				$this->response[Message] = DataFound;
				$this->response['data'] = $result->result_array();
			}
			else{
				$this->response[Status] = Success;
				$this->response[Message] = NoDataFound;
			}
		}
		else{
			$this->response[Status] = Failure;
			$this->response[Message] = 'Please send required parameters';
		}
		apiResponse($this->response);
	}
	
	
}
