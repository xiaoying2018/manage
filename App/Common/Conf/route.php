<?php

	return array(
		/* 路由设置 */
    	'URL_ROUTER_ON' => true,// 开启路由	

    	 'URL_ROUTE_RULES' => array(
        // login
        'login$' => 'Manage/Login/show',// 登录页面
        'dologin' => 'Manage/Login/dologin',// 登录验证
        'outlogin' => 'Manage/Login/outlogin',// 退出登录

        // 站点配置
        'basesetting$' => 'Manage/Basesetting/index',// 站点配置页

        // 用户
        'user$' => 'Manage/User/index',// 用户列表页
        'user/create' => 'Manage/User/create',// 用户创建
        'user/edit' => 'Manage/User/edit',// 用户编辑
        'user/delete' => 'Manage/User/delete',// 用户删除
        'user/changestatus' => 'Manage/User/changestatus',// 用户状态更新
        'user/resetpassword' => 'Manage/User/resetpassword',// 用户密码重置

        // 个人资料
        'mysetting' => 'Manage/My/mysetting',// 修改个人资料
        'setmypassword' => 'Manage/My/setmypassword',// 修改自己的密码

        // 角色
        'role$' => 'Manage/Role/index',// 角色列表页
        'role/create' => 'Manage/Role/create',// 角色创建
        'role/edit' => 'Manage/Role/edit',// 展示角色编辑页面
        'role/delete' => 'Manage/Role/delete',// 角色删除
        'role/changestatus' => 'Manage/Role/changestatus',// 角色状态更新

        // 权限
        'premission$' => 'Manage/Premission/index',// 权限列表页
        'premission/create' => 'Manage/Premission/create',// 权限创建
        'premission/edit' => 'Manage/Premission/edit',// 展示权限编辑页面
        'premission/delete' => 'Manage/Premission/delete',// 权限删除

        // 展会城市
        'zhanhui$' => 'Manage/Zhanhui/index',// 展会城市列表页
        'zhanhui/create' => 'Manage/Zhanhui/create',// 创建展会城市
        'zhanhui/edit' => 'Manage/Zhanhui/edit',// 修改展会城市
        'zhanhui/delete' => 'Manage/Zhanhui/delete',// 删除展会城市
        'zhanhui/changestatus' => 'Manage/Zhanhui/changestatus',// 更新展会城市状态

        // 展会场次
        'round/view' => 'Manage/Zhanhui/roundView',// 展会场次弹窗
        'round/create' => 'Manage/Zhanhui/roundCreate',// 展会场次添加
        'round/edit' => 'Manage/Zhanhui/roundedit',// 修改展会场次
        'round/delete' => 'Manage/Zhanhui/roundDelete',// 展会场次删除
        'round/changestatus' => 'Manage/Zhanhui/roundchangestatus',// 更新展会场次状态

        // 打call 名师
        'call/teacher$' => 'Manage/Call/index',// 名师列表页
        'call/teacher/create' => 'Manage/Call/teacherCreate',// 创建名师
        'call/teacher/edit' => 'Manage/Call/teacherEdit',// 修改名师
        'call/teacher/delete' => 'Manage/Call/teacherDelete',// 删除名师
        'call/teacher/changestatus' => 'Manage/Call/teacherChangestatus',// 更新名师状态
        // 打call 学员
        'call/student$' => 'Manage/Call/studentShow',// 学员列表页
        // 打call 日志
        'call/log$' => 'Manage/Call/calllog',// 打Call日志列表页

        /** =========================================================
         * 无需经过权限判断的
         ========================================================== */

        // 前端页面所需系统接口 需要绕过权限判断的接口 TODO 后面抽空把列表页接口放在列表页同方法中 (AJAX请求返回数据,普通请求返回视图). dragon.
        'pretree/api' => 'Manage/FrontEndApi/preTreeApi',// 权限树数据接口
        'user/search' => 'Manage/FrontEndApi/usersearch',// 用户列表数据接口
        'role/search' => 'Manage/FrontEndApi/rolesearch',// 角色列表数据接口
        'zhanhui/search' => 'Manage/FrontEndApi/zhanhuisearch',// 角色列表数据接口
        'call/teacher/search' => 'Manage/FrontEndApi/callteachersearch',// 名师数据接口
        'call/student/search' => 'Manage/FrontEndApi/callstudentsearch',// 学员数据接口
        'call/log/search' => 'Manage/FrontEndApi/calllogsearch',// 打Call日志数据接口
        'premission/search' => 'Manage/FrontEndApi/premissionsearch',// 权限列表数据接口
        'role/bind/pre' => 'Manage/FrontEndApi/updatePreApi',// 角色关联权限接口

        // 图片上传接口
        'uploadone' => 'Manage/FrontEndApi/uploadone',// 普通图片上传接口
        'uploadkoubei' => 'Manage/FrontEndApi/uploadkoubei',// 带压缩小图的图片上传接口

        // wait = 已完成,但暂未投入应用的功能
        // 提供给其他站点的数据接口 xy = xiaoying.net
        'xy/zhanhui' => 'Manage/XiaoyingApi/zhanhui',// 展会数据 wait
        'xy/call/stu/create' => 'Manage/XiaoyingApi/callStudentCreate',// 创建打Call学员 wait
        'xy/call/stu/update' => 'Manage/XiaoyingApi/updateCallStudentInfo',// 更新打Call学员 wait
        'xy/call/create' => 'Manage/XiaoyingApi/createCallLog',// 创建打Call记录 wait
        'xy/call/share' => 'Manage/XiaoyingApi/shareCallback',// 分享成功回调方法 wait
        'xy/call/log' => 'Manage/XiaoyingApi/callLog',// 打call日志 wait
        'xy/call/teacher' => 'Manage/XiaoyingApi/callTeacher',// 打call老师列表 wait
        'xy/call/student' => 'Manage/XiaoyingApi/callStudent',// 打call学员排行榜 wait
        'crontab/reset/sharestatus' => 'Manage/XiaoyingApi/resetShareStatus',// 定时更新分享状态 wait
        'crontab/update/studentcallnum' => 'Manage/XiaoyingApi/addStudentCallNum',// 定时更新学员call值 每天加5个 wait
        'xy/call/sendsms' => 'Manage/XiaoyingApi/sendsms',// 发送短信验证码 wait

             ##资讯前端接口
             'article/catesearch'=>'Manage/FrontEndApi/catesearch',  //分类列表
             'article/contentssearch'=>'Manage/FrontEndApi/contentssearch',  //内容列表
             'article/detailsearch'=>'Manage/FrontEndApi/detailsearch',  //内容详情
             'vote'=>'Manage/FrontEndApi/vote',  //点赞
             'reads'=>'Manage/FrontEndApi/reads',  //阅读量
             ##地区联动
             'getarea'=>'Manage/FrontEndApi/getarea',
             ##日本院校库前台接口
             'getlangschool'=>'Manage/FrontEndApi/schoolsearch1',   //语言学校列表
             'getjapschool'=>'Manage/FrontEndApi/schoolsearch2',   //日本学校列表
             'getschooldetail'=>'Manage/FrontEndApi/schooldetail',   //日本语言学校和日本大学学校详情
             'clicks'=>'Manage/FrontEndApi/clicks',  //增加浏览次数
             'school/xueli'=>'Manage/FrontEndApi/getxueli', //专业学历列表
             'school/cate'=>'Manage/FrontEndApi/getcate', //  专业分类
             'school/schoolprogram'=>'Manage/FrontEndApi/getschoolprogram', //获取学校的专业
             ##韩国
             'getkoreaschool'=>'Manage/FrontEndApi/schoolsearch3',//学校列表
             'getkoreadetail'=>'Manage/FrontEndApi/koreaschooldetail',//学校详情
             ##新加坡
             'getsgpschool'=>'Manage/FrontEndApi/schoolsearch4',//学校列表
             'getsgpdetail'=>'Manage/FrontEndApi/sgpschooldetail',//学校详情
             'sgpmajordetail'=>'Manage/FrontEndApi/sgpmajordetail',//学校专业
             ##推荐位图片前台获取
             'getrecom'=>'Manage/FrontEndApi/getrecom',

             // 提供给其他站点的数据接口 ixy = i.xiaoying.net
             'ixy/koubei/get' => 'Manage/Ixiaoying/koubei',// 口碑
             'ixy/koubei/upstar' => 'Manage/Ixiaoying/upstar',// 口碑点赞

             //标签管理
             'tags/index' => 'Manage/Tags/index',  //分类列表
             'tags/create'=>'Manage/Tags/add', //新增分类
             'tags/delete'=>'Manage/Tags/delete', //删除

             //日本学校管理
             'school/getone'=>'Manage/School/getone',  //列表
             'school/index'=>'Manage/School/index',  //列表
             'school/search'=>'Manage/School/search',
             ##语言学校
             'school/addlschool'=>'Manage/School/add1',    //新增语言学校
             'school/delete1'=>'Manage/School/delete1',
             'school/edit1'=>'Manage/School/edit1',     //语言学校修改
             ##日本大学
             'school/addcschool'=>'Manage/School/add2',    //新增语言学校
             'school/edit2'=>'Manage/School/edit2',     //语言学校修改

             ##韩国学校
             'koreaschool/search'=>'Manage/Koreaschool/search',
             'koreaschool/add'=>'Manage/Koreaschool/add',    //新增韩国学校
             'koreaschool/edit'=>'Manage/Koreaschool/edit',  //修改韩国学校
             'koreaschool/delete'=>'Manage/Koreaschool/delete',  //修改韩国学校

             'uploadall'=>'Manage/FrontEndApi/uploadall',//院校多图片上传接口
             'delfile'=>'Manage/File/delfile',        //删除图片
             ##新加坡学校
             'sgpschool/search'=>'Manage/Sgpschool/search',
             'sgpschool/add'=>'Manage/Sgpschool/add',    //新增新加坡学校
             'sgpschool/edit'=>'Manage/Sgpschool/edit',  //修改新加坡学校
             'sgpschool/delete'=>'Manage/Sgpschool/delete',  //修改新加坡学校

             //文章资讯
             'articlecate/search'=>'Manage/Articlecate/search',
             'articlecate/addcate'=>'Manage/Articlecate/addcate', //新增分类
             'articlecate/editcate'=>'Manage/Articlecate/editcate',      //分类修改
             'articlecate/deletecate'=>'Manage/Articlecate/deletecate', //删除分类
             'articlecate/getallcate' =>'Manage/Articlecate/getallcate',   //所有分类

             'article/search'=>'Manage/Article/search', //列表接口
             'article/create'=>'Manage/Article/add',   //新增
             'article/edit'=>'Manage/Article/edit',   //修改
             'article/delete'=>'Manage/Article/delete',  //删除

             //推荐位图片管理
             'recom/index'=>'Manage/Recom/index',   //首页
             'recom/search'=>'Manage/Recom/search', //列表接口
             'recom/create'=>'Manage/Recom/add',   //新增
             'recom/edit'=>'Manage/Recom/edit',   //修改
    ),
	);

