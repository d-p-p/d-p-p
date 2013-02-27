var dppPingTimeout = 4000; // time in milliseconds to wait for a response from the ping
var pingResults;
var ping_average = 0;
var ping_stddev = 0;

function DppPingCollector(ip, numofpings, resultarray) {
	
	// clear the array
	eval(resultarray+"=[];");
	
	// set timer for performing each ping
	for (var i=0; i<numofpings; i++) {
		setTimeout("DppFirePing(\""+ip+"\",function(result){"+resultarray+"["+i+"]=result;});",i*dppPingTimeout);
	}
	
	setTimeout("subsequentRequest(average(pingResults),stddev(pingResults,average(pingResults)),pingResults.length,pingResults.join(','));",numofpings*dppPingTimeout);
}

function DppFirePing(ip, callback) {
	
	// create a gif url we suppose doesn't exist
    var imgUrl = "http://"+ip+"/"+(new Date()).getTime()+".gif";
    var img = new Image();
	var startTime = (new Date()).getTime();

    // calculate the round trip time for an error response
   img.onerror = function() {
        var endTime = (new Date()).getTime();
		var pingTime = endTime - startTime;
		if (pingTime < dppPingTimeout)
        	callback(pingTime);
    };

	// start ping
    img.src = imgUrl;
		
}



function doPing(ip,num_pings) {
	DppPingCollector(ip,num_pings,"pingResults");
//	setTimeout("alert(\"IP: "+ip+"\nResults: \"+pingResults.join(\",\")+\" Average: \"+ping_average+\" StdDev: \"+ping_stddev);",num_pings*dppPingTimeout);
}

function average(list)
{	var total = 0;
	var count = 0;
	for (var i = 0; i < list.length; i++)
	{	if (list[i] > 0)
		{	total = total + list[i];
			count++;
	}	}
	if (total == 0 || count == 0) { return -1; }
	else { return Math.round(total / count); }
}

function stddev(list,avg)
{	var total = 0;
	var count = 0;
	for (var i = 0; i < list.length; i++)
	{	if (list[i] > 0) { total = total + ((list[i]-avg)*(list[i]-avg)); count++; }
	}
	if (count > 1) { return Math.round(Math.sqrt((total/(count-1)))); }
	else { return -1; }
}

function random_string(strlen)
{	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var randomstring = '';
	for (i = 0; i < strlen; i++)
	{	var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}
	return randomstring;
}


var comm = {};
comm =
{	server:null, // server address
	oncomplete:null, // function to call on complete
	sessid:null,
	timeout:2500, // default time in milliseconds to wait for a response
	spacing:500, // default time between the first and second request
	img:null,
	timer:null,
	p1:null,p2:null,p3:null,p4:null,
	data:"",
	start:function(num) {
		comm.img = new Image();
		// basic request
		var url = comm.server + "?i="+comm.sessid + '&key='+Math.random();
		if (num % 2 == 1) { // first request
			url = url + comm.data;
			// set onload function
			comm.img.onload = function()
			{	// cancel the timeout
				clearTimeout(comm.timer);
				comm.timer = null;
				// get the width and height
				comm.p1 = comm.img.width-1;
				comm.p2 = comm.img.height-1;
				// prepare for second request
				comm.img = null;
				comm.timer = setTimeout("comm.start("+(num+1)+")",comm.spacing);
			};
		}
		else
		{	comm.img.onload = function()
			{	// cancel the timeout
				clearTimeout(comm.timer);
				comm.timer = null;
				// get the width and height
				comm.p3 = comm.img.width-1;
				comm.p4 = comm.img.height-1;
				// finish the request
				comm.img = null;
				comm.oncomplete();
			};
		}
		comm.timer = setTimeout("comm.timedout()",comm.timeout);
		comm.img.src = url;
	},
	timedout:function() {
	clearTimeout(comm.timer);
	comm.timer = null;		
	}
};

comm.server = "http://d-p-p.org/x.gif";
comm.oncomplete = handleResponse;
var target_ip = '';

function handleResponse()
{	target_ip = comm.p1+"."+comm.p2+"."+comm.p3+"."+comm.p4;	
	if ( (comm.p1 < 0) || (comm.p2 < 0) || (comm.p3 < 0) || (comm.p4 < 0) ) { var t = setTimeout("firstRequest();",1000); }
	else if (target_ip != '0.0.0.0') { var t = setTimeout("start_ping('"+target_ip+"');",1000); }
}

function firstRequest()
{	if (dppProt != "https://")
	{	comm.sessid = random_string(24);
		comm.data = "&g=1.000_1.000"
		comm.start(1);
	}
}

function subsequentRequest(ping_avg,ping_dev,ping_cnt,all_pings)
{	comm.data = "&p=" + target_ip + "_" + ping_avg + "_" + ping_dev + "_" + ping_cnt + "_" + all_pings;
	comm.start(3);
}

function start_ping(ip) { doPing(ip,5); }

var t = setTimeout("firstRequest();",20000);