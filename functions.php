<?php
function  getFeriados($ano = null)
	{
	if ($ano === null)
	{
	$ano = intval(date('Y'));
	}
	$pascoa     = easter_date($ano); 
	$dia_pascoa = date('j', $pascoa);
	$mes_pascoa = date('n', $pascoa);
	$ano_pascoa = date('Y', $pascoa);
	//$feriados = array('14/03', '15/03', '30/04', '20/05');
	//mes dia //ano
	$feriados = array(                      // Tatas Fixas dos feriados Nacionais Basileiras
	mktime(0, 0, 0, 1,  1,   $ano), // Confraternização Universal 
	mktime(0, 0, 0, 4,  21,  $ano), // Tiradentes 
	mktime(0, 0, 0, 5,  1,   $ano), // Dia do Trabalhador 
	mktime(0, 0, 0, 9,  7,   $ano), // Dia da Independência 
	mktime(0, 0, 0, 10,  12, $ano), // N. S. Aparecida 
	mktime(0, 0, 0, 11,  2,  $ano), // Todos os santos 
	mktime(0, 0, 0, 11, 15,  $ano), // Proclamação da republica 
	mktime(0, 0, 0, 12, 25,  $ano), // Natal
	mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 48,  $ano_pascoa),//2ºfeira Carnaval
	mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 47,  $ano_pascoa),//3ºfeira Carnaval	
	mktime(0, 0, 0, $mes_pascoa, $dia_pascoa - 2 ,  $ano_pascoa),//6ºfeira Santa  
	mktime(0, 0, 0, $mes_pascoa, $dia_pascoa     ,  $ano_pascoa),//Pascoa
	mktime(0, 0, 0, $mes_pascoa, $dia_pascoa + 60,  $ano_pascoa),//Corpus Cirist
	);
	sort($feriados);
	return $feriados;
	} // Retorna todos os feriados do ano

 function isFeriado($data = null){
	$data = ($data == null) ? date('Y-m-d H:i:s') : $data;
	$fer = false;
	$data = date_create($data);
	$convertDate = mktime (0, 0, 0, $data->format('m') , $data->format('d'), $data->format('Y'));
	if(in_array($convertDate, getFeriados($data->format('Y')))){
	$fer = true;	
	}
	return $fer;
	} // Verifica se a data X é feriado ou não


function isFinalSemana($data = ''){
	$fin = false;
	$data = date_create($data);
	$dt = (date('N', strtotime($data->format('Y-m-d H:i:s'))));
	if( $dt >= 6){ //Segunda a Sexta, Sabado = 6 , Domingo = 7
	$fin = true;	// Final de Semana AEE
	}
	return $fin;
	}

	
function isHoraUtil($hora_atual, $horario_ini, $horario_fim){
	$ihu = false;
	$hora_atual = strtotime($hora_atual);
	$horario_ini = strtotime($horario_ini);
	$horario_fim = strtotime($horario_fim);
	if($hora_atual >= $horario_ini && $hora_atual <= $horario_fim){
		$ihu = true;
	}
	return $ihu;
}


function calculaDataUtil($data){
	$dat = date_create($data);
	if(isFeriado($data) || isFinalSemana($data)){
		$dat->add(new DateInterval('P1D'));
		return calculaDataUtil($dat->format('Y-m-d H:i:s'));
	}
	return $dat->format('Y-m-d H:i:s');
}

function validaHoraPlanejada($horaplanejada, $horaentradaexpediente, $horasaidaexpediente){
	$retorno = $horaplanejada;
	$dateNow = date('Y-m-d');
	$dateNowComplete = $dateNow.' '.date('H:i:s');
	$objhoraplanejada = date_create($horaplanejada);
	$objhoraentradaexp = date_create($dateNow.' '.$horaentradaexpediente);
	$objhorasaidaexp = date_create($dateNow.' '.$horasaidaexpediente);
	$timeHoraPlanejada =  strtotime($dateNow.' '.$objhoraplanejada->format('H:i:s'));
	$timeHoraEntrada = strtotime($objhoraentradaexp->format('Y-m-d H:i:s'));
	$timeHoraSaida = strtotime($objhorasaidaexp->format('Y-m-d H:i:s'));
	$dataplanejada = $objhoraplanejada->format('Y-m-d');
	if($timeHoraPlanejada < $timeHoraEntrada  ){
		$retorno = $dataplanejada.' '.$objhoraentradaexp->format('H:i:s');
	}
	else if($timeHoraPlanejada > $timeHoraSaida){
		$retorno = $dataplanejada.' '.$objhorasaidaexp->format('H:i:s');
	}
	return $retorno;
}

