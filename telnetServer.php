#!/usr/local/bin/php -q
<?php

//Menu functionality
function printMenu(){
	
	return "\nOUTPUT: \n"
		. "1. Type: help    --- for this Menu.\n"
		. "2. Type: indir   --- listing current working directory on serverside\n";
}


//Formatting output
function setOutput($out){

	return "\nOUTPUT: \n"
	. "===================================\n"
	. "$out\n"
	. "===================================\n"
	. "\nCLIENT:>>>";	
}


error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();


$recv_port = 2048;
$address = $argv[1];
$port =  intval($argv[2]);

//Testing if Cmdline cariables are set
//if(!isset($argv[1])){

//	echo "Argument vector 1 set";

//}


$output = '';

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
}

if (socket_bind($sock, $address, $port) === false) {
    echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}

if (socket_listen($sock, 5) === false) {
    echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
}



do {
    if (($msgsock = socket_accept($sock)) === false) {
        echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
        break;
    }

    

    /* Send instructions. */
    $msg = "\nWelcome to the PHP Telnet Server. \n" .
        	"To quit, type 'quit'. To shut down the server type 'shutdown'.\n"
		. "\nCLIENT:>>> \n";

    
	socket_write($msgsock, $msg, strlen($msg));
	

    do {
        if (false === ($buf = socket_read($msgsock, $recv_port, PHP_NORMAL_READ))) {
            echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
            break 2;
        }
        if (!$buf = trim($buf)) {
            continue;
        }
        if ($buf == 'quit') {
            break;
        }
        if ($buf == 'shutdown') {
            socket_close($msgsock);
            break 2;
        }
	if($buf == 'help'){

		$output = printMenu();
			
	}
	if($buf == 'indir'){
		
		$output = shell_exec('ls');
		
	}

	if ($output){
		$output = setOutput($output);
	}


        $talkback = "\nPHP SERVER RECEIVED: '$buf'.\nCLIENT:>>>\n";
        socket_write($msgsock, $talkback, strlen($talkback));
	socket_write($msgsock, $output, strlen($output));	

	
	echo "\nSERVER RECEIVED: \t$buf\t\t" . " from $argv[1]:$recv_port\n";

	
	
	//reset output
	$output = "";


	//message on serverside on startup
	echo "\nSERVER LISTENING: \n";
	
    } while (true);
    socket_close($msgsock);
} while (true);

socket_close($sock);
?>

