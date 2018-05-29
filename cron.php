#!/usr/bin/php -q 
<?php

$cls = array();
for($i=20;$i<70;$i++){

  $pid = pcntl_fork();
  if(!$pid){
	# Child
	$ip="129.206.166.$i";
	$pf=popen("ping -c 1 -W 1 $ip","r");
	if($pf){
		$read = trim(fread($pf, 255)); 
		pclose($pf);
	}
	if(strstr($read,":")){
		$r=0;
		$pf=popen("PASSWD=geheim /usr/bin/net rpc registry enumerate 'HKEY_USERS' -S $ip  -U ad\\\\Administrator 2>&1 ","r");
		if($pf){
                        $cl=0;
                        while(!feof($pf)){
                                $read = trim(fgets($pf, 80));
                                if(strstr($read,"_Classes")){ $cl++; $r=1;      }
                                if(strstr($read,"Connection failed")){ $r = 2; break; }
                        }
                        if($cl>1) $r=6;
                        if($r==0) $r=3;
                        pclose($pf);
                }
		
	} else {
		$r = 4;
		$hn=gethostbyaddr($ip);
		if( ! ($hn == $ip)){
			$r = 5;
		}
	}

	$w = sprintf("{\"ip\":\"%s\",\"css\":\"%s\"}",$i,$r);

	$shared_id = shmop_open(getmypid(),"c",0644,strlen($w));
	shmop_write($shared_id,$w,0);
	shmop_close($shared_id);

        exit($i);	

    } else { 
		# Parent
		$cls[]=$pid;
    }	

}

$bel=array();

$je=time();
foreach($cls as $val){
	// Warten bis die Kind-Prozesse beendet sind 
	while (pcntl_waitpid($val, $status) != -1) {
		if( time()-$je>8) posix_kill($val,0); // nach 8 Sek gnadenlos killen 
	}

	// Ergebis aus dem Shared-Memory-Block holen
	$shared_id = shmop_open($val,"a",0,0);
       	$share_data = shmop_read($shared_id,0,shmop_size($shared_id));
	shmop_delete($shared_id);
	shmop_close($shared_id);

	$bel[]=$share_data;
}

// Ergenis im JSON-Format schreiben. 
$fp=fopen("/tmp/buserpcs.tmp","w");
fputs($fp,"[");
foreach($bel as $val){ fputs($fp,"$val,");}
fputs($fp,"$val]");
fclose($fp);

copy("/tmp/buserpcs.tmp","/home/uniadmin/html/local/query_busers/buserpcs.json");

exit(0);

?>
