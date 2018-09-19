<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/7/26
 * Time: 16:57
 */

namespace Manage\Controller;

use Manage\Model\CallLogModel;
use Manage\Model\CallStudentModel;
use Manage\Model\CallTeacherModel;
use Manage\Model\ZhanhuiModel;
use Manage\Model\ZhanhuiRoundModel;
use Think\Controller;

class XiaoyingApiController extends Controller
{
    /**
     * 城市展会数据
     */
    public function zhanhui()
    {
        $city = (new ZhanhuiModel())->field('id,name')->where(['status'=>['eq',1]])->order('sort')->select();

        if ($city)
        {
            foreach ($city as $k => $v)
            {
                $city[$k]['round'] = (new ZhanhuiRoundModel())->field('id,time,addr,link')->where(['status'=>['eq',1],'zhanhui_id'=>['eq',$v['id']]])->select()?:[];
            }
        }

        $this->ajaxReturn(['status'=>true,'data'=>$city]);
    }

    /**
     * 发送短信验证码
     */
    public function sendsms()
    {
        $phone = I('post.phone');

        if (!$phone || !is_phone($phone) ) $this->ajaxReturn(['status'=>false,'msg'=>'手机号码格式不正确']);

        // 是否已发送
        if( ($SMS=session( 'call_sms_'.$phone)) && ($SMS['time']>time()) ) $this->ajaxReturn( ['status'=>false,'msg'=>'短信已发送,请注意查收'] );

        //随机数字
        $code = getRandStr(4,2);

        // 短信内容
        $content = "【小莺出国】您的四位数字验证码为：{$code} ;有效期为20分钟";

        // 发送短信验证码
        $send_status = sendsms($phone,$content);

        // 发送失败
        if ($send_status != 0) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试.']);

        // 发送成功,保存发送状态
        session('call_sms_'.$phone,['code'=>$code,'time'=>time()+1200]);

        $this->ajaxReturn(['status'=>true]);
    }

