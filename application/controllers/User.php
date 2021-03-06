<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->library(array('session','form_validation'));
        $this->load->model('user_model');
        $this->load->helper('date');
    }

    public function index(){
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
            redirect('login');
        }
        $data = new stdClass();
        $data->user = $this->user_model->get_user($_SESSION['user_id']);
        $this->load->view('templates/header');
        $this->load->view('user/profile', $data);
        $this->load->view('templates/footer');

    }

    public function register(){
        $data = new stdClass();

        $this->load->helper('form');

        $data->title = "Signup";

        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|alpha_numeric_spaces|min_length[4]|max_length[30]');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
        $this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|required|min_length[6]|matches[password]');

        if ($this->form_validation->run() === false){

            // validation not ok, send validation errors to the view
            $this->load->view('templates/header');
            $this->load->view('user/register', $data);
            $this->load->view('templates/footer');

        }else{

            // set variables from the form
            $user = array(
            'fullname' => $this->input->post('fullname'),
            'email'    => $this->input->post('email'),
            );
            $password = $this->input->post('password');

            if ($this->user_model->create_user($user, $password)){

                $data->messagetitle = "Registration Successful";
                $data->messagedetails = "Please Login to continue";
                // user creation ok
                $this->load->view('templates/header');
                $this->load->view('message_view', $data);
                $this->load->view('templates/footer');

            }else{

                // user creation failed, this should never happen
                $data->error = 'There was a problem creating your new account. Please try again.';

                // send error to the view
                $this->load->view('templates/header');
                $this->load->view('user/register', $data);
                $this->load->view('templates/footer');

            }

        }
    }

    public function login(){

        $data = new stdClass();

        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == false) {

            // validation not ok, send validation errors to the view
            $this->load->view('templates/header');
            $this->load->view('user/login');
            $this->load->view('templates/footer');

        } else {

            // set variables from the form
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            if ($this->user_model->resolve_user_login($email, $password)) {

                $user_id = $this->user_model->get_user_id_from_email($email);
                $user    = $this->user_model->get_user($user_id);

                // set session user datas
                $_SESSION['user_id']      = (int)$user->id;
                $_SESSION['logged_in']    = (bool)true;


                // user login ok
                // $this->load->view('header');
                // $this->load->view('user/dashboard', $data);
                // $this->load->view('footer');
                redirect('user/');

            } else {

                // login failed
                $data->error = 'Wrong username or password.';

                // send error to the view
                $this->load->view('templates/header');
                $this->load->view('user/login', $data);
                $this->load->view('templates/footer');

            }

        }
    }

    public function logout(){

        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {

            // remove session datas
            foreach ($_SESSION as $key => $value) {
                unset($_SESSION[$key]);
            }

            // user logout ok
        }
        redirect('login');

    }

    public function edit(){
        // create the data object
        $data = new stdClass();
        if ( !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == false){
            redirect('login');
        }
        $data->user = $this->user_model->get_user($_SESSION['user_id']);

        $this->load->helper('form');


        $this->form_validation->set_rules('fullname', 'Full Name', 'trim|required|alpha_numeric_spaces|min_length[4]|max_length[30]');

        $email = $this->input->post('email');
        if ($email != $data->user->email){
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
        }





        if ($this->form_validation->run() === false){


            $this->load->view('templates/header');
            $this->load->view('user/edit_profile', $data);
            $this->load->view('templates/footer');
        }else{
            $user = array(
                'fullname' => $this->input->post('fullname'),
                'email'    => $this->input->post('email'),
            );
            $password = $this->input->post('password');
            if ($this->user_model->update($_SESSION['user_id'], $user, $password)){
                redirect('user');
            }


            $data->error = 'Incorrect Password, Profile update failed';

            // send error to the view
            $this->load->view('templates/header');
            $this->load->view('user/edit_profile', $data);
            $this->load->view('templates/footer');
        }



    }

    public function test(){
        $data = new stdClass();
        $data->messagetitle = "Registration Successful";
        $data->messagedetails = "Please Login to continue";
        // user creation ok
        $this->load->view('templates/header', $data);
        $this->load->view('message_view', $data);
        $this->load->view('templates/footer');
    }


}