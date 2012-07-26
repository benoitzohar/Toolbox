<?php

$encoding_key = '';


/************ CLASSES *************/
class SocketClient {
	private $s;private $si='';private $sp='';private $st=array('sec'=>10,'usec'=>500000);	
	public function __construct($c) { $this->si = $c['address'];$this->sp = $c['port']; }
	public function __destruct() {if ($this->s) socket_close($this->s);}
	public function sendData($data) {
		if (empty($data)) return false;
		if (!$this->s) { 
			if (empty($this->si)) return 'Socket IP is undefined';
			if (empty($this->sp)) return 'Socket Port is undefined.';		
			$this->s =  socket_create(AF_INET, SOCK_STREAM, SOL_TCP); 
			if ($this->s === false) return socket_strerror(socket_last_error());
			socket_set_option($this->s,SOL_SOCKET,SO_RCVTIMEO,$this->st);
			socket_set_option($this->s,SOL_SOCKET,SO_SNDTIMEO,$this->st);
			$connect_result = socket_connect($this->_socket, $this->si, $this->sp);
			if ($connect_result === false) return "Unable to connect to the socket.";
		}
		if (is_array($data)) $data = @json_encode($data);
		$data .= chr(13).chr(10);
		$write_result = socket_write($this->_socket, $data, strlen($data));if (!$write_result) return "Unable to send data to the socket.";
		$out=$buffer=$asc='';
		while($asc != PHP_EOL) {socket_recv($this->_socket, $buffer, 2920,0);$asc=substr($buffer, -1,1); $out.=$buffer;}if (empty($out)) return false;
		$out = @json_decode(utf8_encode(trim($out)),true);if (empty($out)) return 'Unable to parse (JSON) socket result: '.$out;
		return $out;
	}
}



/*********** ACTIONS **************/

if (!empty($_REQUEST['action'])) {
	
	switch ($_REQUEST['action']) {
		
		case 'restart_apache' :
			$ot = shell_exec('echo "sudo /etc/init.d/apache2 restart" | sudo at now');
			echo 'Apache is restarting ... '.$ot;
			exit();
		break;
		
		case 'restart_socket' : 
			$ot = shell_exec('echo "sudo /etc/init.d/dsockbackoffice restart" | sudo at now');
			echo 'The socket server is restarting ... '.$ot;
			exit();
		break;
		
	}
	
}


$js_code = '

	var row_limit = 7;
	var xhr_fields = ["xhr_url"];
	for(var i=0;i<=row_limit;i++) {xhr_fields.push("xhr_key"+i);xhr_fields.push("xhr_val"+i);}
	
	function getPostDataXHR() {
		var d = {};
		for(var i=0;i<=row_limit;i++) {			
			var k = $("#xhr_key"+i).val();
			var v = $("#xhr_val"+i).val();
			if (k && v) d[k] = v;
		}
		return d;
	}
	
	function getURLXHR(get) { 
		var u = $("#xhr_url").val();
		if (u.substr(0,4) != "http") u = "http://"+u;
		if (get) {
			if (u.substr(-1, 1) != "?") u += "?";
			for(var i=0;i<=row_limit;i++) {			
				var k = $("#xhr_key"+i).val();
				var v = $("#xhr_val"+i).val();
				if (k || v) {
					if (i>0) u += "&";
					u += k+"="+v;
				}
			}
		}
		return u;
	}
	
	function doReqXHR() {
		$("#loader").show();
		$("#xhr_res").empty();
		var t = $("input[name=\"method_inp\"]:checked").val();
		try {
			if (t == "GET") $.get(getURLXHR(true),fillResXHR);
			else if (t == "POST") $.post(getURLXHR(),getPostDataXHR(),fillResXHR);
		} catch(e) { fillResXHR(e);}
	}
	
	function fillResXHR(d) {
		$("#xhr_res").text(d);
		
		$("#loader").hide();	
	}
	function htmlize() {
		$("#xhr_res").html($("#xhr_res").text().replace(/\n/gi,"<br />"));
	}


	function doReqRS(r) {		
		$("#loader").show();
		$("#rs_res").load("'.$_SERVER["SCRIPT_NAME"].'",{action:r});
		var d = new Date();
		var d_txt = d.getHours()+"h"+d.getMinutes()+"m "+d.getSeconds()+"s";
		$("#"+r+" .last_done span").text(d_txt);
		if (localStorage) localStorage[r] = d_txt;
		$(".box").append("<div class=\"hider\"></div>");
		setTimeout(function() {
			$("#rs_res").empty();
			$(".hider").remove();
			$("#loader").hide();
		},3000);
	}