function calculaDiferencaDeHoras($dataplanejada, $horasaida){
	$dateNow = date('Y-m-d');
	$objhoraplanejad = date_create($dataplanejada);
	$objhoraplanejada = date_create($dateNow.' '.$objhoraplanejad->format('H:i:s'));
	$objhorasaida = date_create($dateNow.' '.$horasaida);
	$objdiferenca = date_diff($objhoraplanejada, $objhorasaida);
	return $objdiferenca->h;
};


function verificaFeriadoFimSemana($dt_inic,$dt_fim){
	$ct = 0;
	$objdata = date_create($dt_inic);
	$timeHorasInicial = (int)(strtotime($dt_inic));
	$timeHorasFinal =  (int)(strtotime($dt_fim));
	while($timeHorasInicial <= $timeHorasFinal){
		$dt = $objdata->format('Y-m-d H:i:s');
		if(!isFinalSemana($dt) && !isFeriado($dt)){
			$ct++;
		}	
		$objdata->add(new DateInterval('P1D'));
		$timeHorasInicial = strtotime($objdata->format('Y-m-d H:i:s'));
	}
	return $ct;
}// Retorna a quatidade de dia util

	
function verificaDiaPorDia($dtx){
	$dt_inic = @$dtx[0];
	$dt_fim =  @$dtx[1];
	$horatrabalhoinicial = @$dtx[2];
	$horatrabalhofinal = @$dtx[3];
	$horatrabalhofinal2 = $horatrabalhofinal;
	$dt_inic = validaHoraPlanejada($dt_inic, $horatrabalhoinicial, $horatrabalhofinal);
	$data_inicial_contagem = $dt_inic;
	$dt_fim = validaHoraPlanejada($dt_fim, $horatrabalhoinicial, $horatrabalhofinal);
	$difs = verificaFeriadoFimSemana($dt_inic,$dt_fim);
	$dataDiferencaPlanejada = (date_diff(date_create($dt_inic), date_create($dt_fim)))->d;
	$dataDiferencaPlanejadaSemFeriados = $dataDiferencaPlanejada;
	$diferencasaidainicial =  calculaDiferencaDeHoras($dt_inic, $horatrabalhofinal); // Diferenca de horas do primeiro dia
	$diferencasaidainicial2 =  calculaDiferencaDeHoras($dt_inic, $horatrabalhoinicial); 
	$diferencasaidafinal = calculaDiferencaDeHoras($dt_fim, $horatrabalhoinicial); // Diferenca  de horas do dia final
	$diferencasaidafinal2 = calculaDiferencaDeHoras($dt_fim, date_create($dt_inic)->format('H:i:s')); // Diferenca  de horas do dia final e inicial
	$data_fim = date_create($dt_fim);
	$horatrabalhoinicial = date('Y-m-d').' '.$horatrabalhoinicial;
	$horatrabalhofinal = date('Y-m-d').' '.$horatrabalhofinal;
	$horatrabalhodiafinal = date('Y-m-d').' '.$data_fim->format('H:i:s');
	$objhoraini = date_create($horatrabalhoinicial);
	$objhorafim = date_create($horatrabalhofinal);
	$objhoraplanejada = date_create($dt_fim);
	$datainicialcont = date_create($data_inicial_contagem);
	$contagemDias = 0;
	$timeHorasInicial = (int)(strtotime($data_inicial_contagem));
	$timeHorasFinal =  (int)(strtotime($dt_fim));
	$htrab = date_diff(date_create($horatrabalhofinal),  date_create($horatrabalhoinicial)  );
	$horastrabdia = $htrab->h ; //Calcula a quantidade  de horas trabalhadas por dia
	$horas_uteis = 0;
	
	while((($timeHorasInicial < $timeHorasFinal) === true)){
		$data_verify = $datainicialcont->format('Y-m-d H:i:s');
		$data_verify2 = $datainicialcont->format('Y-m-d');
		
		if(!isFinalSemana($data_verify) && !isFeriado($data_verify)){
			$verificaUltimoDia = verificaUltimoDia($datainicialcont, $objhoraplanejada);
			
		if($contagemDias == 0 && $verificaUltimoDia == 0){
			$horas_uteis += $horastrabdia - $diferencasaidainicial2; // Se for o primeiro dia vai somar a diferenca de horas do momento atual até o horario de saida
			if($dataDiferencaPlanejadaSemFeriados == 0){
			$horas_uteis += $diferencasaidafinal;
			}

			if($difs == 1 ){
			$horas_uteis += $diferencasaidainicial;
			}
		}
		
		else if($verificaUltimoDia == 1){			
			if($dataDiferencaPlanejadaSemFeriados > 1){	
			//echo '3 =>Horas Uteis >> '.$horas_uteis.' Adicionado '.$diferencasaidafinal.' horas Dia >> '.$data_verify2.'<br>';
			$horas_uteis += $diferencasaidafinal; // Se for o ultimo dia vai somar as horas de diferenca, caso de 1 unico dia
			}else{
			$horas_uteis += $diferencasaidafinal2; // Caso de 1 unico dia
			//echo '3 =>Horas Uteis >> '.$horas_uteis.' Diferenca >> '.$diferencasaidafinal.' horas Dia >> '.$data_verify2.'<br>';
			return $horas_uteis;
			}

		}
		else if($verificaUltimoDia == 2){
			return $horas_uteis;
		}else{	
			$horas_uteis += $horastrabdia;	
		} // Se for um dia normal
		} // Se não  for feriado ou final de semana, contabiliza as horas uteis
		
		else{
			//Feriaddoooooooooooooooooooooooooooo
		}	
		$datainicialcont->add(new DateInterval('P1D')); //Adiciona 1 Dia e recalcula
		$timeHorasInicial =  strtotime($datainicialcont->format('Y-m-d H:i:s')) ;
		$contagemDias++;
	}// Faz a contagem de tempo 
	
	return $horas_uteis;
}// Função que faz o calculo das horas uteis em PHP


