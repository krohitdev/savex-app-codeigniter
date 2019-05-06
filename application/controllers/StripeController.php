<?php
defined('BASEPATH') OR exit('No direct script access allowed');
   
class StripeController extends CI_Controller {
    
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
    }
    
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function index()
    {
        $this->load->view('my_stripe');
    }
       
    /**
     * Get All Data from this method.
     *
     * @return Response
    */
    public function stripePost()
    {
		print_r($this->input->post());
		die();
        require_once('application/libraries/stripe-php/init.php');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
     
        \Stripe\Charge::create ([
                "amount" => 100 * 1,
                "currency" => "usd",
                "source" => $this->input->post('stripeToken'),
                "description" => "Test payment from itsolutionstuff.com." 
        ]);
            
        $this->session->set_flashdata('success', 'Payment made successfully.');
		 
		//redirect('StripeController/my-stripe', 'refresh');
        redirect('StripeController', 'refresh');
    }
	
	public function stripePay()
    {
		//print_r($this->input->post());
		
        require_once('application/libraries/stripe-php/init.php');
        \Stripe\Stripe::setApiKey($this->config->item('stripe_secret'));
     
		//\Stripe\Stripe::setApiKey("sk_test_N6DvftMr6HEkLC6WIRqwFjxn006hS4BkIu");

		try {
			$token = \Stripe\Token::create([
			  'card' => [
				'number' => $this->input->post('number'),
				'exp_month' => $this->input->post('exp_month'),
				'exp_year' => $this->input->post('exp_year'),
				'cvc' => $this->input->post('cvc')
			  ]
			]);
		
			if(isset($token->id)){
				$response = \Stripe\Charge::create ([
					"amount" => 100 * $this->input->post('amount'),
					"currency" => "usd",
					"source" => $token->id,
					"description" => "Test payment" 
				]);
				
				if($response->id){
					$this->response[Status] = Success;
					$this->response[Message] = 'Payment Successful';
				}
				
			}
		}
		catch ( Stripe\Error\Base $e ) {
			$this->response[Status] = Failure;
			$this->response[Message] = $e->getMessage();
		}
		catch ( Exception $e ) {
			$this->response[Status] = Failure;
			$this->response[Message] = $e->getMessage();
		}
		
		apiResponse($this->response);
		
    }
	
}