    /**
     * 创建打Call学员
     */
    public function callStudentCreate()
    {
        $data = I();// 接收参数

        $student = $this->checkCallStudent($data);// 验证数据

        $has_user = (new CallStudentModel())->field('id,create_time,update_time,update_user',true)->where(['phone'=>['eq',$student['phone']]])->find();// 是否已注册

        if ($has_user['id']) $this->ajaxReturn(['status'=>true,'res'=>$has_user]);// 如果已注册,返回用户信息

        // 如果是新用户 填充数据
        $student['call_num'] = 5;
        $student['share_num'] = 0;
        $student['share_status'] = 0;
        $student['create_time'] = time();
        $student['status'] = 1;

        try{
            $res = (new CallStudentModel())->add($student);// 插入
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请联系管理员']);// 如果添加失败

        $student['id'] = $res;

        $this->ajaxReturn(['status'=>true,'res'=>$student]);// 注册成功,返回用户信息
    }

    /**
     * 验证打Call学员的数据
     */
    private function checkCallStudent($student)
    {

        // 过滤
        $data['name'] = $student['name'];
        $data['phone'] = $student['phone'];
        $data['code'] = $student['code'];

        // 验证
        if (!$student['name'] || strlen($student['name']) < 4) $this->ajaxReturn(['status'=>false,'msg'=>'用户名不可小于4个字符']);

        if (!$student['phone'] || !is_phone($student['phone'])) $this->ajaxReturn(['status'=>false,'msg'=>'手机号码格式不正确']);

        // 短信验证码
        if( !($SMS = session('call_sms_'.$data['phone'])) || ($SMS['code'] != $data['code']) ) $this->ajaxReturn( ['status'=>false,'msg'=>'短信验证码错误'] );// 验证码无效

        if( $SMS['time'] < time() )
        {
            session('call_sms_'.$data['phone'],null);
            $this->ajaxReturn( ['status'=>false,'remark'=>'短信验证码过期，请重新获取'] );// 验证码过期
        }

        // 验证成功,清空session
        session('call_sms_'.$data['phone'],null);

        return $data;
    }

    /**
     * 更新打Call学员的数据
     */
    private function updateCallStudent($student_id,$phone)
    {
        $data = I();// 接收参数

        if (!$data || !$data['ui'] || !$data['phone']) $this->ajaxReturn(['status'=>false,'res'=>'缺少参数']);// 缺少参数

        // 根据ID和手机号码获取用户信息
        $has_user = (new CallStudentModel())->field('id,create_time,update_time,update_user',true)->where(['phone'=>['eq',$student['phone']]])->find();// 是否已注册

        $this->ajaxReturn(['status'=>true,'res'=>$student]);// 注册成功,返回用户信息
    }

    /**
     * 新建打call记录
     * 1.更新老师的call值
     * 2.添加打call记录
     * 3.更新用户call值
     */
    public function createCallLog()
    {
        $data = I();// 接收参数

        // 过滤
        $user_id = intval($data['ui']);// ui = user_id 用户编号
        $teacher_id = intval($data['ti']);// ti = teacher_id 教师编号
        $user_phone = $data['p'];// p = phone 手机
        $call_num = intval($data['c']);// c = call_num call值

        if (!$user_id || !$teacher_id || !$user_phone || !$call_num) $this->ajaxReturn(['status'=>false,'msg'=>'非法请求 | 100003']);// 缺少参数

        $user = (new CallStudentModel())->find($user_id);// 获取当前用户

        if (!$user) $this->ajaxReturn(['status'=>false,'msg'=>'非法请求 | 100004']);// 用户不存在

        if ($user_phone != $user['phone']) $this->ajaxReturn(['status'=>false,'msg'=>'非法请求 | 100005']);// 用户和手机号码不匹配

        $teacher = (new CallTeacherModel())->find($teacher_id);// 获取老师信息

        if (!$teacher) $this->ajaxReturn(['status'=>false,'msg'=>'未找到要打call的老师!']);// 老师不存在

        if ($user['call_num'] < $call_num) $this->ajaxReturn(['status'=>false,'msg'=>'Call值不足!']);// Call值不足

        // 更新老师的call值
        $teacher_save_data['call_num'] = $teacher['call_num'] + $call_num;// 新call值

        try{
            $update_teacher = (new CallTeacherModel())->where(['id'=>['eq',$teacher_id]])->save($teacher_save_data);// 更新老师的call值
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>'数据异常,请稍后再试!','error'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$update_teacher) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护中,请稍后再试!']);// 系统异常

        // 添加打call记录
        $call_log_data['stu_id'] = $user_id;
        $call_log_data['tea_id'] = $teacher_id;
        $call_log_data['call_num'] = $call_num;
        $call_log_data['create_time'] = time();

        (new CallLogModel())->add($call_log_data);// 插入打call记录

        // 更新用户call值
        $student_save_data['call_num'] = $user['call_num'] - $call_num;// 新call值

        (new CallStudentModel())->where(['id'=>['eq',$user_id]])->save($student_save_data);// 更新用户的call值

        $this->ajaxReturn(['status'=>true,'msg'=>'打call成功!']);// 打call成功
    }

    /**
     * 分享成功回调
     */
    public function shareCallback()
    {
        // 接收用户ID的参数
        $par = I('');
        $user_id = $par['ui'];

        if (!$user_id) $this->ajaxReturn(['status'=>false,'msg'=>'非法请求 | 100003']);// 缺少参数

        $user = (new CallStudentModel())->find($user_id);// 获取当前用户

        if (!$user) $this->ajaxReturn(['status'=>false,'msg'=>'非法请求 | 100004']);// 用户不存在

        if ($user['share_status'] == 1) $this->ajaxReturn(['status'=>false,'msg'=>'每天只能通过分享获取一次call值,请明天再来吧!']);// 当日已分享

        $update_user_data['call_num'] = $user['call_num'] + 10;// 分享成功加10个call值

        $update_user_data['share_status'] = 1;// 分享成功后修改分享状态 每天只能通过分享获取一次call值

        $update_user_data['share_num'] = $user['share_num'] + 1;// 更新分享次数

        (new CallStudentModel())->where(['id'=>['eq',$user_id]])->save($update_user_data);// 更新用户信息

        $this->ajaxReturn(['status'=>true,'msg'=>'分享成功,10个Call值已进入您的账户!']);// 打call成功

    }

    /**
     * 重置用户的分享状态
     * TODO 活动结束后,可删除此方法和相关的定时任务 (活动结束时间待定)
     */
    public function resetShareStatus()
    {
        (new CallStudentModel())->where(['share_status'=>['eq',1]])->save(['share_status'=>0]);// 每天00.00分重置用户的分享状态为0
    }
    
    /**
     * 读取打call记录
     */
    public function callLog()
    {
        $call_log = (new CallLogModel())->field('id', true)->order('create_time desc')->select();

        if ($call_log)
        {
            foreach ($call_log as $k => $v)
            {
                $call_log[$k]['student'] = (new CallStudentModel())->find($v['stu_id'])['name']?:' - ';
                $call_log[$k]['teacher'] = (new CallTeacherModel())->find($v['tea_id'])['name']?:' - ';
                $call_log[$k]['create_time'] = date('m-d H:i:s',$v['create_time']);
                $call_log[$k]['title'] = $call_log[$k]['create_time'].' | '.$call_log[$k]['student'].' 为 '.$call_log[$k]['teacher'].' 打call x '.$call_log[$k]['call_num'];
            }
        }

        $this->ajaxReturn(['status'=>true,'data'=>$call_log]);
    }

    /**
     * 读取老师列表
     */
    public function callTeacher()
    {
        $no_field = 'sort,create_time,create_user,update_time,update_user,status';// 要排除的字段

        $teacher = (new CallTeacherModel())->field($no_field,true)->where(['status'=>['eq',1]])->order('sort')->select();// 获取老师列表

        // 如果需要 获得的支持的次数 和 人数
        if ($teacher)
        {
            foreach ($teacher as $k => $v)
            {
//                $teacher[$k]['number_of_call'] = (new CallLogModel())->where(['tea_id'=>['eq',$v['id']]])->count();// 支持次数
//                $teacher[$k]['number_of_supporter'] = count((new CallLogModel())->where(['tea_id'=>['eq',$v['id']]])->group('stu_id')->select());// 支持人数
                $number_of_call = (new CallLogModel())->where(['tea_id'=>['eq',$v['id']]])->count();// 支持次数
                $number_of_supporter = count((new CallLogModel())->where(['tea_id'=>['eq',$v['id']]])->group('stu_id')->select());// 支持人数
                if ($number_of_call && $number_of_supporter)
                {
                    $teacher[$k]['outer'] = '近期获得投票 '.$number_of_call.' 次,'.'获得 '.$number_of_supporter.' 位学员的喜欢';
                }else{
                    $teacher[$k]['outer'] = '暂未获得关注~';
                }
            }
        }

        $this->ajaxReturn(['status'=>true,'data'=>$teacher]);
    }

    /**
     * 学员打call排行榜
     */
    public function callStudent()
    {
        $no_field = 'phone,share_status,create_time,create_user,update_time,update_user,status';// 要排除的字段

        $students = (new CallStudentModel())->field($no_field,true)->where(['status'=>['eq',1]])->order('share_num desc')->select();// 获取学员列表

        // 如果需要 统计历史call_num总数 和 已经用掉的call_num
        if ($students)
        {
            foreach ($students as $k => $v)
            {
//                $students[$k]['count'] = ($v['share_num'] * 10) + 5;// 共获得call值
//                $students[$k]['use'] = $students[$k]['count'] - $v['call_num'];// 已经使用掉的call值
//                $students[$k]['number_of_call'] = (new CallLogModel())->where(['stu_id'=>['eq',$v['id']]])->count();// 支持次数
//                $students[$k]['number_of_teacher'] = count((new CallLogModel())->where(['stu_id'=>['eq',$v['id']]])->group('tea_id')->select());// 支持人数
                $count = ($v['share_num'] * 10) + 5;// 共获得call值
                $use = $count - $v['call_num'];// 已经使用掉的call值
                $number_of_call = (new CallLogModel())->where(['stu_id'=>['eq',$v['id']]])->count();// 打call次数
                $number_of_teacher = count((new CallLogModel())->where(['stu_id'=>['eq',$v['id']]])->group('tea_id')->select());// 支持人数
                $students[$k]['outer'] = '共获得 '.$count.' 个call值,近期为 '.$number_of_teacher.' 位老师打call '.$number_of_call.' 次,共使用 '.$use.' 个call值';// 不一定需要的
            }
        }

        // 按照已经用掉的call_num 倒序,计算出排行榜
        sortArrByField($students,'use',true);

        $this->ajaxReturn(['status'=>true,'data'=>$students]);
    }

    // TODO 微信,微博,等各大平台分享功能对接
}