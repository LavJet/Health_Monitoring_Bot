<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        $this->load->model(array( 
            'website/section_model',
            'website/home_model',
            'website/item_model',
            'website/appointment_model',
            'website/setting_model'
        ));  
    } 
 
    public function index()
    {
        $data['title'] = display('website_setting'); 
        #-----------Setting-------------# 
        $data['setting'] = $this->home_model->setting();
        // redirect if website status is disabled
        if ($data['setting']->status == 0) 
            redirect(base_url('login'));
        #-----------Section-------------# 
        $sections = $this->home_model->sections();
        $dataSection = array();
        if(!empty($sections)):
            foreach ($sections as $section) {
                $dataSection[$section->name] = array(
                    'name'  => $section->name,
                    'title' => $section->title,
                    'description'     => $section->description,
                    'featured_image'  => $section->featured_image,
                );
            }
        endif; 
        $data['section'] = $dataSection;

        #----------Section Item---------# 
        $items = $this->home_model->items();
        $dataItem = array();
        if(!empty($items)):
            $sl = 0;
            foreach ($items as $item) {
                $dataItem[$item->name][$sl++] = array(
                    'id'          => $item->id,
                    'name'        => $item->name,
                    'title'       => $item->title,
                    'description' => $item->description,
                    'icon_image'  => $item->icon_image,
                    'position'    => $item->position,
                    'status'      => $item->status,
                    'counter'     => $item->counter,
                    'date'        => $item->date
                );
            }
        endif;
        $data['items'] = $dataItem;

        #-------------All Data-----------#  
        $data['latest_news'] = $this->home_model->latest_news(3); 
        $data['sliders'] = $this->home_model->sliders(); 
        $data['doctors'] = $this->home_model->doctors();
        $data['departments'] = $this->home_model->departments();
        $data['department_list'] = $this->appointment_model->department_list(); 
        $data['comments'] = $this->home_model->comments();
        #-------------------------------#       

        $this->load->view('website/main_wrapper',$data);
    }
 
    //all post details without slider
    public function details($id = null)
    { 
        $data['title'] = display('details');  
        #-----------Setting-------------# 
        $data['setting'] = $this->home_model->setting();
        // redirect if website status is disabled
        if ($data['setting']->status == 0) 
            redirect(base_url('login'));
        #-------------------------------#    
        //set items two times for details and pagination 
        $data['item'] = $this->home_model->blog_details($id);
        $data['comments'] = $this->home_model->comments_details($id);
            //update item view counter  
            $this->home_model->update_counter($id);
        $data['latest_news'] = $this->home_model->latest_news(3); 
        $data['recent_news'] = $this->home_model->recent_news(20);    
        #-------------------------------#  
        $this->load->view('website/details_wrapper',$data);
    } 

    //slider post details
    public function slider($id = null)
    {
        $data['title'] = display('details');
        #-----------Setting-------------# 
        $data['setting'] = $this->home_model->setting();
        // redirect if website status is disabled
        if ($data['setting']->status == 0) 
            redirect(base_url('login'));
        #-------------------------------#   
        $data['item'] = $this->home_model->slider_details($id); 
        $data['latest_news'] = $this->home_model->latest_news(3);  
        #-------------------------------#   
        $this->load->view('website/slider_wrapper',$data);
    }

    public function requestTrail(){

        $name = $this->input->post('name',true);
        $hospitalname = $this->input->post('hospitalname',true);
        $emailfrom = $this->input->post('emailaddress',true);
        $contactno = $this->input->post('contactno',true);
        $message = $this->input->post('message',true);

        //Load email library
        $this->load->library('email');

        //SMTP & mail configuration
        $config = array(
            'protocol' => 'smtp',
            'smtp_host' => $this->config->item('smtp_host'),
            'smtp_port' => 465,
            'smtp_user' => $this->config->item('smtp_user'),
            'smtp_pass' => $this->config->item('smtp_pass'),
            'mailtype' => 'html',
            'charset' => 'iso-8859-1'
        );
        $this->email->initialize($config);
        $this->email->set_mailtype("html");
        $this->email->set_newline("\r\n");

        //$content = file_get_contents($this->load->view('mail/contact_us', '' , TRUE));
//        $mail->MsgHTML($content);

//Email content
        $htmlContent = '<h1>Sending email via Gmail SMTP server</h1>';
        $htmlContent .= '<p>This email has sent via Gmail SMTP server from CodeIgniter application.</p>';

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Create email headers
        $headers .= 'From: '.$emailfrom."\r\n".
            'Reply-To: '.$emailfrom."\r\n" .
            'X-Mailer: PHP/' . phpversion();

        //$this->email->header($headers);

        $message = $name .   "<br/><br/>"  . $message
            . "<br/>" .  $contactno . "<br/><br/>" ;

        $data['email'] = (object)$postData = array(
            'from'        => $emailfrom,
            'to'          => 'info@thinkbots.tech',
            'subject'     => 'New Request Demo',
            'message'     =>   $message,

        );

        $this->email->to($postData['to']);
        $this->email->from($postData['from']);
        $this->email->subject($postData['subject']);
        $this->email->message($postData['message']);


        if($this->email->send()){
            echo json_encode(array('status'=>200, 'message'=>'Thank You! We have Received Your Request We Will Get In Touch Soon'));

        } else {
            echo json_encode(array('status'=>500, 'message'=>'Something Connection Issue or Try again Please'));

        }
    }

    public function forgotPassword(){
        $emailaddress = $this->input->post('emailaddress',true);
        $data['mail']  = $this->home_model->checkMail($emailaddress);

        if($data['mail'] == 0) {
            $this->form_validation->set_message('email_check', 'The {field} field must contain a unique value.');
        }
    }

}