function verificaUltimoDia($datainicialcont, $objhoraplanejada){
	$val = 0; //  0 =>  Dia Normal
	$dataatualano = $datainicialcont->format('Y');
	$dataatualmes = $datainicialcont->format('m');
	$dataatualdia = $datainicialcont->format('d');
	$dataplanejadaano = $objhoraplanejada->format('Y');
	$dataplanejadames = $objhoraplanejada->format('m');
	$dataplanejadadia = $objhoraplanejada->format('d');
	
	if($dataatualano == $dataplanejadaano && $dataatualmes == $dataplanejadames){
		if($dataatualdia == $dataplanejadadia){
			$val = 1; // Mesmo dia
		}
		
		if($dataatualdia  > $dataplanejadadia){
			$val = 2;
		}
	}// Se for no mesmo ano e mês	
	return $val;
}

$dt_inic = '2018-12-31 17:00:00'; // Mesmo que seja hora menor ou maior, só será contado a partir da hora de entrada/saida
$dt_fim = '2019-01-03 08:00:00'; // Mesmo que seja menor ou maior, só será contado a partir da hora de entrada/saida	
$dtx[0] = 	$dt_inic; //Data Inicio
$dtx[1] =   $dt_fim; // Data Fim
$dtx[2] =   '8:00'; // Hora de Inicio da Jornada de trabalho /Expediente
$dtx[3] =   '18:00'; // Hora Fim da Jornada de trabalho / Expediente
// $dtx[4] = array('29/08');
$horas_uteis = verificaDiaPorDia($dtx); // Retorna um array com horas uteis, dias_uteis, finais_semana e ultimo_dia util
echo "Horas Uteis >> ".$horas_uteis;
?>
