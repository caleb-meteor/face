<?php
//数据库所有表字段
$alertcycle = array('alertsetid','alertweek','btime','etime');
$alertrecord = array('alertrecordid','alerttime','bodypicurl','facepicurl','employee_empid','alertpiccul','score','dev_devid','serinfo_serid','capresultid');
$alertset = array('alertsetid','name','devid','libid','score','alerttype','btime','etime','state');
$areapro = array('proid','proname');
$areareg = array('areaid','proid','fatherareaid','areaname','areacode','rperson','rphone');
$capresult = array('capresultid','caprecid','libid','libnum','score','serinfo_serid','isalert');
$capturerecord = array('caprecid','captime','bodypicurl','facepicurl','quality','dev_devid');
$conitem = array('itemid','itemname','fitemval','sitemval');
$dev = array('devid','devname','typeid','areaid','devip','port','rtspurl','serid','state','remark');
$devlog = array('recordid','devname','state','recordtime','remark','typeid','devid');
$devtype = array('typeid','typename');
$employee = array('empid','areaid','name','code','sex','phone','email','libnum','remark');
$employeepho = array('employeephoid','empid','photo');
$functionbuttonreg = array('funbutid','funid','butcode');
$functionreg = array('funid','prefunid','funname','url','ordernum','iconcls');
$libtoser = array('libid','serid');
$photolib = array('libid','libcode','libname');
$phototolib = array('employeephoid','serid','libid','libnum');
$rolereg = array('roleid','rolename','remark','functionlist');
$serinfo = array('serid','typeid','sername','serip','serport','remark','state','pserid');
$userarea = array('userid','areaid','rules');
$userreg = array('userid','areaid','username','userpassword','roleid','bindingip','clientip','truename','sex','mobile','email','usertag','fatherid','state');