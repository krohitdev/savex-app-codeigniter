<?php
class UserModel extends CI_Model {
    
	private $table;
    function __construct(){
        // Call the Model constructor
        parent::__construct(); 
		echo $this->table;
		
    }

	
	public function insertUser($d)
    {  
		$data = array(
			'username'=>$d['username'],
			'password'=>md5($d['password']),
			'email_id'=>$d['email'],
			'profile_image'=>$d['profile_image'],
			'contact_no'=> $d['contact'],
			'location'=> $d['location'],
			'token'=>$d['token']
		);
		
		$query = $this->db->insert_string('user',$data);             
		$this->db->query($query);
		return $this->db->insert_id();
    }
	
	public function getUser($d){  
		$data = array(
                'password'=>md5($d['password']),
                'contact_no'=>$d['contact']
            );
			
		return $this->db->get_where('user', $data);
		
    }
	
	public function find_user_by_id($d){
		return $this->db->get_where('user', $d);
	}

	public function find_group_by_id($d){
		return $this->db->get_where('savex_groups', $d);
	}
	
	
	
	public function validateUser($d,$where=''){  
		$data = array(
                'email_id'=>$d['email'],
                'contact_no'=>$d['contact']
            );
		
		$this->db->select('*');
		$this->db->from('user');
		$this->db->or_where('email_id', $d['email']);
		$this->db->or_where('contact_no', $d['contact']);
		//$this->db->where('id!=', $d['id']);
		$q = $this->db->get();
		return $q;
		/*print_r($q->num_rows());
		die;
		return $this->db->get_where('user', $data); */
	
	}
	
	public function validateUserUpdate($data){
		
		$sql = "SELECT *  FROM user WHERE id != '$data[id]' AND email_id = '$data[email]' AND contact_no = '$data[contact]'";
		$query = $this->db->query($sql);

		return $query->num_rows();
		//die($query);
	}
	
	public function updateUser($d,$where){
		$query = $this->db->update_string('user', $d, $where);
		$this->db->query($query);
		return $this->db->affected_rows();
		
	}
	
	public function select_contact(){
		$this->db->select('*');
		$this->db->from('user');
		$q = $this->db->get();
		return $q;
	}
	
	public function insert_group($d){  
	
		$data = array(
			'group_name'=>$d['group_name'],
			'image'=>$d['group_image'],
			'created_by'=>$d['user_id'],
			'amount'=>$d['amount'],
			'pay_date'=>$d['date_of_amount']
		);
		
		$query = $this->db->insert_string('savex_groups',$data);             
		$this->db->query($query);
		return $this->db->insert_id();
    }
	
	
	public function insert_group_members($gid,$uid){
		$data = array(
			'group_id'=>$gid,
			'user_id'=>$uid
		);
		
		$query = $this->db->insert_string('savex_groups_users',$data);
		return $this->db->query($query);		
	}
	
	public function insert_card($d){  
	
		$this->db->where('user_id',$d['user_id']);
		$q = $this->db->get('card_info');

		if ( $q->num_rows() > 0 ) {
			$where = array('user_id'=>$d['user_id']);
			$data = array(
					'card_holder_name'=>$d['user_name'],
					'card_number'=>$d['card_no'],
					'cvv'=>$d['cvv'],
					'month'=>$d['month'],
					'year'=>$d['year']
				);
			$query = $this->db->update_string('card_info', $data, $where);
			$this->db->query($query);
			return $this->db->affected_rows();
			//return false;
		}
		else {
			$data = array(
				'user_id'=>$d['user_id'],
				'card_holder_name'=>$d['user_name'],
				'card_number'=>$d['card_no'],
				'cvv'=>$d['cvv'],
				'month'=>$d['month'],
				'year'=>$d['year']
			);
		
			$query = $this->db->insert_string('card_info',$data);             
			$this->db->query($query);
			return $this->db->insert_id();
		}
   
   
		
    }
	
	public function get_card_info($d){  
		return $this->db->get_where('card_info', $d);
    }
	
	public function insert_notification($d){  
	
		$data = array(
			'type'=>$d['type'],
			'message'=>$d['message'],
			'firebase_message_id'=>$d['fb_msg_id'],
			'sender_id'=>$d['user_id'],
			'receiver_id'=>$d['receiver_id'],
			'group_id'=>$d['gid'],
		);
		
		$query = $this->db->insert_string('savex_notification',$data);             
		$this->db->query($query);
		return $this->db->insert_id();
    }
	
	public function custom_insert($tab,$data){
		
		$query = $this->db->insert_string($this->table,$data);             
		$this->db->query($query);
		$this->setter_table();
		return $this->db->insert_id();
	}
	
	public function custom_where($d){
		return $this->db->get_where($this->table, $d);
    }
	
	public function setter_table()
    {
		$this->table = ''; 
		return $this->table;
    }
	
	public function set_table($tab){
		if($tab=='rate'){
			$this->table = 'savex_users_rating';
		}
		return $this->table;
	}
	
	
	public function get_user_rating($d){
		$this->db->select('*');
		$this->db->from('savex_users_rating');
		$this->db->join('user', 'user.id = savex_users_rating.rate_to', 'left');
		//$this->db->join('user', 'user.id = savex_users_rating.rate_by');
		$this->db->where('rate_to','2');
		///print_r($this->db);die;
		$query = $this->db->get();
		return $query;
	}
	
	public function sendFCMMessage($data,$target){

        //FCM API end-point

        $url = 'https://fcm.googleapis.com/fcm/send';

        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key

        $server_key = 'AAAAFyP5kX4:APA91bETZPYt66-KUJ_CwoZ9QPiIYN3gLcBEDHeRiz1qqiFyQhlrYHpOnncGmvwLv77J-DXS2m5xj9vQQZg-5E9RWz_rXjgMBpqulGqtySKKw6j-aR_b8RHBwUhyU42m8rze_HS04Zpk';

        $fields = array("to"=>$target,'notification'=>$data);

        /*$fields['data'] = $data;

        if(is_array($target)){

            $fields['registration_ids'] = $target;

        }else{

            $fields['to'] = $target;

        }*/

        //header with content_type api key

        $headers = array(

            'Content-Type:application/json',

            'Authorization:key='.$server_key

        );

        //CURL request to route notification to FCM connection server (provided by Google)

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);

        if ($result === FALSE) {

            die('Oops! FCM Send Error: ' . curl_error($ch));

        }
		//print_r($result);
        curl_close($ch);
		//die;
        return $result;

    }
	
	
	
	


}