// rot 13
var rot13map;
function rot13init(){
  var map = new Array();var s = "abcdefghijklmnopqrstuvwxyz"; 
  for(i=0; i<s.length; i++)map[s.charAt(i)]=s.charAt((i+13)%26);
  for (i=0; i<s.length; i++)map[s.charAt(i).toUpperCase()]=s.charAt((i+13)%26).toUpperCase();
  return map;
}
function rot13(a){
  if (!rot13map)rot13map=rot13init();s = "";
  for (i=0;i<a.length;i++){var b = a.charAt(i);s += (b>=\'A\' && b<=\'Z\' || b>=\'a\' && b<=\'z\' ? rot13map[b] : b);}
  return s;
}

// md5
function array(a){for(i=0;i<a;i++)this[i]=0;this.length=a}function integer(a){return a%(4294967295+1)}function shr(a,b){a=integer(a);b=integer(b);if(a-2147483648>=0){a=a%2147483648;a>>=b;a+=1073741824>>b-1}else a>>=b;return a}function shl1(a){a=a%2147483648;if(a&1073741824==1073741824){a-=1073741824;a*=2;a+=2147483648}else a*=2;return a}function shl(a,b){a=integer(a);b=integer(b);for(var c=0;c<b;c++)a=shl1(a);return a}function and(a,b){a=integer(a);b=integer(b);var c=a-2147483648;var d=b-2147483648;if(c>=0)if(d>=0)return(c&d)+2147483648;else return c&b;else if(d>=0)return a&d;else return a&b}function or(a,b){a=integer(a);b=integer(b);var c=a-2147483648;var d=b-2147483648;if(c>=0)if(d>=0)return(c|d)+2147483648;else return(c|b)+2147483648;else if(d>=0)return(a|d)+2147483648;else return a|b}function xor(a,b){a=integer(a);b=integer(b);var c=a-2147483648;var d=b-2147483648;if(c>=0)if(d>=0)return c^d;else return(c^b)+2147483648;else if(d>=0)return(a^d)+2147483648;else return a^b}function not(a){a=integer(a);return 4294967295-a}function F(a,b,c){return or(and(a,b),and(not(a),c))}function G(a,b,c){return or(and(a,c),and(b,not(c)))}function H(a,b,c){return xor(xor(a,b),c)}function I(a,b,c){return xor(b,or(a,not(c)))}function rotateLeft(a,b){return or(shl(a,b),shr(a,32-b))}function FF(a,b,c,d,e,f,g){a=a+F(b,c,d)+e+g;a=rotateLeft(a,f);a=a+b;return a}function GG(a,b,c,d,e,f,g){a=a+G(b,c,d)+e+g;a=rotateLeft(a,f);a=a+b;return a}function HH(a,b,c,d,e,f,g){a=a+H(b,c,d)+e+g;a=rotateLeft(a,f);a=a+b;return a}function II(a,b,c,d,e,f,g){a=a+I(b,c,d)+e+g;a=rotateLeft(a,f);a=a+b;return a}function transform(a,b){var c=0,d=0,e=0,f=0;var g=transformBuffer;c=state[0];d=state[1];e=state[2];f=state[3];for(i=0;i<16;i++){g[i]=and(a[i*4+b],255);for(j=1;j<4;j++){g[i]+=shl(and(a[i*4+j+b],255),j*8)}}c=FF(c,d,e,f,g[0],S11,3614090360);f=FF(f,c,d,e,g[1],S12,3905402710);e=FF(e,f,c,d,g[2],S13,606105819);d=FF(d,e,f,c,g[3],S14,3250441966);c=FF(c,d,e,f,g[4],S11,4118548399);f=FF(f,c,d,e,g[5],S12,1200080426);e=FF(e,f,c,d,g[6],S13,2821735955);d=FF(d,e,f,c,g[7],S14,4249261313);c=FF(c,d,e,f,g[8],S11,1770035416);f=FF(f,c,d,e,g[9],S12,2336552879);e=FF(e,f,c,d,g[10],S13,4294925233);d=FF(d,e,f,c,g[11],S14,2304563134);c=FF(c,d,e,f,g[12],S11,1804603682);f=FF(f,c,d,e,g[13],S12,4254626195);e=FF(e,f,c,d,g[14],S13,2792965006);d=FF(d,e,f,c,g[15],S14,1236535329);c=GG(c,d,e,f,g[1],S21,4129170786);f=GG(f,c,d,e,g[6],S22,3225465664);e=GG(e,f,c,d,g[11],S23,643717713);d=GG(d,e,f,c,g[0],S24,3921069994);c=GG(c,d,e,f,g[5],S21,3593408605);f=GG(f,c,d,e,g[10],S22,38016083);e=GG(e,f,c,d,g[15],S23,3634488961);d=GG(d,e,f,c,g[4],S24,3889429448);c=GG(c,d,e,f,g[9],S21,568446438);f=GG(f,c,d,e,g[14],S22,3275163606);e=GG(e,f,c,d,g[3],S23,4107603335);d=GG(d,e,f,c,g[8],S24,1163531501);c=GG(c,d,e,f,g[13],S21,2850285829);f=GG(f,c,d,e,g[2],S22,4243563512);e=GG(e,f,c,d,g[7],S23,1735328473);d=GG(d,e,f,c,g[12],S24,2368359562);c=HH(c,d,e,f,g[5],S31,4294588738);f=HH(f,c,d,e,g[8],S32,2272392833);e=HH(e,f,c,d,g[11],S33,1839030562);d=HH(d,e,f,c,g[14],S34,4259657740);c=HH(c,d,e,f,g[1],S31,2763975236);f=HH(f,c,d,e,g[4],S32,1272893353);e=HH(e,f,c,d,g[7],S33,4139469664);d=HH(d,e,f,c,g[10],S34,3200236656);c=HH(c,d,e,f,g[13],S31,681279174);f=HH(f,c,d,e,g[0],S32,3936430074);e=HH(e,f,c,d,g[3],S33,3572445317);d=HH(d,e,f,c,g[6],S34,76029189);c=HH(c,d,e,f,g[9],S31,3654602809);f=HH(f,c,d,e,g[12],S32,3873151461);e=HH(e,f,c,d,g[15],S33,530742520);d=HH(d,e,f,c,g[2],S34,3299628645);c=II(c,d,e,f,g[0],S41,4096336452);f=II(f,c,d,e,g[7],S42,1126891415);e=II(e,f,c,d,g[14],S43,2878612391);d=II(d,e,f,c,g[5],S44,4237533241);c=II(c,d,e,f,g[12],S41,1700485571);f=II(f,c,d,e,g[3],S42,2399980690);e=II(e,f,c,d,g[10],S43,4293915773);d=II(d,e,f,c,g[1],S44,2240044497);c=II(c,d,e,f,g[8],S41,1873313359);f=II(f,c,d,e,g[15],S42,4264355552);e=II(e,f,c,d,g[6],S43,2734768916);d=II(d,e,f,c,g[13],S44,1309151649);c=II(c,d,e,f,g[4],S41,4149444226);f=II(f,c,d,e,g[11],S42,3174756917);e=II(e,f,c,d,g[2],S43,718787259);d=II(d,e,f,c,g[9],S44,3951481745);state[0]+=c;state[1]+=d;state[2]+=e;state[3]+=f}function init(){count[0]=count[1]=0;state[0]=1732584193;state[1]=4023233417;state[2]=2562383102;state[3]=271733878;for(i=0;i<digestBits.length;i++)digestBits[i]=0}function update(a){var b,c;b=and(shr(count[0],3),63);if(count[0]<4294967295-7)count[0]+=8;else{count[1]++;count[0]-=4294967295+1;count[0]+=8}buffer[b]=and(a,255);if(b>=63){transform(buffer,0)}}function finish(){var a=new array(8);var b;var c=0,d=0,e=0;for(c=0;c<4;c++){a[c]=and(shr(count[0],c*8),255)}for(c=0;c<4;c++){a[c+4]=and(shr(count[1],c*8),255)}d=and(shr(count[0],3),63);e=d<56?56-d:120-d;b=new array(64);b[0]=128;for(c=0;c<e;c++)update(b[c]);for(c=0;c<8;c++)update(a[c]);for(c=0;c<4;c++){for(j=0;j<4;j++){digestBits[c*4+j]=and(shr(state[c],j*8),255)}}}function hexa(a){var b="0123456789abcdef";var c="";var d=a;for(hexa_i=0;hexa_i<8;hexa_i++){c=b.charAt(Math.abs(d)%16)+c;d=Math.floor(d/16)}return c}function MD5(a){var b,c,d,e,f,g,h;init();for(d=0;d<a.length;d++){b=a.charAt(d);update(ascii.lastIndexOf(b))}finish();e=f=g=h=0;for(i=0;i<4;i++)e+=shl(digestBits[15-i],i*8);for(i=4;i<8;i++)f+=shl(digestBits[15-i],(i-4)*8);for(i=8;i<12;i++)g+=shl(digestBits[15-i],(i-8)*8);for(i=12;i<16;i++)h+=shl(digestBits[15-i],(i-12)*8);c=hexa(h)+hexa(g)+hexa(f)+hexa(e);return c}var state=new array(4);var count=new array(2);count[0]=0;count[1]=0;var buffer=new array(64);var transformBuffer=new array(16);var digestBits=new array(16);var S11=7;var S12=12;var S13=17;var S14=22;var S21=5;var S22=9;var S23=14;var S24=20;var S31=4;var S32=11;var S33=16;var S34=23;var S41=6;var S42=10;var S43=15;var S44=21;var ascii="01234567890123456789012345678901"+" !\"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ"+"[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~"

 
 function onEncrypt() {
 	var txta = $("#encrypt_txtarea");
 	var initial_value = txta.val();
 	var encrypt_method = $(".encrypt_method button.active").text();
 	if (encrypt_method == "rot13") {
 		txta.val(rot13(initial_value));
 	} 
 	else if (encrypt_method == "md5") {
 		txta.val(MD5(initial_value));
 	}
 	txta.select();
 }	
 
	$(function() {
	
		// map href links
		$.each($("div.navbar a"),function() {
			if (/#/.test($(this).attr("href"))) {
				$(this).click(function() {
					setTimeout(function() {
						$(".tabcont").addClass("hidden"); console.log(document.location.hash.replace("#",""));
						$(document.location.hash).removeClass("hidden");
						$("div.navbar li").removeClass("active");
						$("div.navbar a[href=\""+document.location.hash+"\"]").parents("li").addClass("active");
					},100);
				});
			}
		});
		
		if (!document.location.hash) 	$("#XHR").removeClass("hidden");
		else {
			$(document.location.hash).removeClass("hidden");
			$("div.navbar li").removeClass("active");
			$("div.navbar a[href=\""+document.location.hash+"\"]").parents("li").addClass("active");
		}
			
		if (localStorage) {
			for(var k in xhr_fields){
				if (localStorage[xhr_fields[k]]) $("#"+xhr_fields[k]).val(localStorage[xhr_fields[k]]);
				$("#"+xhr_fields[k]).change(function(){
					localStorage[$(this).attr("id")] = $(this).val();
				});
			}
			if (localStorage["meth"]){ 	$("#"+localStorage["meth"]).attr("checked","checked"); 	}
			
			if (localStorage["restart_apache"]) $("#restart_apache .last_done span").text(localStorage["restart_apache"]);
			if (localStorage["restart_socket"]) $("#restart_socket .last_done span").text(localStorage["restart_socket"]);
		}
	});

';

?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>Toolbox</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/x-icon" href="data:image/x-icon;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAuCAYAAABeUotNAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBNYWNpbnRvc2giIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NTQ5ODMwOEQwMTlFMTFFMEExQ0JDNUE0MUMwQjk5OTkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NTQ5ODMwOEUwMTlFMTFFMEExQ0JDNUE0MUMwQjk5OTkiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1NDk4MzA4QjAxOUUxMUUwQTFDQkM1QTQxQzBCOTk5OSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1NDk4MzA4QzAxOUUxMUUwQTFDQkM1QTQxQzBCOTk5OSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PoJnHvoAAARKSURBVHjazJltSFNRGMfnmNYybeqmZc53jUjnLDK0MvchAkVTQakvMYmiCEtDkIpQMCutWPaCfVPq0yKoFekIwsjCD3P0QotY2Zya0cJSCWSf1vOse+1u7eVs91ztGQ/n3rNzz377P+flOVtEak6BaImsGnwXuJpT12y3vn5N8nDEEoC2g58Al/l4bxZcQwIrERAQlevzUtBTJTCXyzVO0pkYGooEcDX4EFP6bBMZGblw9XLnLVAzigRUCEUxxEN+Qu22qKioBV33ud7Kij1TcDtHpKgAoPcJIW1wexfcuRygZYz7tTOtJ/tZSJjIDtKOJTheKFpbwNlVkGdqOLD/PUIqs1WOUDqmqWh6MDU1u3ZaoDCECskoSjXsAe3Ll68TKVmqSdIOoS1++dmpsbezoCiS8vfV0as2BGvz4NFAHZQyz3p/kPmw/rpegQ/BtYxa6OXyhNhgbZxO50ZmEwiiZL6Ms3rghqEVY+hpuFQqXUnYthq8j733tvWZ+TKoh81ClM62kUgiEySB5A/NAofSy7RM2eAJmcduFh7bbka6MpFa6FdJpb9CfETLHQYAWeYLEi03J9tGba/fkJttDeM5bXLGJhf4z0C5gaZ0+0dqin62jSt45gc+LTYm5se++to3tNZR7csRU5sQuWLxtq2jUDhoTCYtyZITjmECc/Z0ywBczvNVVDBItMajh/SZGWkmJh8NmxRnp04oyEK1ytTSfMwMl2Y+e706WHLMF3LQoNfDpZHNV8X/EySOybraKoPxoRsSM61PnKOIa9khcQkqKS4a7Wg7NZyqTFlgII3hpnm8INNSlWPXdRf07ywf4p6/GMnCutIdxWNyefxCdWX5NJu3gI+w49LjxKpIySVdkG3hQiYq5NMDBn0vo5Yvm2dUNPs7Q5EoKuOjpBvygRvSyoRzDbiSeXuSAQua8QcbozJ/iQIZpGJ6EJVMTbHjEQSi52TUW8zyv09ZeR9FeEHiBBn4C3lXvj7XyWfCiYWAxGXmSndHfxolSH/HZV6QKwDyZk9Xb01V+QeETEjOcdJYwnwpquMFea0bICtwhTDQgvQ1mfo4xwQ+kKBktkNE0biTiR9kz6VFyPh1dCG5odeFC4nW1HhEX7NXOEg29Dgem8Lt4PDBA/rWluPmP5BZgkCyoa/mA3nxXBsmtsa4tcJBukOfEB8XHS5kV+ci5CeRwCZWqTY54VAa0osLKVubaQn1ee6L1PDH/hJ1UdntcftEFskD++pqDLduXBl2QyZlWvgqNfvtM/GsnzPcu9OflKiYDtZ4S2GBiSZkSKHHD4Sk1vrk8b3eQLAI+dR4H48Iz5Ya0g3K7PUGgLUjLOaP3j+pbC5UsZCWNYkZZpp/9RCP0VhFuvtizmFbAUW9fWIyreFwY9XMzM94rN+zWzPafb7dxEAaaSsFn0s8mTx2Q4TFnNer3T+HraW2CB/yI6wGc18OpEW0zPZbgAEAIRQsmKTtuwoAAAAASUVORK5CYII=">
<link href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
<link href="http://twitter.github.com/bootstrap/assets/css/bootstrap-responsive.css" rel="stylesheet">
<link href="http://twitter.github.com/bootstrap/assets/css/docs.css" rel="stylesheet">
<link href="http://twitter.github.com/bootstrap/assets/js/google-code-prettify/prettify.css" rel="stylesheet">
<!--[if lt IE 9]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<style>

#xhr_header { margin:0;width:100%;height:auto;background-color: #eee;border:1px solid #ccc;padding:5px;}
#xhr_params_box { height:250px;width:530px;margin:10px;padding:10px;border:1px solid #ddd;overflow: auto; }
#xhr_method_box { }
#xhr_send_button { margin-left:100px; height:35px;width:250px; }
#xhr_res {padding: 10px;margin-bottom:150px;}

#rs_res {	margin: 30px;width:600px;min-height:50px;border : 1px solid #aaa;border-radius: 3px;}
.box {	position:relative;	float : left; width:300px; margin:20px;	border : 1px solid #ccc; margin-top:0; background-color:#ddd;}
.box button { width : 200px; height : 35px; margin : 10px; }
.box .last_done { text-align : center; width:100%; }
.hider { position:absolute;	top:0;left:0;right:0;bottom:0; opacity:0.5; background-color : #ddd; z-index:999099; }

</style>
</head>
<body data-spy="scroll" data-target=".subnav" data-offset="50">

<div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="">Toolbox</a>
          <div class="nav-collapse collapse">
            <ul class="nav">
              <li class=""><a href="#XHR">XHR</a></li>
              <li class=""><a href="#Socket">Socket</a></li>
              <li class=""><a href="#Encryption">Encryption</a></li>
              <li class=""><a href="#Timestamp">Timestamp</a></li>
              <li class=""><a href="#Restart">Restart</a></li>
              <li class="divider-vertical"></li>
              <li class=""><a href="http://benoitzohar.fr/manager-app">About</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

<div class="container">

	<div id="XHR" class="tabcont hidden">
		<div id="xhr_header">
			<div class="input-prepend"><span class="add-on">URL</span><input type="text" id="xhr_url" style="width:500px;" value=""/></div>
			<div id="xhr_params_box">
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key0" /><span class="add-on">Value</span><input type="text" id="xhr_val0" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key1" /><span class="add-on">Value</span><input type="text" id="xhr_val1" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key2" /><span class="add-on">Value</span><input type="text" id="xhr_val2" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key3" /><span class="add-on">Value</span><input type="text" id="xhr_val3" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key4" /><span class="add-on">Value</span><input type="text" id="xhr_val4" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key5" /><span class="add-on">Value</span><input type="text" id="xhr_val5" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key6" /><span class="add-on">Value</span><input type="text" id="xhr_val6" /></div>
				<div class="input-prepend"><span class="add-on">Key</span><input type="text" id="xhr_key7" /><span class="add-on">Value</span><input type="text" id="xhr_val7" /></div>
			</div>
			<div id="method_box">
				<input type="radio" name="method_inp" id="meth_get" value="GET" onchange="if(localStorage) localStorage['meth']='meth_get';" /> GET / 
				<input type="radio" name="method_inp" id="meth_post" value="POST" checked="checked" onchange="if(localStorage) localStorage['meth']='meth_post';"  /> POST 
				<button id="xhr_send_button" onclick="doReqXHR();" class="btn btn-primary">Send request</button></div>
				<button id="xhr_htmlize_button" onclick="htmlize();"  class="btn btn-info" style="float:right;">Parse HTML in response</button>
		</div>
		
		<div id="xhr_res"></div>


	</div>
	<div id="Socket" class="tabcont hidden">

	</div>
	<div id="Encryption" class="tabcont hidden">

		<textarea id="encrypt_txtarea" style="width:500px;height:300px;" ></textarea>
		<div class="btn-group encrypt_method" data-toggle="buttons-radio">
		  <button class="btn active">md5</button>
		  <button class="btn">rot13</button>
		</div>
		<button class="btn btn-primary" onclick="onEncrypt();">Encrypt / Decrypt</button>
		

	</div>
	
	<div id="Timestamp" class="tabcont hidden">
	
		<div class="input-prepend"><span class="add-on">Day</span><input type="text" class="span1" id="ts_day" /></div>
		<div class="input-prepend"><span class="add-on">Month</span><input type="text" class="span1" id="ts_month" /></div>
		<div class="input-prepend"><span class="add-on">Year</span><input type="text" class="span1" id="ts_year" /></div>
		
		<div class="input-prepend"><span class="add-on">Hour</span><input type="text" class="span1" id="ts_hour" /></div>
		<div class="input-prepend"><span class="add-on">Minute</span><input type="text" class="span1" id="ts_minute" /></div>
		<div class="input-prepend"><span class="add-on">Second</span><input type="text" class="span1" id="ts_second" /></div>
		
		<input type="text" id="ts_input" onkeydown="refreshTimestamp();"/>
		<button class="btn" onclick="resetTimestamp();">Now</button>
		

	</div>
	
	<div id="Restart" class="tabcont hidden">
		<?php
		
		$s = array(
			array('Restart Apache','restart_apache'),
			array('Restart Socket Server','restart_socket')
		);
		
		echo '<div id="rs_res"></div>';
		
		foreach($s as $t) {
			echo '<div class="box" id="'.$t[1].'">';
			echo '<center><button class="btn btn-primary" onclick="doReqRS(\''.$t[1].'\');">'.$t[0].'</button></center>';
			echo '<div class="last_done">last restart: <span>-</span></div>';
			echo '</div>';
		} ?>
		
	</div>
	
</div>

<div class="navbar navbar-fixed-bottom">
	<div class="navbar-inner">
		<button class="btn btn-inverse"><i class="icon-cog icon-white"></i></button>
   	</div>
</div>


<div id="loader" style="position:absolute;z-index:99999;top:52px;right:12px;opacity:0.8;display:none;"><img src="http://benoitzohar.fr/loader.gif" style="height:28px;" /></div>

<script src="http://twitter.github.com/bootstrap/assets/js/jquery.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/google-code-prettify/prettify.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-transition.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-alert.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-modal.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-dropdown.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-scrollspy.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-tab.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-tooltip.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-popover.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-button.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-collapse.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-carousel.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/bootstrap-typeahead.js"></script>
<script src="http://twitter.github.com/bootstrap/assets/js/application.js"></script>
<script><?php echo $js_code; ?></script>

</body>
</html>