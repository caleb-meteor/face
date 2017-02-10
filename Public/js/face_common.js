/**
 * 比较两个时间先后/请保证两者传入对象的一致性最多精确到秒
 * @param  {object} f_time Date对象
 * @param  {object} s_time Date对象
 * @return {boolean} true 前者比后者大 其他情况返回false
 */
function checkTime(f_time,s_time){
	var f_mtime = f_time.getTime();
	var s_mtime = s_time.getTime();
	if(f_mtime > s_mtime){
		return true;
	}else{
		return false;
	}
	//return f_mtime > s_mtime ? true : false ;
}
/**
 * 格式化时间 将字符串形式的时间返回Date对象  2016-12-13 10:55:23
 * @param  {string} date 时间
 * @return {object}      Date对象
 */
function formatDate (dateSting) {
	var datetime = dateSting.split(' ');
	var date     = datetime[0].split('-');
	var time     = datetime[1].split(':');
	var year  = date[0];
	var month = date[1]-1;
	var day   = date[2];
	var hour  = time[0] ? time[0] : '';
	var minute  = time[1] ? time[1] : '';
	var second  = time[2] ? time[2] : '';
	return new Date(year,month,day,hour,minute,second);
}

function lastDay () {
	var nowDate = new Date(new Date()-24*60*60*1000);
	var tm = nowDate.getMonth() + 1;
	var m = tm > 9 ? tm : '0' + tm; //月
	var d = nowDate.getDate() > 9 ? nowDate.getDate() : '0'+nowDate.getDate(); //日
	var h = nowDate.getHours() > 9 ? nowDate.getHours() : '0'+nowDate.getHours(); //时
	var i = nowDate.getMinutes() > 9 ? nowDate.getMinutes() : '0'+nowDate.getMinutes(); //分
	var s = nowDate.getSeconds() > 9 ? nowDate.getSeconds() : '0'+nowDate.getSeconds(); //秒
	return  nowDate.getFullYear()+'-'+ m +'-'+ d +' '+h+':'+i+':'+s;
}
function nowTime() {
	var nowDate = new Date();
	var tm = nowDate.getMonth() + 1;
	var m = tm > 9 ? tm : '0' + tm; //月
	var d = nowDate.getDate() > 9 ? nowDate.getDate() : '0'+nowDate.getDate(); //日
	var h = nowDate.getHours() > 9 ? nowDate.getHours() : '0'+nowDate.getHours(); //时
	var i = nowDate.getMinutes() > 9 ? nowDate.getMinutes() : '0'+nowDate.getMinutes(); //分
	var s = nowDate.getSeconds() > 9 ? nowDate.getSeconds() : '0'+nowDate.getSeconds(); //秒
	return  nowDate.getFullYear()+'-'+ m +'-'+ d +' '+h+':'+i+':'+s;
}