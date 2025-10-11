<?php

# Computenames verkürzt: medma1176, ... 
$rere=array("1176","1173","1174","1180","1181","1181","1178","1184","1182","1185","1177","1187","1183","1191","1189","1188","1190","1186","1179","1192","1175");

$cls = array();

# Parallel Abfrage
for($i=0;$i<count($rere);$i++){

  $pid = pcntl_fork();
  if(!$pid){
        # Child
        $read = "";
        $hostname=sprintf("medma%s.medma.ad.uni-heidelberg.de",$rere[$i]);
        $ip = gethostbyname($hostname);
        $pf=popen("ping -c 1 -W 1 $ip","r");
        if($pf){
                $read = trim(fread($pf, 255));
                pclose($pf);
        }

        if(strstr($read,"ttl=")){
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
                /*
                $hn=gethostbyaddr($ip);
                if( ! ($hn == $ip)){
                        $r = 5;
                }
                */
        }

        $w = sprintf("{\"name\":\"medma%s\",\"css\":\"%s\"}",$rere[$i],$r);

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

copy("/tmp/buserpcs.tmp","/home/uniadmin/html/local/query_busers2/buserpcs.json");

############
/*
        1: Einfach Anmeldung
        2: Connection Fail
        3: Grün - keine Anmeldung
        4: Kein Ping
        5: DNS Error
        6: Doppelanmeldung

        STatistik für alle realen Rechner erstellen...
*/
$stat=array(0,0,0,0,0,0,0);
$all=0;
foreach($bel as $val){
        $x=json_decode($val);
        if(isset($x->css)){
                if(in_array($x->name,$rere)){
                        $j=$x->css;
                        $stat[$j]++;
                        $all++;
                }
        }
}

$belegt=$stat[1]+$stat[6];
$frei=$stat[3]+$stat[4];

$alles=$all;

exit(0);

?>
