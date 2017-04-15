<?php


class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url','url_helper'));
        $this->load->model(array('User_model'));

    }
    public function index(){
        echo '接口测试<br>登录接口url：<br>';
        echo 'index.php/user/login';
        echo 'User fuck';
    }
    /**
     * @param $data
     * @param int $ret
     * @param null $msg
     * 返回JSON数据到前端
     */
    private function response($data,$ret=200,$msg=null){
        $response=array('ret'=>$ret,'data'=>$data,'msg'=>$msg);
        $this->output
            ->set_status_header($ret)
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_header('Pragma: no-cache')
            ->set_header('Expires: 0')
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response))
            ->_display();
        exit;
    }


    /**
     * 登录接口
     * @desc 用于验证并登录用户
     * @return int code 操作码，1表示登录成功，0表示登录失败
     * @return object info 用户信息对象
     * @return int info.id 用户ID
     * @return string info.nickname 用户昵称
     * @return string msg 提示信息
     *
     */
    public function login(){
        $data=array(
            'email' => $this->input->post('user_email'),
            'password' => $this->input->post('password'),
        );
        $this->form_validation->set_data($data);
        if ($this->form_validation->run('login') == FALSE)
            $this->response(null,400,validation_errors());
        $re['code']=0;
        $model= $this->User_model->user_email($data);
        if(!$model){
            $msg='该邮箱尚未注册！';
        }elseif(md5($data['password'])!=$model['password']){
            $msg='密码错误，请重试！';
        }else{
            $re['info']= array('user_id' => $model['id'], 'user_name' => $model['nickname'], 'user_email' => $model['email']);
            $re['code']='1';
            $msg='登录成功！';
        }
        $this->response($re,200,$msg);
    }

    public function reg(){
        $data=array(
            'nickname'=>$this->input->post('user_name'),
            'email'=>$this->input->post('user_email'),
            'password'=>$this->input->post('password'),
            'code' =>$this->input->post('i_code'),
        );
        $this->form_validation->set_data($data);
        if ($this->form_validation->run('reg') == FALSE)
            $this->response(null,400,validation_errors());
        $re['code']=0;
        $user_email=$this->User_model->user_email($data);
        $user_nickname=$this->User_model->user_nickname($data);
        $invite_code = $this->User_model->invite_code($data['code']);
        if(!empty($user_email)){
            $msg='该邮箱已注册！';
        }elseif (!empty($user_nickname)){
            $msg='该昵称已注册！';
        }elseif(empty($invite_code)||$invite_code['used']==0){
            $msg='邀请码已过期或不存在！';
        }else{
            $re['info']=$this->User_model->reg($data);
            $re['code']=1;
            $msg='注册成功，并自动登录！';
        }
        $this->response($re,200,$msg);
    }


}