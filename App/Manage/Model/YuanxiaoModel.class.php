<?php
/**
 * author jasper.jing
 */
namespace Manage\Model;

use Think\Model\RelationModel;
class YuanxiaoModel extends RelationModel
{
    protected $trueTableName = 'yuanxiao';


    public  $country =array(
        '日本'=>'日本'
    );


    public $property=array(
        '国立'=>'国立',
        '公立'=>'公立',
        '私立'=>'私立'
    );


    public $category=array(
        '高中'=>'高中',
        '专门学校'=>'专门学校',
        '大学'=>'大学',
        '语言学校'=>'语言学校'
    );


    public $subject_category = array(
        '学部'=>'学部',
        '研究科'=>'研究科',
    );

    public $img_category= array(
        '学校图标'=>'学校图标',
        '学校默认图片'=>'学校默认图片',
        '学校图库'=>'学校图库',
        '学校背景图片'=>'学校背景图片',
    );

    public $location=array(
        '関東·甲信越'=>'関東·甲信越',
        '関西'=>'関西',
        '東海·北陸'=>'東海·北陸',
        '北海道'=>'北海道',
        '東北'=>'東北',
        '中国·四国'=>'中国·四国',
        '九州·沖縄'=>'九州·沖縄',
    );


    public $region =array(
        '茨城県'=>'茨城県',
        '栃木県'=>'栃木県',
        '群馬県'=>'群馬県',
        '埼玉県'=>'埼玉県',
        '千葉県'=>'千葉県',
        '東京都'=>'東京都',
        '神奈川県'=>'神奈川県',
        '新潟県'=>'新潟県',
        '滋賀県'=>'滋賀県',
        '京都府'=>'京都府',
        '大阪府'=>'大阪府',
        '兵庫県'=>'兵庫県',
        '奈良県'=>'奈良県',
        '和歌山県'=>'和歌山県',
        '富山県'=>'富山県',
        '石川県'=>'石川県',
        '福井県'=>'福井県',
        '岐阜県'=>'岐阜県',
        '静岡県'=>'静岡県',
        '愛知県'=>'愛知県',
        '三重県'=>'三重県',
        '北海道'=>'北海道',
        '青森県'=>'青森県',
        '岩手県'=>'岩手県',
        '宮城県'=>'宮城県',
        '鳥取県'=>'鳥取県',
        '島根県'=>'島根県',
        '岡山県'=>'岡山県',
        '広島県'=>'広島県',
        '山口県'=>'山口県',
        '徳島県'=>'徳島県',
        '香川県'=>'香川県',
        '愛媛県'=>'愛媛県',
        '高知県'=>'高知県',
        '福岡県'=>'福岡県',
        '佐賀県'=>'佐賀県',
        '長崎県'=>'長崎県',
        '熊本県'=>'熊本県',
        '大分県'=>'大分県',
        '宮崎県'=>'宮崎県',
        '鹿児島'=>'鹿児島',
        '県沖縄県'=>'県沖縄県',
    );

    protected $_validate = array(
        //必填项
        array('name_cn','require','学校中文名称必须填写!'),
        array('country','require','国家必须选择!'),
        array('property','require','属性必须选择!'),
        array('category','require','类型必须选择!'),
        //存在就验证
        array('name_en','/^[A-Za-z]+$/','学校英文名称只能输入英文！',self::VALUE_VALIDATE,'regex'),
        array('email','email','请输入正确的邮箱！',self::VALUE_VALIDATE),
        array('build_at','/^\d{4}$/','请输入成立时间！',self::VALUE_VALIDATE,'regex'),
        //array('web_site','url','请输入正确的官网地址【http://】！',self::VALUE_VALIDATE),
        array('fee_year','currency','请输入正确的年学费！',self::VALUE_VALIDATE),
        array('must_fee','currency','请输入正确的需缴纳学费！',self::VALUE_VALIDATE),

    );

    protected $_auto = array (
        array('status','1'),  // 新增的时候把status字段设置为1
        //array('created_at','getTime',1,'callback'),  // 新增的时候把status字段设置为1
        array('creator','getUser',1,'callback'),  // 新增的时候把status字段设置为1
        array('updater','getUser',3,'callback'),  // 新增的时候把status字段设置为1
        array('updated_at','getTime',3,'callback') , // 对password字段在新增和编辑的时候使md5函数处理
    );


    public function getTime()
    {
        return date('Y-m-d H:i:s',time());
    }

    public function getUser()
    {
        $user =session('xy_manager');
        return $user['id'];
    }

    public function __before_insert()
    {

    }

}