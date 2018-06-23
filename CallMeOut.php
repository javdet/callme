<?php
/**
* CallMe calls handler for output calls   
* @author Автор: ViStep.RU
* @version 1.0
* @copyright: ViStep.RU (admin@vistep.ru)
*/

require __DIR__ . '/vendor/autoload.php';

$helper = new HelperFuncs();
$callami = new CallAMI();

//настройки
$tech = $helper->getConfig('tech');
$authToken = $helper->getConfig('authToken');
$context = $helper->getConfig('context');
$intPrefix = $helper->getConfig('intPrefix');

//данные в запросе
$request = $_REQUEST;

//проверяем не пустой ли request
if(!empty($request)){
    //логируем запрос
    $helper->writeToLog($request,'request');
    if (!is_null($request['action'])){ //есть ли action
        switch ($request['action']) {
            case 'sendcall2b24': //отправляем инфу о звонке в битрикс
                $helper->writeToLog($request,'sendcall2b24 action');
                if (is_null($request['call_id']) || is_null($request['FullFname']) || is_null($request['CallIntNum']) || is_null($request['CallDuration']) || is_null($request['CallDisposition'])){
                    $helper->writeToLog($resultFromB24,'sendcall2b24 error in params');
                    exit('error in params');
                }
		// Если звонок с софтфона
		if (empty($request['call_id'])) {
		    $call_file = "tmp/".$request['CallIntNum'];
		    $f_call = fopen($call_file, "r");
		    $request['call_id'] = fgets($f_call);
		    fclose($f_call);
		    $helper->writeToLog($request['call_id'],'sendcall2b24 set call_id');
		}
		// Если входящий звонок
		if (strlen($request['call_id']) < 20) {
		    $call_file = "tmp/in_".$request['CallIntNum'];
		    $f_call = fopen($call_file, "r");
		    $request['call_id'] = fgets($f_call);
		    fclose($f_call);
		    $helper->writeToLog($request['call_id'],'sendcall2b24 set in call_id');
		}
                $resultFromB24 = $helper->uploadRecordedFile($request['call_id'],$request['FullFname'],$request['CallIntNum'],$request['CallDuration'],$request['CallDisposition']); 
                //логируем, что нам рассказал битрикс в ответ на наш реквест
                $helper->writeToLog($resultFromB24,'sendcall2b24 upload call status');
		//$resultFromB24 = $helper->attachRecordedFile($request['call_id'],$request['FullFname']);
		//$helper->writeToLog($resultFromB24,'sendcall2b24 attach file');
            break;
	    case 'sendoutcall2b24': //отправляем инфу об исходящем инициираванным с софтфона
        	$helper->writeToLog($request,'sendoutcall2b24 action');
		if (is_null($request['CallIntNum']) || is_null($request['CalledNumber']) ){
                    $helper->writeToLog('is_null', 'sendoutcall2b24 error in params');
                    exit('error in params');
                }
		$call_id = $helper->runOutputCall($request['CalledNumber'], $request['CallIntNum']);
		$call_file = "tmp/".$request['CallIntNum'];
		$f_call = fopen($call_file, "w");
		fwrite($f_call, $call_id);
		fclose($f_call);
		$helper->showInputCall($request['CallIntNum'], $call_id);
		$helper->writeToLog($resultFromB24,'sendoutcall2b24 upload call status');
	    break;
	    case 'sendoutfinish2b24': //отправляем инфу об завершенном исходящем инициираванным с софтфона
        	$helper->writeToLog($request,'sendoutfinish2b24 action');
		if (is_null($request['CallIntNum']) || is_null($request['FullFname']) || is_null($request['CallDuration']) || is_null($request['CallDisposition'])){
                    $helper->writeToLog('is_null', 'sendoutfinish2b24 error in params');
                    exit('error in params');
                }
		$call_file = "tmp/".$request['CallIntNum'];
		$f_call = fopen($call_file, "r");
		$call_id = fgets($f_call);
		fclose($f_call);
		$helper->writeToLog($call_id,'sendoutfinish2b24 set call_id');
		$resultFromB24 = $helper->uploadRecordedFile($call_id, $request['FullFname'], $request['CallIntNum'], $request['CallDuration'], $request['CallDisposition']);
		$helper->writeToLog($resultFromB24,'sendoutfinish2b24 call finish');
	    break;
            default:
                $helper->writeToLog($request['event'],'Go fuck yourself');
                break;
            }
    } else {
        //проверяем авторизацию по токену
        if ($request['auth']['application_token'] === $authToken) {
            $intNum = $helper->getIntNumByUSER_ID($request['data']['USER_ID']);
            $intNum = $intPrefix.$intNum;
            $helper->writeToLog($intNum,'intnum');
            $CalledNumber = $request['data']['PHONE_NUMBER_INTERNATIONAL'];
            $helper->writeToLog($CalledNumber,'CalledNumber');
            $CallID = $request['data']['CALL_ID'];
            $helper->writeToLog($CallID,'CALL_ID');
            //дергаем ивен, переданный из битрикса
            switch ($request['event']) {
                case 'ONEXTERNALCALLSTART': //внешний звонок
                    $helper->writeToLog($request['event'],'ONEXTERNALCALLSTART');
                    //отправляем на астериск
                    $response = $callami->OriginateCall($intNum, $CalledNumber, $tech, $CallID, $context);
                    $helper->writeToLog($response,'PAMI response');
                    break; 
                default:
                    $helper->writeToLog($request['event'],'Go fuck yourself');
                    break;
                }
            } else $helper->writeToLog($request,'Not authorized!!!');
    }
} else exit('error in params'); //если пустой request то отдаем ошибку в параметрах