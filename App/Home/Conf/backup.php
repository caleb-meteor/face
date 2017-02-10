<?php
//区域
$areareg = 'areaid,区域id,text,0|proid,区域属性,select,1|fatherareaid,父区域ID,select,0|areaname,区域名称,text,0|areacode,区域编号,text,0|rperson,联系人,text,0|rphone,联系方式,text,0';
//设备管理
$dev = 'devid,设备ID,text,0|devname,设备名称,text,1|typeid,设备类型,select,1|areaid,归属区域,select,1|devip,设备IP,text,1|port,设备端口,text,0|rtspurl,视屏地址,text,0|serid,服务id,text,0|state,状态,text,0|remark,备注,text,0';
//告警记录
$alertrecord = 'alertrecordid,告警记录id,text,0|alerttime,告警时间,text,0|bodypicurl,全身图片,text,0|facepicurl,面部特写,text,0|employee_empid,被告警人员,text,0|alertpiccul,被告警图片,text,0|score,相识度,text,0|dev_devid,拍摄相机,text,0|serinfo_serid,服务器,text,0|capresultid,抓拍记录,text,0';