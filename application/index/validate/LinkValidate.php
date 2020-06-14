<?php
/**
 *Created by PhpStorm
 *User: CCH
 *Date: 2020/6/13
 *Time: 16:34
 *Note: 友情链接验证类
 */

namespace app\index\validate;
use think\Validate;

class LinkValidate extends Validate
{
    protected $rule = [
        'name'      => 'require|max:25',
        'url'       => 'require|url',
        'descript'  => 'require|max:50',
        'contacts'  => 'require|max:10',
        'phone'     => 'require|regex:/^1\d{10}$/',
        'email'     => 'require|email',
    ];

    protected $message  = [
        'name.require'      =>  '网站名称必须填写',
        'name.max'          =>  '网站名称最多不能超过25个字符',
        'url.require'       =>  '网址URL必须填写',
        'url.url'           =>  '网址URL格式错误',
        'contacts.require'  =>  '姓名必须填写',
        'contacts.max'      =>  '姓名不能超过10个字符',
        'phone.require'     =>  '联系方式必须填写',
        'phone.regex'       =>  '联系方式格式错误',
        'email.require'     =>  'Email必须填写',
        'email.regex'       =>  'Email格式错误',
        'descript.require'  =>  '网站介绍必须填写',
        'descript.max'      =>  '网站介绍最多不能超过50个字符',
    ];
}